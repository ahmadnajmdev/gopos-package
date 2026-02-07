<?php

namespace Gopos\Filament\Pages;

use DB;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Gopos\Filament\Clusters\Sales\Resources\Customers\CustomerResource;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;
use Gopos\Models\Category;
use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Gopos\Models\PosSession;
use Gopos\Models\Product;
use Gopos\Models\Sale;
use Gopos\Models\SaleItem;
use Gopos\Services\LoyaltyService;
use Gopos\Services\POSSessionService;
use Gopos\Services\ReceiptPrinterService;
use Gopos\Services\SplitPaymentService;
use Livewire\WithPagination;

class Pos extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('POS');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sales');
    }

    public $formData;

    public $selectedCategoryId;

    public $search;

    public $totalAmount = 0;

    public $subTotal = 0;

    public $discount = 0;

    public $selectedCurrency;

    public $exchangeRate = 1;

    public function getOrderColumnOptions(): array
    {
        return [
            'name' => __('Name'),
            'price' => __('Price'),
            'stock' => __('Stock'),
            'created_at' => __('Created at'),
        ];
    }

    public function getOrderDirectionOptions(): array
    {
        $options = [
            'price' => [
                'asc' => __('Low to High'),
                'desc' => __('High to Low'),
            ],
            'name' => [
                'asc' => __('A to Z'),
                'desc' => __('Z to A'),
            ],
            'stock' => [
                'asc' => __('Low to High'),
                'desc' => __('High to Low'),
            ],
            'created_at' => [
                'asc' => __('Oldest to Newest'),
                'desc' => __('Newest to Oldest'),
            ],
        ];

        return $options[$this->orderColumn] ?? [
            'asc' => __('Ascending'),
            'desc' => __('Descending'),
        ];
    }

    public $orderColumn = 'id';

    public $orderDirection = 'desc';

    public $paidAmount = 0;

    public $showDiscountModal = false;

    public $showPaymentModal = false;

    public $discountAmount = 0;

    public $discountType = 'fixed';

    public $paymentMethod = 'cash';

    public $paymentAmount = 0;

    public $barcodeInput = '';

    public ?PosSession $currentSession = null;

    public array $splitPayments = [];

    public bool $useSplitPayment = false;

    public bool $showSuccessModal = false;

    public ?Sale $lastSale = null;

    public float $changeAmount = 0;

    public function getViewData(): array
    {
        return [
            'products' => Product::query()
                ->when($this->selectedCategoryId, function ($query) {
                    $query->where('category_id', $this->selectedCategoryId);
                })
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy($this->orderColumn, $this->orderDirection)
                ->paginate(9),
            'categories' => Category::all(),
            'currencies' => Currency::all(),
            'orderColumnOptions' => $this->getOrderColumnOptions(),
            'orderDirectionOptions' => $this->getOrderDirectionOptions(),
        ];
    }

    protected string $view = 'gopos::filament.pages.pos';

    public function mount()
    {
        // Set default currency to base currency
        $baseCurrency = Currency::getBaseCurrency();
        $this->selectedCurrency = $baseCurrency ? $baseCurrency->id : null;
        $this->exchangeRate = $baseCurrency ? $baseCurrency->exchange_rate : 1;

        // Load current POS session
        $this->loadCurrentSession();

        $this->form->fill([
            'currency_id' => $this->selectedCurrency,
            'exchange_rate' => $this->exchangeRate,
            'sale_date' => now(),
            'discount' => 0,
        ]);
    }

    protected function loadCurrentSession(): void
    {
        $sessionService = app(POSSessionService::class);
        $this->currentSession = $sessionService->getCurrentSession();
    }

    public function hasOpenSession(): bool
    {
        return $this->currentSession !== null;
    }

    public $cart = [];

    public function getTitle(): string
    {
        return '';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('sale_number')
                    ->default(Sale::generateSaleNumber()),
                DatePicker::make('sale_date')
                    ->default(now())
                    ->native(false)
                    ->required(),
                Select::make('customer_id')
                    ->label(__('Customer'))
                    ->placeholder(__('Walk-in Customer'))
                    ->options(
                        Customer::all()->mapWithKeys(function ($customer) {
                            return [
                                $customer->id => $customer->name.' ('.$customer->phone.')',
                            ];
                        })
                    )
                    ->createOptionForm(fn (Schema $schema) => CustomerResource::form($schema))
                    ->createOptionUsing(function (array $data): int {
                        return Customer::create($data)->getKey();
                    }),
                Select::make('currency_id')
                    ->label(__('Currency'))
                    ->options(function () {
                        return Currency::all()->mapWithKeys(function ($currency) {
                            return [$currency->id => $currency->name.' ('.$currency->symbol.')'];
                        });
                    })
                    ->default(function () {
                        $baseCurrency = Currency::getBaseCurrency();

                        return $baseCurrency?->id;
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $currency = Currency::find($state);
                            $this->selectedCurrency = $state;
                            $this->exchangeRate = $currency ? $currency->exchange_rate : 1;
                            $this->formData['exchange_rate'] = $this->exchangeRate;
                            $set('exchange_rate', $this->exchangeRate);
                            $this->calculateFormData();
                            $this->updatedSelectedCurrency($state);
                        }
                    })
                    ->required(),
                Hidden::make('exchange_rate')
                    ->default(function () {
                        $baseCurrency = Currency::getBaseCurrency();

                        return $baseCurrency ? $baseCurrency->exchange_rate : 1;
                    })
                    ->live(),
                Hidden::make('discount')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->calculateFormData();
                    }),
                Hidden::make('sub_total')
                    ->default(0)
                    ->live(),
                Hidden::make('total_amount')
                    ->default(0)
                    ->live(),
                Hidden::make('amount_in_base_currency')
                    ->default(0)
                    ->live(),
                Hidden::make('paid_amount')
                    ->default(0)
                    ->live(),
            ])->statePath('formData');
    }

    public function addToCart($productId)
    {
        $product = Product::query()
            ->where('id', $productId)->first();

        $cartQuantity = 0;
        foreach ($this->cart as $item) {
            if ($item['product_id'] == $productId) {
                $cartQuantity = $item['stock'];
                break;
            }
        }

        if ($product->stock <= $cartQuantity) {
            Notification::make()
                ->title(__('Not enough stock'))
                ->body(__('You have already added all available stock for this product.'))
                ->danger()
                ->send();

            return;
        }
        if ($product->stock <= 0) {
            Notification::make()
                ->title(__('Out of stock'))
                ->danger()
                ->send();

            return;
        }

        foreach ($this->cart as $key => $item) {
            if ($item['product_id'] == $productId) {
                $this->cart[$key]['stock'] += 1;
                $this->calculateFormData();

                return;
            }
        }

        // Convert product price to selected currency
        $currency = Currency::find($this->selectedCurrency);
        $baseCurrency = Currency::getBaseCurrency();
        $convertedPrice = $product->price;

        if ($currency && $baseCurrency && $currency->id !== $baseCurrency->id) {
            // Convert from base currency to selected currency
            $convertedPrice = $product->price * $currency->exchange_rate / $baseCurrency->exchange_rate;
        }

        $this->cart[] = [
            'image' => $product->image,
            'name' => $product->name,
            'product_id' => $productId,
            'stock' => 1,
            'unit' => $product->unit->abbreviation,
            'price' => $convertedPrice,
            'original_price' => $product->price, // Keep original price for reference
        ];
        $this->calculateFormData();
    }

    public function addToCartByBarcode()
    {
        $barcode = trim((string) $this->barcodeInput);
        if ($barcode === '') {
            return;
        }

        $product = Product::query()->where('barcode', $barcode)->first();

        if (! $product) {
            Notification::make()
                ->title(__('Product not found'))
                ->body(__('Barcode: ').$barcode)
                ->danger()
                ->send();
            $this->barcodeInput = '';

            return;
        }

        $this->addToCart($product->id);

        Notification::make()
            ->title(__('Added to cart'))
            ->body($product->name)
            ->success()
            ->send();

        $this->barcodeInput = '';
    }

    public function updatedCart()
    {
        $this->calculateFormData();
    }

    public function calculateFormData()
    {
        $subTotal = 0;
        $total = 0;
        $discount = 0;

        foreach ($this->cart as $item) {
            $subTotal += (float) $item['price'] * (float) $item['stock'];
            $total += (float) $item['price'] * (float) $item['stock'];
        }

        if (isset($this->formData['discount']) && $this->formData['discount']) {
            $discount = $this->formData['discount'];
            $total -= $discount;
        }

        // Calculate amount in base currency
        $currency = Currency::find($this->selectedCurrency);
        $baseCurrency = Currency::getBaseCurrency();
        $amountInBaseCurrency = $total;

        if ($currency && $baseCurrency && $currency->id !== $baseCurrency->id) {
            $amountInBaseCurrency = $total * $baseCurrency->exchange_rate / $currency->exchange_rate;
        }

        $this->formData['sub_total'] = $subTotal;
        $this->formData['total_amount'] = $total;
        $this->formData['amount_in_base_currency'] = $amountInBaseCurrency;
        $this->formData['paid_amount'] = $total;
        $this->paymentAmount = $total;
    }

    public function updatedSelectedCurrency($currencyId)
    {
        if ($currencyId) {
            $currency = Currency::find($currencyId);
            $this->exchangeRate = $currency ? $currency->exchange_rate : 1;
            $this->formData['currency_id'] = $currencyId;
            $this->formData['exchange_rate'] = $this->exchangeRate;

            // Recalculate cart prices in new currency
            $this->recalculateCartPrices();
            $this->calculateFormData();
        }
    }

    private function recalculateCartPrices()
    {
        $currency = Currency::find($this->selectedCurrency);
        $baseCurrency = Currency::getBaseCurrency();

        if (! $currency || ! $baseCurrency) {
            return;
        }

        foreach ($this->cart as $key => $item) {
            if ($currency->id === $baseCurrency->id) {
                // Converting to base currency
                $this->cart[$key]['price'] = $item['original_price'];
            } else {
                // Converting from base currency to selected currency
                $this->cart[$key]['price'] = $item['original_price'] * $currency->exchange_rate / $baseCurrency->exchange_rate;
            }
        }
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateFormData();
    }

    public function updateCartItemStock($index, $stock)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['stock'] = max(1, (float) $stock);
            $this->calculateFormData();
        }
    }

    public function increaseCartItem($index)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['stock'] = (float) $this->cart[$index]['stock'] + 1;
            $this->calculateFormData();
        }
    }

    public function decreaseCartItem($index)
    {
        if (isset($this->cart[$index])) {
            $newStock = (float) $this->cart[$index]['stock'] - 1;
            if ($newStock < 1) {
                $newStock = 1;
            }
            $this->cart[$index]['stock'] = $newStock;
            $this->calculateFormData();
        }
    }

    public function openDiscountModal()
    {
        $this->dispatch('open-modal', id: 'discount-modal');
    }

    public function closeDiscountModal()
    {
        $this->dispatch('close-modal', id: 'discount-modal');
    }

    public function applyDiscount()
    {
        if ($this->discountType === 'percentage') {
            $this->formData['discount'] = ($this->formData['sub_total'] ?? 0) * ($this->discountAmount / 100);
        } else {
            $this->formData['discount'] = $this->discountAmount;
        }

        $this->calculateFormData();
        $this->closeDiscountModal();

        Notification::make()
            ->title(__('Discount Applied'))
            ->success()
            ->send();
    }

    public function openPaymentModal()
    {
        $this->paymentAmount = $this->formData['total_amount'] ?? 0;
        $this->dispatch('open-modal', id: 'payment-modal');
    }

    public function closePaymentModal()
    {
        $this->dispatch('close-modal', id: 'payment-modal');
    }

    public function createInvoice()
    {
        try {
            $this->validate([
                'formData.sale_date' => 'required|date',
                'formData.customer_id' => 'nullable|exists:customers,id',
                'formData.currency_id' => 'required|exists:currencies,id',
                'formData.discount' => 'numeric',
                'formData.sub_total' => 'numeric',
                'formData.total_amount' => 'numeric',
            ]);

            // Validate stock availability
            foreach ($this->cart as $item) {
                $product = Product::query()->find($item['product_id']);
                if (! $product) {
                    Notification::make()
                        ->title(__('Product not found'))
                        ->body(__('Product: ').$item['name'])
                        ->danger()
                        ->send();

                    return;
                }

                if ($product->stock < $item['stock']) {
                    Notification::make()
                        ->title(__('Insufficient stock'))
                        ->body(__('Product: ').$item['name'].' - '.__('Available: ').$product->stock.' '.$item['unit'])
                        ->danger()
                        ->send();

                    return;
                }
            }

            DB::beginTransaction();

            $sale = Sale::create([
                'sale_date' => $this->formData['sale_date'],
                'customer_id' => $this->formData['customer_id'] ?? null,
                'currency_id' => $this->formData['currency_id'],
                'exchange_rate' => $this->formData['exchange_rate'],
                'discount' => $this->formData['discount'],
                'paid_amount' => 0,
                'sub_total' => $this->formData['sub_total'],
                'total_amount' => $this->formData['total_amount'],
                'amount_in_base_currency' => $this->formData['amount_in_base_currency'],
                'pos_session_id' => $this->currentSession?->id,
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'stock' => (float) $item['stock'],
                    'price' => (float) $item['price'],
                    'discount_amount' => 0,
                    'total_amount' => $item['price'] * $item['stock'],
                ]);
            }

            $this->resetForm();
            DB::commit();

            Notification::make()
                ->title(__('Invoice Created Successfully'))
                ->success()
                ->send();

            $this->redirect(SaleResource::getUrl('invoice', ['record' => $sale]), navigate: true);
        } catch (Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title(__('Error creating invoice'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function createInvoiceAndPayment()
    {
        try {
            $this->validate([
                'formData.sale_date' => 'required|date',
                'formData.customer_id' => 'nullable|exists:customers,id',
                'formData.currency_id' => 'required|exists:currencies,id',
                'formData.discount' => 'numeric',
                'formData.sub_total' => 'numeric',
                'formData.total_amount' => 'numeric',
                'paymentAmount' => 'required|numeric|min:0',
                'paymentMethod' => 'required|string',
            ]);

            // Validate stock availability
            foreach ($this->cart as $item) {
                $product = Product::query()->find($item['product_id']);
                if (! $product) {
                    Notification::make()
                        ->title(__('Product not found'))
                        ->body(__('Product: ').$item['name'])
                        ->danger()
                        ->send();

                    return;
                }

                if ($product->stock < $item['stock']) {
                    Notification::make()
                        ->title(__('Insufficient stock'))
                        ->body(__('Product: ').$item['name'].' - '.__('Available: ').$product->stock.' '.$item['unit'])
                        ->danger()
                        ->send();

                    return;
                }
            }

            DB::beginTransaction();

            $sale = Sale::create([
                'sale_date' => $this->formData['sale_date'],
                'customer_id' => $this->formData['customer_id'] ?? null,
                'currency_id' => $this->formData['currency_id'],
                'exchange_rate' => $this->formData['exchange_rate'],
                'discount' => $this->formData['discount'],
                'paid_amount' => $this->paymentAmount,
                'sub_total' => $this->formData['sub_total'],
                'total_amount' => $this->formData['total_amount'],
                'amount_in_base_currency' => $this->formData['amount_in_base_currency'],
                'pos_session_id' => $this->currentSession?->id,
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'stock' => (float) $item['stock'],
                    'price' => (float) $item['price'],
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $item['price'] * $item['stock'],
                ]);
            }

            // Record payment(s)
            if ($this->useSplitPayment && ! empty($this->splitPayments)) {
                // Use split payment service
                $splitPaymentService = app(SplitPaymentService::class);
                $splitPaymentService->processPayments($sale, $this->splitPayments, $this->currentSession);
            } else {
                // Single payment - record to session
                $splitPaymentService = app(SplitPaymentService::class);
                $splitPaymentService->processPayments($sale, [
                    [
                        'method' => $this->paymentMethod,
                        'amount' => $this->paymentAmount,
                        'tendered' => $this->paymentAmount,
                    ],
                ], $this->currentSession);
            }

            // Process loyalty points if customer exists
            if ($sale->customer) {
                $loyaltyService = app(LoyaltyService::class);
                $loyaltyService->processSale($sale);
            }

            // Calculate change
            $this->changeAmount = max(0, $this->paymentAmount - $sale->total_amount);
            $this->lastSale = $sale;

            $this->resetFormButKeepLastSale();
            $this->closePaymentModal();
            DB::commit();

            Notification::make()
                ->title(__('Invoice and Payment Created Successfully'))
                ->success()
                ->send();

            // Show success modal with print options
            $this->showSuccessModal = true;
        } catch (Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title(__('Error creating invoice and payment'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function resetForm()
    {
        $this->cart = [];
        $this->formData = [];
        $this->paymentAmount = 0;
        $this->discountAmount = 0;
        $this->paymentMethod = 'cash';
        $this->useSplitPayment = false;
        $this->splitPayments = [];
        $this->lastSale = null;
        $this->changeAmount = 0;
        $this->showSuccessModal = false;

        // Reset to base currency
        $baseCurrency = Currency::getBaseCurrency();
        $this->selectedCurrency = $baseCurrency ? $baseCurrency->id : null;
        $this->exchangeRate = $baseCurrency ? $baseCurrency->exchange_rate : 1;

        $this->form->fill([
            'currency_id' => $this->selectedCurrency,
            'exchange_rate' => $this->exchangeRate,
        ]);

        $this->calculateFormData();
    }

    private function resetFormButKeepLastSale()
    {
        $this->cart = [];
        $this->formData = [];
        $this->paymentAmount = 0;
        $this->discountAmount = 0;
        $this->paymentMethod = 'cash';
        $this->useSplitPayment = false;
        $this->splitPayments = [];

        // Reset to base currency
        $baseCurrency = Currency::getBaseCurrency();
        $this->selectedCurrency = $baseCurrency ? $baseCurrency->id : null;
        $this->exchangeRate = $baseCurrency ? $baseCurrency->exchange_rate : 1;

        $this->form->fill([
            'currency_id' => $this->selectedCurrency,
            'exchange_rate' => $this->exchangeRate,
        ]);

        $this->calculateFormData();
    }

    public function pay()
    {
        $this->openPaymentModal();
    }

    public function addSplitPayment(): void
    {
        $this->splitPayments[] = [
            'method' => 'cash',
            'amount' => 0,
        ];
    }

    public function removeSplitPayment(int $index): void
    {
        unset($this->splitPayments[$index]);
        $this->splitPayments = array_values($this->splitPayments);
    }

    public function getSplitPaymentTotal(): float
    {
        return array_sum(array_column($this->splitPayments, 'amount'));
    }

    public function getSplitPaymentRemaining(): float
    {
        $total = $this->formData['total_amount'] ?? 0;

        return max(0, $total - $this->getSplitPaymentTotal());
    }

    public function setQuickAmount(float $amount): void
    {
        $this->paymentAmount = $amount;
    }

    public function printThermalReceipt(): void
    {
        if (! $this->lastSale) {
            Notification::make()
                ->title(__('No sale to print'))
                ->danger()
                ->send();

            return;
        }

        // Reload sale with all necessary relationships
        $sale = Sale::with(['items.product', 'customer', 'payments', 'posSession.user'])
            ->find($this->lastSale->id);

        if (! $sale) {
            Notification::make()
                ->title(__('Sale not found'))
                ->danger()
                ->send();

            return;
        }

        $receiptService = app(ReceiptPrinterService::class);
        $receiptData = $receiptService->generateReceipt($sale);

        // Use js() for direct JavaScript execution with JSON encoding for safety
        $htmlJson = json_encode($receiptData['html']);
        $this->js("window.printThermalReceipt({$htmlJson})");
    }

    public function viewInvoice(): void
    {
        if (! $this->lastSale) {
            return;
        }

        $this->redirect(SaleResource::getUrl('invoice', ['record' => $this->lastSale]), navigate: true);
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastSale = null;
        $this->changeAmount = 0;
    }

    public function newSale(): void
    {
        $this->closeSuccessModal();
        $this->resetForm();
    }

    public function getCurrencySymbol()
    {
        if ($this->selectedCurrency) {
            $currency = Currency::find($this->selectedCurrency);

            return $currency ? $currency->symbol : 'IQD';
        }

        return 'IQD';
    }

    public function getCurrencyCode()
    {
        if ($this->selectedCurrency) {
            $currency = Currency::find($this->selectedCurrency);

            return $currency ? $currency->code : 'IQD';
        }

        return 'IQD';
    }
}

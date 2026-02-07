<?php

namespace Gopos\Database\Seeders;

use Gopos\Models\Category;
use Gopos\Models\Currency;
use Gopos\Models\Customer;
use Gopos\Models\Expense;
use Gopos\Models\ExpenseType;
use Gopos\Models\Income;
use Gopos\Models\IncomeType;
use Gopos\Models\InventoryMovement;
use Gopos\Models\Product;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Gopos\Models\Supplier;
use Gopos\Models\Unit;
use Gopos\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        // Create demo users
        $this->seedUsers();
        $this->command->info('Users seeded.');

        // Create units
        $this->seedUnits();
        $this->command->info('Units seeded.');

        // Create categories
        $this->seedCategories();
        $this->command->info('Categories seeded.');

        // Create products with initial stock
        $this->seedProducts();
        $this->command->info('Products seeded.');

        // Create customers
        $this->seedCustomers();
        $this->command->info('Customers seeded.');

        // Create suppliers
        $this->seedSuppliers();
        $this->command->info('Suppliers seeded.');

        // Create expense types and incomes types
        $this->seedExpenseAndIncomeTypes();
        $this->command->info('Expense and Income types seeded.');

        // Create purchases
        $this->seedPurchases();
        $this->command->info('Purchases seeded.');

        // Create sales
        $this->seedSales();
        $this->command->info('Sales seeded.');

        // Create expenses
        $this->seedExpenses();
        $this->command->info('Expenses seeded.');

        // Create incomes
        $this->seedIncomes();
        $this->command->info('Incomes seeded.');

        $this->command->info('Demo data seeding completed!');
        // DONE
    }

    protected function seedUsers(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'active' => true,
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'active' => true,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }

    protected function seedUnits(): void
    {
        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pc'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Milliliter', 'abbreviation' => 'ml'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
            ['name' => 'Dozen', 'abbreviation' => 'dz'],
            ['name' => 'Meter', 'abbreviation' => 'm'],
            ['name' => 'Carton', 'abbreviation' => 'ctn'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'Electronics', 'active' => true],
            ['name' => 'Clothing', 'active' => true],
            ['name' => 'Food & Beverages', 'active' => true],
            ['name' => 'Home & Kitchen', 'active' => true],
            ['name' => 'Health & Beauty', 'active' => true],
            ['name' => 'Office Supplies', 'active' => true],
            ['name' => 'Sports & Outdoors', 'active' => true],
            ['name' => 'Toys & Games', 'active' => true],
            ['name' => 'Automotive', 'active' => true],
            ['name' => 'Books & Stationery', 'active' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }

    protected function seedProducts(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        $pieceUnit = Unit::where('abbreviation', 'pc')->first();
        $kgUnit = Unit::where('abbreviation', 'kg')->first();
        $boxUnit = Unit::where('abbreviation', 'box')->first();

        $products = [
            // Electronics
            ['name' => 'iPhone 15 Pro', 'category' => 'Electronics', 'cost' => 900000, 'price' => 1200000, 'stock' => 15, 'unit' => $pieceUnit],
            ['name' => 'Samsung Galaxy S24', 'category' => 'Electronics', 'cost' => 750000, 'price' => 950000, 'stock' => 20, 'unit' => $pieceUnit],
            ['name' => 'MacBook Air M3', 'category' => 'Electronics', 'cost' => 1100000, 'price' => 1450000, 'stock' => 8, 'unit' => $pieceUnit],
            ['name' => 'AirPods Pro', 'category' => 'Electronics', 'cost' => 180000, 'price' => 250000, 'stock' => 30, 'unit' => $pieceUnit],
            ['name' => 'iPad Air', 'category' => 'Electronics', 'cost' => 550000, 'price' => 750000, 'stock' => 12, 'unit' => $pieceUnit],
            ['name' => 'Smart Watch', 'category' => 'Electronics', 'cost' => 120000, 'price' => 180000, 'stock' => 25, 'unit' => $pieceUnit],
            ['name' => 'Bluetooth Speaker', 'category' => 'Electronics', 'cost' => 45000, 'price' => 75000, 'stock' => 40, 'unit' => $pieceUnit],
            ['name' => 'USB-C Cable', 'category' => 'Electronics', 'cost' => 5000, 'price' => 12000, 'stock' => 100, 'unit' => $pieceUnit],

            // Clothing
            ['name' => 'Men T-Shirt', 'category' => 'Clothing', 'cost' => 8000, 'price' => 15000, 'stock' => 50, 'unit' => $pieceUnit],
            ['name' => 'Women Dress', 'category' => 'Clothing', 'cost' => 25000, 'price' => 45000, 'stock' => 30, 'unit' => $pieceUnit],
            ['name' => 'Jeans Pants', 'category' => 'Clothing', 'cost' => 18000, 'price' => 35000, 'stock' => 40, 'unit' => $pieceUnit],
            ['name' => 'Winter Jacket', 'category' => 'Clothing', 'cost' => 45000, 'price' => 85000, 'stock' => 20, 'unit' => $pieceUnit],
            ['name' => 'Sports Shoes', 'category' => 'Clothing', 'cost' => 35000, 'price' => 65000, 'stock' => 25, 'unit' => $pieceUnit],

            // Food & Beverages
            ['name' => 'Bottled Water (Pack)', 'category' => 'Food & Beverages', 'cost' => 3000, 'price' => 5000, 'stock' => 100, 'unit' => $boxUnit],
            ['name' => 'Coca Cola (6 Pack)', 'category' => 'Food & Beverages', 'cost' => 4500, 'price' => 7500, 'stock' => 80, 'unit' => $boxUnit],
            ['name' => 'Coffee Beans', 'category' => 'Food & Beverages', 'cost' => 12000, 'price' => 20000, 'stock' => 35, 'unit' => $kgUnit],
            ['name' => 'Green Tea Box', 'category' => 'Food & Beverages', 'cost' => 8000, 'price' => 15000, 'stock' => 45, 'unit' => $boxUnit],
            ['name' => 'Chocolate Bar', 'category' => 'Food & Beverages', 'cost' => 2000, 'price' => 3500, 'stock' => 150, 'unit' => $pieceUnit],
            ['name' => 'Chips (Large)', 'category' => 'Food & Beverages', 'cost' => 2500, 'price' => 4000, 'stock' => 120, 'unit' => $pieceUnit],

            // Home & Kitchen
            ['name' => 'Blender', 'category' => 'Home & Kitchen', 'cost' => 35000, 'price' => 55000, 'stock' => 15, 'unit' => $pieceUnit],
            ['name' => 'Microwave Oven', 'category' => 'Home & Kitchen', 'cost' => 85000, 'price' => 125000, 'stock' => 10, 'unit' => $pieceUnit],
            ['name' => 'Cooking Pot Set', 'category' => 'Home & Kitchen', 'cost' => 45000, 'price' => 75000, 'stock' => 20, 'unit' => $pieceUnit],
            ['name' => 'Rice Cooker', 'category' => 'Home & Kitchen', 'cost' => 28000, 'price' => 45000, 'stock' => 18, 'unit' => $pieceUnit],
            ['name' => 'Kitchen Knife Set', 'category' => 'Home & Kitchen', 'cost' => 15000, 'price' => 28000, 'stock' => 25, 'unit' => $pieceUnit],

            // Health & Beauty
            ['name' => 'Shampoo', 'category' => 'Health & Beauty', 'cost' => 5000, 'price' => 9000, 'stock' => 60, 'unit' => $pieceUnit],
            ['name' => 'Face Cream', 'category' => 'Health & Beauty', 'cost' => 12000, 'price' => 22000, 'stock' => 35, 'unit' => $pieceUnit],
            ['name' => 'Perfume', 'category' => 'Health & Beauty', 'cost' => 35000, 'price' => 65000, 'stock' => 20, 'unit' => $pieceUnit],
            ['name' => 'Toothpaste', 'category' => 'Health & Beauty', 'cost' => 2500, 'price' => 4500, 'stock' => 80, 'unit' => $pieceUnit],
            ['name' => 'Hand Sanitizer', 'category' => 'Health & Beauty', 'cost' => 3000, 'price' => 5500, 'stock' => 100, 'unit' => $pieceUnit],

            // Office Supplies
            ['name' => 'Printer Paper (Ream)', 'category' => 'Office Supplies', 'cost' => 8000, 'price' => 12000, 'stock' => 50, 'unit' => $boxUnit],
            ['name' => 'Pen Set', 'category' => 'Office Supplies', 'cost' => 3000, 'price' => 6000, 'stock' => 75, 'unit' => $boxUnit],
            ['name' => 'Stapler', 'category' => 'Office Supplies', 'cost' => 4000, 'price' => 8000, 'stock' => 40, 'unit' => $pieceUnit],
            ['name' => 'Notebook', 'category' => 'Office Supplies', 'cost' => 2000, 'price' => 4000, 'stock' => 100, 'unit' => $pieceUnit],
            ['name' => 'Desk Organizer', 'category' => 'Office Supplies', 'cost' => 12000, 'price' => 22000, 'stock' => 25, 'unit' => $pieceUnit],
        ];

        foreach ($products as $productData) {
            $category = Category::where('name', $productData['category'])->first();

            $product = Product::updateOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $category?->id,
                    'unit_id' => $productData['unit']?->id,
                    'cost' => $productData['cost'],
                    'price' => $productData['price'],
                    'low_stock_alert' => 5,
                    'barcode' => 'PRD'.str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                ]
            );

            // Create initial stock movement if product doesn't have stock yet
            $currentStock = $product->movements()->sum('quantity');
            if ($currentStock < $productData['stock']) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => $productData['stock'] - $currentStock,
                    'reason' => 'Demo data initial stock',
                    'movement_date' => now(),
                ]);
            }
        }
    }

    protected function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Ahmad Ali', 'email' => 'ahmad@example.com', 'phone' => '+964 750 123 4567', 'address' => 'Erbil, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Sara Hassan', 'email' => 'sara@example.com', 'phone' => '+964 751 234 5678', 'address' => 'Sulaymaniyah, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Mohammed Karim', 'email' => 'mohammed@example.com', 'phone' => '+964 770 345 6789', 'address' => 'Duhok, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Fatima Omar', 'email' => 'fatima@example.com', 'phone' => '+964 751 456 7890', 'address' => 'Baghdad, Iraq', 'active' => true],
            ['name' => 'Hussein Abbas', 'email' => 'hussein@example.com', 'phone' => '+964 770 567 8901', 'address' => 'Basra, Iraq', 'active' => true],
            ['name' => 'Layla Mustafa', 'email' => 'layla@example.com', 'phone' => '+964 750 678 9012', 'address' => 'Erbil, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Omar Saleh', 'email' => 'omar@example.com', 'phone' => '+964 751 789 0123', 'address' => 'Kirkuk, Iraq', 'active' => true],
            ['name' => 'Noor Ahmed', 'email' => 'noor@example.com', 'phone' => '+964 770 890 1234', 'address' => 'Sulaymaniyah, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Karwan Jamal', 'email' => 'karwan@example.com', 'phone' => '+964 750 901 2345', 'address' => 'Erbil, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'Zainab Rashid', 'email' => 'zainab@example.com', 'phone' => '+964 751 012 3456', 'address' => 'Duhok, Kurdistan Region, Iraq', 'active' => true],
            ['name' => 'ABC Company', 'email' => 'contact@abc.com', 'phone' => '+964 750 111 2222', 'address' => 'Erbil Business Center', 'active' => true, 'note' => 'Corporate client - 10% discount'],
            ['name' => 'XYZ Trading', 'email' => 'sales@xyz.com', 'phone' => '+964 751 333 4444', 'address' => 'Sulaymaniyah Trade Zone', 'active' => true, 'note' => 'Wholesale customer'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }

    protected function seedSuppliers(): void
    {
        $suppliers = [
            ['name' => 'Tech Distributors Ltd', 'email' => 'orders@techdist.com', 'phone' => '+964 750 100 1000', 'address' => 'Erbil Industrial Zone', 'active' => true, 'note' => 'Main electronics supplier'],
            ['name' => 'Fashion Imports Co', 'email' => 'supply@fashionimports.com', 'phone' => '+964 751 200 2000', 'address' => 'Sulaymaniyah Business Park', 'active' => true, 'note' => 'Clothing and accessories'],
            ['name' => 'Food & Beverage Wholesale', 'email' => 'orders@fbwholesale.com', 'phone' => '+964 770 300 3000', 'address' => 'Baghdad Commercial District', 'active' => true, 'note' => 'Food products supplier'],
            ['name' => 'Home Essentials Trading', 'email' => 'sales@homeessentials.com', 'phone' => '+964 750 400 4000', 'address' => 'Duhok Trade Center', 'active' => true, 'note' => 'Kitchen and home products'],
            ['name' => 'Beauty & Care Imports', 'email' => 'orders@beautycare.com', 'phone' => '+964 751 500 5000', 'address' => 'Erbil City Mall Area', 'active' => true, 'note' => 'Health and beauty products'],
            ['name' => 'Office Solutions Inc', 'email' => 'supply@officesolutions.com', 'phone' => '+964 770 600 6000', 'address' => 'Erbil Business Tower', 'active' => true, 'note' => 'Office supplies and stationery'],
            ['name' => 'General Trading Company', 'email' => 'info@generaltrading.com', 'phone' => '+964 750 700 7000', 'address' => 'Sulaymaniyah Industrial Area', 'active' => true, 'note' => 'Multi-category supplier'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['email' => $supplier['email']],
                $supplier
            );
        }
    }

    protected function seedExpenseAndIncomeTypes(): void
    {
        $expenseTypes = [
            'Rent',
            'Utilities (Electric, Water)',
            'Salaries & Wages',
            'Internet & Phone',
            'Marketing & Advertising',
            'Office Supplies',
            'Maintenance & Repairs',
            'Transportation',
            'Insurance',
            'Bank Fees',
            'Professional Services',
            'Miscellaneous',
        ];

        foreach ($expenseTypes as $type) {
            ExpenseType::updateOrCreate(['name' => $type]);
        }

        $incomeTypes = [
            'Product Sales',
            'Service Income',
            'Consultation Fees',
            'Shipping Charges',
            'Late Payment Fees',
            'Commission Income',
            'Rental Income',
            'Interest Income',
            'Other Income',
        ];

        foreach ($incomeTypes as $type) {
            IncomeType::updateOrCreate(['name' => $type]);
        }
    }

    protected function seedPurchases(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        if (! $currency) {
            return;
        }

        $suppliers = Supplier::all();
        if ($suppliers->isEmpty()) {
            return;
        }

        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        // Get the max existing purchase ID to ensure unique numbers
        $maxId = Purchase::max('id') ?? 0;

        // Create purchases for the last 3 months
        for ($i = 0; $i < 15; $i++) {
            $purchaseDate = now()->subDays(rand(1, 90));
            $supplier = $suppliers->random();

            // Pre-calculate items and totals
            $itemCount = rand(2, 6);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subTotal = 0;
            $items = [];
            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 30);
                $unitCost = $product->cost;
                $total = $quantity * $unitCost;
                $subTotal += $total;
                $items[] = [
                    'product_id' => $product->id,
                    'stock' => $quantity,
                    'cost' => $unitCost,
                    'total_amount' => $total,
                ];
            }

            $discount = rand(0, 1) ? rand(1, 5) * $subTotal / 100 : 0;
            $totalAmount = $subTotal - $discount;
            $paidAmount = rand(0, 2) == 0 ? 0 : (rand(0, 1) ? $totalAmount : $totalAmount * rand(30, 80) / 100);

            // Use explicit purchase number to avoid race conditions
            $purchaseNumber = 'PUR-'.str_pad($maxId + $i + 1, 5, '0', STR_PAD_LEFT);

            // Insert directly to avoid model events causing issues
            $purchaseId = DB::table('purchases')->insertGetId([
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $purchaseDate,
                'supplier_id' => $supplier->id,
                'currency_id' => $currency->id,
                'exchange_rate' => $currency->exchange_rate,
                'sub_total' => $subTotal,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => round($paidAmount, 2),
                'amount_in_base_currency' => $totalAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create items without triggering inventory movements (we'll handle stock differently)
            foreach ($items as $item) {
                DB::table('purchase_items')->insert([
                    'purchase_id' => $purchaseId,
                    'product_id' => $item['product_id'],
                    'stock' => $item['stock'],
                    'cost' => $item['cost'],
                    'total_amount' => $item['total_amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add inventory movement
                DB::table('inventory_movements')->insert([
                    'product_id' => $item['product_id'],
                    'type' => 'purchase',
                    'quantity' => $item['stock'],
                    'purchase_id' => $purchaseId,
                    'reason' => 'Demo purchase',
                    'movement_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function seedSales(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        if (! $currency) {
            return;
        }

        $customers = Customer::all();
        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        // Get max sale ID to ensure unique numbers
        $maxId = Sale::max('id') ?? 0;

        // Create sales for the last 3 months
        for ($i = 0; $i < 50; $i++) {
            $saleDate = now()->subDays(rand(0, 90));
            $customer = rand(0, 3) == 0 ? null : $customers->random();

            // Pre-calculate items and totals
            $itemCount = rand(1, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subTotal = 0;
            $items = [];
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 5);
                $unitPrice = $product->price;
                $total = $quantity * $unitPrice;
                $subTotal += $total;
                $items[] = [
                    'product_id' => $product->id,
                    'stock' => $quantity,
                    'price' => $unitPrice,
                    'total_amount' => $total,
                ];
            }

            $discount = rand(0, 4) == 0 ? rand(1, 10) * $subTotal / 100 : 0;
            $totalAmount = $subTotal - $discount;
            $paidAmount = rand(0, 10) > 1 ? $totalAmount : (rand(0, 1) ? 0 : $totalAmount * rand(30, 80) / 100);

            // Use explicit sale number
            $saleNumber = 'INV-'.str_pad($maxId + $i + 1, 4, '0', STR_PAD_LEFT);

            // Insert directly to avoid model events
            $saleId = DB::table('sales')->insertGetId([
                'sale_number' => $saleNumber,
                'sale_date' => $saleDate,
                'customer_id' => $customer?->id,
                'currency_id' => $currency->id,
                'exchange_rate' => $currency->exchange_rate,
                'sub_total' => $subTotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => round($paidAmount, 2),
                'amount_in_base_currency' => $totalAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create items and inventory movements
            foreach ($items as $item) {
                DB::table('sale_items')->insert([
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'stock' => $item['stock'],
                    'price' => $item['price'],
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $item['total_amount'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add inventory movement (negative for sales)
                DB::table('inventory_movements')->insert([
                    'product_id' => $item['product_id'],
                    'type' => 'sale',
                    'quantity' => -$item['stock'],
                    'sale_id' => $saleId,
                    'reason' => 'Demo sale',
                    'movement_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function seedExpenses(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        if (! $currency) {
            return;
        }

        $expenseTypes = ExpenseType::all();
        if ($expenseTypes->isEmpty()) {
            return;
        }

        $expenseData = [
            ['type' => 'Rent', 'amount' => 1500000, 'recurring' => true],
            ['type' => 'Utilities (Electric, Water)', 'amount' => 250000, 'recurring' => true],
            ['type' => 'Salaries & Wages', 'amount' => 3500000, 'recurring' => true],
            ['type' => 'Internet & Phone', 'amount' => 75000, 'recurring' => true],
            ['type' => 'Marketing & Advertising', 'amount' => 200000, 'recurring' => false],
            ['type' => 'Office Supplies', 'amount' => 50000, 'recurring' => false],
            ['type' => 'Maintenance & Repairs', 'amount' => 150000, 'recurring' => false],
            ['type' => 'Transportation', 'amount' => 100000, 'recurring' => false],
        ];

        // Create expenses for the last 3 months
        for ($month = 0; $month < 3; $month++) {
            $monthDate = now()->subMonths($month);

            foreach ($expenseData as $data) {
                $expenseType = $expenseTypes->where('name', $data['type'])->first();
                if (! $expenseType) {
                    continue;
                }

                // Add some randomness to amounts
                $amount = $data['amount'] * (0.9 + (rand(0, 20) / 100));

                // Recurring expenses every month, others randomly
                if ($data['recurring'] || rand(0, 2) == 0) {
                    $expense = new Expense;
                    $expense->expense_type_id = $expenseType->id;
                    $expense->currency_id = $currency->id;
                    $expense->amount = round($amount, 2);
                    $expense->note = $data['type'].' - '.$monthDate->format('F Y');
                    $expense->created_at = $monthDate->copy()->setDay(rand(1, 28));
                    $expense->save();
                }
            }
        }
    }

    protected function seedIncomes(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        if (! $currency) {
            return;
        }

        $incomeTypes = IncomeType::all();
        if ($incomeTypes->isEmpty()) {
            return;
        }

        $incomeData = [
            ['type' => 'Shipping Charges', 'min' => 25000, 'max' => 75000],
            ['type' => 'Consultation Fees', 'min' => 50000, 'max' => 200000],
            ['type' => 'Late Payment Fees', 'min' => 5000, 'max' => 25000],
            ['type' => 'Commission Income', 'min' => 30000, 'max' => 150000],
            ['type' => 'Other Income', 'min' => 10000, 'max' => 50000],
        ];

        // Create incomes for the last 3 months
        for ($i = 0; $i < 20; $i++) {
            $data = $incomeData[array_rand($incomeData)];
            $incomeType = $incomeTypes->where('name', $data['type'])->first();
            if (! $incomeType) {
                continue;
            }

            $amount = rand($data['min'], $data['max']);

            $income = new Income;
            $income->income_type_id = $incomeType->id;
            $income->currency_id = $currency->id;
            $income->amount = $amount;
            $income->description = $data['type'].' - Transaction #'.($i + 1);
            $income->created_at = now()->subDays(rand(0, 90));
            $income->save();
        }
    }
}

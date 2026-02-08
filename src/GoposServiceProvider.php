<?php

namespace Gopos;

use Gopos\Console\Commands\ImportDatabase;
use Gopos\Console\Commands\MakeReport;
use Gopos\Console\Commands\MakeReportPage;
use Gopos\Console\Commands\Refresh;
use Gopos\Console\Commands\SeedDemoDataCommand;
use Gopos\Console\Commands\SendTestEmail;
use Gopos\Events\SaleCreated;
use Gopos\Events\SaleReturnCreated;
use Gopos\Events\StockCountPosted;
use Gopos\Events\StockTransferCompleted;
use Gopos\Listeners\PostSaleToGL;
use Gopos\Listeners\PostStockCountAdjustments;
use Gopos\Listeners\ProcessSaleReturn;
use Gopos\Listeners\ProcessStockTransfer;
use Gopos\Listeners\UpdateInventoryOnSale;
use Gopos\Models\Expense;
use Gopos\Models\Income;
use Gopos\Models\Payment;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Gopos\Observers\ExpenseObserver;
use Gopos\Observers\IncomeObserver;
use Gopos\Observers\PaymentObserver;
use Gopos\Observers\PurchaseObserver;
use Gopos\Observers\SaleObserver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class GoposServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gopos.php', 'gopos');

        $this->app->booting(function () {
            $this->ensureSessionDriverWorks();
        });

        // Tell Laravel where to find factories for Gopos models
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            if (str_starts_with($modelName, 'Gopos\\Models\\')) {
                $modelBaseName = class_basename($modelName);

                return "Gopos\\Database\\Factories\\{$modelBaseName}Factory";
            }

            // Fall back to default resolution for non-Gopos models
            return 'Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gopos');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'gopos');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->registerObservers();
        $this->registerEvents();
        $this->registerPolicies();
        $this->registerLivewireComponents();
        $this->registerModularTranslations();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportDatabase::class,
                MakeReport::class,
                MakeReportPage::class,
                Refresh::class,
                SeedDemoDataCommand::class,
                SendTestEmail::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/gopos.php' => config_path('gopos.php'),
            ], 'gopos-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/gopos'),
            ], 'gopos-views');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'gopos-migrations');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/gopos'),
            ], 'gopos-lang');

            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'gopos-seeders');

            $this->publishes([
                __DIR__.'/../docs' => base_path('docs'),
            ], 'gopos-docs');
        }
    }

    protected function registerObservers(): void
    {
        Sale::observe(SaleObserver::class);
        Purchase::observe(PurchaseObserver::class);
        Expense::observe(ExpenseObserver::class);
        Income::observe(IncomeObserver::class);
        Payment::observe(PaymentObserver::class);
    }

    protected function registerEvents(): void
    {
        Event::listen(SaleCreated::class, [UpdateInventoryOnSale::class, PostSaleToGL::class]);
        Event::listen(SaleReturnCreated::class, [ProcessSaleReturn::class]);
        Event::listen(StockTransferCompleted::class, [ProcessStockTransfer::class]);
        Event::listen(StockCountPosted::class, [PostStockCountAdjustments::class]);
    }

    protected function registerPolicies(): void
    {
        $policies = [
            \Gopos\Models\Customer::class => \Gopos\Policies\CustomerPolicy::class,
            \Gopos\Models\Sale::class => \Gopos\Policies\SalePolicy::class,
            \Gopos\Models\SaleReturn::class => \Gopos\Policies\SaleReturnPolicy::class,
            \Gopos\Models\Supplier::class => \Gopos\Policies\SupplierPolicy::class,
            \Gopos\Models\Purchase::class => \Gopos\Policies\PurchasePolicy::class,
            \Gopos\Models\PurchaseReturn::class => \Gopos\Policies\PurchaseReturnPolicy::class,
            \Gopos\Models\Warehouse::class => \Gopos\Policies\WarehousePolicy::class,
            \Gopos\Models\Product::class => \Gopos\Policies\ProductPolicy::class,
            \Gopos\Models\Category::class => \Gopos\Policies\CategoryPolicy::class,
            \Gopos\Models\Unit::class => \Gopos\Policies\UnitPolicy::class,
            \Gopos\Models\StockTransfer::class => \Gopos\Policies\StockTransferPolicy::class,
            \Gopos\Models\StockCount::class => \Gopos\Policies\StockCountPolicy::class,
            \Gopos\Models\InventoryMovement::class => \Gopos\Policies\InventoryMovementPolicy::class,
            \Gopos\Models\Account::class => \Gopos\Policies\AccountPolicy::class,
            \Gopos\Models\JournalEntry::class => \Gopos\Policies\JournalEntryPolicy::class,
            \Gopos\Models\Income::class => \Gopos\Policies\IncomePolicy::class,
            \Gopos\Models\Expense::class => \Gopos\Policies\ExpensePolicy::class,
            \Gopos\Models\IncomeType::class => \Gopos\Policies\IncomeTypePolicy::class,
            \Gopos\Models\ExpenseType::class => \Gopos\Policies\ExpenseTypePolicy::class,
            \Gopos\Models\TaxCode::class => \Gopos\Policies\TaxCodePolicy::class,
            \Gopos\Models\Currency::class => \Gopos\Policies\CurrencyPolicy::class,
            \Gopos\Models\AuditLog::class => \Gopos\Policies\AuditLogPolicy::class,
            \Gopos\Models\User::class => \Gopos\Policies\UserPolicy::class,
            \Gopos\Models\Role::class => \Gopos\Policies\RolePolicy::class,
            \Gopos\Models\Permission::class => \Gopos\Policies\PermissionPolicy::class,
            \Gopos\Models\Department::class => \Gopos\Policies\DepartmentPolicy::class,
            \Gopos\Models\Position::class => \Gopos\Policies\PositionPolicy::class,
            \Gopos\Models\Employee::class => \Gopos\Policies\EmployeePolicy::class,
            \Gopos\Models\LeaveType::class => \Gopos\Policies\LeaveTypePolicy::class,
            \Gopos\Models\Leave::class => \Gopos\Policies\LeavePolicy::class,
            \Gopos\Models\Holiday::class => \Gopos\Policies\HolidayPolicy::class,
            \Gopos\Models\Payroll::class => \Gopos\Policies\PayrollPolicy::class,
            \Gopos\Models\Branch::class => \Gopos\Policies\BranchPolicy::class,
        ];

        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('invoice-p-d-f', \Gopos\Http\Livewire\InvoicePDF::class);
        Livewire::component('sale-invoice', \Gopos\Http\Livewire\SaleInvoice::class);
        Livewire::component('install-wizard', \Gopos\Http\Livewire\Install\InstallWizard::class);
    }

    protected function registerModularTranslations(): void
    {
        $modulesPath = __DIR__.'/../resources/lang/modules';
        if (file_exists($modulesPath)) {
            $modules = glob($modulesPath.'/*', GLOB_ONLYDIR);
            foreach ($modules as $module) {
                $this->app['translator']->addJsonPath($module);
            }
        }

        // Also check the app's lang/modules path for customer overrides
        $appModulesPath = app()->langPath('modules');
        if (file_exists($appModulesPath)) {
            $modules = glob($appModulesPath.'/*', GLOB_ONLYDIR);
            foreach ($modules as $module) {
                $this->app['translator']->addJsonPath($module);
            }
        }
    }

    protected function ensureSessionDriverWorks(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        try {
            $connection = config('database.default');
            $database = config("database.connections.{$connection}.database");

            $pdo = new \PDO(
                sprintf(
                    '%s:host=%s;port=%s;dbname=%s',
                    config("database.connections.{$connection}.driver"),
                    config("database.connections.{$connection}.host", '127.0.0.1'),
                    config("database.connections.{$connection}.port", '3306'),
                    $database
                ),
                config("database.connections.{$connection}.username"),
                config("database.connections.{$connection}.password")
            );

            $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
            if ($stmt->rowCount() === 0) {
                Config::set('session.driver', 'file');
            }
        } catch (\Exception $e) {
            Config::set('session.driver', 'file');
        }
    }
}

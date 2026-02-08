<?php

namespace Gopos\Console\Commands;

use Gopos\Database\Seeders\CurrencySeeder;
use Gopos\Database\Seeders\DemoSeeder;
use Gopos\Models\Account;
use Gopos\Models\AccountType;
use Gopos\Models\BankAccount;
use Gopos\Models\CostCenter;
use Gopos\Models\Currency;
use Gopos\Models\FiscalPeriod;
use Gopos\Models\LoyaltyProgram;
use Gopos\Models\Warehouse;
use Gopos\Models\WarehouseLocation;
use Illuminate\Console\Command;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'demo:seed
                            {--fresh : Wipe the database before seeding}
                            {--force : Force the operation to run in production}
                            {--module= : Seed only a specific module (accounting, inventory, pos, all)}';

    protected $description = 'Seed dummy data for all tables for demo purposes';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('This command cannot run in production without the --force option.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->option('force') && ! $this->confirm('This will wipe all data. Are you sure?')) {
                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true]);
        }

        $module = $this->option('module') ?? 'all';

        $this->info('Starting demo data seeding...');

        // Always seed currencies first
        $seedOptions = ['--class' => CurrencySeeder::class, '--no-interaction' => true];
        if ($this->option('force')) {
            $seedOptions['--force'] = true;
        }
        $this->call('db:seed', $seedOptions);
        $this->info('✓ Currencies seeded');

        match ($module) {
            'accounting' => $this->seedAccountingModule(),
            'inventory' => $this->seedInventoryModule(),
            'pos' => $this->seedPOSModule(),
            'all' => $this->seedAllModules(),
            default => $this->error("Unknown module: {$module}"),
        };

        $this->newLine();
        $this->info('Demo data seeding completed successfully!');
        $this->newLine();
        $this->table(
            ['Credential', 'Value'],
            [
                ['Admin Email', 'admin@demo.com'],
                ['Cashier Email', 'cashier@demo.com'],
                ['Manager Email', 'manager@demo.com'],
                ['Password', 'Admin@123'],
            ]
        );

        return self::SUCCESS;
    }

    protected function seedAllModules(): void
    {
        // Seed base POS demo data
        $seedOptions = ['--class' => DemoSeeder::class, '--no-interaction' => true];
        if ($this->option('force')) {
            $seedOptions['--force'] = true;
        }
        $this->call('db:seed', $seedOptions);
        $this->info('✓ Base POS data seeded');

        $this->seedAccountingModule();
        $this->seedInventoryModule();
        $this->seedPOSModule();
    }

    protected function seedAccountingModule(): void
    {
        $this->components->task('Seeding Account Types', fn () => $this->seedAccountTypes());
        $this->components->task('Seeding Cost Centers', fn () => $this->seedCostCenters());
        $this->components->task('Seeding Accounts', fn () => $this->seedAccounts());
        $this->components->task('Seeding Fiscal Periods', fn () => $this->seedFiscalPeriods());
        $this->components->task('Seeding Bank Accounts', fn () => $this->seedBankAccounts());
    }

    protected function seedInventoryModule(): void
    {
        $this->components->task('Seeding Warehouses', fn () => $this->seedWarehouses());
        $this->components->task('Seeding Warehouse Locations', fn () => $this->seedWarehouseLocations());
    }

    protected function seedPOSModule(): void
    {
        $this->components->task('Seeding Loyalty Programs', fn () => $this->seedLoyaltyPrograms());
    }

    protected function seedAccountTypes(): void
    {
        $types = [
            ['name' => 'Asset', 'name_ar' => 'أصول', 'normal_balance' => 'debit', 'display_order' => 1],
            ['name' => 'Liability', 'name_ar' => 'التزامات', 'normal_balance' => 'credit', 'display_order' => 2],
            ['name' => 'Equity', 'name_ar' => 'حقوق الملكية', 'normal_balance' => 'credit', 'display_order' => 3],
            ['name' => 'Revenue', 'name_ar' => 'إيرادات', 'normal_balance' => 'credit', 'display_order' => 4],
            ['name' => 'Expense', 'name_ar' => 'مصروفات', 'normal_balance' => 'debit', 'display_order' => 5],
        ];

        foreach ($types as $type) {
            AccountType::updateOrCreate(['name' => $type['name']], $type);
        }
    }

    protected function seedCostCenters(): void
    {
        $centers = [
            ['code' => 'CC-001', 'name' => 'Administration', 'name_ar' => 'الإدارة', 'type' => CostCenter::TYPE_DEPARTMENT],
            ['code' => 'CC-002', 'name' => 'Sales', 'name_ar' => 'المبيعات', 'type' => CostCenter::TYPE_DEPARTMENT],
            ['code' => 'CC-003', 'name' => 'Operations', 'name_ar' => 'العمليات', 'type' => CostCenter::TYPE_DEPARTMENT],
            ['code' => 'CC-004', 'name' => 'IT Department', 'name_ar' => 'قسم تقنية المعلومات', 'type' => CostCenter::TYPE_DEPARTMENT],
            ['code' => 'CC-005', 'name' => 'Human Resources', 'name_ar' => 'الموارد البشرية', 'type' => CostCenter::TYPE_DEPARTMENT],
        ];

        foreach ($centers as $center) {
            CostCenter::updateOrCreate(
                ['code' => $center['code']],
                array_merge($center, ['is_active' => true])
            );
        }
    }

    protected function seedAccounts(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        $assetType = AccountType::where('name', 'Asset')->first();
        $liabilityType = AccountType::where('name', 'Liability')->first();
        $equityType = AccountType::where('name', 'Equity')->first();
        $revenueType = AccountType::where('name', 'Revenue')->first();
        $expenseType = AccountType::where('name', 'Expense')->first();

        if (! $assetType) {
            return;
        }

        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash', 'name_ar' => 'النقدية', 'type' => $assetType, 'opening_balance' => 50000000],
            ['code' => '1100', 'name' => 'Bank', 'name_ar' => 'البنك', 'type' => $assetType, 'opening_balance' => 100000000],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'name_ar' => 'الذمم المدينة', 'type' => $assetType],
            ['code' => '1300', 'name' => 'Inventory', 'name_ar' => 'المخزون', 'type' => $assetType],
            ['code' => '1400', 'name' => 'Fixed Assets', 'name_ar' => 'الأصول الثابتة', 'type' => $assetType],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable', 'name_ar' => 'الذمم الدائنة', 'type' => $liabilityType],
            ['code' => '2100', 'name' => 'Taxes Payable', 'name_ar' => 'الضرائب المستحقة', 'type' => $liabilityType],
            ['code' => '2200', 'name' => 'Salaries Payable', 'name_ar' => 'الرواتب المستحقة', 'type' => $liabilityType],

            // Equity
            ['code' => '3000', 'name' => 'Owner Capital', 'name_ar' => 'رأس المال', 'type' => $equityType, 'opening_balance' => 100000000],
            ['code' => '3100', 'name' => 'Retained Earnings', 'name_ar' => 'الأرباح المحتجزة', 'type' => $equityType],

            // Revenue
            ['code' => '4000', 'name' => 'Sales Revenue', 'name_ar' => 'إيرادات المبيعات', 'type' => $revenueType],
            ['code' => '4100', 'name' => 'Service Revenue', 'name_ar' => 'إيرادات الخدمات', 'type' => $revenueType],
            ['code' => '4200', 'name' => 'Other Income', 'name_ar' => 'إيرادات أخرى', 'type' => $revenueType],

            // Expenses
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'name_ar' => 'تكلفة البضاعة المباعة', 'type' => $expenseType],
            ['code' => '5100', 'name' => 'Salaries Expense', 'name_ar' => 'مصروف الرواتب', 'type' => $expenseType],
            ['code' => '5200', 'name' => 'Rent Expense', 'name_ar' => 'مصروف الإيجار', 'type' => $expenseType],
            ['code' => '5300', 'name' => 'Utilities Expense', 'name_ar' => 'مصروف المرافق', 'type' => $expenseType],
            ['code' => '5400', 'name' => 'Marketing Expense', 'name_ar' => 'مصروف التسويق', 'type' => $expenseType],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                [
                    'account_type_id' => $account['type']?->id,
                    'currency_id' => $currency?->id,
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'name_ar' => $account['name_ar'],
                    'is_active' => true,
                    'is_system' => true,
                    'opening_balance' => $account['opening_balance'] ?? 0,
                    'current_balance' => $account['opening_balance'] ?? 0,
                ]
            );
        }
    }

    protected function seedFiscalPeriods(): void
    {
        $currentYear = now()->year;

        for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
            FiscalPeriod::updateOrCreate(
                ['name' => "Fiscal Year {$year}"],
                [
                    'name' => "Fiscal Year {$year}",
                    'start_date' => "{$year}-01-01",
                    'end_date' => "{$year}-12-31",
                    'status' => $year < $currentYear ? 'closed' : 'open',
                ]
            );
        }
    }

    protected function seedBankAccounts(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        $bankAccount = Account::where('code', '1100')->first();

        $banks = [
            [
                'bank_name' => 'Kurdistan International Bank',
                'bank_name_ar' => 'بنك كردستان الدولي',
                'account_number' => '1234567890',
                'iban' => 'IQ98KURD0001234567890123',
                'branch' => 'Erbil Main Branch',
                'opening_balance' => 50000000,
            ],
            [
                'bank_name' => 'Trade Bank of Iraq',
                'bank_name_ar' => 'المصرف التجاري العراقي',
                'account_number' => '0987654321',
                'iban' => 'IQ98TBI00001234567890123',
                'branch' => 'Erbil Branch',
                'opening_balance' => 25000000,
            ],
            [
                'bank_name' => 'Cihan Bank',
                'bank_name_ar' => 'بنك جيهان',
                'account_number' => '5555666677',
                'iban' => 'IQ98CIHN0001234567890123',
                'branch' => 'City Center Branch',
                'opening_balance' => 30000000,
            ],
        ];

        foreach ($banks as $bank) {
            BankAccount::updateOrCreate(
                ['account_number' => $bank['account_number']],
                array_merge($bank, [
                    'account_id' => $bankAccount?->id,
                    'currency_id' => $currency?->id,
                    'current_balance' => $bank['opening_balance'],
                    'is_active' => true,
                ])
            );
        }
    }

    protected function seedWarehouses(): void
    {
        $warehouses = [
            [
                'code' => 'WH-MAIN',
                'name' => 'Main Warehouse',
                'name_ar' => 'المستودع الرئيسي',
                'address' => 'Industrial Area, Erbil',
                'phone' => '+964 750 111 2233',
                'is_default' => true,
            ],
            [
                'code' => 'WH-STORE',
                'name' => 'Retail Store',
                'name_ar' => 'متجر التجزئة',
                'address' => 'City Center, Erbil',
                'phone' => '+964 750 222 3344',
                'is_default' => false,
            ],
            [
                'code' => 'WH-SULY',
                'name' => 'Sulaymaniyah Branch',
                'name_ar' => 'فرع السليمانية',
                'address' => 'Business District, Sulaymaniyah',
                'phone' => '+964 751 333 4455',
                'is_default' => false,
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::updateOrCreate(
                ['code' => $wh['code']],
                array_merge($wh, [
                    'is_active' => true,
                    'allow_negative_stock' => false,
                ])
            );
        }
    }

    protected function seedWarehouseLocations(): void
    {
        $mainWarehouse = Warehouse::where('code', 'WH-MAIN')->first();
        if (! $mainWarehouse) {
            return;
        }

        $locations = [
            ['aisle' => 'A', 'shelf' => '1', 'bin' => '01', 'name' => 'Aisle A - Shelf 1', 'description' => 'Electronics & Accessories'],
            ['aisle' => 'A', 'shelf' => '2', 'bin' => '01', 'name' => 'Aisle A - Shelf 2', 'description' => 'Electronics - Phones'],
            ['aisle' => 'B', 'shelf' => '1', 'bin' => '01', 'name' => 'Aisle B - Shelf 1', 'description' => 'Clothing - Men'],
            ['aisle' => 'B', 'shelf' => '2', 'bin' => '01', 'name' => 'Aisle B - Shelf 2', 'description' => 'Clothing - Women'],
            ['aisle' => 'C', 'shelf' => '1', 'bin' => '01', 'name' => 'Aisle C - Shelf 1', 'description' => 'Food & Beverages'],
            ['aisle' => 'C', 'shelf' => '2', 'bin' => '01', 'name' => 'Aisle C - Shelf 2', 'description' => 'Snacks & Drinks'],
            ['aisle' => 'D', 'shelf' => '1', 'bin' => '01', 'name' => 'Aisle D - Shelf 1', 'description' => 'Home & Kitchen'],
            ['aisle' => 'D', 'shelf' => '2', 'bin' => '01', 'name' => 'Aisle D - Shelf 2', 'description' => 'Kitchen Appliances'],
            ['aisle' => 'E', 'shelf' => '1', 'bin' => '01', 'name' => 'Aisle E - Shelf 1', 'description' => 'Health & Beauty'],
            ['aisle' => 'E', 'shelf' => '2', 'bin' => '01', 'name' => 'Aisle E - Shelf 2', 'description' => 'Office Supplies'],
        ];

        foreach ($locations as $loc) {
            WarehouseLocation::updateOrCreate(
                ['warehouse_id' => $mainWarehouse->id, 'name' => $loc['name']],
                array_merge($loc, [
                    'warehouse_id' => $mainWarehouse->id,
                    'is_active' => true,
                ])
            );
        }
    }

    protected function seedLoyaltyPrograms(): void
    {
        $programs = [
            [
                'name' => 'Standard Rewards',
                'name_ar' => 'المكافآت القياسية',
                'type' => 'points',
                'points_per_currency' => 1,
                'currency_per_point' => 100,
                'min_redemption_points' => 100,
                'settings' => [
                    'tiers' => [
                        ['name' => 'Bronze', 'threshold' => 0],
                        ['name' => 'Silver', 'threshold' => 1000],
                        ['name' => 'Gold', 'threshold' => 5000],
                    ],
                ],
            ],
            [
                'name' => 'Premium Rewards',
                'name_ar' => 'المكافآت المميزة',
                'type' => 'points',
                'points_per_currency' => 2,
                'currency_per_point' => 50,
                'min_redemption_points' => 50,
                'settings' => [
                    'tiers' => [
                        ['name' => 'Silver', 'threshold' => 0],
                        ['name' => 'Gold', 'threshold' => 2500],
                        ['name' => 'Platinum', 'threshold' => 10000],
                    ],
                ],
            ],
        ];

        foreach ($programs as $program) {
            LoyaltyProgram::updateOrCreate(
                ['name' => $program['name']],
                array_merge($program, ['is_active' => true])
            );
        }
    }
}

<?php

namespace Gopos\Console\Commands;

use Gopos\Database\Seeders\CurrencySeeder;
use Gopos\Database\Seeders\DemoSeeder;
use Gopos\Models\Account;
use Gopos\Models\AccountType;
use Gopos\Models\Attendance;
use Gopos\Models\BankAccount;
use Gopos\Models\CostCenter;
use Gopos\Models\Currency;
use Gopos\Models\Department;
use Gopos\Models\Employee;
use Gopos\Models\FiscalPeriod;
use Gopos\Models\Holiday;
use Gopos\Models\LeaveBalance;
use Gopos\Models\LeaveType;
use Gopos\Models\LoyaltyProgram;
use Gopos\Models\PayrollComponent;
use Gopos\Models\PayrollPeriod;
use Gopos\Models\Position;
use Gopos\Models\User;
use Gopos\Models\Warehouse;
use Gopos\Models\WarehouseLocation;
use Gopos\Models\WorkSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'demo:seed
                            {--fresh : Wipe the database before seeding}
                            {--force : Force the operation to run in production}
                            {--module= : Seed only a specific module (hr, accounting, inventory, pos, all)}';

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
            'hr' => $this->seedHRModule(),
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
        $this->seedHRModule();
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

    protected function seedHRModule(): void
    {
        $this->components->task('Seeding Work Schedules', fn () => $this->seedWorkSchedules());
        $this->components->task('Seeding Departments', fn () => $this->seedDepartments());
        $this->components->task('Seeding Positions', fn () => $this->seedPositions());
        $this->components->task('Seeding Leave Types', fn () => $this->seedLeaveTypes());
        $this->components->task('Seeding Payroll Components', fn () => $this->seedPayrollComponents());
        $this->components->task('Seeding Employees', fn () => $this->seedEmployees());
        $this->components->task('Seeding Leave Balances', fn () => $this->seedLeaveBalances());
        $this->components->task('Seeding Holidays', fn () => $this->seedHolidays());
        $this->components->task('Seeding Attendance Records', fn () => $this->seedAttendance());
        $this->components->task('Seeding Payroll Periods', fn () => $this->seedPayrollPeriods());
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

    protected function seedWorkSchedules(): void
    {
        $schedules = [
            [
                'name' => 'Standard Office Hours',
                'name_ar' => 'ساعات العمل العادية',
                'work_start_time' => '08:00',
                'work_end_time' => '17:00',
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'working_hours' => 8,
                'working_days' => [0, 1, 2, 3, 4], // Sunday to Thursday
                'late_tolerance_minutes' => 15,
                'early_leave_tolerance_minutes' => 10,
                'is_default' => true,
            ],
            [
                'name' => 'Shift A (Morning)',
                'name_ar' => 'الوردية أ (صباحي)',
                'work_start_time' => '06:00',
                'work_end_time' => '14:00',
                'break_start_time' => '10:00',
                'break_end_time' => '10:30',
                'working_hours' => 8,
                'working_days' => [0, 1, 2, 3, 4, 5],
                'late_tolerance_minutes' => 10,
                'early_leave_tolerance_minutes' => 10,
                'is_default' => false,
            ],
            [
                'name' => 'Shift B (Evening)',
                'name_ar' => 'الوردية ب (مسائي)',
                'work_start_time' => '14:00',
                'work_end_time' => '22:00',
                'break_start_time' => '18:00',
                'break_end_time' => '18:30',
                'working_hours' => 8,
                'working_days' => [0, 1, 2, 3, 4, 5],
                'late_tolerance_minutes' => 10,
                'early_leave_tolerance_minutes' => 10,
                'is_default' => false,
            ],
        ];

        foreach ($schedules as $schedule) {
            WorkSchedule::updateOrCreate(
                ['name' => $schedule['name']],
                array_merge($schedule, ['is_active' => true])
            );
        }
    }

    protected function seedDepartments(): void
    {
        $costCenters = CostCenter::pluck('id', 'code');

        $departments = [
            ['code' => 'ADMIN', 'name' => 'Administration', 'name_ar' => 'الإدارة', 'cost_center' => 'CC-001'],
            ['code' => 'SALES', 'name' => 'Sales', 'name_ar' => 'المبيعات', 'cost_center' => 'CC-002'],
            ['code' => 'OPS', 'name' => 'Operations', 'name_ar' => 'العمليات', 'cost_center' => 'CC-003'],
            ['code' => 'IT', 'name' => 'Information Technology', 'name_ar' => 'تقنية المعلومات', 'cost_center' => 'CC-004'],
            ['code' => 'HR', 'name' => 'Human Resources', 'name_ar' => 'الموارد البشرية', 'cost_center' => 'CC-005'],
            ['code' => 'FIN', 'name' => 'Finance', 'name_ar' => 'المالية', 'cost_center' => 'CC-001'],
            ['code' => 'WH', 'name' => 'Warehouse', 'name_ar' => 'المستودع', 'cost_center' => 'CC-003'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'code' => $dept['code'],
                    'name' => $dept['name'],
                    'name_ar' => $dept['name_ar'],
                    'cost_center_id' => $costCenters[$dept['cost_center']] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedPositions(): void
    {
        $departments = Department::pluck('id', 'code');

        $positions = [
            ['code' => 'CEO', 'title' => 'Chief Executive Officer', 'title_ar' => 'الرئيس التنفيذي', 'dept' => 'ADMIN', 'min' => 5000000, 'max' => 10000000, 'level' => 1],
            ['code' => 'CFO', 'title' => 'Chief Financial Officer', 'title_ar' => 'المدير المالي', 'dept' => 'FIN', 'min' => 3500000, 'max' => 6000000, 'level' => 2],
            ['code' => 'HRM', 'title' => 'HR Manager', 'title_ar' => 'مدير الموارد البشرية', 'dept' => 'HR', 'min' => 2000000, 'max' => 4000000, 'level' => 3],
            ['code' => 'SM', 'title' => 'Sales Manager', 'title_ar' => 'مدير المبيعات', 'dept' => 'SALES', 'min' => 2000000, 'max' => 4000000, 'level' => 3],
            ['code' => 'ITM', 'title' => 'IT Manager', 'title_ar' => 'مدير تقنية المعلومات', 'dept' => 'IT', 'min' => 2500000, 'max' => 4500000, 'level' => 3],
            ['code' => 'WHM', 'title' => 'Warehouse Manager', 'title_ar' => 'مدير المستودع', 'dept' => 'WH', 'min' => 1500000, 'max' => 3000000, 'level' => 3],
            ['code' => 'ACC', 'title' => 'Accountant', 'title_ar' => 'محاسب', 'dept' => 'FIN', 'min' => 800000, 'max' => 1800000, 'level' => 4],
            ['code' => 'SR', 'title' => 'Sales Representative', 'title_ar' => 'مندوب مبيعات', 'dept' => 'SALES', 'min' => 600000, 'max' => 1500000, 'level' => 5],
            ['code' => 'CSH', 'title' => 'Cashier', 'title_ar' => 'أمين صندوق', 'dept' => 'SALES', 'min' => 500000, 'max' => 1000000, 'level' => 5],
            ['code' => 'WHW', 'title' => 'Warehouse Worker', 'title_ar' => 'عامل مستودع', 'dept' => 'WH', 'min' => 400000, 'max' => 800000, 'level' => 6],
            ['code' => 'ITS', 'title' => 'IT Support', 'title_ar' => 'دعم تقني', 'dept' => 'IT', 'min' => 700000, 'max' => 1500000, 'level' => 5],
            ['code' => 'HRA', 'title' => 'HR Assistant', 'title_ar' => 'مساعد موارد بشرية', 'dept' => 'HR', 'min' => 500000, 'max' => 1200000, 'level' => 5],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(
                ['code' => $pos['code']],
                [
                    'department_id' => $departments[$pos['dept']] ?? null,
                    'code' => $pos['code'],
                    'title' => $pos['title'],
                    'title_ar' => $pos['title_ar'],
                    'min_salary' => $pos['min'],
                    'max_salary' => $pos['max'],
                    'level' => $pos['level'],
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedLeaveTypes(): void
    {
        $types = [
            ['code' => 'AL', 'name' => 'Annual Leave', 'name_ar' => 'إجازة سنوية', 'days' => 21, 'paid' => true, 'carry' => true, 'max_carry' => 10, 'color' => '#22c55e'],
            ['code' => 'SL', 'name' => 'Sick Leave', 'name_ar' => 'إجازة مرضية', 'days' => 14, 'paid' => true, 'carry' => false, 'doc' => true, 'color' => '#ef4444'],
            ['code' => 'ML', 'name' => 'Maternity Leave', 'name_ar' => 'إجازة أمومة', 'days' => 90, 'paid' => true, 'carry' => false, 'color' => '#ec4899'],
            ['code' => 'PL', 'name' => 'Paternity Leave', 'name_ar' => 'إجازة أبوة', 'days' => 5, 'paid' => true, 'carry' => false, 'color' => '#3b82f6'],
            ['code' => 'UL', 'name' => 'Unpaid Leave', 'name_ar' => 'إجازة بدون راتب', 'days' => 30, 'paid' => false, 'carry' => false, 'color' => '#6b7280'],
            ['code' => 'CL', 'name' => 'Compassionate Leave', 'name_ar' => 'إجازة عزاء', 'days' => 5, 'paid' => true, 'carry' => false, 'color' => '#8b5cf6'],
            ['code' => 'MR', 'name' => 'Marriage Leave', 'name_ar' => 'إجازة زواج', 'days' => 5, 'paid' => true, 'carry' => false, 'color' => '#f59e0b'],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'code' => $type['code'],
                    'name' => $type['name'],
                    'name_ar' => $type['name_ar'],
                    'default_days_per_year' => $type['days'],
                    'is_paid' => $type['paid'],
                    'can_carry_forward' => $type['carry'] ?? false,
                    'max_carry_forward_days' => $type['max_carry'] ?? 0,
                    'requires_approval' => true,
                    'requires_document' => $type['doc'] ?? false,
                    'color' => $type['color'],
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedPayrollComponents(): void
    {
        $components = [
            // Earnings
            ['code' => 'BASIC', 'name' => 'Basic Salary', 'name_ar' => 'الراتب الأساسي', 'type' => 'earning', 'calc' => 'fixed', 'mandatory' => true, 'taxable' => true, 'order' => 1],
            ['code' => 'HOUSING', 'name' => 'Housing Allowance', 'name_ar' => 'بدل السكن', 'type' => 'earning', 'calc' => 'percentage', 'pct' => 25, 'mandatory' => false, 'taxable' => true, 'order' => 2],
            ['code' => 'TRANS', 'name' => 'Transportation Allowance', 'name_ar' => 'بدل المواصلات', 'type' => 'earning', 'calc' => 'fixed', 'amount' => 100000, 'mandatory' => false, 'taxable' => true, 'order' => 3],
            ['code' => 'FOOD', 'name' => 'Food Allowance', 'name_ar' => 'بدل الطعام', 'type' => 'earning', 'calc' => 'fixed', 'amount' => 75000, 'mandatory' => false, 'taxable' => true, 'order' => 4],
            ['code' => 'OT', 'name' => 'Overtime', 'name_ar' => 'العمل الإضافي', 'type' => 'earning', 'calc' => 'fixed', 'mandatory' => false, 'taxable' => true, 'order' => 5],
            ['code' => 'BONUS', 'name' => 'Bonus', 'name_ar' => 'مكافأة', 'type' => 'earning', 'calc' => 'fixed', 'mandatory' => false, 'taxable' => true, 'order' => 6],

            // Deductions
            ['code' => 'TAX', 'name' => 'Income Tax', 'name_ar' => 'ضريبة الدخل', 'type' => 'deduction', 'calc' => 'percentage', 'pct' => 5, 'mandatory' => true, 'taxable' => false, 'order' => 10],
            ['code' => 'SS', 'name' => 'Social Security', 'name_ar' => 'التأمينات الاجتماعية', 'type' => 'deduction', 'calc' => 'percentage', 'pct' => 5, 'mandatory' => true, 'taxable' => false, 'order' => 11],
            ['code' => 'LOAN', 'name' => 'Loan Deduction', 'name_ar' => 'خصم قرض', 'type' => 'deduction', 'calc' => 'fixed', 'mandatory' => false, 'taxable' => false, 'order' => 12],
            ['code' => 'ADV', 'name' => 'Salary Advance', 'name_ar' => 'سلفة راتب', 'type' => 'deduction', 'calc' => 'fixed', 'mandatory' => false, 'taxable' => false, 'order' => 13],
            ['code' => 'ABS', 'name' => 'Absence Deduction', 'name_ar' => 'خصم غياب', 'type' => 'deduction', 'calc' => 'fixed', 'mandatory' => false, 'taxable' => false, 'order' => 14],
        ];

        foreach ($components as $comp) {
            PayrollComponent::updateOrCreate(
                ['code' => $comp['code']],
                [
                    'code' => $comp['code'],
                    'name' => $comp['name'],
                    'name_ar' => $comp['name_ar'],
                    'type' => $comp['type'],
                    'calculation_type' => $comp['calc'],
                    'default_amount' => $comp['amount'] ?? 0,
                    'percentage' => $comp['pct'] ?? 0,
                    'is_taxable' => $comp['taxable'],
                    'is_mandatory' => $comp['mandatory'],
                    'applies_to_all' => $comp['mandatory'],
                    'sort_order' => $comp['order'],
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedEmployees(): void
    {
        $currency = Currency::where('base', true)->first() ?? Currency::first();
        $defaultSchedule = WorkSchedule::where('is_default', true)->first();
        $departments = Department::pluck('id', 'code');
        $positions = Position::pluck('id', 'code');

        // Create users for employees
        $employeeUsers = [
            ['name' => 'Ahmad Kareem', 'email' => 'ahmad.kareem@demo.com'],
            ['name' => 'Sara Hussein', 'email' => 'sara.hussein@demo.com'],
            ['name' => 'Mohammed Ali', 'email' => 'mohammed.ali@demo.com'],
            ['name' => 'Fatima Omar', 'email' => 'fatima.omar@demo.com'],
            ['name' => 'Karwan Jamal', 'email' => 'karwan.jamal@demo.com'],
            ['name' => 'Noor Ahmed', 'email' => 'noor.ahmed@demo.com'],
            ['name' => 'Omar Hassan', 'email' => 'omar.hassan@demo.com'],
            ['name' => 'Layla Mustafa', 'email' => 'layla.mustafa@demo.com'],
            ['name' => 'Hussein Abbas', 'email' => 'hussein.abbas@demo.com'],
            ['name' => 'Zainab Rashid', 'email' => 'zainab.rashid@demo.com'],
        ];

        $userIds = [];
        foreach ($employeeUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'active' => true,
                ])
            );
            $userIds[$userData['email']] = $user->id;
        }

        $employees = [
            ['first' => 'Ahmad', 'last' => 'Kareem', 'email' => 'ahmad.kareem@demo.com', 'dept' => 'ADMIN', 'pos' => 'CEO', 'salary' => 7500000, 'type' => 'full_time'],
            ['first' => 'Sara', 'last' => 'Hussein', 'email' => 'sara.hussein@demo.com', 'dept' => 'FIN', 'pos' => 'CFO', 'salary' => 4500000, 'type' => 'full_time'],
            ['first' => 'Mohammed', 'last' => 'Ali', 'email' => 'mohammed.ali@demo.com', 'dept' => 'IT', 'pos' => 'ITM', 'salary' => 3500000, 'type' => 'full_time'],
            ['first' => 'Fatima', 'last' => 'Omar', 'email' => 'fatima.omar@demo.com', 'dept' => 'HR', 'pos' => 'HRM', 'salary' => 3000000, 'type' => 'full_time'],
            ['first' => 'Karwan', 'last' => 'Jamal', 'email' => 'karwan.jamal@demo.com', 'dept' => 'SALES', 'pos' => 'SM', 'salary' => 3000000, 'type' => 'full_time'],
            ['first' => 'Noor', 'last' => 'Ahmed', 'email' => 'noor.ahmed@demo.com', 'dept' => 'FIN', 'pos' => 'ACC', 'salary' => 1200000, 'type' => 'full_time'],
            ['first' => 'Omar', 'last' => 'Hassan', 'email' => 'omar.hassan@demo.com', 'dept' => 'SALES', 'pos' => 'SR', 'salary' => 900000, 'type' => 'full_time'],
            ['first' => 'Layla', 'last' => 'Mustafa', 'email' => 'layla.mustafa@demo.com', 'dept' => 'SALES', 'pos' => 'CSH', 'salary' => 700000, 'type' => 'full_time'],
            ['first' => 'Hussein', 'last' => 'Abbas', 'email' => 'hussein.abbas@demo.com', 'dept' => 'WH', 'pos' => 'WHW', 'salary' => 600000, 'type' => 'full_time'],
            ['first' => 'Zainab', 'last' => 'Rashid', 'email' => 'zainab.rashid@demo.com', 'dept' => 'IT', 'pos' => 'ITS', 'salary' => 1000000, 'type' => 'contract'],
        ];

        foreach ($employees as $emp) {
            Employee::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'user_id' => $userIds[$emp['email']] ?? null,
                    'department_id' => $departments[$emp['dept']] ?? null,
                    'position_id' => $positions[$emp['pos']] ?? null,
                    'work_schedule_id' => $defaultSchedule?->id,
                    'currency_id' => $currency?->id,
                    'first_name' => $emp['first'],
                    'last_name' => $emp['last'],
                    'email' => $emp['email'],
                    'phone' => '+964 750 '.rand(100, 999).' '.rand(1000, 9999),
                    'gender' => $emp['first'] === 'Sara' || $emp['first'] === 'Fatima' || $emp['first'] === 'Noor' || $emp['first'] === 'Layla' || $emp['first'] === 'Zainab' ? 'female' : 'male',
                    'birth_date' => now()->subYears(rand(25, 45))->subDays(rand(1, 365)),
                    'hire_date' => now()->subYears(rand(1, 5))->subDays(rand(1, 365)),
                    'employment_type' => $emp['type'],
                    'basic_salary' => $emp['salary'],
                    'salary_type' => 'monthly',
                    'status' => Employee::STATUS_ACTIVE,
                    'nationality' => 'Iraqi',
                    'address' => 'Erbil, Kurdistan Region, Iraq',
                    'city' => 'Erbil',
                    'country' => 'Iraq',
                ]
            );
        }
    }

    protected function seedLeaveBalances(): void
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $entitledDays = $leaveType->default_days_per_year ?? 0;
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'entitled_days' => $entitledDays,
                        'used_days' => rand(0, min(5, (int) $entitledDays)),
                        'pending_days' => 0,
                        'carried_forward' => $leaveType->can_carry_forward ? rand(0, 5) : 0,
                        'adjustment' => 0,
                    ]
                );
            }
        }
    }

    protected function seedHolidays(): void
    {
        $year = now()->year;

        $holidays = [
            ['name' => 'New Year', 'name_ar' => 'رأس السنة', 'date' => "{$year}-01-01", 'type' => Holiday::TYPE_PUBLIC, 'recurring' => true],
            ['name' => 'Kurdish New Year (Newroz)', 'name_ar' => 'عيد نوروز', 'date' => "{$year}-03-21", 'type' => Holiday::TYPE_PUBLIC, 'recurring' => true],
            ['name' => 'Labour Day', 'name_ar' => 'عيد العمال', 'date' => "{$year}-05-01", 'type' => Holiday::TYPE_PUBLIC, 'recurring' => true],
            ['name' => 'Eid al-Fitr Day 1', 'name_ar' => 'عيد الفطر اليوم 1', 'date' => "{$year}-04-10", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Fitr Day 2', 'name_ar' => 'عيد الفطر اليوم 2', 'date' => "{$year}-04-11", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Fitr Day 3', 'name_ar' => 'عيد الفطر اليوم 3', 'date' => "{$year}-04-12", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Adha Day 1', 'name_ar' => 'عيد الأضحى اليوم 1', 'date' => "{$year}-06-16", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Adha Day 2', 'name_ar' => 'عيد الأضحى اليوم 2', 'date' => "{$year}-06-17", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Adha Day 3', 'name_ar' => 'عيد الأضحى اليوم 3', 'date' => "{$year}-06-18", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Eid al-Adha Day 4', 'name_ar' => 'عيد الأضحى اليوم 4', 'date' => "{$year}-06-19", 'type' => Holiday::TYPE_RELIGIOUS, 'recurring' => false],
            ['name' => 'Iraqi Independence Day', 'name_ar' => 'عيد الاستقلال', 'date' => "{$year}-10-03", 'type' => Holiday::TYPE_PUBLIC, 'recurring' => true],
            ['name' => 'Company Foundation Day', 'name_ar' => 'يوم تأسيس الشركة', 'date' => "{$year}-06-01", 'type' => Holiday::TYPE_COMPANY, 'recurring' => true],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'name_ar' => $holiday['name_ar'],
                    'date' => $holiday['date'],
                    'type' => $holiday['type'],
                    'is_recurring' => $holiday['recurring'],
                    'is_paid' => true,
                ]
            );
        }
    }

    protected function seedAttendance(): void
    {
        $employees = Employee::where('status', Employee::STATUS_ACTIVE)->get();
        $defaultSchedule = WorkSchedule::where('is_default', true)->first();

        if (! $defaultSchedule) {
            return;
        }

        // Generate attendance for the last 30 days
        for ($i = 30; $i >= 1; $i--) {
            $date = now()->subDays($i);

            // Skip if it's not a working day (Friday/Saturday for Iraq)
            $dayOfWeek = $date->dayOfWeek; // 0 = Sunday
            if (! in_array($dayOfWeek, $defaultSchedule->working_days ?? [])) {
                continue;
            }

            // Skip if it's a holiday
            if (Holiday::isHoliday($date)) {
                continue;
            }

            foreach ($employees as $employee) {
                // 90% chance of attendance
                if (rand(1, 100) > 90) {
                    continue;
                }

                // Random clock in time around 8:00
                $clockInHour = 8;
                $clockInMinute = rand(-10, 30); // Some early, some late
                if ($clockInMinute < 0) {
                    $clockInMinute = 60 + $clockInMinute;
                    $clockInHour = 7;
                }

                $clockIn = $date->copy()->setTime($clockInHour, abs($clockInMinute));

                // Random clock out time around 17:00
                $clockOutHour = 17;
                $clockOutMinute = rand(-15, 30);
                if ($clockOutMinute < 0) {
                    $clockOutMinute = 60 + $clockOutMinute;
                    $clockOutHour = 16;
                }

                $clockOut = $date->copy()->setTime($clockOutHour, abs($clockOutMinute));

                // Calculate worked hours
                $breakStart = $date->copy()->setTime(12, 0);
                $breakEnd = $date->copy()->setTime(13, 0);
                $workedMinutes = $clockIn->diffInMinutes($clockOut);
                $breakMinutes = 60; // 1 hour break
                $workedHours = ($workedMinutes - $breakMinutes) / 60;

                // Determine if late
                $scheduleStart = $date->copy()->setTime(8, 0);
                $isLate = $clockIn->gt($scheduleStart->copy()->addMinutes(15));
                $lateMinutes = $isLate ? $clockIn->diffInMinutes($scheduleStart) : 0;

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'clock_in' => $clockIn->format('H:i'),
                        'clock_out' => $clockOut->format('H:i'),
                        'break_start' => $breakStart->format('H:i'),
                        'break_end' => $breakEnd->format('H:i'),
                        'worked_hours' => round($workedHours, 2),
                        'overtime_hours' => max(0, round($workedHours - 8, 2)),
                        'status' => 'present',
                        'is_late' => $isLate,
                        'late_minutes' => $lateMinutes,
                        'early_leave' => false,
                        'early_leave_minutes' => 0,
                        'notes' => null,
                    ]
                );
            }
        }
    }

    protected function seedPayrollPeriods(): void
    {
        // Create payroll periods for the last 3 months
        for ($i = 3; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $status = match (true) {
                $i === 0 => PayrollPeriod::STATUS_DRAFT,
                $i === 1 => PayrollPeriod::STATUS_PROCESSED,
                default => PayrollPeriod::STATUS_PAID,
            };

            PayrollPeriod::updateOrCreate(
                ['year' => $year, 'month' => $month],
                [
                    'name' => $date->format('F Y'),
                    'year' => $year,
                    'month' => $month,
                    'start_date' => $date->copy()->startOfMonth(),
                    'end_date' => $date->copy()->endOfMonth(),
                    'payment_date' => $date->copy()->endOfMonth()->addDays(5),
                    'status' => $status,
                ]
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

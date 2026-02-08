<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'sales',
        'sale_returns',
        'customers',
        'purchases',
        'purchase_returns',
        'suppliers',
        'products',
        'categories',
        'warehouses',
        'stock_transfers',
        'stock_counts',
        'inventory_movements',
        'accounts',
        'journal_entries',
        'incomes',
        'expenses',
        'income_types',
        'expense_types',
        'bank_accounts',
        'budgets',
        'cost_centers',
        'employees',
        'departments',
        'positions',
        'leaves',
        'payrolls',
        'pos_sessions',
        'held_sales',
        'product_batches',
        'product_serials',
        'loyalty_programs',
        'customer_loyalty',
        'bank_reconciliations',
        'journal_entry_templates',
    ];

    public function up(): void
    {
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Main Branch',
            'name_ar' => 'الفرع الرئيسي',
            'name_ckb' => 'لقی سەرەکی',
            'code' => 'MAIN',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign all existing users to the default branch
        $userIds = DB::table('users')->pluck('id');
        $records = $userIds->map(fn ($userId) => [
            'branch_id' => $branchId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if (! empty($records)) {
            DB::table('branch_user')->insert($records);
        }

        // Assign all existing records to the default branch
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'branch_id')) {
                DB::table($table)->whereNull('branch_id')->update(['branch_id' => $branchId]);
            }
        }
    }

    public function down(): void
    {
        // Reset branch_id to null on all tables
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'branch_id')) {
                DB::table($table)->update(['branch_id' => null]);
            }
        }

        DB::table('branch_user')->truncate();
        DB::table('branches')->where('code', 'MAIN')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        // Sales cluster
        'sales',
        'sale_returns',
        'customers',
        // Purchases cluster
        'purchases',
        'purchase_returns',
        'suppliers',
        // Inventory cluster
        'products',
        'categories',
        'warehouses',
        'stock_transfers',
        'stock_counts',
        'inventory_movements',
        // Accounting cluster
        'accounts',
        'journal_entries',
        'incomes',
        'expenses',
        'income_types',
        'expense_types',
        'bank_accounts',
        'budgets',
        'cost_centers',
        // HR cluster
        'employees',
        'departments',
        'positions',
        'leaves',
        'payrolls',
        // POS / Other
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
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
                    $blueprint->index('branch_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropForeign([$table.'_branch_id_foreign']);
                    $blueprint->dropIndex([$table.'_branch_id_index']);
                    $blueprint->dropColumn('branch_id');
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that have team_id column.
     */
    protected array $tablesWithTeamId = [
        'products',
        'categories',
        'units',
        'customers',
        'suppliers',
        'sales',
        'sale_items',
        'sale_returns',
        'sale_return_items',
        'purchases',
        'purchase_items',
        'purchase_returns',
        'purchase_return_items',
        'expenses',
        'expense_types',
        'incomes',
        'income_types',
        'inventory_movements',
        'payments',
        'currencies',
        'product_attributes',
        'tax_codes',
        'tax_exemptions',
        'accounts',
        'account_types',
        'fiscal_periods',
        'journal_entries',
        'journal_entry_lines',
        'audit_logs',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop subscriptions table (has foreign keys to teams and plans)
        Schema::dropIfExists('subscriptions');

        // Drop plans table
        Schema::dropIfExists('plans');

        // Update sales table - remove team_id from composite unique and drop team_id
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'team_id')) {
            // Drop the composite unique index first
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->dropUnique(['sale_number', 'team_id']);
                });
            } catch (\Exception $e) {
                // Index might not exist
            }

            // Add back simple unique on sale_number if not exists
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->unique('sale_number');
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        // Update purchases table - remove team_id from composite unique and drop team_id
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'team_id')) {
            try {
                Schema::table('purchases', function (Blueprint $table) {
                    $table->dropUnique(['purchase_number', 'team_id']);
                });
            } catch (\Exception $e) {
                // Index might not exist
            }

            try {
                Schema::table('purchases', function (Blueprint $table) {
                    $table->unique('purchase_number');
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        // Remove team_id from all business tables
        foreach ($this->tablesWithTeamId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'team_id')) {
                // Drop all indexes that include team_id column (must be done first)
                $this->dropIndexesContainingColumn($tableName, 'team_id');

                // Drop foreign key constraint using raw SQL to avoid naming issues
                $this->dropForeignKeyIfExists($tableName, 'team_id');

                // Drop the column using raw SQL to avoid any caching issues
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `team_id`");
                } catch (\Exception $e) {
                    // Column might already be dropped or have other issues
                    // Log it but continue
                }
            }
        }

        // Remove latest_team_id from users table
        if (Schema::hasColumn('users', 'latest_team_id')) {
            $this->dropForeignKeyIfExists('users', 'latest_team_id');

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('latest_team_id');
            });
        }

        // Remove is_superadmin from users table if exists
        if (Schema::hasColumn('users', 'is_superadmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_superadmin');
            });
        }

        // Drop team_user pivot table
        Schema::dropIfExists('team_user');

        // Drop teams table
        Schema::dropIfExists('teams');
    }

    /**
     * Drop all indexes that contain the specified column
     */
    protected function dropIndexesContainingColumn(string $table, string $column): void
    {
        // Get all indexes that contain this column (excluding PRIMARY)
        $indexes = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND INDEX_NAME != 'PRIMARY'
        ", [$table, $column]);

        foreach ($indexes as $index) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index->INDEX_NAME}`");
            } catch (\Exception $e) {
                // Index might already be dropped
            }
        }
    }

    /**
     * Drop foreign key if it exists (MySQL specific)
     */
    protected function dropForeignKeyIfExists(string $table, string $column): void
    {
        $foreignKeyName = $table.'_'.$column.'_foreign';

        // Check if foreign key exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME = ?
        ", [$table, $foreignKeyName]);

        if (count($foreignKeys) > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKeyName}`");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it removes significant functionality
        // To restore, you would need to re-run the original migrations
        throw new \Exception('This migration cannot be reversed. Please restore from backup if needed.');
    }
};

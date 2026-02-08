<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tables without default value
        $tablesNoDefault = [
            'currencies',
            'sales',
            'purchases',
            'payments',
            'expenses',
            'incomes',
            'purchase_returns',
            'sale_returns',
        ];

        foreach ($tablesNoDefault as $table) {
            if (Schema::hasColumn($table, 'exchange_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->decimal('exchange_rate', 20, 12)->change();
                });
            }
        }

        // Tables with default(1)
        $tablesWithDefault = [
            'journal_entries',
            'sale_payments',
            'pos_session_transactions',
        ];

        foreach ($tablesWithDefault as $table) {
            if (Schema::hasColumn($table, 'exchange_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->decimal('exchange_rate', 20, 12)->default(1)->change();
                });
            }
        }
    }

    public function down(): void
    {
        $tablesWithDefault = [
            'journal_entries',
            'sale_payments',
            'pos_session_transactions',
        ];

        foreach ($tablesWithDefault as $table) {
            if (Schema::hasColumn($table, 'exchange_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->decimal('exchange_rate', 10, 6)->default(1)->change();
                });
            }
        }

        $tablesNoDefault = [
            'currencies',
            'sales',
            'purchases',
            'payments',
            'expenses',
            'incomes',
            'purchase_returns',
            'sale_returns',
        ];

        foreach ($tablesNoDefault as $table) {
            if (Schema::hasColumn($table, 'exchange_rate')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->decimal('exchange_rate', 10, 2)->change();
                });
            }
        }
    }
};

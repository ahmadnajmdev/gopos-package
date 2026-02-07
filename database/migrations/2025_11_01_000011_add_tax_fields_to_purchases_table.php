<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'tax_code_id')) {
                $table->foreignId('tax_code_id')->nullable()->after('currency_id')->constrained('tax_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchases', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 4)->nullable()->after('tax_code_id');
            }
            if (! Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
            if (! Schema::hasColumn('purchases', 'tax_amount_in_base_currency')) {
                $table->decimal('tax_amount_in_base_currency', 15, 2)->default(0)->after('tax_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'tax_code_id')) {
                $table->dropForeign(['tax_code_id']);
                $table->dropColumn('tax_code_id');
            }
            if (Schema::hasColumn('purchases', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
            if (Schema::hasColumn('purchases', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('purchases', 'tax_amount_in_base_currency')) {
                $table->dropColumn('tax_amount_in_base_currency');
            }
        });
    }
};

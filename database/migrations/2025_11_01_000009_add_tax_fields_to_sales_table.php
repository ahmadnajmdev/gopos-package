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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('tax_code_id')->nullable()->after('currency_id')->constrained('tax_codes')->nullOnDelete();
            $table->decimal('tax_rate', 5, 4)->nullable()->after('tax_code_id');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            $table->decimal('tax_amount_in_base_currency', 15, 2)->default(0)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn(['tax_code_id', 'tax_rate', 'tax_amount', 'tax_amount_in_base_currency']);
        });
    }
};

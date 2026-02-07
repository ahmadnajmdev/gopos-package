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
        Schema::table('purchase_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_items', 'tax_code_id')) {
                $table->foreignId('tax_code_id')->nullable()->after('discount_amount')->constrained('tax_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 4)->default(0)->after('tax_code_id');
            }
            // tax_amount already exists in original table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'tax_code_id')) {
                $table->dropForeign(['tax_code_id']);
                $table->dropColumn('tax_code_id');
            }
            if (Schema::hasColumn('purchase_items', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
            // tax_amount is from original table, don't drop it
        });
    }
};

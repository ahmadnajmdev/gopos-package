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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'default_tax_code_id')) {
                $table->foreignId('default_tax_code_id')->nullable()->after('category_id')->constrained('tax_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'is_tax_exempt')) {
                $table->boolean('is_tax_exempt')->default(false)->after('default_tax_code_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'default_tax_code_id')) {
                $table->dropForeign(['default_tax_code_id']);
                $table->dropColumn('default_tax_code_id');
            }
            if (Schema::hasColumn('products', 'is_tax_exempt')) {
                $table->dropColumn('is_tax_exempt');
            }
        });
    }
};

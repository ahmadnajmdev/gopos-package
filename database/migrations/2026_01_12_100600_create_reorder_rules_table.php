<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reorder_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('minimum_quantity', 15, 4);
            $table->decimal('maximum_quantity', 15, 4)->nullable();
            $table->decimal('reorder_point', 15, 4);
            $table->decimal('reorder_quantity', 15, 4);
            $table->integer('lead_time_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_create_po')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });

        // Add costing method to products
        Schema::table('products', function (Blueprint $table) {
            $table->enum('costing_method', ['fifo', 'lifo', 'avco', 'specific'])->default('avco')->after('warranty_months');
            $table->decimal('average_cost', 15, 4)->default(0)->after('costing_method');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['costing_method', 'average_cost']);
        });

        Schema::dropIfExists('reorder_rules');
    }
};

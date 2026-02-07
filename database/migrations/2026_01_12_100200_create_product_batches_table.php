<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'batch_number', 'warehouse_id']);
        });

        // Add batch tracking to inventory_movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('location_id')->constrained('product_batches')->nullOnDelete();
        });

        // Add batch tracking flag to products
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_batches')->default(false)->after('low_stock_alert');
            $table->boolean('has_expiry')->default(false)->after('track_batches');
            $table->integer('expiry_warning_days')->default(30)->after('has_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_batches', 'has_expiry', 'expiry_warning_days']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::dropIfExists('product_batches');
    }
};

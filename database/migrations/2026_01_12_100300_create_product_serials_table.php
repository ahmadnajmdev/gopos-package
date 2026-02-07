<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'sold', 'reserved', 'damaged', 'returned'])->default('available');
            $table->decimal('cost', 15, 4)->default(0);
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained()->nullOnDelete();
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });

        // Add serial tracking to inventory_movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->json('serial_ids')->nullable()->after('batch_id');
        });

        // Add serial tracking flag to products
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_serials')->default(false)->after('has_expiry');
            $table->integer('warranty_months')->nullable()->after('track_serials');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_serials', 'warranty_months']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn('serial_ids');
        });

        Schema::dropIfExists('product_serials');
    }
};

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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'mobile_payment', 'credit'])->default('cash');
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->constrained();
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->decimal('amount_in_base_currency', 15, 2);
            $table->string('reference_number')->nullable(); // Card auth code, transfer ref, etc.
            $table->decimal('tendered_amount', 15, 2)->nullable(); // Amount given by customer (for cash)
            $table->decimal('change_amount', 15, 2)->nullable(); // Change returned
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

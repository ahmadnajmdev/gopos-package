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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('terminal_id', 100)->nullable();
            $table->timestamp('opening_time');
            $table->timestamp('closing_time')->nullable();
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed', 'suspended'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('opening_time');
        });

        Schema::create('pos_session_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_session_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sale', 'refund', 'cash_in', 'cash_out', 'expense'])->default('sale');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'mobile_payment', 'credit'])->default('cash');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['pos_session_id', 'type']);
        });

        // Add pos_session_id to sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('pos_session_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn('pos_session_id');
        });

        Schema::dropIfExists('pos_session_transactions');
        Schema::dropIfExists('pos_sessions');
    }
};

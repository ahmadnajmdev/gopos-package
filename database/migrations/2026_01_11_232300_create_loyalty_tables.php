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
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->enum('type', ['points', 'cashback', 'tiered'])->default('points');
            $table->decimal('points_per_currency', 10, 4)->default(1); // Points earned per base currency unit
            $table->decimal('currency_per_point', 10, 4)->default(0.01); // Value of each point
            $table->integer('min_redemption_points')->default(100);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Tier thresholds, bonus multipliers, etc.
            $table->timestamps();
        });

        Schema::create('customer_loyalty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_program_id')->constrained()->cascadeOnDelete();
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->string('tier', 50)->nullable(); // 'bronze', 'silver', 'gold', 'platinum'
            $table->timestamp('tier_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'loyalty_program_id']);
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_loyalty_id')->constrained('customer_loyalty')->cascadeOnDelete();
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjust', 'bonus'])->default('earn');
            $table->integer('points');
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customer_loyalty');
        Schema::dropIfExists('loyalty_programs');
    }
};

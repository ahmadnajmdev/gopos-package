<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])
                ->default('pending')
                ->after('paid_amount');
        });

        // Update existing sales based on paid_amount
        DB::statement("
            UPDATE sales
            SET status = CASE
                WHEN paid_amount >= total_amount THEN 'paid'
                WHEN paid_amount > 0 THEN 'partial'
                ELSE 'pending'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

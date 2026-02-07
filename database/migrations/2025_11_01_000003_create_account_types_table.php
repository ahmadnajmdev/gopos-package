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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Seed default account types
        DB::table('account_types')->insert([
            ['id' => 1, 'name' => 'Asset', 'name_ar' => 'الأصول', 'normal_balance' => 'debit', 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Liability', 'name_ar' => 'الخصوم', 'normal_balance' => 'credit', 'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Equity', 'name_ar' => 'حقوق الملكية', 'normal_balance' => 'credit', 'display_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Revenue', 'name_ar' => 'الإيرادات', 'normal_balance' => 'credit', 'display_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Expense', 'name_ar' => 'المصروفات', 'normal_balance' => 'debit', 'display_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};

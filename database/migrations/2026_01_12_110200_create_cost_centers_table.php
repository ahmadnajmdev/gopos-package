<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['department', 'project', 'location', 'product_line', 'other'])->default('department');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add cost_center_id to journal_entry_lines
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->foreignId('cost_center_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });

        // Add cost_center_id to expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('cost_center_id')->nullable()->after('expense_type_id')->constrained()->nullOnDelete();
        });

        // Add cost_center_id to incomes
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('cost_center_id')->nullable()->after('income_type_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });

        Schema::dropIfExists('cost_centers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('fiscal_period_id')->constrained()->cascadeOnDelete();
            $table->enum('budget_type', ['operating', 'capital', 'cash_flow', 'project'])->default('operating');
            $table->enum('status', ['draft', 'submitted', 'approved', 'active', 'closed'])->default('draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('january', 15, 2)->default(0);
            $table->decimal('february', 15, 2)->default(0);
            $table->decimal('march', 15, 2)->default(0);
            $table->decimal('april', 15, 2)->default(0);
            $table->decimal('may', 15, 2)->default(0);
            $table->decimal('june', 15, 2)->default(0);
            $table->decimal('july', 15, 2)->default(0);
            $table->decimal('august', 15, 2)->default(0);
            $table->decimal('september', 15, 2)->default(0);
            $table->decimal('october', 15, 2)->default(0);
            $table->decimal('november', 15, 2)->default(0);
            $table->decimal('december', 15, 2)->default(0);
            $table->decimal('annual_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['budget_id', 'account_id', 'cost_center_id']);
        });

        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->integer('revision_number');
            $table->string('reason');
            $table->decimal('previous_total', 15, 2);
            $table->decimal('new_total', 15, 2);
            $table->json('changes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_revisions');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
    }
};

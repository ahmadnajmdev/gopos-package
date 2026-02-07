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
        Schema::create('journal_entry_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('journal_entry_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('description')->nullable();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('is_percentage')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_template_lines');
        Schema::dropIfExists('journal_entry_templates');
    }
};

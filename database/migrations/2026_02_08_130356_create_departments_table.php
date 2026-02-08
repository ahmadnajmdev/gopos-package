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
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (! Schema::hasColumn('departments', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name');
                }
                if (! Schema::hasColumn('departments', 'name_ckb')) {
                    $table->string('name_ckb')->nullable()->after('name_ar');
                }
                if (! Schema::hasColumn('departments', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('name_ckb')->constrained('departments')->cascadeOnDelete();
                }
                if (! Schema::hasColumn('departments', 'description')) {
                    $table->text('description')->nullable()->after('parent_id');
                }
                if (! Schema::hasColumn('departments', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
            });

            return;
        }

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

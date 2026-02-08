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
        if (Schema::hasTable('positions')) {
            Schema::table('positions', function (Blueprint $table) {
                if (! Schema::hasColumn('positions', 'title_ar')) {
                    $table->string('title_ar')->nullable()->after('title');
                }
                if (! Schema::hasColumn('positions', 'title_ckb')) {
                    $table->string('title_ckb')->nullable()->after('title_ar');
                }
                if (! Schema::hasColumn('positions', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->after('title_ckb')->constrained('departments')->nullOnDelete();
                }
                if (! Schema::hasColumn('positions', 'min_salary')) {
                    $table->decimal('min_salary', 12, 2)->nullable()->after('department_id');
                }
                if (! Schema::hasColumn('positions', 'max_salary')) {
                    $table->decimal('max_salary', 12, 2)->nullable()->after('min_salary');
                }
                if (! Schema::hasColumn('positions', 'description')) {
                    $table->text('description')->nullable()->after('max_salary');
                }
                if (! Schema::hasColumn('positions', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
            });

            return;
        }

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('title_ckb')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->decimal('min_salary', 12, 2)->nullable();
            $table->decimal('max_salary', 12, 2)->nullable();
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
        Schema::dropIfExists('positions');
    }
};

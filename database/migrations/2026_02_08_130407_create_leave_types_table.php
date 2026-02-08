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
        if (Schema::hasTable('leave_types')) {
            Schema::table('leave_types', function (Blueprint $table) {
                if (! Schema::hasColumn('leave_types', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name');
                }
                if (! Schema::hasColumn('leave_types', 'name_ckb')) {
                    $table->string('name_ckb')->nullable()->after('name_ar');
                }
                if (! Schema::hasColumn('leave_types', 'days_allowed')) {
                    $table->integer('days_allowed')->default(0)->after('name_ckb');
                }
                if (! Schema::hasColumn('leave_types', 'is_paid')) {
                    $table->boolean('is_paid')->default(true)->after('days_allowed');
                }
                if (! Schema::hasColumn('leave_types', 'color')) {
                    $table->string('color')->nullable()->after('is_paid');
                }
                if (! Schema::hasColumn('leave_types', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('color');
                }
            });

            return;
        }

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->integer('days_allowed')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

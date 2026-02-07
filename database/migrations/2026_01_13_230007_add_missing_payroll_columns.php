<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing column to payroll_components
        Schema::table('payroll_components', function (Blueprint $table) {
            $table->boolean('applies_to_all')->default(true)->after('is_mandatory');
        });

        // Add missing columns to payroll_periods
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->after('name');
            $table->unsignedTinyInteger('month')->nullable()->after('year');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete()->after('processed_by');
            $table->timestamp('paid_at')->nullable()->after('paid_by');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['year', 'month', 'processed_by', 'paid_by', 'paid_at']);
        });

        Schema::table('payroll_components', function (Blueprint $table) {
            $table->dropColumn('applies_to_all');
        });
    }
};

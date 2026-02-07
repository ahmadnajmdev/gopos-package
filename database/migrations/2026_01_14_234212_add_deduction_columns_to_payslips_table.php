<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('absent_deduction', 15, 2)->default(0)->after('absent_days');
            $table->decimal('loan_deduction', 15, 2)->default(0)->after('overtime_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['absent_deduction', 'loan_deduction']);
        });
    }
};

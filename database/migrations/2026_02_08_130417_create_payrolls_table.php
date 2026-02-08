<?php

use Gopos\Enums\PayrollStatus;
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
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (! Schema::hasColumn('payrolls', 'employee_id')) {
                    $table->foreignId('employee_id')->after('id')->constrained('employees')->cascadeOnDelete();
                }
                if (! Schema::hasColumn('payrolls', 'pay_period_start')) {
                    $table->date('pay_period_start')->after('employee_id');
                }
                if (! Schema::hasColumn('payrolls', 'pay_period_end')) {
                    $table->date('pay_period_end')->after('pay_period_start');
                }
                if (! Schema::hasColumn('payrolls', 'basic_salary')) {
                    $table->decimal('basic_salary', 12, 2)->default(0)->after('pay_period_end');
                }
                if (! Schema::hasColumn('payrolls', 'deductions')) {
                    $table->decimal('deductions', 12, 2)->default(0)->after('basic_salary');
                }
                if (! Schema::hasColumn('payrolls', 'bonuses')) {
                    $table->decimal('bonuses', 12, 2)->default(0)->after('deductions');
                }
                if (! Schema::hasColumn('payrolls', 'overtime_pay')) {
                    $table->decimal('overtime_pay', 12, 2)->default(0)->after('bonuses');
                }
                if (! Schema::hasColumn('payrolls', 'net_pay')) {
                    $table->decimal('net_pay', 12, 2)->default(0)->after('overtime_pay');
                }
                if (! Schema::hasColumn('payrolls', 'status')) {
                    $table->string('status')->default(PayrollStatus::Draft->value)->after('net_pay');
                }
                if (! Schema::hasColumn('payrolls', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('payrolls', 'notes')) {
                    $table->text('notes')->nullable()->after('paid_at');
                }
            });

            return;
        }

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('bonuses', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->string('status')->default(PayrollStatus::Draft->value);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

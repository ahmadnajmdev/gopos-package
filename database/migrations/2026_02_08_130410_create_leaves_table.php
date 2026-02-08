<?php

use Gopos\Enums\LeaveStatus;
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
        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                if (! Schema::hasColumn('leaves', 'employee_id')) {
                    $table->foreignId('employee_id')->after('id')->constrained('employees')->cascadeOnDelete();
                }
                if (! Schema::hasColumn('leaves', 'leave_type_id')) {
                    $table->foreignId('leave_type_id')->after('employee_id')->constrained('leave_types')->cascadeOnDelete();
                }
                if (! Schema::hasColumn('leaves', 'start_date')) {
                    $table->date('start_date')->after('leave_type_id');
                }
                if (! Schema::hasColumn('leaves', 'end_date')) {
                    $table->date('end_date')->after('start_date');
                }
                if (! Schema::hasColumn('leaves', 'days')) {
                    $table->decimal('days', 5, 1)->after('end_date');
                }
                if (! Schema::hasColumn('leaves', 'status')) {
                    $table->string('status')->default(LeaveStatus::Pending->value)->after('days');
                }
                if (! Schema::hasColumn('leaves', 'reason')) {
                    $table->text('reason')->nullable()->after('status');
                }
                if (! Schema::hasColumn('leaves', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
                }
            });

            return;
        }

        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 5, 1);
            $table->string('status')->default(LeaveStatus::Pending->value);
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};

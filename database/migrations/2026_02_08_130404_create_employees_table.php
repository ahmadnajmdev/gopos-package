<?php

use Gopos\Enums\EmployeeStatus;
use Gopos\Enums\Gender;
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
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (! Schema::hasColumn('employees', 'employee_number')) {
                    $table->string('employee_number')->unique()->after('id');
                }
                if (! Schema::hasColumn('employees', 'first_name')) {
                    $table->string('first_name')->after('employee_number');
                }
                if (! Schema::hasColumn('employees', 'last_name')) {
                    $table->string('last_name')->after('first_name');
                }
                if (! Schema::hasColumn('employees', 'email')) {
                    $table->string('email')->nullable()->after('last_name');
                }
                if (! Schema::hasColumn('employees', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (! Schema::hasColumn('employees', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('employees', 'gender')) {
                    $table->string('gender')->default(Gender::Male->value)->nullable()->after('date_of_birth');
                }
                if (! Schema::hasColumn('employees', 'hire_date')) {
                    $table->date('hire_date')->after('gender');
                }
                if (! Schema::hasColumn('employees', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->after('hire_date')->constrained('departments')->nullOnDelete();
                }
                if (! Schema::hasColumn('employees', 'position_id')) {
                    $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
                }
                if (! Schema::hasColumn('employees', 'salary')) {
                    $table->decimal('salary', 12, 2)->default(0)->after('position_id');
                }
                if (! Schema::hasColumn('employees', 'status')) {
                    $table->string('status')->default(EmployeeStatus::Active->value)->after('salary');
                }
                if (! Schema::hasColumn('employees', 'address')) {
                    $table->text('address')->nullable()->after('status');
                }
                if (! Schema::hasColumn('employees', 'notes')) {
                    $table->text('notes')->nullable()->after('address');
                }
            });

            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->default(Gender::Male->value)->nullable();
            $table->date('hire_date');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('status')->default(EmployeeStatus::Active->value);
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

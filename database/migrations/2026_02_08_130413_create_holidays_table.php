<?php

use Gopos\Enums\HolidayType;
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
        if (Schema::hasTable('holidays')) {
            Schema::table('holidays', function (Blueprint $table) {
                if (! Schema::hasColumn('holidays', 'name_ar')) {
                    $table->string('name_ar')->nullable()->after('name');
                }
                if (! Schema::hasColumn('holidays', 'name_ckb')) {
                    $table->string('name_ckb')->nullable()->after('name_ar');
                }
                if (! Schema::hasColumn('holidays', 'date')) {
                    $table->date('date')->after('name_ckb');
                }
                if (! Schema::hasColumn('holidays', 'type')) {
                    $table->string('type')->default(HolidayType::Public->value)->after('date');
                }
                if (! Schema::hasColumn('holidays', 'is_recurring')) {
                    $table->boolean('is_recurring')->default(false)->after('type');
                }
            });

            return;
        }

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ckb')->nullable();
            $table->date('date');
            $table->string('type')->default(HolidayType::Public->value);
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};

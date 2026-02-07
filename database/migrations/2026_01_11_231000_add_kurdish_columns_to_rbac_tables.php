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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name_ckb')->nullable()->after('name_ar');
            $table->text('description_ckb')->nullable()->after('description_ar');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('name_ckb')->nullable()->after('name_ar');
            $table->text('description_ckb')->nullable()->after('description_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['name_ckb', 'description_ckb']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['name_ckb', 'description_ckb']);
        });
    }
};

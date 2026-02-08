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
        Schema::table('account_types', function (Blueprint $table) {
            $table->string('name_ckb')->nullable()->after('name_ar');
        });

        DB::table('account_types')->where('name', 'Asset')->update(['name_ckb' => 'دارایی']);
        DB::table('account_types')->where('name', 'Liability')->update(['name_ckb' => 'قەرزەکان']);
        DB::table('account_types')->where('name', 'Equity')->update(['name_ckb' => 'مافی خاوەنداری']);
        DB::table('account_types')->where('name', 'Revenue')->update(['name_ckb' => 'داهات']);
        DB::table('account_types')->where('name', 'Expense')->update(['name_ckb' => 'خەرجی']);
    }

    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn('name_ckb');
        });
    }
};

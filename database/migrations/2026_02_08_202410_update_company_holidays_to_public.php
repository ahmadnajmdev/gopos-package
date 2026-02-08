<?php

use Gopos\Enums\HolidayType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('holidays')
            ->where('type', 'company')
            ->update(['type' => HolidayType::Public->value]);
    }
};

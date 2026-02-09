<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldTypes = [
            'App\\Models\\User',
            'Gopos\\Models\\User',
        ];

        DB::table('model_has_roles')
            ->whereIn('model_type', $oldTypes)
            ->update(['model_type' => 'user']);

        DB::table('model_has_permissions')
            ->whereIn('model_type', $oldTypes)
            ->update(['model_type' => 'user']);
    }

    public function down(): void
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        DB::table('model_has_roles')
            ->where('model_type', 'user')
            ->update(['model_type' => $userModel]);

        DB::table('model_has_permissions')
            ->where('model_type', 'user')
            ->update(['model_type' => $userModel]);
    }
};

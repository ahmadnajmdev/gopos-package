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

        // Delete duplicates in model_has_roles before updating
        // Keep the first occurrence and remove rows that would conflict after rename
        $duplicateRoles = DB::table('model_has_roles')
            ->select('role_id', 'model_id')
            ->whereIn('model_type', $oldTypes)
            ->groupBy('role_id', 'model_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateRoles as $dup) {
            // Delete all but keep one — remove the Gopos\Models\User variant
            DB::table('model_has_roles')
                ->where('role_id', $dup->role_id)
                ->where('model_id', $dup->model_id)
                ->where('model_type', 'Gopos\\Models\\User')
                ->delete();
        }

        // Delete duplicates in model_has_permissions before updating
        $duplicatePermissions = DB::table('model_has_permissions')
            ->select('permission_id', 'model_id')
            ->whereIn('model_type', $oldTypes)
            ->groupBy('permission_id', 'model_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicatePermissions as $dup) {
            DB::table('model_has_permissions')
                ->where('permission_id', $dup->permission_id)
                ->where('model_id', $dup->model_id)
                ->where('model_type', 'Gopos\\Models\\User')
                ->delete();
        }

        // Now safely update all remaining rows
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

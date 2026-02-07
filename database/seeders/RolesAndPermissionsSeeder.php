<?php

namespace Gopos\Database\Seeders;

use Gopos\Services\PermissionService;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new PermissionService;

        // Create all default permissions
        $this->command->info('Creating default permissions...');
        $service->createDefaultPermissions();

        // Create all default roles with their permissions
        $this->command->info('Creating default roles...');
        $service->createDefaultRoles();

        $this->command->info('Roles and permissions created successfully!');
    }
}

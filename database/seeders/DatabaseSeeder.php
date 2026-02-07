<?php

namespace Gopos\Database\Seeders;

use Gopos\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Avoid duplicate user creation by first checking if the user exists

        // Seed currencies
        $this->call([
            CurrencySeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // Seed demo data (only in non-production environments)
        if (app()->environment(['local', 'development', 'demo'])) {
            $this->call([
                DemoSeeder::class,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'test@admin.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        $user->assignRole('super_admin');
    }
}

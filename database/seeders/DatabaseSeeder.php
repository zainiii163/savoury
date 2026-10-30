<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Seeds RBAC definitions and the provisional menu only.
     * Admin/staff user accounts are never hard-coded.
     * Delivery zones, hours, fees, and address remain empty until the owner supplies them.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ProductSeeder::class,
        ]);
    }
}

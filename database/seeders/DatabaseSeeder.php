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
     * Only the supplied provisional menu is seeded. Admin/staff accounts are
     * created deliberately by an operator, never with hard-coded credentials.
     */
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
        ]);
    }
}

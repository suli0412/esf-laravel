<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // weitere Seeder...
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        $user = User::firstOrCreate(
            ['email' => 'admin@esf.local'],
            ['name' => 'ESF Admin', 'password' => Hash::make('changeme123')]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole($admin);
        }
    }
}

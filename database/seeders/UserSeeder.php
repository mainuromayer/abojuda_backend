<?php

namespace Database\Seeders;

use App\Enum\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'       => 'Admin User',
            'email'      => 'admin@admin.com',
            'password'   => Hash::make('12345678'),
            'role'       => Role::ADMIN->value,
            'province'   => 'Admin Province',
            'city'       => 'Admin City',
            'status'     => 'active',
        ]);
    }
}

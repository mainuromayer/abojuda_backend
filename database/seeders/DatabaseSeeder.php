<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call([CategorySeeder::class]);
        // $this->call([PostSeeder::class]);
        $this->call([UserSeeder::class]);
        $this->call([PermissionSeeder::class]);

        $admin = User::where('role', 'admin')->first();
        $admin->givePermissionTo(Permission::all());
    }
}

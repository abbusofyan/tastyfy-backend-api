<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Person;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create 250 Person records
//        Person::factory(250)->create();

        // Create one specific User with a fixed people_id
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'kukuhthewow@gmail.com',
            'password' => Hash::make('kukuhpass'),
        ]);
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $this->call(PermissionSeeder::class);
        $this->call(CustomerSeeder::class);
        // Create 249 users
//        $users = User::factory(249)->create();

        // Update the people_id for each user, starting from 2
//        $users->each(function ($user, $index) {
//            $user->update(['people_id' => $index + 2]);
//        });

        $testUser->assignRole('admin');
        $adminUser->assignRole('admin');

        $this->call(ProductsTableSeeder::class);
    }
}

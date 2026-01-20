<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@volta.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '01000000000',
            'date_of_birth' => '1990-01-01',
            'email_verified_at' => now(),
        ]);

        // Regular User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@volta.com',
            'password' => Hash::make('password'),
            'role' => 'customer', // Assuming customer is the default role
            'phone_number' => '01111111111',
            'date_of_birth' => '1995-01-01',
            'email_verified_at' => now(),
        ]);

        // Random Users
        User::factory(10)->create([
            'role' => 'customer',
        ]);
    }
}

<?php

namespace Database\Seeders;

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
        // Create a staff (admin) user
        User::create([
            'name' => 'Admin User',
            'email' => env('ADMIN_EMAIL'),
            'password' => env('ADMIN_PASSWORD'),
            'is_staff' => true,
        ]);
    }
}

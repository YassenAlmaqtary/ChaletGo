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
        // Create test customer
        User::create([
            'name' => 'أحمد محمد',
            'email' => 'ahmed@test.com',
            'password' => Hash::make('12345678'),
            'phone' => '0501234567',
            'user_type' => 'customer',
            'is_active' => true,
        ]);

        // Create test owner
        User::create([
            'name' => 'سارة أحمد',
            'email' => 'sara@test.com',
            'password' => Hash::make('12345678'),
            'phone' => '0507654321',
            'user_type' => 'owner',
            'is_active' => true,
        ]);

        // Create another customer
        User::create([
            'name' => 'محمد علي',
            'email' => 'mohammed@test.com',
            'password' => Hash::make('12345678'),
            'phone' => '0509876543',
            'user_type' => 'customer',
            'is_active' => true,
        ]);
    }
}

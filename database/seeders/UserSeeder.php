<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'phone' => '1234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'phone' => '1234567891',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ],
        ];

        foreach ($users as $key => $value) {
            User::create($value);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'api_token' => User::generateApiToken(),
            ],
            [
                'name' => 'Another User',
                'email' => 'another@example.com',
                'password' => bcrypt('password'),
                'api_token' => User::generateApiToken(),
            ],
            [
                'name' => 'Another2 User',
                'email' => 'another2@example.com',
                'password' => bcrypt('password'),
                'api_token' => User::generateApiToken(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}

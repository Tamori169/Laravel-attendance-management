<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'ユーザー１',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'ユーザー２',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'ユーザー３',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'email_verified_at' => now(),
        ]);
    }
}

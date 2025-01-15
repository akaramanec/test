<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@ad.min',
            ],
            [
                'name' => 'Olexandr Karamanec',
                'email' => 'o.karamanec@tabster.online',
            ],
        ];

        foreach ($users as $userData) {
            User::where('email', $userData['email'])->delete();

            // Створюємо нового користувача
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now()->subDay()->toDateTimeString(),
                'password' => Hash::make($userData['email']),
            ]);
        }
    }
}

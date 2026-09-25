<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@nexus.test',
                'role' => 'super_admin',
            ],
            [
                'name' => 'HR Administrator',
                'email' => 'hr@nexus.test',
                'role' => 'hr_admin',
            ],
            [
                'name' => 'Department Manager',
                'email' => 'manager@nexus.test',
                'role' => 'manager',
            ],
            [
                'name' => 'Regular Employee',
                'email' => 'employee@nexus.test',
                'role' => 'employee',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $defaultPassword,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }

        // Akun nonaktif untuk pengujian login gagal
        $inactiveUser = User::firstOrCreate(
            ['email' => 'inactive@nexus.test'],
            [
                'name' => 'Inactive User',
                'password' => $defaultPassword,
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );
        $inactiveUser->syncRoles(['employee']);
    }
}

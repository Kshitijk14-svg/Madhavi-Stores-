<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'kshitijmay14@gmail.com'],
            [
                'name'              => 'Kshitij',
                'email'             => 'kshitijmay14@gmail.com',
                'password'          => Hash::make('kshitij@123'),
                'is_admin'          => true,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $this->command->info('✓ Admin user created: kshitijmay14@gmail.com');
    }
}

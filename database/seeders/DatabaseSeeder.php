<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('riders')->insert([
            'name' => 'Test Rider',
            'email' => 'rider@example.com',
            'password' => Hash::make('password'), // password = password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
   public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'administrador',
            'email_verified_at' => now(), 
        ]);

         User::create([
            'name' => 'htt',
            'email' => 'diperishilla2468@gmail.com',
            'password' => Hash::make('12345679'), 
            'role' => 'administrador',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
   public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'administrador',
            'email_verified_at' => now(), // ← AGREGAR ESTA LÍNEA
        ]);

         User::create([
            'name' => 'htt',
            'email' => 'diperishilla2468@gmail.com',
            'password' => Hash::make('12345679'), // Hash con mayúscula.
            'role' => 'administrador',
        ]);
    }
}

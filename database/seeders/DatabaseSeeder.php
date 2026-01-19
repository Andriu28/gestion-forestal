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
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'administrador',
            'email_verified_at' => now(), // ← AGREGAR ESTA LÍNEA
        ]);

         User::create([
            'name' => 'Diper',
            'email' => 'diper@gmail.com',
            'password' => Hash::make('12345679'), // Hash con mayúscula
            'role' => 'administrador',
            'email_verified_at' => now(), // ← AGREGAR ESTA LÍNEA
        ]);

        User::create([
            'name' => 'Básico',
            'email' => 'basico@gmail.com',
            'password' => Hash::make('12345670'), // Hash con mayúscula
            'role' => 'basico',
            'email_verified_at' => now(), // ← AGREGAR ESTA LÍNEA
        ]);
        
    }
}

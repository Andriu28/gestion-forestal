<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProducersSeeder extends Seeder
{
    public function run()
    {
        $producers = [
            [
                'name' => 'Juan',
                'lastname' => 'García',
                'description' => 'Productor de madera de pino con 20 años de experiencia en el sector forestal.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'María',
                'lastname' => 'Rodríguez',
                'description' => 'Especialista en gestión sostenible de bosques nativos.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Carlos',
                'lastname' => 'López',
                'description' => 'Productor de eucalipto para industria papelera.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ana',
                'lastname' => 'Martínez',
                'description' => 'Gestora de plantaciones forestales certificadas FSC.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pedro',
                'lastname' => 'Sánchez',
                'description' => 'Productor de madera noble para mueblería fina.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]     
        ];

        DB::table('producers')->insert($producers);
    }
}
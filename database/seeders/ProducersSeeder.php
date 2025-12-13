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
            ],
            [
                'name' => 'Laura',
                'lastname' => 'Fernández',
                'description' => 'Especialista en reforestación y conservación de suelos.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Diego',
                'lastname' => 'Pérez',
                'description' => 'Productor de madera para construcción y estructuras.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Elena',
                'lastname' => 'Gómez',
                'description' => 'Gestora de bosques comunitarios y proyectos sociales.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Miguel',
                'lastname' => 'Hernández',
                'description' => 'Productor de madera certificada para exportación.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sofía',
                'lastname' => 'Díaz',
                'description' => 'Ingeniera forestal especializada en silvicultura preventiva.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Javier',
                'lastname' => 'Ruiz',
                'description' => 'Productor de leña y biomasa para energía renovable.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Carmen',
                'lastname' => 'Moreno',
                'description' => 'Gestora de viveros forestales y producción de plantines.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Antonio',
                'lastname' => 'Álvarez',
                'description' => 'Productor tradicional con técnicas ancestrales de manejo forestal.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Isabel',
                'lastname' => 'Romero',
                'description' => 'Especialista en dendrología y especies nativas.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Francisco',
                'lastname' => 'Navarro',
                'description' => 'Productor de corcho y productos forestales no maderables.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('producers')->insert($producers);
    }
}
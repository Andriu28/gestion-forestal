<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProducersSeeder extends Seeder
{
    public function run()
    {
        $producers = [
            [
                'name' => 'Juan',
                'lastname' => 'García',
                'cedula' => 'V-12345678',
                'description' => 'Productor de madera de pino con 20 años de experiencia en el sector forestal.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'María',
                'lastname' => 'Rodríguez',
                'cedula' => 'V-23456789',
                'description' => 'Especialista en gestión sostenible de bosques nativos.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Carlos',
                'lastname' => 'López',
                'cedula' => 'V-34567890',
                'description' => 'Productor de eucalipto para industria papelera.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Ana',
                'lastname' => 'Martínez',
                'cedula' => 'V-45678901',
                'description' => 'Gestora de plantaciones forestales certificadas FSC.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pedro',
                'lastname' => 'Sánchez',
                'cedula' => 'V-56789012',
                'description' => 'Productor de madera noble para mueblería fina.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Laura',
                'lastname' => 'Fernández',
                'cedula' => 'V-67890123',
                'description' => 'Especialista en reforestación y conservación de suelos.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Diego',
                'lastname' => 'Pérez',
                'cedula' => 'V-78901234',
                'description' => 'Productor de madera para construcción y estructuras.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Elena',
                'lastname' => 'Gómez',
                'cedula' => 'V-89012345',
                'description' => 'Gestora de bosques comunitarios y proyectos sociales.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Miguel',
                'lastname' => 'Hernández',
                'cedula' => 'V-90123456',
                'description' => 'Productor de madera certificada para exportación.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sofía',
                'lastname' => 'Díaz',
                'cedula' => 'V-01234567',
                'description' => 'Ingeniera forestal especializada en silvicultura preventiva.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Javier',
                'lastname' => 'Ruiz',
                'cedula' => 'V-11223344',
                'description' => 'Productor de leña y biomasa para energía renovable.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Carmen',
                'lastname' => 'Moreno',
                'cedula' => 'V-22334455',
                'description' => 'Gestora de viveros forestales y producción de plantines.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Antonio',
                'lastname' => 'Álvarez',
                'cedula' => 'V-33445566',
                'description' => 'Productor tradicional con técnicas ancestrales de manejo forestal.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Isabel',
                'lastname' => 'Romero',
                'cedula' => 'V-44556677',
                'description' => 'Especialista en dendrología y especies nativas.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Francisco',
                'lastname' => 'Navarro',
                'cedula' => 'V-55667788',
                'description' => 'Productor de corcho y productos forestales no maderables.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Fernando',
                'lastname' => 'Ramírez',
                'cedula' => 'V-66778899',
                'description' => 'Productor de caucho natural con 15 años en el cultivo de heveas.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Marta',
                'lastname' => 'Torres',
                'cedula' => 'V-77889900',
                'description' => 'Gestora de proyectos de conservación y manejo sostenible de bosques tropicales.',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('producers')->insert($producers);
    }
}
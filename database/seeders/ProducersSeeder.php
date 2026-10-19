<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProducersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $prefix = 'CSJ';

        $rawProducers = [
            ['name' => 'Juan',      'lastname' => 'García',     'cedula' => '12345678', 'description' => 'Productor de madera de pino con 20 años de experiencia en el sector forestal.'],
            ['name' => 'María',     'lastname' => 'Rodríguez',  'cedula' => '23456789', 'description' => 'Especialista en gestión sostenible de bosques nativos.'],
            ['name' => 'Carlos',    'lastname' => 'López',      'cedula' => '34567890', 'description' => 'Productor de eucalipto para industria papelera.'],
            ['name' => 'Ana',       'lastname' => 'Martínez',   'cedula' => '45678901', 'description' => 'Gestora de plantaciones forestales certificadas FSC.'],
            ['name' => 'Pedro',     'lastname' => 'Sánchez',    'cedula' => '56789012', 'description' => 'Productor de madera noble para mueblería fina.'],
            ['name' => 'Laura',     'lastname' => 'Fernández',  'cedula' => '67890123', 'description' => 'Especialista en reforestación y conservación de suelos.'],
            ['name' => 'Diego',     'lastname' => 'Pérez',      'cedula' => '78901234', 'description' => 'Productor de madera para construcción y estructuras.'],
            ['name' => 'Elena',     'lastname' => 'Gómez',      'cedula' => '89012345', 'description' => 'Gestora de bosques comunitarios y proyectos sociales.'],
            ['name' => 'Miguel',    'lastname' => 'Hernández',  'cedula' => '90123456', 'description' => 'Productor de madera certificada para exportación.'],
            ['name' => 'Sofía',     'lastname' => 'Díaz',       'cedula' => '01234567', 'description' => 'Ingeniera forestal especializada en silvicultura preventiva.'],
            ['name' => 'Javier',    'lastname' => 'Ruiz',       'cedula' => '11223344', 'description' => 'Productor de leña y biomasa para energía renovable.'],
            ['name' => 'Carmen',    'lastname' => 'Moreno',     'cedula' => '22334455', 'description' => 'Gestora de viveros forestales y producción de plantines.'],
            ['name' => 'Antonio',   'lastname' => 'Álvarez',    'cedula' => '33445566', 'description' => 'Productor tradicional con técnicas ancestrales de manejo forestal.'],
            ['name' => 'Isabel',    'lastname' => 'Romero',     'cedula' => '44556677', 'description' => 'Especialista en dendrología y especies nativas.'],
            ['name' => 'Francisco', 'lastname' => 'Navarro',    'cedula' => '55667788', 'description' => 'Productor de corcho y productos forestales no maderables.'],
            ['name' => 'Fernando',  'lastname' => 'Ramírez',    'cedula' => '66778899', 'description' => 'Productor de caucho natural con 15 años en el cultivo de heveas.'],
            ['name' => 'Marta',     'lastname' => 'Torres',     'cedula' => '77889900', 'description' => 'Gestora de proyectos de conservación y manejo sostenible de bosques tropicales.'],

            // Productor de ejemplo (igual al de la interfaz)
            ['name' => 'ROOT',      'lastname' => 'ÁLVAREZ',    'cedula' => '28000000', 'description' => 'VBNVBN'],
        ];

        $producers = [];

        foreach ($rawProducers as $item) {
            $cedulaType = 'V';
            $cedula     = preg_replace('/\D/', '', $item['cedula']);

            $producers[] = [
                'code'        => $prefix . $cedulaType . $cedula, // CSJV28000000
                'name'        => $item['name'],
                'lastname'    => $item['lastname'],
                'cedula_type' => $cedulaType,
                'cedula'      => $cedula,
                'description' => $item['description'],
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('producers')->insert($producers);
    }
}
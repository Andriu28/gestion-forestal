<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reportes = [
            [
                'titulo' => 'Reporte de Deforestación',
                'descripcion' => 'Análisis de pérdida de cobertura forestal por año',
                'ruta' => 'reports.deforestacion', // Cambiado a 'reports'
                'icono' => '🌳',
                'color' => 'green'
            ],
            [
                'titulo' => 'Reporte de Usuarios',
                'descripcion' => 'Estadísticas de usuarios registrados y actividad',
                'ruta' => 'reports.usuarios', // Cambiado a 'reports'
                'icono' => '👥',
                'color' => 'blue'
            ],
            [
                'titulo' => 'Reporte de Proveedores',
                'descripcion' => 'Información de proveedores activos e inactivos',
                'ruta' => '#',
                'icono' => '🏢',
                'color' => 'indigo',
                'proximamente' => true
            ]
        ];

        return view('reports.index', compact('reportes'));
    }

    public function deforestacion()
    {
        // Datos de ejemplo para la demo
        $datosDeforestacion = [
            'anios' => [2020, 2021, 2022, 2023, 2024],
            'areaBoscosa' => [5000, 4800, 4500, 4200, 4000],
            'areaDeforestada' => [200, 400, 500, 800, 1000],
            'porcentajePerdida' => [4.0, 8.3, 11.1, 19.0, 25.0]
        ];

        $poligonos = [
            ['id' => 1, 'nombre' => 'Reserva Amazonas Norte', 'area_total' => 1500],
            ['id' => 2, 'nombre' => 'Bosque Andino Central', 'area_total' => 2500],
            ['id' => 3, 'nombre' => 'Selva Pacífica Sur', 'area_total' => 3000]
        ];

        return view('reports.deforestacion', compact('datosDeforestacion', 'poligonos'));
    }

    public function usuarios()
    {
        $estadisticasUsuarios = [
            'total' => 150,
            'activos' => 142,
            'inactivos' => 8,
            'roles' => [
                'basico' => 120,
                'avanzado' => 25,
                'admin' => 5
            ],
            'registros_mensuales' => [
                'Ene' => 15, 'Feb' => 12, 'Mar' => 18, 'Abr' => 20,
                'May' => 25, 'Jun' => 22, 'Jul' => 30, 'Ago' => 28
            ]
        ];

        return view('reports.usuarios', compact('estadisticasUsuarios'));
    }
}
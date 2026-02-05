<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SupportController extends Controller
{
    /**
     * Muestra la página de soporte técnico.
     */
    public function index(): View
    {
        return view('support.index');
    }

    /**
     * Genera un PDF con la información de soporte.
     */
    public function generatePdf()
    {
        // Datos que quieras pasar al PDF (opcional)
        $data = [
            'date' => date('d/m/Y'),
            'title' => 'Guía de Soporte Técnico',
            'is_pdf' => true,
        ];

        // Usamos una vista específica para el PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('support.pdf', $data); // Importante para cargar imágenes;

        // Descarga el archivo
        return $pdf->download('soporte-tecnico.pdf');
    }
}
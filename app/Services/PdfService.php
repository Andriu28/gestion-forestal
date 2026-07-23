<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Genera un archivo PDF para descargar.
     *
     * @param string $view
     * @param array $data
     * @param string $filename
     * @param string $paper
     * @param string $orientation
     * @param array $options    Opciones adicionales para Dompdf (dpi, isRemoteEnabled, etc.)
     * @param int    $timeout   Tiempo máximo de ejecución en segundos
     * @return \Illuminate\Http\Response
     */
    public function download(
        string $view,
        array $data,
        string $filename = 'documento.pdf',
        string $paper = 'a4',
        string $orientation = 'portrait',
        array $options = [],
        int $timeout = 60
    ) {
        // Aumentar tiempo de ejecución
        set_time_limit($timeout);

        // Opciones por defecto
        $defaultOptions = [
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ];

        // Fusionar opciones personalizadas
        $mergedOptions = array_merge($defaultOptions, $options);

        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions($mergedOptions);

        return $pdf->download($filename);
    }
}
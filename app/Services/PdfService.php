<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Genera un archivo PDF para descargar.
     *
     * @param string $view La ruta de la vista Blade (ej: 'reports.my-pdf')
     * @param array $data Los datos que requiere la vista
     * @param string $filename El nombre con el que se descargará el archivo
     * @param string $paper Tamaño del papel
     * @param string $orientation Orientación (portrait o landscape)
     * @return \Illuminate\Http\Response
     */
    public function download(
        string $view, 
        array $data, 
        string $filename = 'documento.pdf', 
        string $paper = 'a4', 
        string $orientation = 'portrait'
    ) {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->download($filename);
    }

    
}
<?php

namespace App\Http\Controllers;

use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProducerRequest;
use App\Http\Requests\UpdateProducerRequest;
use App\Traits\Filterable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ProducerController extends Controller
{
    use Filterable;

    /**
     * Muestra la lista de productores con filtros.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');  // Cambia 'all' por 'active'

        $query = Producer::query();  // Esto ya excluye los eliminados por defecto

        // busqueda flexible con el trait
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['name', 'lastname', 'description'], // columnas de la tabla producers
            [] // sin relaciones por ahora
        );

        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            'all'      => $query, // ¡CORREGIDO! No usar withTrashed()
            default    => $query->where('is_active', true), // Por defecto solo activos
        };

        $producers = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Agregar contadores para estadísticas
        $deletedCount = Producer::onlyTrashed()->count();
        $activeCount = Producer::where('is_active', true)->count();
        $inactiveCount = Producer::where('is_active', false)->count();

        return view('producers.index', compact('producers', 'search', 'status', 'deletedCount', 'activeCount', 'inactiveCount'));
    }

    /**
     * Muestra el formulario para crear un nuevo productor.
     */
    public function create()
    {
        return view('producers.create');
    }

     /**
     * Muestra solo los productores eliminados (soft deleted).
     */
    public function deleted(Request $request)
    {
        $search = $request->input('search');

        $query = Producer::onlyTrashed()
            ->when($search, function ($query, $search) {
                return $query->search($search);
            });

        $producers = $query
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('producers.deleted', compact('producers', 'search'));
    }

    /**
     * Guarda un nuevo productor en la base de datos.
     */
    public function store(StoreProducerRequest $request)
    {
        try {
            Producer::create($request->validated());

            // Alerta de éxito con SweetAlert2
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor creado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Alerta de error con SweetAlert2
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al crear el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Muestra los detalles de un productor.
     */
    public function show(Producer $producer)
    {
        return view('producers.show', compact('producer'));
    }

    /**
     * Muestra el formulario para editar un productor.
     */
    public function edit(Producer $producer)
    {
        return view('producers.edit', compact('producer'));
    }

    /**
     * Actualiza un productor existente.
     */
    public function update(UpdateProducerRequest $request, Producer $producer)
    {
        try {
            $producer->update($request->validated());

            // Alerta de éxito con SweetAlert2
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor actualizado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Alerta de error con SweetAlert2
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al actualizar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
    * Elimina (soft delete) un productor - MEJORADO para AJAX
    */
    public function destroy(Request $request, Producer $producer)
    {
        try {
            $producer->delete();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor deshabilitado exitosamente.',
                    'producer_id' => $producer->id,
                    'redirect' => route('producers.deleted') // Opcional: redirección si se necesita
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor deshabilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al eliminar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Restaura un productor eliminado - MEJORADO para AJAX
     */
    public function restore(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->restore();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor habilitado exitosamente.',
                    'producer_id' => $producer->id,
                    'redirect' => route('producers.index') // Opcional: redirección si se necesita
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor habilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al restaurar el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al restaurar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Elimina permanentemente un productor - MEJORADO para AJAX
     */
    public function forceDelete(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->forceDelete();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor eliminado permanentemente.',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor eliminado permanentemente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un productor.
     */
    public function toggleStatus(Request $request, Producer $producer)
    {
        try {
            $oldStatus = $producer->is_active;
            $producer->update(['is_active' => !$producer->is_active]);
            
            $newStatus = $producer->is_active;
            $statusText = $producer->is_active ? 'activado' : 'desactivado';

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Productor {$statusText} exitosamente.",
                    'is_active' => $newStatus,
                    'status_text' => $producer->is_active ? 'Activo' : 'Inactivo',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => "Productor {$statusText} exitosamente."
                ]); 

        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ]); 
        }
    }

    /**
     * Obtiene los detalles de un productor para mostrar en modal.
     */
    public function details(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            
            // Contar polígonos asociados (si tienes la relación)
            $polygonsCount = 0;
            if (method_exists($producer, 'polygons')) {
                $polygonsCount = $producer->polygons()->count();
            }
            
            $producerData = [
                'id' => $producer->id,
                'name' => $producer->name,
                'lastname' => $producer->lastname,
                'description' => $producer->description,
                'is_active' => $producer->is_active,
                'deleted_at' => $producer->deleted_at,
                'created_at' => $producer->created_at,
                'updated_at' => $producer->updated_at,
                'polygons_count' => $polygonsCount,
            ];
            
            return response()->json([
                'success' => true,
                'producer' => $producerData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del productor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Localiza el logo institucional (PNG/JPG primero, SVG como respaldo) y
     * lo prepara para incrustarlo en el PDF. Misma lógica que
     * AuditLogController::getLogoImage(), copiada aquí directamente (sin
     * trait) para que ambos controladores no dependan de un archivo
     * compartido.
     *
     * @return array{type: string, src?: string, content?: string}|null
     */
    private function getLogoImage(): ?array
    {
        $rasterCandidates = [
            'images/logo.png',
            'logo.png',
            'images/logo.jpg',
            'logo.jpg',
            'favicon.png',
        ];

        foreach ($rasterCandidates as $relative) {
            $path = public_path($relative);
            if (!is_file($path)) {
                continue;
            }

            try {
                $data = file_get_contents($path);
                $mime = function_exists('mime_content_type')
                    ? (mime_content_type($path) ?: 'image/png')
                    : 'image/png';

                return [
                    'type' => 'image',
                    'src' => 'data:' . $mime . ';base64,' . base64_encode($data),
                ];
            } catch (\Throwable $e) {
                Log::warning('Producers PDF: no se pudo leer el logo raster', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $svgPath = public_path('favicon.svg');
        if (is_file($svgPath)) {
            try {
                $svg = file_get_contents($svgPath);
                $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
                $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);

                if (!preg_match('/<svg[^>]*\swidth\s*=/i', $svg)) {
                    $svg = preg_replace('/<svg/', '<svg width="46" height="46"', $svg, 1);
                }

                return [
                    'type' => 'svg',
                    'content' => trim($svg),
                ];
            } catch (\Throwable $e) {
                Log::warning('Producers PDF: no se pudo leer el logo SVG', [
                    'path' => $svgPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Producers PDF: no se encontró ningún logo institucional; se usará el monograma de respaldo.', [
            'buscado_en' => array_merge($rasterCandidates, ['favicon.svg']),
        ]);

        return null;
    }

    /**
 * Genera un PDF con la lista de productores aplicando los mismos filtros.
 */
public function generatePdf(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status', 'all');

    $query = Producer::query();

    // Búsqueda flexible con el trait
    $query = $this->applySearchFilter(
        $query,
        $search,
        ['name', 'lastname', 'description'],
        []
    );

    // Filtro de estado
    match ($status) {
        'active'   => $query->where('is_active', true),
        'inactive' => $query->where('is_active', false),
        'deleted'  => $query->onlyTrashed(),
        'all'      => $query->withTrashed(),
        default    => $query,
    };

    $producers = $query->orderBy('name')->get();

    // Contadores para estadísticas
    $activeCount = Producer::where('is_active', true)->count();
    $inactiveCount = Producer::where('is_active', false)->count();
    $deletedCount = Producer::onlyTrashed()->count();

    $filters = [
        'search' => $search,
        'status' => $status,
        'total' => $producers->count(),
        'active_count' => $activeCount,
        'inactive_count' => $inactiveCount,
        'deleted_count' => $deletedCount,
        'generated_at' => now()->format('d/m/Y H:i:s'),
        'generated_by' => auth()->user()->name ?? 'Sistema',
        'company_name' => 'Cacao San José',
        'rif' => 'J-12345678-9',
    ];

   // Logo institucional: mismo método que AuditLogController::getLogoImage(),
    // prioriza el logo raster real (PNG/JPG) antes de caer a SVG, y usa el
    // mismo fallback visual (.logo-fallback del Blade).
    $logo = $this->getLogoImage();

    $pdf = Pdf::loadView('producers.pdf', [
        'producers' => $producers,
        'filters' => $filters,
        'logo' => $logo,
    ]);

    // Mismas opciones de DomPDF que en AuditLogController::generatePdf().
    // Antes esto pasaba por PdfService con dpi=>72 y sin isPhpEnabled
    // explícito, lo que probablemente impedía que el <script type="text/php">
    // del encabezado/pie de página del Blade se ejecutara, y cambiaba el
    // escalado de todo el documento respecto al de auditoría.
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOption('isPhpEnabled', true);          // Habilita el script embebido de encabezado/pie de página
    $pdf->setOption('isHtml5ParserEnabled', true);   // Habilita HTML5

    return $pdf->download('productores_' . now()->format('Y-m-d_H-i') . '.pdf');
}

}
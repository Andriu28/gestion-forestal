<?php

namespace App\Http\Controllers;

use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProducerRequest;
use App\Http\Requests\UpdateProducerRequest;
use App\Traits\Filterable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Gestión de Productores Agroforestales
 * 
 * Administra el padrón de productores del sistema:
 * 1. Operaciones CRUD con soporte dual (respuestas HTML tradicionales con redirección y JSON para peticiones AJAX).
 * 2. Filtrado y conteo de estadísticas (activos, inactivos y en papelera/soft-deleted).
 * 3. Papelera de reciclaje con restauración (`restore`) y eliminación física definitiva (`forceDelete`).
 * 4. Alternancia rápida de estado activo/inactivo (`toggleStatus`) sin recargar la página.
 * 5. Generación de informes impresos en PDF con resolución institucional de logos (PNG/JPG/SVG en Base64).
 */
class ProducerController extends Controller
{
    use Filterable;

    /**
     * Muestra el padrón de productores aplicando filtros:
     * - Búsqueda por texto (nombre, apellido, descripción, código interno).
     * - Estado operativo ('active', 'inactive', 'deleted', 'all').
     * - Calcula contadores agregados para los badges de la cabecera.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = Producer::query();

        // Búsqueda flexible en columnas del productor
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['name', 'lastname', 'description', 'code'],
            []
        );

        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            'all'      => $query, 
            default    => $query->where('is_active', true),
        };

        $producers = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Métricas agregadas para visualización en las tarjetas del panel
        $deletedCount  = Producer::onlyTrashed()->count();
        $activeCount   = Producer::where('is_active', true)->count();
        $inactiveCount = Producer::where('is_active', false)->count();

        return view('producers.index', compact('producers', 'search', 'status', 'deletedCount', 'activeCount', 'inactiveCount'));
    }

    /**
     * Muestra la vista para registrar un nuevo productor.
     */
    public function create()
    {
        return view('producers.create');
    }

    /**
     * Muestra la ficha técnica detallada del productor y sus parcelas asociadas.
     */
    public function show(Producer $producer)
    {
        return view('producers.show', compact('producer'));
    }

    /**
     * Muestra el formulario para modificar la información de un productor.
     */
    public function edit(Producer $producer)
    {
        return view('producers.edit', compact('producer'));
    }

    /**
     * Muestra exclusivamente la lista de productores eliminados lógicamente (papelera de reciclaje).
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
     * Almacena un nuevo productor validado mediante `StoreProducerRequest`.
     */
    public function store(StoreProducerRequest $request)
    {
        try {
            Producer::create($request->validated());

            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => 'Productor creado exitosamente.'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al crear el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Actualiza los datos de un productor validado con `UpdateProducerRequest`.
     */
    public function update(UpdateProducerRequest $request, Producer $producer)
    {
        try {
            $producer->update($request->validated());

            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => 'Productor actualizado exitosamente.'
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al actualizar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Envía un productor a la papelera (SoftDelete):
     * - Responde con JSON si la petición proviene de un fetch/AJAX en el navegador.
     * - Responde con redirección tradicional con mensaje flash en caso contrario.
     */
    public function destroy(Request $request, Producer $producer)
    {
        try {
            $producer->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Productor deshabilitado exitosamente.',
                    'producer_id' => $producer->id,
                    'redirect'    => route('producers.deleted')
                ]);
            }

            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => 'Productor deshabilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el productor: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al eliminar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Recupera un productor de la papelera de reciclaje restaurando su disponibilidad.
     */
    public function restore(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->restore();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Productor habilitado exitosamente.',
                    'producer_id' => $producer->id,
                    'redirect'    => route('producers.index')
                ]);
            }

            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => 'Productor habilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al restaurar el productor: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al restaurar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Elimina físicamente el registro de la base de datos sin posibilidad de recuperación.
     */
    public function forceDelete(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->forceDelete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Productor eliminado permanentemente.',
                    'producer_id' => $producer->id
                ]);
            }

            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => 'Productor eliminado permanentemente.'
                ]);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Alterna el estado operativo (is_active) entre Activo e Inactivo.
     */
    public function toggleStatus(Request $request, Producer $producer)
    {
        try {
            $producer->update(['is_active' => !$producer->is_active]);
            $statusText = $producer->is_active ? 'activado' : 'desactivado';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => "Productor {$statusText} exitosamente.",
                    'is_active'   => $producer->is_active,
                    'status_text' => $producer->is_active ? 'Activo' : 'Inactivo',
                    'producer_id' => $producer->id
                ]);
            }

            return redirect()->back()
                ->with('swal', [
                    'icon'  => 'success',
                    'title' => 'Éxito',
                    'text'  => "Productor {$statusText} exitosamente."
                ]); 

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('swal', [
                    'icon'  => 'error',
                    'title' => 'Error',
                    'text'  => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ]); 
        }
    }

    /**
     * Retorna la información serializada del productor para cargar ventanas modales vía AJAX:
     * Incluye conteo de parcelas asignadas (`polygons_count`) y cédula formateada.
     */
    public function details(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            
            $polygonsCount = 0;
            if (method_exists($producer, 'polygons')) {
                $polygonsCount = $producer->polygons()->count();
            }
            
            $producerData = [
                'id'                => $producer->id,
                'code'              => $producer->code,
                'name'              => $producer->name,
                'lastname'          => $producer->lastname,
                'cedula_type'       => $producer->cedula_type,
                'cedula'            => $producer->cedula,
                'cedula_formatted'  => $producer->cedula_formatted, 
                'description'       => $producer->description,
                'is_active'         => $producer->is_active,
                'deleted_at'        => $producer->deleted_at,
                'created_at'        => $producer->created_at,
                'updated_at'        => $producer->updated_at,
                'polygons_count'    => $polygonsCount,
            ];
            
            return response()->json([
                'success'  => true,
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
     * Algoritmo de resolución de isotipo/logo corporativo para impresión en PDF:
     * 1. Busca en orden de precedencia imágenes ráster (PNG/JPG) en la carpeta pública.
     * 2. Si encuentra un archivo ráster, lo codifica en un Data-URI Base64 para incrustarlo
     *    directamente en el HTML sin depender de rutas HTTP relativas que puedan fallar en DomPDF.
     * 3. Si no hay ráster, busca el vector SVG (`favicon.svg`), le remueve cabeceras XML y ajusta
     *    sus dimensiones width/height para inyección inline.
     * 4. Si ningún archivo existe, retorna null para utilizar el monograma CSS de respaldo.
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
                    'src'  => 'data:' . $mime . ';base64,' . base64_encode($data),
                ];
            } catch (\Throwable $e) {
                Log::warning('Producers PDF: no se pudo leer el logo raster', [
                    'path'  => $path,
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
                    'type'    => 'svg',
                    'content' => trim($svg),
                ];
            } catch (\Throwable $e) {
                Log::warning('Producers PDF: no se pudo leer el logo SVG', [
                    'path'  => $svgPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Producers PDF: no se encontró ningún logo institucional; se usará el monograma de respaldo.');
        return null;
    }

    /**
     * Genera y descarga el padrón de productores en formato PDF aplicando los mismos filtros de búsqueda:
     * - Configuración de hoja en formato A4 vertical (portrait).
     * - Habilita `isPhpEnabled` para permitir la numeración dinámica de páginas en el pie del documento.
     * - Incluye estadísticas de cabecera (totales, activos, inactivos y eliminados).
     */
    public function generatePdf(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = Producer::query();

        $query = $this->applySearchFilter(
            $query,
            $search,
            ['name', 'lastname', 'description', 'code'],
            []
        );

        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            'all'      => $query->withTrashed(),
            default    => $query,
        };

        $producers = $query->orderBy('name')->get();

        $activeCount   = Producer::where('is_active', true)->count();
        $inactiveCount = Producer::where('is_active', false)->count();
        $deletedCount  = Producer::onlyTrashed()->count();

        $filters = [
            'search'         => $search,
            'status'         => $status,
            'total'          => $producers->count(),
            'active_count'   => $activeCount,
            'inactive_count' => $inactiveCount,
            'deleted_count'  => $deletedCount,
            'generated_at'   => now()->format('d/m/Y H:i:s'),
            'generated_by'   => auth()->user()->name ?? 'Sistema',
            'company_name'   => 'Cacao San José',
            'rif'            => 'J-12345678-9',
        ];

        $logo = $this->getLogoImage();

        $pdf = Pdf::loadView('producers.pdf', [
            'producers' => $producers,
            'filters'   => $filters,
            'logo'      => $logo,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isPhpEnabled', true);          // Habilita script embebido para pie de página y conteo de hojas
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf->download('productores_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
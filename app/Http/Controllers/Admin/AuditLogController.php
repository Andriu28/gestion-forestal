<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\Filterable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    use Filterable;

    // Datos de la organización para el encabezado del PDF.
    // Si más adelante quieres poder cambiarlos por entorno sin tocar código,
    // muévelos a config/company.php + .env (COMPANY_RIF=...).
    private const COMPANY_NAME = 'Cacao San José';
    private const COMPANY_RIF = 'J-12345678-9';

    // Rutas candidatas para el logo institucional, en orden de preferencia.
    // Se prioriza raster (PNG/JPG) porque DomPDF lo renderiza de forma mucho
    // más confiable que SVG. Ajusta esta lista a donde realmente vive tu logo.
    private const LOGO_RASTER_CANDIDATES = [
        'images/logo.png',
        'logo.png',
        'images/logo.jpg',
        'logo.jpg',
        'favicon.png',
    ];

    private const LOGO_SVG_CANDIDATE = 'favicon.svg';

    /**
     * Localiza el logo institucional y lo prepara para incrustarlo en el PDF.
     *
     * Por qué no cargaba antes: el código original sólo buscaba
     * public/favicon.svg y, si el archivo no existía (por ejemplo si el
     * proyecto trae únicamente favicon.ico, o el logo real vive en otra
     * ruta), el método devolvía null en silencio — sin log, sin excepción —
     * así que el letterhead simplemente se quedaba sin logo sin ninguna
     * pista de por qué.
     *
     * Además, aunque el SVG exista, el soporte de SVG en DomPDF es parcial:
     * no resuelve <use>/<symbol>, gradientes complejos, filtros ni imágenes
     * referenciadas, y si el <svg> no trae width/height explícitos (sólo
     * CSS), a veces termina calculando un tamaño de 0 dentro de la celda.
     * Por eso ahora se prioriza PNG/JPG en base64 (muy confiable en DomPDF)
     * y sólo se cae a SVG, con el fix de tamaño, si no hay un raster
     * disponible. Si no se encuentra nada, se deja constancia en el log
     * en vez de fallar en silencio, y la vista usa un monograma de
     * respaldo para que el encabezado nunca se vea "roto".
     *
     * @return array{type: string, src?: string, content?: string}|null
     */
    private function getLogoImage(): ?array
    {
        foreach (self::LOGO_RASTER_CANDIDATES as $relative) {
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
                Log::warning('Audit PDF: no se pudo leer el logo raster', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $svgPath = public_path(self::LOGO_SVG_CANDIDATE);
        if (is_file($svgPath)) {
            try {
                $svg = file_get_contents($svgPath);
                $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
                $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);

                // DomPDF a veces ignora el tamaño puesto sólo por CSS si el
                // <svg> no trae width/height explícitos: se inyectan si faltan.
                if (!preg_match('/<svg[^>]*\swidth\s*=/i', $svg)) {
                    $svg = preg_replace('/<svg/', '<svg width="46" height="46"', $svg, 1);
                }

                return [
                    'type' => 'svg',
                    'content' => trim($svg),
                ];
            } catch (\Throwable $e) {
                Log::warning('Audit PDF: no se pudo leer el logo SVG', [
                    'path' => $svgPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Audit PDF: no se encontró ningún logo institucional; se usará el monograma de respaldo.', [
            'buscado_en' => array_merge(self::LOGO_RASTER_CANDIDATES, [self::LOGO_SVG_CANDIDATE]),
        ]);

        return null;
    }

    /**
     * Traduce los filtros activos de la request a etiquetas legibles para
     * mostrar como "chips" en el PDF. Vive en el controlador (y no en el
     * Blade) para que la vista no tenga que conocer los valores crudos de
     * los query params ni sus mapeos.
     *
     * @return array<string, string> Label => Valor formateado
     */
    private function buildActiveFilterLabels(Request $request): array
    {
        $labels = [];

        if ($search = $request->get('search')) {
            $labels['Búsqueda'] = '"' . $search . '"';
        }

        if ($dateFrom = $request->get('date_from')) {
            $labels['Desde'] = Carbon::parse($dateFrom)->format('d/m/Y');
        }

        if ($dateTo = $request->get('date_to')) {
            $labels['Hasta'] = Carbon::parse($dateTo)->format('d/m/Y');
        }

        $role = $request->get('role');
        if ($role && $role !== 'all') {
            $roleLabels = [
                'administrador' => 'Administrador',
                'tecnico' => 'Técnico',
                'basico' => 'Básico',
                'system' => 'Sistema',
            ];
            $labels['Rol'] = $roleLabels[$role] ?? ucfirst($role);
        }

        $userId = $request->get('user_id');
        if ($userId && $userId !== 'all') {
            $user = User::find($userId);
            if ($user) {
                $labels['Usuario'] = $user->name;
            }
        }

        $eventType = $request->get('event_type');
        if ($eventType && $eventType !== 'all') {
            $eventLabels = [
                'created' => 'Creaciones',
                'updated' => 'Actualizaciones',
                'deleted' => 'Eliminaciones',
                'restored' => 'Restauraciones',
                'login' => 'Inicios de sesión',
                'logout' => 'Cierres de sesión',
                'role_change' => 'Cambios de rol',
            ];
            $labels['Evento'] = $eventLabels[$eventType] ?? $eventType;
        }

        $subjectType = $request->get('subject_type');
        if ($subjectType && $subjectType !== 'all') {
            $labels['Modelo'] = $subjectType;
        }

        return $labels;
    }

    /**
     * Etiqueta legible del período cubierto por el reporte.
     */
    private function buildPeriodLabel(?string $dateFrom, ?string $dateTo): string
    {
        if ($dateFrom && $dateTo) {
            return Carbon::parse($dateFrom)->format('d/m/Y') . ' – ' . Carbon::parse($dateTo)->format('d/m/Y');
        }

        if ($dateFrom) {
            return 'Desde ' . Carbon::parse($dateFrom)->format('d/m/Y');
        }

        if ($dateTo) {
            return 'Hasta ' . Carbon::parse($dateTo)->format('d/m/Y');
        }

        return 'Histórico completo';
    }

    private function buildAuditQuery(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $role = $request->get('role');
        $userId = $request->get('user_id');
        $eventType = $request->get('event_type');
        $subjectType = $request->get('subject_type');

        $query = Activity::with(['causer', 'subject'])->latest();

        $query = $this->applyDateFilters($query, $dateFrom, $dateTo);
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['description'],
            ['causer' => ['name', 'email', 'role']]
        );

        $query->where(function ($q) {
            $q->whereNull('causer_id')
              ->orWhereHas('causer', function ($sub) {
                  $sub->where('role', '!=', 'tecnico');
              });
        });

        if ($role && $role !== 'all') {
            if ($role === 'system') {
                $query->whereNull('causer_id');
            } else {
                $query->whereHas('causer', function ($q) use ($role) {
                    $q->where('role', $role);
                });
            }
        }

        if ($userId && $userId !== 'all') {
            $query->where('causer_id', $userId);
        }

        if ($eventType && $eventType !== 'all') {
            $eventMap = [
                'created' => '%created%',
                'updated' => '%updated%',
                'deleted' => '%deleted%',
                'restored' => '%restored%',
                'login' => '%iniciado sesión%',
                'logout' => '%cerrado sesión%',
                'role_change' => '%fue actualizado su rol%',
            ];
            if (isset($eventMap[$eventType])) {
                $query->where('description', 'like', $eventMap[$eventType]);
            }
        }

        if ($subjectType && $subjectType !== 'all') {
            $modelMap = [
                'User' => 'App\Models\User',
                'Polygon' => 'App\Models\Polygon',
                'Producer' => 'App\Models\Producer',
            ];
            if (isset($modelMap[$subjectType])) {
                $query->where('subject_type', $modelMap[$subjectType]);
            }
        }

        return $query;
    }

    private function loadSubjectRelations($activities)
    {
        $activities->each(function ($activity) {
            if ($activity->subject && $activity->subject_type === 'App\Models\Polygon') {
                $activity->subject->loadMissing(['producer', 'parish']);
            }
            /* if ($activity->subject && $activity->subject_type === 'App\Models\Producer') {
                $activity->subject->loadMissing(['parish']);
            } */
        });
        return $activities;
    }

    public function showAuditLog(Request $request)
    {
        $validationError = $this->validateDateRange(
            $request->get('date_from'),
            $request->get('date_to')
        );
        if ($validationError) {
            return $validationError;
        }

        $query = $this->buildAuditQuery($request);
        $activities = $query->paginate(10);
        $this->loadSubjectRelations($activities);

        $activities->appends([
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'role' => $request->get('role'),
            'user_id' => $request->get('user_id'),
            'event_type' => $request->get('event_type'),
            'subject_type' => $request->get('subject_type'),
        ]);
// dd($activities->total(), $activities->items());
        $users = User::orderBy('name')->get(['id', 'name']);
        
        return view('admin.audit_log', [
            'activities' => $activities,
            'search' => $request->get('search'),
            'dateFrom' => $request->get('date_from'),
            'dateTo' => $request->get('date_to'),
            'role' => $request->get('role'),
            'userId' => $request->get('user_id'),
            'eventType' => $request->get('event_type'),
            'subjectType' => $request->get('subject_type'),
            'users' => $users,
        ]);
    }

    public function generatePdf(Request $request)
    {
        $validationError = $this->validateDateRange(
            $request->get('date_from'),
            $request->get('date_to')
        );
        if ($validationError) {
            return $validationError;
        }

        $query = $this->buildAuditQuery($request);
        $activities = $query->get();
        $this->loadSubjectRelations($activities);

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $filters = [
            'total' => $activities->count(),
            'distinct_users' => $activities->pluck('causer_id')->filter()->unique()->count(),
            'period_label' => $this->buildPeriodLabel($dateFrom, $dateTo),
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'generated_by' => optional($request->user())->name ?: 'Sistema',
            'company_name' => self::COMPANY_NAME,
            'rif' => self::COMPANY_RIF,
        ];

        $pdf = Pdf::loadView('admin.audit_pdf', [
            'activities' => $activities,
            'filters' => $filters,
            'activeFilters' => $this->buildActiveFilterLabels($request),
            'logo' => $this->getLogoImage(),
        ]);

        // Orientación de la hoja: 'portrait' = vertical, 'landscape' = horizontal.
        // Si vuelves a horizontal, no hace falta tocar nada más: el encabezado y
        // pie de página del Blade obtienen el ancho de la hoja de forma dinámica.
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isPhpEnabled', true);         // Habilita PHP embebido (numeración/encabezado por página)
        $pdf->setOption('isHtml5ParserEnabled', true);  // Habilita HTML5

        return $pdf->download('auditoria_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
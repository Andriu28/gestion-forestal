<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\Filterable;
use App\Services\PdfService;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class AuditLogController extends Controller
{
    
    use Filterable;

    private const COMPANY_NAME = 'Cacao San José';
    private const COMPANY_RIF = '23333';

    /**
     * Obtiene el logo SVG para el PDF
     */
    private function getLogoSvg(): string
    {
        // Intentar cargar desde archivo favicon.svg
        $svgPath = public_path('favicon.svg');
        if (file_exists($svgPath)) {
            $svg = file_get_contents($svgPath);
            $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
            $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);
            return trim($svg);
        }
        
        // SVG del logo de Cacao San José (hardcodeado)
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 167" width="100%" height="100%">
            <g transform="translate(0,167) scale(0.1,-0.1)" fill="#ffffff">
                <path d="M1052 1609 l-40 -20 15 -37 c24 -57 111 -228 160 -315 24 -44 43 -80 41 -81 -2 -1 -26 -11 -54 -22 l-52 -21 -13 31 c-13 28 -26 34 -148 74 -247 80 -375 81 -537 2 -170 -82 -282 -223 -360 -450 -39 -113 -39 -113 72 -159 134 -56 231 -74 376 -68 102 4 125 2 121 -9 -10 -27 19 -203 48 -284 17 -47 38 -106 46 -131 l16 -46 60 -7 c33 -3 111 -6 173 -6 l113 0 16 30 c8 16 15 31 15 35 0 3 -37 5 -82 5 l-83 0 60 33 c177 96 281 211 337 376 39 114 28 285 -28 447 -4 10 43 -55 105 -144 138 -203 182 -261 200 -262 8 0 20 -8 27 -18 11 -15 14 -15 28 -2 13 13 2 35 -98 190 -164 254 -277 448 -392 677 l-102 201 -40 -19z m-283 -369 c94 -18 309 -91 315 -107 7 -18 -20 -53 -40 -53 -8 0 -16 11 -20 25 -4 14 -15 27 -28 30 -11 3 -68 16 -126 30 -290 69 -504 -3 -647 -218 -44 -66 -112 -215 -113 -244 0 -17 56 -40 170 -70 82 -21 109 -24 200 -19 58 3 118 8 133 12 26 6 28 4 25 -17 -4 -30 -31 -36 -163 -38 -94 -1 -189 14 -260 41 -71 26 -145 61 -145 68 0 22 46 153 72 205 40 80 107 173 157 218 131 118 304 169 470 137z m3 -85 c138 -24 146 -34 23 -28 -203 10 -351 -47 -506 -195 -98 -92 -103 -82 -16 33 87 116 193 176 352 198 55 7 62 7 147 -8z m475 -47 c25 -32 94 -287 100 -363 16 -235 -115 -439 -362 -563 -55 -27 -183 -72 -207 -72 -5 0 -23 30 -39 67 -101 220 -98 454 6 628 70 117 148 176 337 259 133 58 150 63 165 44z m-372 -17 l30 -8 -41 -11 c-23 -7 -93 -23 -155 -37 -192 -42 -270 -74 -407 -167 -90 -60 -82 -38 17 54 126 117 276 177 436 176 50 0 104 -4 120 -7z m-37 -134 c-48 -45 -94 -94 -103 -109 -41 -66 -208 -128 -365 -134 l-105 -5 118 35 c139 42 217 79 312 149 123 90 208 147 219 147 6 0 -28 -37 -76 -83z m-43 47 c-77 -63 -206 -150 -264 -176 -59 -27 -237 -78 -321 -92 l-35 -5 35 30 c63 55 226 159 291 185 52 22 276 80 314 83 6 0 -3 -11 -20 -25z m-115 -254 c0 -6 -6 -26 -14 -45 -12 -28 -23 -36 -67 -49 -68 -21 -200 -20 -292 0 -40 9 -75 19 -79 23 -4 3 48 6 115 7 125 1 225 21 293 57 35 19 44 21 44 7z"/>
                <path d="M1060 1007 c-174 -94 -275 -199 -330 -342 -29 -76 -32 -235 -7 -335 32 -120 58 -180 79 -180 34 0 167 58 236 104 156 102 228 215 252 399 12 90 9 118 -38 321 -17 77 -30 95 -67 94 -5 0 -62 -28 -125 -61z m180 -117 c46 -195 17 -366 -82 -492 -36 -46 -135 -121 -210 -159 l-57 -29 108 108 c114 115 153 174 189 294 28 90 34 164 25 285 -5 56 -6 99 -2 95 4 -4 17 -50 29 -102z m-155 81 c-6 -5 -34 -27 -64 -50 -149 -111 -238 -287 -248 -489 -3 -62 -9 -110 -14 -105 -17 18 -30 169 -20 238 21 145 128 294 271 378 61 35 96 48 75 28z m31 -39 c-8 -16 -53 -80 -101 -143 -100 -133 -144 -218 -180 -349 -39 -142 -46 -149 -39 -40 10 176 62 310 161 420 43 47 152 136 171 139 1 1 -4 -12 -12 -27z m73 -143 c1 -76 -31 -195 -75 -279 -37 -70 -130 -182 -186 -221 -26 -19 -22 -9 31 69 87 129 115 196 156 367 20 83 41 166 47 185 l12 35 7 -50 c4 -27 8 -75 8 -106z m-105 -71 c-40 -163 -86 -262 -192 -404 -50 -67 -67 -84 -70 -69 -5 38 57 257 97 338 36 74 177 276 192 277 4 0 -9 -64 -27 -142z"/>
                <path d="M1713 440 c0 -25 2 -35 4 -22 2 12 2 32 0 45 -2 12 -4 2 -4 -23z"/>
            </g>
        </svg>';
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
            ['description', 'properties'],
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
        try {
            // Validación de fechas (igual que antes)
            $validationError = $this->validateDateRange(
                $request->get('date_from'),
                $request->get('date_to')
            );
            if ($validationError) {
                return $validationError;
            }

            // Construir la consulta (sin relaciones pesadas)
            $query = $this->buildAuditQuery($request, $forPdf = true);

            // Limitar a 500 registros (o menos si quieres)
            $activities = $query->limit(500)->get();

            // Preparar datos
            $filters = [
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'role' => $request->get('role'),
                'user_id' => $request->get('user_id'),
                'event_type' => $request->get('event_type'),
                'subject_type' => $request->get('subject_type'),
                'total' => $activities->count(),
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'limited' => $activities->count() >= 500,
            ];

            return $this->pdfService->download(
                'admin.audit_pdf',
                compact('activities', 'filters'),
                'auditoria_' . now()->format('Y-m-d_H-i') . '.pdf',
                'a4',
                'landscape',
                ['dpi' => 72],
                180 // 3 minutos
            );

        } catch (FatalError $e) {
            // Capturar específicamente errores fatales (incluye timeout)
            if (str_contains($e->getMessage(), 'Maximum execution time')) {
                return redirect()->back()
                    ->with('error', 'El PDF es demasiado grande para generarse en este momento. Por favor, aplica filtros más restrictivos (fechas, usuario, etc.) e intenta de nuevo.');
            }
            throw $e; // Si es otro error fatal, lo relanzamos

        } catch (Throwable $e) {
            // Capturar cualquier otra excepción
            Log::error('Error al generar PDF de auditoría', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Ocurrió un error al generar el PDF. Por favor, intenta de nuevo o contacta al administrador.');
        }

        $query = $this->buildAuditQuery($request);
        $activities = $query->get();
        $this->loadSubjectRelations($activities);

        $filters = [
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'role' => $request->get('role'),
            'user_id' => $request->get('user_id'),
            'event_type' => $request->get('event_type'),
            'subject_type' => $request->get('subject_type'),
            'total' => $activities->count(),
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'company_name' => self::COMPANY_NAME,
            'rif' => self::COMPANY_RIF,
        ];

        $pdf = Pdf::loadView('admin.audit_pdf', [
            'activities' => $activities,
            'filters' => $filters,
            'logoSvg' => $this->getLogoSvg(),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('auditoria_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
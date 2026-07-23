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

    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    private function buildAuditQuery(Request $request, $forPdf = false)
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
    }
}
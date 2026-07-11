<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\Filterable;

class AuditLogController extends Controller
{
    use Filterable;

    public function showAuditLog(Request $request)
    {
        // Obtener parámetros
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $role = $request->get('role');
        $userId = $request->get('user_id');
        $eventType = $request->get('event_type');
        $subjectType = $request->get('subject_type');

        // Validar rango de fechas (trait)
        $validationError = $this->validateDateRange($dateFrom, $dateTo);
        if ($validationError) {
            return $validationError;
        }

        // Consulta base
        $query = Activity::with(['causer', 'subject'])->latest();

        // Aplicar filtros de fecha (trait)
        $query = $this->applyDateFilters($query, $dateFrom, $dateTo);

        // Aplicar búsqueda flexible (trait)
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['description', 'properties'], // columnas de la tabla activities
            ['causer' => ['name', 'email', 'role']] // relación y sus columnas
        );

        // --- Filtros específicos de auditoría ---
        // Filtro por rol del causer
        if ($role && $role !== 'all') {
            if ($role === 'system') {
                $query->whereNull('causer_id');
            } else {
                $query->whereHas('causer', function ($q) use ($role) {
                    $q->where('role', $role);
                });
            }
        }

        // Filtro por usuario específico
        if ($userId && $userId !== 'all') {
            $query->where('causer_id', $userId);
        }

        // Filtro por tipo de evento
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

        // Filtro por modelo afectado
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

        // Paginar resultados
        $activities = $query->paginate(10);

        // Mantener filtros en la paginación
        $activities->appends([
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'role' => $role,
            'user_id' => $userId,
            'event_type' => $eventType,
            'subject_type' => $subjectType,
        ]);

        // Obtener lista de usuarios para el select
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.audit_log', compact(
            'activities', 'search', 'dateFrom', 'dateTo',
            'role', 'userId', 'eventType', 'subjectType', 'users'
        ));
    }

    public function generatePdf(Request $request)
    {
        // Obtener parámetros
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $role = $request->get('role');
        $userId = $request->get('user_id');
        $eventType = $request->get('event_type');
        $subjectType = $request->get('subject_type');

        // Validar rango de fechas (trait)
        $validationError = $this->validateDateRange($dateFrom, $dateTo);
        if ($validationError) {
            return $validationError;
        }

        $query = Activity::with(['causer', 'subject'])->latest();

        // Aplicar filtros de fecha (trait)
        $query = $this->applyDateFilters($query, $dateFrom, $dateTo);

        // Aplicar búsqueda flexible (trait)
        $query = $this->applySearchFilter(
            $query,
            $search,
            ['description', 'properties'],
            ['causer' => ['name', 'email', 'role']]
        );

        // --- Filtros específicos (igual que en showAuditLog) ---
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

        $activities = $query->get();

        $filters = [
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'role' => $role,
            'user_id' => $userId,
            'event_type' => $eventType,
            'subject_type' => $subjectType,
            'total' => $activities->count(),
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('admin.audit_pdf', [
            'activities' => $activities,
            'filters' => $filters,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('auditoria_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
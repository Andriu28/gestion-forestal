<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    public function showAuditLog(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $role = $request->get('role');
        $userId = $request->get('user_id');
        $eventType = $request->get('event_type');
        $subjectType = $request->get('subject_type');

        $query = Activity::with(['causer', 'subject'])->latest();

        // Búsqueda por texto
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    })
                    ->orWhereRaw('properties::text LIKE ?', ["%{$search}%"]);
            });
        }

        // Filtro por rango de fechas
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Filtro por rol del causer
        if ($role && $role !== 'all') {
            $query->whereHas('causer', function($q) use ($role) {
                if ($role === 'system') {
                    $q->whereNull('id'); // Causer es null (sistema)
                } else {
                    $q->where('role', $role);
                }
            });
        }

        // Filtro por usuario específico
        if ($userId && $userId !== 'all') {
            $query->where('causer_id', $userId);
        }

        // Filtro por tipo de evento (basado en description)
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
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.audit_log', compact('activities', 'search', 'dateFrom', 'dateTo', 'role', 'userId', 'eventType', 'subjectType', 'users'));
    }

    public function generatePdf(Request $request)
    {
        // Obtener todos los filtros
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $role = $request->get('role');
        $userId = $request->get('user_id');
        $eventType = $request->get('event_type');
        $subjectType = $request->get('subject_type');

        $query = Activity::with(['causer', 'subject'])->latest();

        // Búsqueda por texto
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    })
                    ->orWhereRaw('properties::text LIKE ?', ["%{$search}%"]);
            });
        }

        // Filtro por rango de fechas
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Filtro por rol del causer
        if ($role && $role !== 'all') {
            if ($role === 'system') {
                // Actividades realizadas por el sistema (sin usuario)
                $query->whereNull('causer_id');
            } else {
                $query->whereHas('causer', function($q) use ($role) {
                    $q->where('role', $role);
                });
            }
        }

        // Filtro por usuario específico
        if ($userId && $userId !== 'all') {
            $query->where('causer_id', $userId);
        }

        // Filtro por tipo de evento (basado en description)
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

        $activities = $query->get(); // Todos los registros (sin paginar)

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
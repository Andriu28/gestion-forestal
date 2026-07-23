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
        ];

        $pdf = Pdf::loadView('admin.audit_pdf', [
            'activities' => $activities,
            'filters' => $filters,
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('auditoria_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
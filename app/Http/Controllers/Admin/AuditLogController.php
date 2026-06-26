<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AuditLogController extends Controller
{
    public function showAuditLog(Request $request)
    {
        $search = $request->get('search');
        
        // Iniciar query
        $query = Activity::with(['causer', 'subject'])
            ->latest();
        
        // Aplicar búsqueda si existe
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                ->orWhereHas('causer', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                })
                // Buscar en properties (PostgreSQL: convertir a texto y buscar)
                ->orWhereRaw('properties::text LIKE ?', ["%{$search}%"]);
            });
        }
        
        // Paginar resultados (10 por página)
        $activities = $query->paginate(10);
        
        // Si hay búsqueda, mantener el parámetro en los links de paginación
        if ($search) {
            $activities->appends(['search' => $search]);
        }
        /* dd($activities); */
        return view('admin.audit_log', compact('activities', 'search'));
    }
}
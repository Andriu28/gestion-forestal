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
                // Buscar en descripción
                $q->where('description', 'like', "%{$search}%")
                  // Buscar por nombre de usuario causante
                  ->orWhereHas('causer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  // Buscar actividades de sesión
                  ->orWhere('description', 'like', '%sesión%');
            });
        }
        
        // Paginar resultados (10 por página)
        $activities = $query->paginate(10);
        
        // Si hay búsqueda, mantener el parámetro en los links de paginación
        if ($search) {
            $activities->appends(['search' => $search]);
        }
        
        return view('admin.audit_log', compact('activities', 'search'));
    }
}
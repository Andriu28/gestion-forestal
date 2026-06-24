<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource, priorizando el usuario actual.
     */
    public function index(Request $request)
    {
        // Obtener parámetros de filtro
        $search = $request->get('search');
        $role = $request->get('role', 'all');
        $status = $request->get('status', 'active');
        
        // Iniciar query excluyendo al usuario autenticado
        $query = User::where('id', '!=', auth()->id());
        
        // Aplicar filtro de búsqueda
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Aplicar filtro de rol
        if ($role !== 'all') {
            $query->where('role', $role);
        }
        
        // Aplicar filtro de estado
        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'disabled') {
            $query->onlyTrashed();
        }
        // 'all' no necesita filtro especial
        
        // Paginar resultados (sin insertar al usuario actual)
        $users = $query->paginate(15);
        
        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }
    
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    public function updateUserRole(Request $request, $userId)
    {
        $request->validate([
            'role' => ['required', 'string', 'in:basico,administrador,tecnico'],
        ]);

        try {
            // Buscar el usuario incluyendo deshabilitados
            $user = User::withTrashed()->findOrFail($userId);
            $oldRole = $user->role;
            
            // Actualizar el rol
            $user->update(['role' => $request->role]);

            // Registrar la actividad de manera personalizada
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_role' => $oldRole,
                    'new_role' => $request->role,
                    'updated_fields' => ['role']
                ])
                ->log("Usuario '{$user->name}' fue actualizado su rol");

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rol de usuario actualizado exitosamente.'
                ]);
            }

            // Respuesta tradicional para navegación normal
            return back()->with('swal', [
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Rol de usuario actualizado exitosamente.'
            ]);
            
        } catch (\Exception $e) {
            // Manejo de errores para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el rol: ' . $e->getMessage()
                ], 500);
            }
            
            // Manejo de errores tradicional
            return back()->with('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al actualizar el rol: ' . $e->getMessage()
            ]);
        }
    }
        
   public function destroy(Request $request, User $user)
    {
        // No permitimos que un usuario se elimine a sí mismo
        if (auth()->user()->id === $user->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes deshabilitar tu propia cuenta.'
                ], 422);
            }
            
            session()->flash('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No puedes deshabilitar tu propia cuenta.'
            ]);
            return redirect()->route('admin.users.index');
        }

        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario deshabilitado exitosamente.',
                'user_id' => $user->id
            ]);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Usuario deshabilitado exitosamente.'
        ]);
        return redirect()->route('admin.users.index');
    }

   public function enableUser(Request $request, $userId)
    {
    // USAR withTrashed() para buscar usuarios deshabilitados
    $user = User::withTrashed()->findOrFail($userId);

    $user->restore();

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Usuario habilitado exitosamente.',
            'user_id' => $user->id
        ]);
    }

    return redirect()->route('admin.users.disabled')
        ->with('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Usuario habilitado exitosamente.'
        ]);
    }

     public function listDisabledUsers(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role', 'all');
        
        $query = User::onlyTrashed();
        
        // Aplicar filtro de búsqueda
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Aplicar filtro de rol
        if ($role !== 'all') {
            $query->where('role', $role);
        }
        
        $users = $query->paginate(15);
        
        return view('admin.users.disabled', compact('users', 'search', 'role'));
    }
}

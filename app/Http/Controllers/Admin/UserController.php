<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // <-- Asegúrate de tener esta importación

class UserController extends Controller
{
    /**
     * Display a listing of the resource, priorizando el usuario actual.
     */
    public function index()
    {
        // 1. Obtener el ID del usuario autenticado
        $currentUserId = Auth::id();
        
        // 2. Obtener los demás usuarios (excluyendo el actual) y paginarlos
        $otherUsersPaginator = User::where('id', '!=', $currentUserId)
                                   ->paginate(15);

        // 3. Obtener el objeto del usuario actual
        $currentUser = User::find($currentUserId);
        
        // 4. Insertar el usuario actual al comienzo de la colección de la página actual
        // Solo lo hacemos si estamos en la primera página para evitar duplicados en otras páginas.
        if ($otherUsersPaginator->currentPage() === 1 && $currentUser) {
            $otherUsersPaginator->getCollection()->prepend($currentUser);
        }

        // 5. La variable $users ahora contiene el paginador con el usuario actual primero (en la pág. 1)
        $users = $otherUsersPaginator;

        return view('admin.users.index', compact('users'));
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
            'role' => ['required', 'string', 'in:basico,administrador'],
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

   

    public function listDisabledUsers()
    {
        $users = User::onlyTrashed()->paginate(15); // Obtiene solo los usuarios deshabilitados
        return view('admin.users.disabled', compact('users'));
    }
}

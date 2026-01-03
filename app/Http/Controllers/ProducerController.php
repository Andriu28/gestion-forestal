<?php

namespace App\Http\Controllers;

use App\Models\Producer;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProducerRequest;
use App\Http\Requests\UpdateProducerRequest;

class ProducerController extends Controller
{
    /**
     * Muestra la lista de productores con filtros.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = Producer::query()
            ->when($search, function ($query, $search) {
                return $query->search($search);
            });

        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'deleted'  => $query->onlyTrashed(),
            'all'      => $query->withTrashed(),
            default    => $query,
        };

        $producers = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('producers.index', compact('producers', 'search', 'status'));
    }

    /**
     * Muestra el formulario para crear un nuevo productor.
     */
    public function create()
    {
        return view('producers.create');
    }

    /**
     * Guarda un nuevo productor en la base de datos.
     */
    public function store(StoreProducerRequest $request)
    {
        try {
            Producer::create($request->validated());

            // Alerta de éxito con SweetAlert2
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor creado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Alerta de error con SweetAlert2
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al crear el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Muestra los detalles de un productor.
     */
    public function show(Producer $producer)
    {
        return view('producers.show', compact('producer'));
    }

    /**
     * Muestra el formulario para editar un productor.
     */
    public function edit(Producer $producer)
    {
        return view('producers.edit', compact('producer'));
    }

    /**
     * Actualiza un productor existente.
     */
    public function update(UpdateProducerRequest $request, Producer $producer)
    {
        try {
            $producer->update($request->validated());

            // Alerta de éxito con SweetAlert2
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor actualizado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Alerta de error con SweetAlert2
            return redirect()->back()
                ->withInput()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al actualizar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Elimina (soft delete) un productor.
     */
    public function destroy(Request $request, Producer $producer)
    {
        try {
            $producer->delete();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor deshabilitado exitosamente.',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor deshabilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al eliminar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Restaura un productor eliminado.
     */
    public function restore(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->restore();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor habilitado exitosamente.',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor habilitado exitosamente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al restaurar el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al restaurar el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Elimina permanentemente un productor.
     */
    public function forceDelete(Request $request, $id)
    {
        try {
            $producer = Producer::withTrashed()->findOrFail($id);
            $producer->forceDelete();

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Productor eliminado permanentemente.',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->route('producers.index')
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => 'Productor eliminado permanentemente.'
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al eliminar permanentemente el productor: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un productor.
     */
    public function toggleStatus(Request $request, Producer $producer)
    {
        try {
            $oldStatus = $producer->is_active;
            $producer->update(['is_active' => !$producer->is_active]);
            
            $newStatus = $producer->is_active;
            $statusText = $producer->is_active ? 'activado' : 'desactivado';

            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Productor {$statusText} exitosamente.",
                    'is_active' => $newStatus,
                    'status_text' => $producer->is_active ? 'Activo' : 'Inactivo',
                    'producer_id' => $producer->id
                ]);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => "Productor {$statusText} exitosamente."
                ]);
        } catch (\Exception $e) {
            // Respuesta para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ], 500);
            }

            // Respuesta tradicional
            return redirect()->back()
                ->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Error al cambiar el estado del productor: ' . $e->getMessage()
                ]);
        }
    }
}
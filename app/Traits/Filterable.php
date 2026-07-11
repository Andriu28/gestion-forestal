<?php

namespace App\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

trait Filterable
{
    /**
     * Valida el rango de fechas.
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function validateDateRange($dateFrom, $dateTo): ?RedirectResponse
    {
        if ($dateFrom && $dateTo && $dateTo < $dateFrom) {
            return Redirect::back()->withErrors([
                'date_to' => 'La fecha "Hasta" no puede ser anterior a la fecha "Desde".'
            ])->withInput();
        }

        if ($dateFrom && $dateFrom > now()->toDateString()) {
            return Redirect::back()->withErrors([
                'date_from' => 'La fecha "Desde" no puede ser futura.'
            ])->withInput();
        }

        if ($dateTo && $dateTo > now()->toDateString()) {
            return Redirect::back()->withErrors([
                'date_to' => 'La fecha "Hasta" no puede ser futura.'
            ])->withInput();
        }

        return null; // Sin errores
    }

    /**
     * Aplica filtros de fecha a la consulta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param string $column (opcional) nombre de la columna de fecha, por defecto 'created_at'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateFilters($query, $dateFrom, $dateTo, string $column = 'created_at')
    {
        if ($dateFrom) {
            $query->whereDate($column, '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate($column, '<=', $dateTo);
        }

        return $query;
    }

    /**
     * Aplica búsqueda flexible (sin acentos y sin distinción mayúsculas) en columnas y relaciones.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @param array $columns Columnas de la tabla principal a buscar (ej. ['name', 'email'])
     * @param array $relations Relaciones con sus columnas (ej. ['causer' => ['name', 'role']])
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearchFilter($query, $search, array $columns = [], array $relations = [])
    {
        if (empty($search)) {
            return $query;
        }

        $search = trim($search);
        $normalized = '%' . $search . '%';

        return $query->where(function ($q) use ($columns, $relations, $normalized) {
            // Búsqueda en columnas de la tabla principal
            foreach ($columns as $column) {
                $q->orWhereRaw("unaccent(LOWER({$column})) ILIKE unaccent(LOWER(?))", [$normalized]);
            }

            // Búsqueda en relaciones
            foreach ($relations as $relation => $relationColumns) {
                $q->orWhereHas($relation, function ($relQuery) use ($relationColumns, $normalized) {
                    foreach ($relationColumns as $column) {
                        $relQuery->orWhereRaw("unaccent(LOWER({$column})) ILIKE unaccent(LOWER(?))", [$normalized]);
                    }
                });
            }
        });
    }
}
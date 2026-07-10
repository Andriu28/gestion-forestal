<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auditoría</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { font-size: 11px; color: #555; margin: 2px 0; }
        .filters { font-size: 9px; color: #666; margin-bottom: 10px; }
        .text-center { text-align: center; }
        .badge { 
            display: inline-block; 
            padding: 0 6px; 
            border-radius: 12px; 
            font-size: 8px; 
            font-weight: bold;
            background: #e5e7eb;
            color: #1f2937;
        }
        .badge-admin { background: #dbeafe; color: #1e40af; }
        .badge-basico { background: #d1fae5; color: #065f46; }
        .badge-tecnico { background: #fef3c7; color: #92400e; }
        .badge-sistema { background: #e5e7eb; color: #4b5563; }
        .change { font-size: 8px; }
        .old { color: #dc2626; text-decoration: line-through; }
        .new { color: #16a34a; font-weight: bold; }
        .arrow { color: #9ca3af; }
        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #888; }
    </style>
</head>
<body>
    @php
        $roleTranslations = [
            'administrador' => 'Administrador',
            'tecnico'       => 'Técnico',
            'basico'        => 'Básico',
        ];
    @endphp
    <div class="header">
        <h1>Registro de Auditoría</h1>
        <p>Generado el: {{ $filters['generated_at'] }}</p>
        @if($filters['search'])
            <p class="filters">Filtro: "{{ $filters['search'] }}" | Total: {{ $filters['total'] }} registros</p>
        @else
            <p class="filters">Total: {{ $filters['total'] }} registros</p>
        @endif
    </div>
    <div class="filters">
        <p>
            Filtros aplicados:
            @if($filters['search']) Búsqueda: "{{ $filters['search'] }}" | @endif
            @if($filters['date_from']) Desde: {{ $filters['date_from'] }} | @endif
            @if($filters['date_to']) Hasta: {{ $filters['date_to'] }} | @endif
            @if($filters['role'] && $filters['role'] != 'all') Rol: {{ $filters['role'] }} | @endif
            @if($filters['event_type'] && $filters['event_type'] != 'all') Evento: {{ $filters['event_type'] }} | @endif
            @if($filters['subject_type'] && $filters['subject_type'] != 'all') Modelo: {{ $filters['subject_type'] }} @endif
        </p>
        <p>Total registros: {{ $filters['total'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Actividad</th>
                <th>Fecha</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $index => $activity)
                @php
                    $roleColors = [
                        'administrador' => 'badge-admin',
                        'basico' => 'badge-basico',
                        'tecnico' => 'badge-tecnico',
                        'default' => 'badge-sistema'
                    ];
                    $roleKey = $activity->causer?->role ?? 'default';
                    $roleLabel = $roleKey === 'default' ? 'Sistema' : ucfirst($roleKey);
                    $roleBadge = $roleColors[$roleKey] ?? 'badge-sistema';

                    // Traducción de descripción
                    $translations = [
                        'El usuario ha sido updated' => 'Usuario actualizado',
                        'El usuario ha sido restored' => 'Usuario restaurado',
                        'El usuario ha sido created' => 'Usuario creado',
                        'El usuario ha sido deleted' => 'Usuario eliminado',
                        'Polygon created' => 'Polígono creado',
                        'Polygon updated' => 'Polígono actualizado',
                        'Polygon deleted' => 'Polígono eliminado',
                        'Polygon restored' => 'Polígono restaurado',
                        'Producer created' => 'Productor creado',
                        'Producer updated' => 'Productor actualizado',
                        'Producer deleted' => 'Productor eliminado',
                        'Producer restored' => 'Productor restaurado',
                    ];
                    $description = $activity->description;
                    $translated = $translations[$description] ?? $description;
                    if (str_contains($description, "fue actualizado su rol")) {
                        $translated = "Rol actualizado";
                    }

                    // Detalles formateados
                    $details = '';
                    if ($activity->properties && $activity->properties->has('old_role') && $activity->properties->has('new_role')) {
                        $details = "Rol: {$activity->properties['old_role']} → {$activity->properties['new_role']}";
                    } elseif ($activity->properties && $activity->properties->has('attributes') && $activity->properties->has('old')) {
                        $excluded = ['description', 'updated_at', 'created_at'];
                        $changes = collect($activity->properties['attributes'])
                            ->filter(function($new, $attr) use ($activity, $excluded) {
                                if (in_array($attr, $excluded)) return false;
                                return ($new != ($activity->properties['old'][$attr] ?? null));
                            })
                            ->take(3);
                        if ($changes->count()) {
                            $parts = [];
                            foreach ($changes as $attr => $newVal) {
                                $oldVal = $activity->properties['old'][$attr] ?? 'N/A';
                                if (is_bool($oldVal) || $oldVal === '0' || $oldVal === '1') $oldVal = $oldVal ? 'Activo' : 'Inactivo';
                                if (is_bool($newVal) || $newVal === '0' || $newVal === '1') $newVal = $newVal ? 'Activo' : 'Inactivo';
                                $parts[] = $attr . ': ' . $oldVal . ' → ' . $newVal;
                            }
                            $details = implode('; ', $parts);
                        } else {
                            $details = 'Sin cambios relevantes';
                        }
                    } elseif ($activity->properties && $activity->properties->has('updated_fields')) {
                        $details = 'Campos actualizados';
                    } else {
                        $details = 'Sin detalles';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $activity->causer?->name ?? 'Sistema' }}</td>
                    <td><span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
                    <td>{{ $translated }}</td>
                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                    <td class="change">{{ $details }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No se encontraron actividades.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Sistema de Gestión Forestal - Cacao San José</div>
</body>
</html>
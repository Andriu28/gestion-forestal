<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auditoría - {{ $filters['company_name'] }}</title>
    <style>
        /* ========== ESTILO PROFESIONAL - TABLA TRADICIONAL ========== */
        @page {
            margin: 50px 30px;
            size: A4 landscape;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        
        /* ========== ENCABEZADO DEL REPORTE ========== */
        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000000;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .report-subtitle {
            font-size: 11px;
            color: #333333;
            margin-bottom: 10px;
        }
        
        .report-meta {
            font-size: 9px;
            color: #666666;
            margin-top: 8px;
        }
        
        .report-meta span {
            margin: 0 10px;
        }
        
        /* ========== INFORMACIÓN DE FILTROS ========== */
        .filters-section {
            margin-bottom: 20px;
            padding: 12px 15px;
            background: #f5f5f5;
            border: 1px solid #dddddd;
        }
        
        .filters-title {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 10px;
            display: inline-block;
            border-bottom: 1px solid #000000;
            padding-bottom: 2px;
        }
        
        .filters-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        
        .filter-row {
            display: table-row;
        }
        
        .filter-cell {
            display: table-cell;
            padding: 3px 0;
        }
        
        .filter-label {
            font-weight: bold;
            color: #333333;
            min-width: 120px;
            padding-right: 10px;
        }
        
        .filter-value {
            color: #000000;
        }
        
        .records-count {
            float: right;
            font-size: 9px;
            color: #666666;
            margin-top: 5px;
        }
        
        .clearfix {
            clear: both;
        }
        
        /* ========== TABLA PRINCIPAL ========== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8.5px;
        }
        
        .data-table thead {
            display: table-header-group;
        }
        
        .data-table th {
            background-color: #979797ff;
            color: #000000;
            font-weight: bold;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #cccccc;
            border-bottom: 2px solid #000000;
            text-transform: uppercase;
            font-size: 8px;
        }
        
        .data-table td {
            padding: 6px 6px;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .data-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        /* ========== BADGES ========== */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        
        .badge-admin {
            background-color: #e0e0e0;
            color: #000000;
            border: 1px solid #cccccc;
        }
        
        .badge-basico {
            background-color: #e8e8e8;
            color: #333333;
            border: 1px solid #dddddd;
        }
        
        .badge-tecnico {
            background-color: #f0f0f0;
            color: #666666;
            border: 1px solid #eeeeee;
        }
        
        .badge-sistema {
            background-color: #f5f5f5;
            color: #999999;
            border: 1px solid #eeeeee;
        }
        
        .badge-created {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .badge-updated {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .badge-deleted {
            background-color: #fce4ec;
            color: #c62828;
            border: 1px solid #f8bbd0;
        }
        
        .badge-restored {
            background-color: #fff3e0;
            color: #e65100;
            border: 1px solid #ffe0b2;
        }
        
        .badge-login {
            background-color: #f5f5f5;
            color: #616161;
            border: 1px solid #e0e0e0;
        }
        
        .badge-logout {
            background-color: #f5f5f5;
            color: #616161;
            border: 1px solid #e0e0e0;
        }
        
        .badge-role_change {
            background-color: #f3e5f5;
            color: #6a1b9a;
            border: 1px solid #e1bee7;
        }
        
        /* ========== DETALLES DE CAMBIOS ========== */
        .change-details {
            font-size: 7.5px;
            line-height: 1.4;
        }
        
        .change-item {
            display: inline-block;
            background: #f5f5f5;
            padding: 1px 5px;
            border-radius: 2px;
            margin: 1px 1px 1px 0;
            font-size: 7px;
            border: 1px solid #eeeeee;
        }
        
        .old-value {
            color: #c62828;
            text-decoration: line-through;
        }
        
        .new-value {
            color: #2e7d32;
            font-weight: bold;
        }
        
        .arrow-symbol {
            color: #999999;
            margin: 0 1px;
        }
        
        .no-details {
            color: #999999;
            font-style: italic;
            font-size: 7px;
        }
        
        /* ========== EVENTOS ========== */
        .event-created { color: #2e7d32; font-weight: bold; }
        .event-updated { color: #1565c0; font-weight: bold; }
        .event-deleted { color: #c62828; font-weight: bold; }
        .event-restored { color: #e65100; font-weight: bold; }
        .event-login { color: #616161; }
        .event-logout { color: #616161; }
        .event-role_change { color: #6a1b9a; font-weight: bold; }
        
        /* ========== UTILIDADES ========== */
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .nowrap {
            white-space: nowrap;
        }
        
        .description-cell {
            max-width: 200px;
            min-width: 100px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        /* ========== EVITAR SALTO DE PÁGINA EN FILAS ========== */
        .data-table tr {
            page-break-inside: avoid;
        }
        
        /* ========== PIE DE PÁGINA ========== */
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #000000;
            font-size: 8px;
            color: #666666;
            text-align: center;
        }
        
        .footer-info {
            margin-bottom: 5px;
        }
        
        /* ========== NUMERACIÓN DE PÁGINAS (Fija en cada página) ========== */
        .page-info {
            position: fixed;
            bottom: 25px;
            right: 30px;
            font-size: 8px;
            color: #999999;
            text-align: right;
        }
        
        .page-info p {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

    <!-- ============================================================ -->
    <!-- ENCABEZADO -->
    <!-- ============================================================ -->
    <div class="report-header">
        <div class="report-title">Registro de Auditoría</div>
        <div class="report-subtitle">{{ $filters['company_name'] }} - Sistema de Gestión Forestal</div>
        <div class="report-meta">
            <span> Generado: {{ $filters['generated_at'] }}</span>
            <span>|</span>
            <span> Total registros: {{ $filters['total'] }}</span>
            <span>|</span>
            <span> Usuario: {{ auth()->user()->name ?? 'Sistema' }}</span>
            <span>|</span>
            <span> IP: {{ request()->ip() ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- FILTROS APLICADOS -->
    <!-- ============================================================ -->
    <div class="filters-section">
        <div class="filters-title">🔍 Filtros Aplicados</div>
        <div class="records-count">Mostrando {{ $activities->count() }} registros</div>
        <div class="clearfix"></div>
        
        <div class="filters-grid">
            @php
                $filtersApplied = [];
                if($filters['search']) $filtersApplied[] = ['label' => 'Búsqueda', 'value' => '"'.$filters['search'].'"'];
                if($filters['date_from']) $filtersApplied[] = ['label' => 'Desde', 'value' => $filters['date_from']];
                if($filters['date_to']) $filtersApplied[] = ['label' => 'Hasta', 'value' => $filters['date_to']];
                if($filters['role'] && $filters['role'] != 'all') $filtersApplied[] = ['label' => 'Rol', 'value' => ucfirst($filters['role'])];
                if($filters['event_type'] && $filters['event_type'] != 'all') $filtersApplied[] = ['label' => 'Evento', 'value' => ucfirst($filters['event_type'])];
                if($filters['subject_type'] && $filters['subject_type'] != 'all') $filtersApplied[] = ['label' => 'Modelo', 'value' => ucfirst($filters['subject_type'])];
            @endphp

            @forelse($filtersApplied as $filter)
            <div class="filter-row">
                <div class="filter-cell filter-label">{{ $filter['label'] }}:</div>
                <div class="filter-cell filter-value">{{ $filter['value'] }}</div>
            </div>
            @empty
            <div class="filter-row">
                <div class="filter-cell filter-label">Ningún filtro aplicado</div>
                <div class="filter-cell filter-value">Mostrando todos los registros</div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TABLA DE DATOS -->
    <!-- ============================================================ -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 14%;">Usuario</th>
                <th style="width: 10%;">Rol</th>
                <th style="width: 18%;">Actividad</th>
                <th style="width: 12%;">Fecha/Hora</th>
                <th style="width: 42%;">Detalles</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $index => $activity)
                @php
                    // ===== ROL =====
                    $roleKey = $activity->causer?->role ?? 'sistema';
                    $roleBadge = 'badge-' . ($roleKey === 'sistema' ? 'sistema' : $roleKey);
                    $roleLabel = $roleKey === 'sistema' ? 'Sistema' : ucfirst($roleKey);

                    // ===== ACTIVIDAD =====
                    $event = $activity->event ?? 'default';
                    $eventClass = 'event-' . $event;
                    $eventBadge = 'badge-' . $event;
                    
                    $eventTranslations = [
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                        'login' => 'Inicio sesión',
                        'logout' => 'Cierre sesión',
                        'role_change' => 'Cambio rol',
                    ];
                    
                    $modelName = $activity->subject_type ? class_basename($activity->subject_type) : null;
                    $modelTranslations = ['User' => 'Usuario', 'Polygon' => 'Polígono', 'Producer' => 'Productor'];
                    $modelTranslation = $modelTranslations[$modelName] ?? $modelName ?? '';
                    
                    if (str_contains($activity->description, 'fue actualizado su rol')) {
                        $translated = 'Rol actualizado';
                    } elseif (!empty($modelTranslation) && isset($eventTranslations[$event])) {
                        $translated = $modelTranslation . ' ' . $eventTranslations[$event];
                    } else {
                        $translated = $activity->description;
                    }

                    // ===== DETALLES =====
                    $detailsHtml = '<span class="no-details">Sin detalles</span>';
                    
                    if ($activity->properties && $activity->properties->has('old_role') && $activity->properties->has('new_role')) {
                        $oldRole = $activity->properties['old_role'] ?? 'N/A';
                        $newRole = $activity->properties['new_role'] ?? 'N/A';
                        $detailsHtml = '<span class="change-item">Rol: <span class="old-value">' . $oldRole . '</span> <span class="arrow-symbol">→</span> <span class="new-value">' . $newRole . '</span></span>';
                    }
                    elseif ($activity->properties && $activity->properties->has('attributes') && $activity->properties->has('old')) {
                        $excluded = ['updated_at', 'created_at', 'deleted_at'];
                        $changes = collect($activity->properties['attributes'])
                            ->filter(function($new, $attr) use ($activity, $excluded) {
                                if (in_array($attr, $excluded)) return false;
                                $old = $activity->properties['old'][$attr] ?? null;
                                return $new != $old;
                            })->take(3);
                        
                        if ($changes->count() > 0) {
                            $parts = [];
                            foreach ($changes as $attr => $newVal) {
                                $oldVal = $activity->properties['old'][$attr] ?? null;
                                if (is_bool($oldVal) || $oldVal === '0' || $oldVal === '1') $oldVal = $oldVal ? 'Activo' : 'Inactivo';
                                if (is_bool($newVal) || $newVal === '0' || $newVal === '1') $newVal = $newVal ? 'Activo' : 'Inactivo';
                                $fieldTranslations = ['name' => 'Nombre', 'email' => 'Correo', 'role' => 'Rol', 'is_active' => 'Estado', 'producer_id' => 'Productor', 'parish_id' => 'Parroquia', 'description' => 'Descripción', 'geometry' => 'Geometría', 'area' => 'Área', 'rut' => 'RUT', 'phone' => 'Teléfono', 'address' => 'Dirección'];
                                $fieldName = $fieldTranslations[$attr] ?? $attr;
                                $parts[] = '<span class="change-item">' . $fieldName . ': <span class="old-value">' . ($oldVal ?? 'N/A') . '</span> <span class="arrow-symbol">→</span> <span class="new-value">' . $newVal . '</span></span>';
                            }
                            $detailsHtml = implode(' ', $parts);
                        }
                    }
                    elseif ($activity->properties && $activity->properties->has('updated_fields')) {
                        $fields = is_array($activity->properties['updated_fields']) ? implode(', ', $activity->properties['updated_fields']) : $activity->properties['updated_fields'];
                        $detailsHtml = '<span class="change-item">Campos: ' . $fields . '</span>';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $activity->causer?->name ?? 'Sistema' }}</strong>
                        @if($activity->causer?->email)
                            <br><span style="font-size:7px;color:#999999;">{{ $activity->causer->email }}</span>
                        @endif
                    </td>
                    <td><span class="status-badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
                    <td>
                        <span class="{{ $eventClass }}">{{ $translated }}</span>
                        @if($activity->subject_type)
                            <br><span style="font-size:7px;color:#999999;">{{ $modelTranslation }}</span>
                        @endif
                    </td>
                    <td class="nowrap">
                        {{ $activity->created_at->format('d/m/Y') }}
                        <br><span style="font-size:7px;color:#999999;">{{ $activity->created_at->format('H:i:s') }}</span>
                    </td>
                    <td><div class="change-details">{!! $detailsHtml !!}</div></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:30px 0;color:#999999;">
                        <span style="font-size:18px;">📭</span>
                        <br><br>
                        <strong style="font-size:11px;color:#333333;">No se encontraron actividades</strong>
                        <br>
                        <span style="font-size:8px;">Para los filtros seleccionados no hay registros disponibles</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- PIE DE PÁGINA -->
    <!-- ============================================================ -->
    <div class="report-footer">
        <div class="footer-info">{{ $filters['company_name'] }} · RIF: {{ $filters['rif'] }} · Sistema de Gestión Forestal</div>
        <div class="footer-info">Documento generado automáticamente - No requiere firma</div>
        <div class="footer-info">Confidencial - Uso exclusivo interno</div>
        <div class="footer-info">© {{ date('Y') }} {{ $filters['company_name'] }} - Todos los derechos reservados</div>
    </div>

    <!-- ============================================================ -->
    <!-- NUMERACIÓN DE PÁGINAS (Fija en cada página) -->
    <!-- ============================================================ -->
    <div class="page-info">
        <p>© {{ date('Y') }} - {{ $filters['company_name'] }}</p>
        <p>Página {PAGE_NUM} de {PAGE_COUNT}</p>
    </div>

    <!-- ============================================================ -->
    <!-- SCRIPT PHP PARA ACTUALIZAR LA NUMERACIÓN -->
    <!-- ============================================================ -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $boldFont = $fontMetrics->getFont("Helvetica", "bold");
            
            $pageWidth = 842;
            $pageHeight = 595;
            $margin = 40;
            $bottomY = 25;
            
            // Actualizar el texto de la página en cada página
            $pageText = "© {{ date('Y') }} - {{ addslashes($filters['company_name']) }}";
            $pdf->page_text($pageWidth - 250, $bottomY, $pageText, $font, 7, [0.5, 0.5, 0.5]);
            
            $pageNumText = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $pdf->page_text($pageWidth - 80, $bottomY, $pageNumText, $boldFont, 7, [0.3, 0.3, 0.3]);
        }
    </script>
</body>
</html>
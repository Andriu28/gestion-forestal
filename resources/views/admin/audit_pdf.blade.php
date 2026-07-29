<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Auditoría</title>
    <style>
        /* Márgenes de la hoja: arriba/abajo reservan espacio para el
           encabezado y pie de página repetidos (ver el bloque de script
           embebido al final del archivo). Los laterales son más angostos que en la
           versión horizontal para aprovechar mejor el ancho, ya que en
           vertical hay menos espacio para las 6 columnas de la tabla. */
        @page {
            margin: 68px 34px 65px 34px;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #222; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #a0a0a0; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background-color: #3f4348; color: #fff; font-weight: bold; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }

        /* ---------- Encabezado institucional (sólo primera página) ---------- */
        .letterhead { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .letterhead td { border: none; padding: 0; vertical-align: middle; }
        /* ==========================================================
           TAMAÑO DEL LOGO — edita aquí.
           Cambia el 46px de las 3 reglas de abajo (svg / img / fallback)
           por el mismo valor en las tres para que el logo real y el
           respaldo con iniciales midan igual. Si lo agrandas más allá
           de ~60px, aumenta también el "width" de .logo-cell (justo
           abajo) para que la columna del logo no lo recorte.
           ========================================================== */
        .letterhead .logo-cell { width: 60px; } /* ancho de la celda que contiene el logo */
        .letterhead .logo-cell svg { width: 46px; height: 46px; } /* tamaño si el logo es SVG */
        .letterhead .logo-cell img { width: 76px; height: 76px; } /* tamaño si el logo es PNG/JPG */
        .letterhead .logo-fallback {
            width: 56px; height: 56px; /* tamaño del monograma cuando no hay logo cargado */
            background: #1f2937;
            color: #fff;
            font-size: 16px; /* si cambias el tamaño de arriba, ajusta este ~ a un tercio del alto */
            font-weight: bold;
            text-align: center;
            line-height: 76px; /* debe ser igual al "height" de arriba para que las iniciales centren bien */
            border-radius: 6px;
        }
        .letterhead .org-cell { padding-left: 12px; }
        .letterhead .org-name { font-size: 24px; font-weight: bold; color: #000000; margin: 0; }
        .letterhead .org-rif { font-size: 12px; color: #3c3c3c; margin: 1px 0 0; }
        .letterhead .doc-info-cell { padding-left: 24px; text-align: right; vertical-align: middle; }
        .letterhead .doc-title { font-size: 16px; color: #22272f; margin: 4px 0 0; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .letterhead .doc-meta { font-size: 12px; color: #5d5d5d; margin: 2px 0 0; }
        .letterhead-rule { border: none; border-top: 2px solid #1f293779; border-bottom: 1px solid #d1d5db; margin: 0 0 12px; height: 0; }

        /* ---------- Marca de agua / fondo ---------- */
        .pdf-content { position: relative; z-index: 1; }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-40deg);
            font-size: 72px;
            color: rgba(0, 0, 0, 0.08);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            z-index: 0;
            white-space: nowrap;
        }

        /* ---------- Tarjetas de resumen ---------- */
        .summary-strip { border-collapse: separate; border-spacing: 6px 0; margin: 0 0 10px -6px; width: calc(100% + 12px); }
        .summary-strip td { border: 2px solid #d2d3d5; background: #f8fafc; border-radius: 4px; padding: 5px 7px; width: 25%; }
        .stat-label { font-size: 10px; color: #4a4c51; text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 2px; }
        .stat-value { font-size: 11px; color: #1f2937; font-weight: bold; margin: 0; }

        /* ---------- Filtros aplicados ---------- */
        .filters-line { margin: 0 0 12px; }
        .filter-tag {
            display: inline-block;
            background: #eef2f7;
            color: #1f2937;
            border: 1px solid #dbe3ec;
            border-radius: 10px;
            padding: 2px 8px;
            margin: 0 6px 4px 0;
            font-size: 10px;
        }

        /* ---------- Tabla de actividad ---------- */
        .text-center { text-align: center; }
        tbody tr:nth-child(even) td { background-color: #0b0b0b17; }
        tbody tr { page-break-inside: avoid; }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            background: #e5e7eb;
            color: #37393c;
        }
        .badge-admin { background: #dbeafe; color: #03050e; }
        .badge-basico { background: #d1fae5; color: #000000; }
        .badge-tecnico { background: #fef3c7; color: #000000; }
        .badge-sistema { background: #e5e7eb; color: #040405; }
        .change { font-size: 8px; line-height: 1.5; }
        .old { color: #dc2626; text-decoration: line-through; }
        .new { color: #16a34a; font-weight: bold; }
        .arrow { color: #9ca3af; margin: 0 3px; }
        .muted { color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>
    @php
        $words = preg_split('/\s+/', trim($filters['company_name']));
        $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
    @endphp

    <div class="watermark">Confidencial</div>

    <div class="pdf-content">
        {{-- ============ Encabezado institucional (portada del reporte) ============ --}}
        <table class="letterhead">
        <tr>
            <td class="logo-cell">
                @if($logo && $logo['type'] === 'image')
                    <img src="{{ $logo['src'] }}" alt="{{ $filters['company_name'] }}">
                @elseif($logo && $logo['type'] === 'svg')
                    {!! $logo['content'] !!}
                @else
                    <div class="logo-fallback">{{ $initials }}</div>
                @endif
            </td>
            <td class="org-cell">
                <p class="org-name">{{ $filters['company_name'] }}</p>
                <p class="org-rif">RIF: {{ $filters['rif'] }}</p>
                <p class="doc-meta">Venezuela es cacao</p>
            </td>
            <td class="doc-info-cell">
                <p class="doc-title">Registro de Auditoría</p>
                <p class="doc-meta">Generado el {{ $filters['generated_at'] }}</p>
                <p class="doc-meta"> por {{ $filters['generated_by'] }}</p>
            </td>
        </tr>
        
    </table>
    <hr class="letterhead-rule">

    {{-- ============ Resumen ejecutivo del reporte ============ --}}
    <table class="summary-strip">
        <tr>
            <td>
                <p class="stat-label">Total de registros</p>
                <p class="stat-value">{{ $filters['total'] }}</p>
            </td>
            <td>
                <p class="stat-label">Usuarios involucrados</p>
                <p class="stat-value">{{ $filters['distinct_users'] }}</p>
            </td>
            <td>
                <p class="stat-label">Período cubierto</p>
                <p class="stat-value">{{ $filters['period_label'] }}</p>
            </td>
        </tr>
    </table>

    {{-- ============ Filtros activos, como chips ============ --}}
    @if(count($activeFilters))
        <p class="filters-line">
            @foreach($activeFilters as $label => $value)
                <span class="filter-tag"><strong>{{ $label }}:</strong> {{ $value }}</span>
            @endforeach
        </p>
    @endif

    {{-- ============ Detalle de actividad ============ --}}
    <table>
        {{-- En vertical hay menos ancho disponible que en horizontal, así que
             "Detalles" (la columna con más texto) recibe más proporción y
             "Rol"/"Fecha" se ajustan a lo mínimo necesario. Los porcentajes
             deben sumar 100. --}}
        <colgroup>
            <col style="width:3%">
            <col style="width:12%">
            <col style="width:7%">
            <col style="width:13%">
            <col style="width:12%">
            <col style="width:53%">
        </colgroup>
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

                    // Detalles formateados (usa .old / .new / .arrow definidos arriba
                    // para mostrar el "antes -> después" como un diff visual real)
                    $details = '<span class="muted">Sin detalles</span>';
                    if ($activity->properties && $activity->properties->has('old_role') && $activity->properties->has('new_role')) {
                        $details = '<strong>Rol:</strong> '
                            . '<span class="old">' . e($activity->properties['old_role']) . '</span>'
                            . '<span class="arrow">&#8594;</span>'
                            . '<span class="new">' . e($activity->properties['new_role']) . '</span>';
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
                                $parts[] = '<strong>' . e($attr) . ':</strong> '
                                    . '<span class="old">' . e($oldVal) . '</span>'
                                    . '<span class="arrow">&#8594;</span>'
                                    . '<span class="new">' . e($newVal) . '</span>';
                            }
                            $details = implode('<br>', $parts);
                        } else {
                            $details = '<span class="muted">Sin cambios relevantes</span>';
                        }
                    } elseif ($activity->properties && $activity->properties->has('updated_fields')) {
                        $details = '<span class="muted">Campos actualizados</span>';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $activity->causer?->name ?? 'Sistema' }}</td>
                    <td><span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span></td>
                    <td>{{ $translated }}</td>
                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                    <td class="change">{!! $details !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No se encontraron actividades.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{--
        Encabezado y pie de página repetidos en TODAS las páginas.
        DomPDF no soporta content: counter(page) de forma confiable ni
        repite HTML normal en cada página, así que se usa su mecanismo
        nativo vía script embebido con $pdf->page_text(). El HTML de arriba
        (letterhead, resumen, filtros) sólo aparece una vez, al inicio del
        documento; esto es lo que realmente se repite en cada hoja impresa.
    --}}
    <script type="text/php">
    if (isset($pdf)) {
        $fontRegular = $fontMetrics->getFont("DejaVu Sans", "normal");
        $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");

        // Detectar orientación automáticamente
        $pageWidth = $pdf->get_width();
        $pageHeight = $pdf->get_height();

        // Si el ancho es > alto, es landscape
        // Si el alto es > ancho, es portrait
        $isLandscape = $pageWidth > $pageHeight;

        // Para A4:
        // Portrait: 595 x 842
        // Landscape: 842 x 595
        // Forzamos los valores exactos para evitar desviaciones
        if ($isLandscape) {
            $pageWidth = 842;
            $pageHeight = 595;
        } else {
            $pageWidth = 595;
            $pageHeight = 842;
        }

        $margin = 28;
        $grayColor = [0.42, 0.45, 0.5];
        $navyColor = [0.12, 0.16, 0.22];

        $companyName = "{{ addslashes($filters['company_name']) }}";
        $rif = "{{ addslashes($filters['rif']) }}";
        $generatedAt = "{{ addslashes($filters['generated_at']) }}";

        // ---------- Franja superior ----------
        $topY = 30;
        $topLeft = $companyName . "  ·  Registro de Auditoría";
        $pdf->page_text($margin, $topY, $topLeft, $fontBold, 8, $navyColor);

        $topRight = "Documento de uso interno · Confidencial";
        $topRightWidth = $fontMetrics->getTextWidth($topRight, $fontRegular, 8);
        $pdf->page_text($pageWidth - $topRightWidth - $margin, $topY, $topRight, $fontRegular, 8, $grayColor);

        // ---------- Pie de página ----------
        $bottomMargin = 35;
        $bottomY = $pageHeight - $bottomMargin;
        
        $pageText = "Página {PAGE_NUM} de {PAGE_COUNT}";
        $pageTextWidth = $fontMetrics->getTextWidth($pageText, $fontBold, 8);
        
        // 🔽 CAMBIA EL VALOR DE centerX PARA MOVER IZQUIERDA/DERECHA 🔽
        $centerX = 266;  // ← VALOR ACTUAL - CÁMBIALO SEGÚN NECESITES
        
        $pdf->page_text($centerX, $bottomY, $pageText, $fontBold, 8, $navyColor);

        $footerLeft = $companyName . " · RIF: " . $rif;
        $pdf->page_text($margin, $bottomY, $footerLeft, $fontRegular, 8, $grayColor);

        $footerRight = "Generado: " . $generatedAt;
        $footerRightWidth = $fontMetrics->getTextWidth($footerRight, $fontRegular, 8);
        $pdf->page_text($pageWidth - $footerRightWidth - $margin, $bottomY, $footerRight, $fontRegular, 8, $grayColor);

        // ✅ LÍNEA ELIMINADA - YA NO ESTÁ AQUÍ
    }
</script>
</body>
</html>
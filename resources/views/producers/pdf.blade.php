<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productores</title>
    <style>
        /* ========== MISMOS ESTILOS QUE AUDITORÍA ========== */
        @page {
            margin: 68px 34px 65px 34px;
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 9.5px; 
            color: #222; 
            margin: 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            border: 1px solid #a0a0a0; 
            padding: 4px 5px; 
            text-align: left; 
            vertical-align: top; 
        }
        th { 
            background-color: #3f4348; 
            color: #fff; 
            font-weight: bold; 
            font-size: 9px; 
            text-transform: uppercase; 
            letter-spacing: 0.3px; 
        }

        /* ---------- Encabezado institucional ---------- */
        .letterhead { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 3px; 
        }
        .letterhead td { 
            border: none; 
            padding: 0; 
            vertical-align: middle; 
        }
        .letterhead .logo-cell { 
            width: 60px; 
        }
        .letterhead .logo-cell svg { 
            width: 46px; 
            height: 46px; 
        }
        .letterhead .logo-cell img { 
            width: 76px; 
            height: 76px; 
        }
        .letterhead .logo-fallback {
            width: 56px; 
            height: 56px;
            background: #1f2937;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            line-height: 76px;
            border-radius: 6px;
        }
        .letterhead .org-cell { 
            padding-left: 12px; 
        }
        .letterhead .org-name { 
            font-size: 24px; 
            font-weight: bold; 
            color: #000000; 
            margin: 0; 
        }
        .letterhead .org-rif { 
            font-size: 12px; 
            color: #3c3c3c; 
            margin: 1px 0 0; 
        }
        .letterhead .doc-info-cell { 
            padding-left: 24px; 
            text-align: right; 
            vertical-align: middle; 
        }
        .letterhead .doc-title { 
            font-size: 16px; 
            color: #22272f; 
            margin: 4px 0 0; 
            font-weight: 500; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .letterhead .doc-meta { 
            font-size: 12px; 
            color: #5d5d5d; 
            margin: 2px 0 0; 
        }
        .letterhead-rule { 
            border: none; 
            border-top: 2px solid #1f293779; 
            border-bottom: 1px solid #d1d5db; 
            margin: 0 0 12px; 
            height: 0; 
        }

        /* ---------- Marca de agua ---------- */
        .pdf-content { 
            position: relative; 
            z-index: 1; 
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-40deg);
            font-size: 68px;
            color: rgba(0, 0, 0, 0.08);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            z-index: 0;
            white-space: nowrap;
        }

        /* ---------- Tarjetas de resumen ---------- */
        .summary-strip { 
            border-collapse: separate; 
            border-spacing: 6px 0; 
            margin: 0 0 10px -6px; 
            width: calc(100% + 12px); 
        }
        .summary-strip td { 
            border: 2px solid #d2d3d5; 
            background: #f8fafc; 
            border-radius: 4px; 
            padding: 5px 7px; 
            width: 25%; 
        }
        .stat-label { 
            font-size: 10px; 
            color: #4a4c51; 
            text-transform: uppercase; 
            letter-spacing: 0.3px; 
            margin: 0 0 2px; 
        }
        .stat-value { 
            font-size: 11px; 
            color: #1f2937; 
            font-weight: bold; 
            margin: 0; 
        }

        /* ---------- Filtros aplicados ---------- */
        .filters-line { 
            margin: 0 0 12px; 
        }
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

        /* ---------- Tabla ---------- */
        .text-center { 
            text-align: center; 
        }
        tbody tr:nth-child(even) td { 
            background-color: #0b0b0b17; 
        }
        tbody tr { 
            page-break-inside: avoid; 
        }

        /* ---------- Badges ---------- */
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            background: #e5e7eb;
            color: #37393c;
        }
        .badge-active {
            background: #d1fae5;
            color: #000000;
        }
        .badge-inactive {
            background: #fef3c7;
            color: #000000;
        }
        .badge-deleted {
            background: #fce4ec;
            color: #000000;
        }

        /* ---------- Descripción ---------- */
        .description-cell {
            max-width: 150px;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .nowrap {
            white-space: nowrap;
        }

    </style>
</head>
<body>
    @php
        $words = preg_split('/\s+/', trim($filters['company_name'] ?? 'Cacao San José'));
        $initials = strtoupper(mb_substr($words[0] ?? '', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
    @endphp

    <!-- MARCA DE AGUA -->
    <div class="watermark">{{ $filters['company_name'] ?? 'Cacao San José' }}</div>

    <div class="pdf-content">
        <!-- ============================================================ -->
        <!-- ENCABEZADO INSTITUCIONAL -->
        <!-- ============================================================ -->
        <table class="letterhead">
            <tr>
                <td class="logo-cell">
                    @if($logo && $logo['type'] === 'image')
    <img src="{{ $logo['src'] }}" alt="{{ $filters['company_name'] ?? 'Cacao San José' }}">
@elseif($logo && $logo['type'] === 'svg')
    {!! $logo['content'] !!}
@else
    <div class="logo-fallback">{{ $initials }}</div>
@endif
                </td>
                <td class="org-cell">
                    <p class="org-name">{{ $filters['company_name'] ?? 'Cacao San José' }}</p>
                    <p class="org-rif">RIF: {{ $filters['rif'] ?? 'J-12345678-9' }}</p>
                    <p class="doc-meta">Venezuela es cacao</p>
                </td>
                <td class="doc-info-cell">
                    <p class="doc-title">Reporte de Productores</p>
                    <p class="doc-meta">Generado el {{ $filters['generated_at'] }}</p>
                    <p class="doc-meta">por {{ $filters['generated_by'] ?? auth()->user()->name ?? 'Sistema' }}</p>
                </td>
            </tr>
        </table>
        <hr class="letterhead-rule">

        <!-- ============================================================ -->
        <!-- RESUMEN EJECUTIVO -->
        <!-- ============================================================ -->
        <table class="summary-strip">
            <tr>
                <td>
                    <p class="stat-label">Total de registros</p>
                    <p class="stat-value">{{ $filters['total'] }}</p>
                </td>
                <td>
                    <p class="stat-label">Activos</p>
                    <p class="stat-value">{{ $filters['active_count'] ?? 0 }}</p>
                </td>
                <td>
                    <p class="stat-label">Inactivos</p>
                    <p class="stat-value">{{ $filters['inactive_count'] ?? 0 }}</p>
                </td>
                <td>
                    <p class="stat-label">Eliminados</p>
                    <p class="stat-value">{{ $filters['deleted_count'] ?? 0 }}</p>
                </td>
            </tr>
        </table>

        <!-- ============================================================ -->
        <!-- FILTROS APLICADOS -->
        <!-- ============================================================ -->
        @php
            $activeFilters = [];
            if(!empty($filters['search'])) $activeFilters['Búsqueda'] = '"' . $filters['search'] . '"';
            if(!empty($filters['status']) && $filters['status'] !== 'all') {
                $statusLabels = ['active' => 'Activos', 'inactive' => 'Inactivos', 'deleted' => 'Eliminados'];
                $activeFilters['Estado'] = $statusLabels[$filters['status']] ?? $filters['status'];
            }
        @endphp

        @if(count($activeFilters))
            <p class="filters-line">
                @foreach($activeFilters as $label => $value)
                    <span class="filter-tag"><strong>{{ $label }}:</strong> {{ $value }}</span>
                @endforeach
            </p>
        @endif

        <!-- ============================================================ -->
        <!-- TABLA DE PRODUCTORES -->
        <!-- ============================================================ -->
        <table>
            <colgroup>
                <col style="width:5%">
                <col style="width:18%">
                <col style="width:18%">
                <col style="width:25%">
                <col style="width:10%">
                <col style="width:12%">
                <col style="width:12%">
            </colgroup>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Creación</th>
                    <th>Actualización</th>
                </tr>
            </thead>
            <tbody>
                @forelse($producers as $index => $producer)
                    @php
                        if($producer->trashed()) {
                            $statusBadge = 'badge-deleted';
                            $statusLabel = 'Eliminado';
                        } elseif($producer->is_active) {
                            $statusBadge = 'badge-active';
                            $statusLabel = 'Activo';
                        } else {
                            $statusBadge = 'badge-inactive';
                            $statusLabel = 'Inactivo';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $producer->name }}</td>
                        <td>{{ $producer->lastname ?? '—' }}</td>
                        <td class="description-cell">
                            {{ $producer->description ? Str::limit($producer->description, 90) : 'Sin descripción' }}
                        </td>
                        <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                        <td class="nowrap">{{ $producer->created_at->format('d/m/Y H:i') }}</td>
                        <td class="nowrap">{{ $producer->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron productores.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- ============================================================ -->
    <!-- SCRIPT PHP PARA NUMERACIÓN DE PÁGINAS (como en auditoría) -->
    <!-- ============================================================ -->
    <script type="text/php">
        if (isset($pdf)) {
            $fontRegular = $fontMetrics->getFont("DejaVu Sans", "normal");
            $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");

            $pageWidth = $pdf->get_width();
            $pageHeight = $pdf->get_height();

            $isLandscape = $pageWidth > $pageHeight;

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

            $companyName = "{{ addslashes($filters['company_name'] ?? 'Cacao San José') }}";
            $rif = "{{ addslashes($filters['rif'] ?? 'J-12345678-9') }}";
            $generatedAt = "{{ addslashes($filters['generated_at']) }}";

            // ---------- Franja superior ----------
            $topY = 30;
            $topLeft = $companyName . "  ·  Reporte de Productores";
            $pdf->page_text($margin, $topY, $topLeft, $fontBold, 8, $navyColor);

            $topRight = "Documento de uso interno · " . $companyName;
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
        }
    </script>
</body>
</html>
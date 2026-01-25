<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Productores</title>
    <style>
        /* ESTILO PROFESIONAL PARA PDF - TABLA TRADICIONAL */
        @page {
            margin: 50px 30px;
            size: A4 portrait;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        
        /* ENCABEZADO DEL REPORTE */
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
        
        /* INFORMACIÓN DE FILTROS */
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
        
        /* TABLA PRINCIPAL */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
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
            font-size: 9px;
        }
        
        .data-table td {
            padding: 7px 6px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .data-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        /* ESTADOS */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .status-active {
            background-color: #f0f0f0;
            color: #000000;
            border: 1px solid #cccccc;
        }
        
        .status-inactive {
            background-color: #f5f5f5;
            color: #666666;
            border: 1px solid #dddddd;
        }
        
        .status-deleted {
            background-color: #f8f8f8;
            color: #999999;
            border: 1px solid #eeeeee;
            text-decoration: line-through;
        }
        
        /* DESCRIPCIÓN */
        .description-cell {
            max-width: 200px;
            min-width: 150px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        /* PIE DE PÁGINA */
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
        
        .page-info {
            position: fixed;
            bottom: 25px;
            right: 30px;
            font-size: 8px;
            color: #999999;
        }
        
        /* ENCABEZADO DE PÁGINA */
        .page-header {
            position: fixed;
            top: -40px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            border-bottom: 1px solid #000000;
            padding: 10px 30px;
            background: #ffffff;
        }
        
        /* UTILIDADES */
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
        
        /* EVITAR SALTO DE PÁGINA EN FILAS */
        .data-table tr {
            page-break-inside: avoid;
        }
        
        /* CONTADOR DE REGISTROS */
        .records-count {
            float: right;
            font-size: 9px;
            color: #666666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="report-header">
        <div class="report-title">Reporte de Productores</div>
        <div class="report-subtitle">Sistema de Gestión de Productores</div>
        <div class="report-meta">
            <span>Generado: {{ $filters['generated_at'] }}</span>
            <span>|</span>
            <span>Total registros: {{ $filters['total'] }}</span>
        </div>
    </div>
    
    <!-- FILTROS APLICADOS -->
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados</div>
        <div class="records-count">Mostrando {{ $producers->count() }} registros</div>
        <div class="clearfix"></div>
        
        <div class="filters-grid">
            <div class="filter-row">
                <div class="filter-cell filter-label">Búsqueda:</div>
                <div class="filter-cell filter-value">{{ $filters['search'] ?: 'Ninguna' }}</div>
            </div>
            <div class="filter-row">
                <div class="filter-cell filter-label">Estado:</div>
                <div class="filter-cell filter-value">
                    @switch($filters['status'])
                        @case('active') Activos @break
                        @case('inactive') Inactivos @break
                        @case('deleted') Eliminados @break
                        @default Todos los estados
                    @endswitch
                </div>
            </div>
        </div>
    </div>
    
    <!-- TABLA DE DATOS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 18%;">Nombre</th>
                <th style="width: 18%;">Apellido</th>
                <th style="width: 30%;">Descripción</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 10%;">Creación</th>
                <th style="width: 9%;">Actualización</th>
            </tr>
        </thead>
        <tbody>
            @foreach($producers as $producer)
            <tr>
                <td class="text-center">{{ $producer->id }}</td>
                <td class="text-bold">{{ $producer->name }}</td>
                <td>{{ $producer->lastname ?? '—' }}</td>
                <td class="description-cell">
                    {{ $producer->description ? Str::limit($producer->description, 90) : 'Sin descripción' }}
                </td>
                <td class="text-center">
                    @if($producer->deleted_at)
                        <span class="status-badge status-deleted">Eliminado</span>
                    @else
                        <span class="status-badge {{ $producer->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $producer->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    @endif
                </td>
                <td class="nowrap">{{ $producer->created_at->format('d/m/Y H:i') }}</td>
                <td class="nowrap">{{ $producer->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- PIE DE PÁGINA -->
    <div class="report-footer">
        <div class="footer-info">Documento generado automáticamente - No requiere firma</div>
        <div class="footer-info">Confidencial - Uso exclusivo interno</div>
        <div class="footer-info">© {{ date('Y') }} Sistema de Gestión de Productores</div>
    </div>
    
    <!-- NUMERO DE PAGINA -->
    <div class="page-info">
        <p>© {{ date('Y') }} - Sistema de Gestión de Productores</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>
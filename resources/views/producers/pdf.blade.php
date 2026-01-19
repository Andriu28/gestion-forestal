<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Productores</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
        }
        
        .header h1 {
            color: #4F46E5;
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .header p {
            color: #666;
            margin: 0;
        }
        
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #4b5563;
        }
        
        .info-value {
            color: #111827;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th {
            background-color: #4F46E5;
            color: white;
            text-align: left;
            padding: 12px 8px;
            font-weight: 600;
            border: none;
        }
        
        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tr:hover {
            background-color: #f3f4f6;
        }
        
        .status-active {
            background-color: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            display: inline-block;
        }
        
        .status-inactive {
            background-color: #f59e0b;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            display: inline-block;
        }
        
        .status-deleted {
            background-color: #ef4444;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            display: inline-block;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .logo {
            width: 100px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Productores</h1>
        <p>Generado el: {{ $filters['generated_at'] }}</p>
        <p>Total de registros: {{ $filters['total'] }}</p>
    </div>
    
    <div class="info-box">
        <h3 style="color: #4F46E5; margin-bottom: 15px;">Filtros aplicados</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Búsqueda:</span>
                <span class="info-value">{{ $filters['search'] ?: 'Ninguna' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    @switch($filters['status'])
                        @case('active') Activos @break
                        @case('inactive') Inactivos @break
                        @case('deleted') Eliminados @break
                        @default Todos
                    @endswitch
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Total registros:</span>
                <span class="info-value">{{ $filters['total'] }}</span>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha Creación</th>
                <th>Última Actualización</th>
            </tr>
        </thead>
        <tbody>
            @foreach($producers as $producer)
            <tr>
                <td>{{ $producer->id }}</td>
                <td>{{ $producer->name }}</td>
                <td>{{ $producer->lastname ?? 'N/A' }}</td>
                <td style="max-width: 200px;">{{ $producer->description ? Str::limit($producer->description, 80) : 'Sin descripción' }}</td>
                <td>
                    @if($producer->deleted_at)
                        <span class="status-deleted">Eliminado</span>
                    @else
                        <span class="{{ $producer->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $producer->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    @endif
                </td>
                <td>{{ $producer->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $producer->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>© {{ date('Y') }} - Sistema de Gestión de Productores</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 40px 50px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        /* Encabezado Principal */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #1a4731;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-title {
            color: #1a4731;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-date {
            text-align: right;
            color: #666;
            font-size: 10px;
        }

        /* Contenedores de Información */
        .section-title {
            background-color: #f0f4f1;
            color: #1a4731;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 15px;
            border-left: 5px solid #1a4731;
        }

        /* Grid de Estadísticas (Simulado con Tabla) */
        .stats-table {
            width: 100%;
            margin-bottom: 25px;
            border-spacing: 10px;
            margin-left: -10px;
        }
        .stat-box {
            width: 25%;
            padding: 15px 10px;
            text-align: center;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .stat-box-value {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .stat-box-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bg-blue { background-color: #e3f2fd; border-color: #bbdefb; color: #0d47a1; }
        .bg-red { background-color: #ffebee; border-color: #ffcdd2; color: #b71c1c; }
        .bg-green { background-color: #e8f5e9; border-color: #c8e6c9; color: #1b5e20; }
        .bg-yellow { 
            background-color: #fffde7; 
            border: 1px solid #fff9c4; 
            color: #827717;            
        }

        /* Tabla de Datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #1a4731;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 10px;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        .data-table tr:nth-child(even) { background-color: #f8f8f8; }
        .total-row td {
            background-color: #eee;
            font-weight: bold;
            border-top: 2px solid #1a4731;
        }

        /* Cuadros de alerta */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 11px;
        }
        .alert-danger {
            background-color: #fdeded;
            border: 1px solid #f5c2c7;
            color: #842029;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .section-title {
            background-color: #f0f4f1;
            color: #1a4731;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 15px;
            border-left: 5px solid #1a4731;
        }

        /* Gráfico de Barras Simple con CSS */
        .chart-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .bar-row {
            margin-bottom: 10px;
            width: 100%;
        }
        .bar-label {
            width: 80px;
            display: inline-block;
            font-size: 10px;
            vertical-align: middle;
        }
        .bar-wrapper {
            width: 400px;
            background-color: #eee;
            display: inline-block;
            height: 15px;
            border-radius: 2px;
            vertical-align: middle;
        }
        .bar-fill {
            height: 100%;
            background-color: #1a4731;
            border-radius: 2px;
        }
        .bar-value {
            display: inline-block;
            font-size: 10px;
            margin-left: 10px;
            vertical-align: middle;
            color: #666;
        }

        /* Gráfico Circular de Distribución (Simulado) */
        .summary-box {
            width: 100%;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-title">Reporte Técnico de Monitoreo</td>
            <td class="header-date">
                Generado: {{ $report_date }}<br>
                
            </td>
        </tr>
    </table>

    <div class="section-title">INFORMACIÓN DEL ÁREA</div>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <strong>Nombre del Proyecto:</strong> {{ $polygon->name }}<br>
                <strong>Período:</strong> {{ $start_year }} - {{ $end_year }}
            </td>
            <td style="width: 50%; vertical-align: top;">
                <strong>Descripción:</strong> {{ $polygon->description ?? 'N/A' }}<br>
                
            </td>
        </tr>
    </table>

    <table class="stats-table">
        <tr>
            <td class="stat-box bg-blue">
                <span class="stat-box-value">{{ number_format($polygon->area_ha, 5) }}</span>
                <span class="stat-box-label">Área Total (ha)</span>
            </td>
            <td class="stat-box bg-red">
                <span class="stat-box-value">{{ number_format($totalDeforestedArea, 5) }}</span>
                <span class="stat-box-label">Deforestación (ha)</span>
            </td>
            <td class="stat-box bg-green">
                <span class="stat-box-value">{{ number_format($conservedArea, 5) }}</span>
                <span class="stat-box-label">Conserva (ha)</span>
            </td>
            <td class="stat-box bg-yellow">
                <span class="stat-box-value">{{ number_format($totalPercentage, 5) }}%</span>
                <span class="stat-box-label">% Pérdida Total</span>
            </td>
        </tr>
    </table>

    <div class="section-title">DESGLOSE ANUAL DE PÉRDIDA FORESTAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>AÑO</th>
                <th>SUPERFICIE AFECTADA (ha)</th>
                <th>% PÉRDIDA ANUAL</th>
                <th>% ACUMULADO</th>
            </tr>
        </thead>
        <tbody>
            @php $cumPct = 0; @endphp
            @foreach($analyses as $analysis)
                @php $cumPct += $analysis->percentage_loss; @endphp
                <tr>
                    <td>{{ $analysis->year }}</td>
                    <td>{{ number_format($analysis->deforested_area_ha, 5) }}</td>
                    <td>{{ number_format($analysis->percentage_loss, 5) }}%</td>
                    <td>{{ number_format($cumPct, 5) }}%</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td>{{ number_format($totalDeforestedArea, 5) }} ha</td>
                <td>{{ number_format($totalPercentage, 5) }}%</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">VISUALIZACIÓN DE PÉRDIDA ANUAL (ha)</div>
    <div class="chart-container">
        @php
            // Buscamos el valor máximo para escalar las barras proporcionalmente
            $maxLoss = $polygon->max('deforested_area_ha') ?: 1;
        @endphp

        @foreach($analyses as $analysis)
            @php
                // Calculamos el ancho de la barra (máximo 100%)
                $width = ($analysis->deforested_area_ha / $maxLoss) * 100;
            @endphp
            <div class="bar-row">
                <span class="bar-label">Año {{ $analysis->year }}</span>
                <div class="bar-wrapper">
                    <div class="bar-fill" style="width: {{ $width }}%;"></div>
                </div>
                <span class="bar-value">{{ number_format($analysis->deforested_area_ha, 2) }} ha</span>
            </div>
        @endforeach
    </div>

    <div class="section-title">DISTRIBUCIÓN DE COBERTURA</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; padding-right: 20px;">
                <p><strong>Estado Actual de la Superficie:</strong></p>
                <div style="margin-bottom: 5px; font-size: 10px;">Conservado ({{ number_format(100 - $totalPercentage, 2) }}%)</div>
                <div style="width: 100%; background-color: #eee; height: 20px;">
                    <div style="width: {{ 100 - $totalPercentage }}%; background-color: #2e7d32; height: 100%;"></div>
                </div>
                <div style="margin-top: 15px; margin-bottom: 5px; font-size: 10px;">Deforestado ({{ number_format($totalPercentage, 2) }}%)</div>
                <div style="width: 100%; background-color: #eee; height: 20px;">
                    <div style="width: {{ $totalPercentage }}%; background-color: #c62828; height: 100%;"></div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: middle; background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
                <p style="font-size: 10px; margin: 0;">
                    <strong>Nota Interpretativa:</strong><br>
                    Las barras superiores representan la relación proporcional de pérdida entre los años analizados. 
                    El gráfico de distribución muestra el estado crítico del polígono frente a su superficie total original.
                </p>
            </td>
        </tr>
    </table>
    
    <div></div>

    <div class="section-title">ANÁLISIS DE IMPACTO</div>
    <p>
        El análisis realizado sobre el polígono <strong>{{ $polygon->name }}</strong> mediante sensores remotos 
        indica que se ha perdido un total de <strong>{{ number_format($totalDeforestedArea, 5) }} hectáreas</strong> 
        en un lapso de {{ $end_year - $start_year + 1 }} años.
    </p>

    @if($totalPercentage > 30)
        <div class="alert alert-danger">
            <strong>AVISO DE IMPACTO CRÍTICO:</strong> El área presenta una tasa de deforestación alta 
            ({{ number_format($totalPercentage, 2) }}%). Se recomienda intervención y verificación en campo 
            para determinar las causas del cambio de uso de suelo.
        </div>
    @endif

    
    <div class="footer">
        Este reporte fue generado por el Sistema de Gestión Forestal © {{ date('Y') }}. 
        La información se basa en datos satelitales procesados.
    </div>
</body>
</html>
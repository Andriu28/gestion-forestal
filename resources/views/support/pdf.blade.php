<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* Configuración de Página */
        /* 1. Reset total de márgenes para evitar la hoja en blanco */
        @page { 
            margin: 0; 
        }
        
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            background-color: #ffffff; /* Blanco para impresión limpia */
            color: #1c1917; /* stone-900 */
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Contenedor Principal (Simulando el bg-stone-100/90) */
        .container {
            background-color: #f5f5f4; /* stone-100 */
            border-radius: 15px; /* rounded-2xl */
            padding: 1cm 1.5cm;
            margin-top: 10px;
            
        }

        /* Cintillo Profesional (Basado en lo que diseñamos) */
        .cintillo {
            border-bottom: 4px solid #065f46; /* emerald-800 */
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        /* Títulos Estilo font-black y uppercase */
        h1 {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            color: #1c1917;
            margin: 0;
        }

        h2 {
            font-size: 18px;
            font-weight: 800;
            color: #064e3b; /* emerald-900 */
            margin-top: 30px;
            border-left: 5px solid #059669; /* emerald-600 */
            padding-left: 15px;
        }

        /* Estilo de Párrafos y Prosa */
        p {
            margin-bottom: 15px;
            font-size: 13px;
            text-align: justify;
        }

        /* Bloques de "Tip" o "Nota" (Como el que hicimos en la web) */
        .tip-box {
            background-color: #ecfdf5; /* emerald-50 */
            border-left: 4px solid #10b981; /* emerald-500 */
            padding: 15px;
            border-radius: 0 10px 10px 0;
            margin: 20px 0;
            font-style: italic;
            font-size: 12px;
        }

        /* Enumeraciones Forestales */
        .step-number {
            display: inline-block;
            background-color: #059669; /* emerald-600 */
            color: white;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 10px;
            color: #78716c; /* stone-500 */
            border-top: 1px solid #e7e5e4;
            padding: 10px 15px;
            background-color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }

        .footer-left {
            text-align: left;
            flex: 1;
        }

        .footer-right {
            text-align: right;
            flex: 1;
        }

        h3 {
            color: #111827;
            font-size: 18px;
            display: block;
            margin-bottom: 15px;
        }

        .bg-emerald-600 {
            background-color: #059669;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 12px;
            margin-right: 10px;
        }

        .prose p {
            color: #374151;
            text-align: justify;
        }

        /* El cuadro de "Tip Pro" */
        .bg-white\/50 {
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            padding: 5px;
            margin: 5px 0;
        }

        .manual-img {
            width: 100%; /* Ocupa el ancho del contenedor stone-100 */
            border: 1px solid #d6d3d1;
            margin-top: 10px;
        }
        .image-caption {
            text-align: center;
            font-size: 10px;
            color: #78716c;
            margin-bottom: 20px;
        }

        /* Estilos para la Portada */
        /* 2. Estilos de la Portada */
        .cover-page {
            width: 100%;
            height: 100%;
            text-align: center;
            padding-top: 150px; /* Ajuste manual del centrado vertical */
            background-color: #ffffff;
            page-break-after: always; /* Obliga a que el contenido empiece en hoja nueva */
        }

        .cover-logo {
            width: 150px;
            margin-bottom: 30px;
        }

        .cover-title {
            font-size: 38pt;
            font-weight: bold;
            color: #064e3b;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .cover-subtitle {
            font-size: 18pt;
            color: #4b5563;
            margin-bottom: 50px;
            letter-spacing: 2px;
        }

        .cover-accent-line {
            width: 100px;
            height: 6px;
            background-color: #059669;
            margin: 0 auto 50px auto;
            border-radius: 3px;
        }

        .cover-info {
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 12px;
            background-color: #f9fafb;
            display: inline-block;
            margin-top: 20px;
        }

        /* Ajustes para imágenes */
        img {
            max-width: 100%;    /* Evita que la imagen sea más ancha que el contenedor */
            height: auto;       /* Mantiene la proporción */
            display: block;
            margin: 10px auto;  /* Centra la imagen y da espacio vertical */
            page-break-inside: avoid; /* Intenta que la imagen no se corte entre dos páginas */
        }

        /* Contenedor específico que usas en content.blade.php */
        .manual-image-container {
            text-align: center;
            margin: 25px 0;
            width: 100%;
            page-break-inside: avoid; /* Crucial para evitar que el pie de foto se separe de la imagen */
        }

        .manual-image-container img {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            /* Ajustamos el ancho máximo al 90% para dejar margen estético en el PDF */
            max-width: 90%; 
            max-height: 400px; /* Limita el alto para que no ocupe toda una página sola */
            object-fit: contain;
        }

        /* Pie de foto de la imagen */
        .manual-image-container p {
            font-size: 11px;
            color: #6b7280;
            margin-top: 8px;
            font-style: italic;
        }

    </style>
</head>
<body>
    <div class="cover-page">
        @if(public_path('images/logo.png'))
            <img src="{{ public_path('images/logo.png') }}" class="cover-logo">
        @else
            <div style="font-size: 50px; color: #064e3b; font-weight: 900; margin-bottom: 40px;">SGF</div>
        @endif
    
        <div class="cover-title">Manual de Usuario</div>
        <div class="cover-subtitle">Sistema de Gestión Forestal</div>
        
        <div class="cover-accent-line"></div>
    
        <div class="cover-info">
            <p style="margin: 5px 0;"><strong>Departamento:</strong> Monitoreo Ambiental</p>
            <p style="margin: 5px 0;"><strong>Versión:</strong> 1.0.0</p>
            <p style="margin: 5px 0;"><strong>Fecha:</strong> {{ date('d/m/Y') }}</p>
        </div>
    
        <div class="cover-footer" style="text-align: left; width: 100%; margin-top: 50px; margin-left: 20px;">
            <h3 style="margin-bottom: 5px; font-size: 16px;">Elaborado por:</h3>
            <ul style="list-style-type: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 3px;">Kevin Salazar</li>
                <li style="margin-bottom: 3px;">Geral Serrano</li>
            </ul>
            <p>© {{ date('Y') }} Gestión Forestal - Todos los derechos reservados.</p>
            <p style="font-size: 10px; color: #999;">Documentación Técnica Oficial</p>
        </div>
    </div>

    <div class="container">

        @include('support.content')
        
    </div>

    <div class="footer">
        <div class="footer-left">
            Elaborado por: Kevin Salazar y Geral Serrano
        </div>
        <div class="footer-right">
            © {{ date('Y') }} Gestión Forestal - Todos los derechos reservados.
        </div>
    </div>

</body>
</html>
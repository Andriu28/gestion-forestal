@php
    $i = 1;
@endphp
<section id="introduccion" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Introducción
    </h3>
    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-400 leading-relaxed">
        <p>El presente manual tiene como propósito ofrecer una guía detallada para el uso del sistema de <strong>Soporte a la Toma de Decisiones para la Gestión Geográfica de Cacao San José</strong>.</p>
    </div>
</section>

<section id="acceso" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Acceso al Sistema
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">El acceso es para aquellos usuarios creados previamente por un administrador.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="font-bold text-emerald-700 mb-2">Inicio de Sesión</h4>
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/inicio de sesion.png') : asset('images/manual/inicio de sesion.png') }}" 
                    alt="Módulo de Análisis" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Ingrese su correo electronico y contraseña. Puede usar el icono del "ojo" para verificar los caracteres ingresados.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="font-bold text-emerald-700 mb-2">Personalización</h4>
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/inicio de sesion-oscuro.png') : asset('images/manual/inicio de sesion-oscuro.png') }}" 
                    alt="Módulo de Análisis" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">El sistema permite alternar entre modo claro y oscuro para mejorar la visibilidad.</p>
        </div>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 rounded-r-lg mb-6">
        <h4 class="text-amber-800 dark:text-amber-400 font-bold text-sm mb-1">¿Olvidó su contraseña?</h4>
        <p class="text-sm text-amber-700 dark:text-amber-500 italic">
            Ingrese su correo en la sección "¿Olvidaste la Contraseña?" para recibir un enlace de recuperación.
        </p>
        <div class="manual-image-container my-6 text-center">
            <img src="{{ isset($is_pdf) ? public_path('images/manual/part1-recu-pass.png') : asset('images/manual/part1-recu-pass.png') }}" 
                alt="Módulo de Análisis" 
                class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            <p class="text-xs text-gray-500 mt-2 italic">Figura: haga clic en ¿Olvidaste la Contraseña?.</p>
        </div>
        <div class="manual-image-container my-6 text-center">
            <img src="{{ isset($is_pdf) ? public_path('images/manual/part2-recu-pass.png') : asset('images/manual/part2-recu-pass.png') }}" 
            alt="Módulo de Análisis" 
            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            <p class="text-xs text-gray-500 mt-2 italic">Figura: Ingrese su correo electronico.</p>
        </div>
        <div class="manual-image-container my-6 text-center">
            <img src="{{ isset($is_pdf) ? public_path('images/manual/part3-recu-pass.png') : asset('images/manual/part3-recu-pass.png') }}" 
                alt="Módulo de Análisis" 
                class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            <p class="text-xs text-gray-500 mt-2 italic">Figura: Revice la bandeja de entre de correo electronico para verificar el mismo y pueda acceder al sistema.</p>
        </div>
    </div>
</section>

<section id="navegacion" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Pagina de Inicio
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">Al ingresar, visualizará una barra lateral que permite el acceso rápido a los módulos principales:</p>
    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li><strong>Inicio:</strong> Resumen general de informacion basica.</li>
        <li><strong>Análisis:</strong> Herramienta principal de monitoreo forestal.</li>
        <li><strong>Polígonos:</strong> Gestión de áreas geográficas delimitadas.</li>
        <li><strong>Productores:</strong> Directorio y gestión de proveedores.</li>
    </ul>

    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/inicio_basico.png') : asset('images/manual/inicio_basico.png') }}" 
             alt="Módulo de Análisis" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Interfaz inicial con informacion basica de la misma.</p>
    </div>
</section>

<section id="analisis" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Análisis
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">La interfaz de <strong>Análisis de Deforestación</strong> está diseñada para ofrecer herramientas avanzadas de teledetección y gestión de datos geográficos en una sola vista:</p>
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/create.png') : asset('images/manual/analisis/create.png') }}" 
             alt="Módulo de Análisis" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Interfaz de configuración y herramientas del módulo de análisis de deforestación.</p>
    </div>
    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li>
            <strong>Visor Cartográfico Interactivo:</strong> Mapa dinámico que permite visualizar capas de pérdida de cobertura arbórea (GFW) con control de opacidad ajustable.
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">OpenStreetMap</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/map_osm.png') : asset('images/manual/analisis/map_osm.png') }}" 
                            alt="Mapa OSM" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Proporciona una vista cartográfica estándar con énfasis en infraestructura vial y centros poblados.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">Vista Satelital</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/map_sat.png') : asset('images/manual/analisis/map_sat.png') }}" 
                            alt="Mapa Satelital" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Imágenes de alta resolución para identificar texturas del terreno y cambios reales en la cobertura vegetal.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">Relieve y Topografía</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/map_relieve.png') : asset('images/manual/analisis/map_relieve.png') }}" 
                            alt="Mapa de Relieve" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Visualiza curvas de nivel y sombreado de pendientes, ideal para análisis de elevación en cuencas.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">Modo Dark Matter</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/map_oscuro.png') : asset('images/manual/analisis/map_oscuro.png') }}" 
                            alt="Mapa Oscuro" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Estética minimalista de alto contraste que resalta las capas de pérdida de bosque y datos analíticos.</p>
                </div>
            </div>  
        </li>
        <li>
            <strong>Herramientas de Digitalización:</strong> Permite delimitar áreas de estudio mediante dibujo libre en pantalla o la inserción manual de coordenadas <strong>UTM (Este, Norte, Zona y Hemisferio)</strong>.
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">Ingreso de Vértices</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/formaManual.png') : asset('images/manual/analisis/formaManual.png') }}" 
                            alt="Formulario de coordenadas" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Permite la inserción precisa de coordenadas geográficas (Este y Norte) para definir los vértices del área de estudio de forma manual.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-emerald-700 mb-2">Geometría del Área (Polígono)</h4>
                    <div class="manual-image-container my-6 text-center">
                        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/poligono.png') : asset('images/manual/analisis/poligono.png') }}" 
                            alt="Visualización de polígono" 
                            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Representación vectorial del área delimitada sobre el visor. El sistema cierra automáticamente la geometría para calcular el área total en hectáreas.
                    </p>
                </div>
            </div>
        </li>
        <li><strong>Importación Multiformato:</strong> Capacidad para cargar archivos geográficos externos en formatos <strong>GeoJSON, KML y Shapefile (ZIP)</strong>(En Mantenimiento).</li>
        <li>
            <strong>Parámetros Temporales:</strong> Configuración de rangos de análisis (desde 2001 hasta 2024) para identificar la cronología de la deforestación.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/parametros.png') : asset('images/manual/analisis/parametros.png') }}" 
                    alt="Visualización de polígono" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    <p class="text-xs text-gray-500 mt-2 italic">Figura: Interfaz de configuración de parametros para realizar el análisis de deforestación.</p>
            </div>
        </li>
        <!-- <li><strong>Gestión de Datos:</strong> Opción para guardar el análisis en el sistema vinculándolo a un nombre específico y descripción del área.</li> -->

        <li>
    <strong>Resultados del Análisis:</strong> Desglose cuantitativo y visual de la pérdida de cobertura arbórea detectada en el rango de años seleccionado.
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="font-bold text-emerald-700 mb-2">Métricas Consolidadas</h4>
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/result-part1.png') : asset('images/manual/analisis/result-part1.png') }}" 
                     alt="Estadísticas numéricas" 
                     class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Exhibe los valores totales de área deforestada (ha), porcentaje de pérdida y el estado actual del predio analizado.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="font-bold text-emerald-700 mb-2">Distribución Temporal</h4>
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/result-part2.png') : asset('images/manual/analisis/result-part2.png') }}" 
                     alt="Gráficas de distribución" 
                     class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Gráficas que segmentan la pérdida de cobertura por periodos, facilitando la identificación de hitos críticos de deforestación.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h4 class="font-bold text-emerald-700 mb-2">Evolución Tendencial</h4>
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/result-part3.png') : asset('images/manual/analisis/result-part3.png') }}" 
                     alt="Gráfica de evolución" 
                     class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Visualización de la tendencia histórica que permite proyectar el comportamiento de la pérdida forestal a través del tiempo.</p>
        </div>
    </div>
</li>

<li>
    <strong>Generación de Reporte:</strong> Exportación de resultados en formato PDF de alta calidad para fines administrativos o legales.
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/analisis/report.png') : asset('images/manual/analisis/report.png') }}" 
             alt="Reporte PDF" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Documento técnico oficial generado automáticamente con el resumen del análisis geográfico.</p>
    </div>
</li>

    <div class="bg-blue-50 dark:bg-gray-800/50 border-l-4 border-blue-500 p-4 mt-4">
        <p class="text-sm text-blue-700 dark:text-blue-300">
            <strong>Nota:</strong> El sistema incluye un validador inteligente que habilita el botón de "Analizar" solo cuando se ha detectado una geometría válida y se han completado los campos requeridos.
        </p>
    </div>

    

</section>

<section id="gestion-poligonos" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Gestión de Polígonos
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">Este módulo permite la administración centralizada de todas las áreas geográficas registradas, ofreciendo herramientas de búsqueda, visualización y control de datos:</p>
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/polygono/index.png') : asset('images/manual/polygono/index.png') }}" 
            alt="Visualización de polígono" 
            class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
    </div>
    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li><strong>Panel de Control Geográfico:</strong> Acceso directo a un mapa general de polígonos y a la función de creación de nuevas áreas.</li>
        <li>
            <strong>Búsqueda Avanzada:</strong> Sistema de filtrado en tiempo real por nombre del polígono o nombre del productor asociado.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/polygono/filtrado.png') : asset('images/manual/polygono/filtrado.png') }}" 
                    alt="Visualización de polígono" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li>
            <strong>Visualización de Atributos:</strong> Tabla detallada que muestra el nombre del área, productor vinculado y dimenciones del area.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/polygono/atributos.png') : asset('images/manual/polygono/atributos.png') }}" 
                    alt="Visualización de polígono" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li>
            <strong>Acciones Rápidas:</strong> Interfaz simplificada para ver detalles mediante ventanas emergentes, editar información existente o eliminar registros.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/polygono/acciones.png') : asset('images/manual/polygono/acciones.png') }}" 
                    alt="Visualización de polígono" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li>
            <strong>Visualización de Detalles:</strong> Ventana emergente que permite revisar la información completa de un polígono sin abandonar la lista principal.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/polygono/detalle.png') : asset('images/manual/polygono/detalle.png') }}" 
                    alt="Visualización de polígono" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                    <p class="text-xs text-gray-500 mt-2 italic">Figura: Se puede observer el nombre del area, estado, diminciones y fecha exacta de registro y de la ultima actualizacion.</p>
            </div>
        </li>
    </ul>

    <div class="bg-amber-50 dark:bg-gray-800/50 border-l-4 border-amber-500 p-4 mt-4">
        <p class="text-sm text-amber-700 dark:text-amber-300">
            <strong>Sugerencia de uso:</strong> Utilice el campo de búsqueda para localizar rápidamente áreas específicas. Si el polígono no aparece, verifique que el nombre del productor esté escrito correctamente.
        </p>
    </div>
</section>

<section id="gestion-productores" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Gestión de Productores
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">Este módulo constituye el directorio central de los proveedores, permitiendo un control detallado sobre la información personal de cada productor:</p>
    
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/productores/index.png') : asset('images/manual/productores/index.png') }}" 
             alt="Gestión de Productores" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Directorio y herramientas de exportación del módulo de productores.</p>
    </div>

    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li>
            <strong>Registro de Proveedores:</strong> Formulario para el alta de nuevos productores en la base de datos del sistema.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/productores/registrar.png') : asset('images/manual/productores/registrar.png') }}" 
                    alt="Gestión de Productores" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                <p class="text-xs text-gray-500 mt-2 italic">Figura: de momento siendo nombre, el unico campo obligatorio.</p>
            </div>
        </li>
        <li>
            <strong>Buscador Inteligente:</strong> Filtrado dinámico por nombre o estado del productor para localizar registros de manera instantánea o visualizar los de interes.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/productores/filtrado.png') : asset('images/manual/productores/filtrado.png') }}" 
                    alt="Gestión de Productores" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li><strong>Reportes en PDF:</strong> Función integrada para generar y descargar un catálogo completo de productores en formato PDF.</li>
        <li>
            <strong>Perfil Detallado:</strong> Visualización mediante ventana emergente de información de los productores.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/productores/detalles.png') : asset('images/manual/productores/detalles.png') }}" 
                    alt="Gestión de Productores" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                <p class="text-xs text-gray-500 mt-2 italic">Figura: Informacion como nombre, apellido, poligonos asignados y fecra de creacion.</p>
            </div>
        </li>
        <li><strong>Control de Estatus:</strong> Indicadores visuales sobre el estado del registro, con opciones para edición y actualización de datos personales.</li>
    </ul>

</section>

<section id="gestion-usuarios" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Gestión de Usuarios y Seguridad
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">Este módulo permite la administración técnica de los accesos al sistema, asegurando que solo el personal autorizado pueda interactuar con los datos sensibles:</p>
    
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/usuario/index.png') : asset('images/manual/usuario/index.png') }}" 
             alt="Gestión de Usuarios" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
    </div>

    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li>
            <strong>Control de Cuentas:</strong> Registro de nuevos usuarios y asignación de credenciales de acceso.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/usuario/registrar.png') : asset('images/manual/usuario/registrar.png') }}" 
                    alt="Gestión de Usuarios" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                <p class="text-xs text-gray-500 mt-2 italic">Figura: Siendo todos los campos necesarios para la creacion del usuario.</p>
            </div>
        </li>
        <li>
            <strong>Administración de Roles:</strong> Visualización y asignación de niveles de permisos (Administrador, Basico, etc.) para restringir funcionalidades según el perfil.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/usuario/roles.png') : asset('images/manual/usuario/roles.png') }}" 
                    alt="Gestión de Usuarios" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
                <p class="text-xs text-gray-500 mt-2 italic">Figura: Panel de administración de cuentas de usuario y permisos.</p>
            </div>
        </li>
        <li><strong>Auditoría de Acceso:</strong> Panel para supervisar la lista de usuarios activos, sus nombres y correos electrónicos vinculados.</li>
        <li><strong>Herramientas de Mantenimiento:</strong> Interfaz para la edición rápida de perfiles o eliminación de cuentas obsoletas.</li>
        <li><strong>Paginación Inteligente:</strong> Sistema de navegación que facilita la gestión de grandes volúmenes de usuarios sin saturar la interfaz.</li>
    </ul>

    <div class="bg-slate-50 dark:bg-gray-800/50 border-l-4 border-slate-500 p-4 mt-4">
        <p class="text-sm text-slate-700 dark:text-slate-300">
            <strong>Seguridad:</strong> Se recomienda revisar periódicamente la lista de usuarios para dar de baja a aquellos que ya no requieran acceso a la plataforma.
        </p>
    </div>
</section>

<section id="configuracion-perfil" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Configuración de Perfil y Seguridad
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">El sistema permite a cada usuario gestionar su propia identidad y niveles de seguridad a través de un panel de configuración personalizado:</p>
    
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/perfil/editar.png') : asset('images/manual/perfil/editar.png') }}" 
             alt="Configuración del Perfil" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Panel de edición de perfil, cambio de contraseña y opciones de seguridad.</p>
    </div>

    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li>
            <strong>Información del Perfil:</strong> Actualización del nombre de usuario y dirección de correo electrónico.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/perfil/infor.png') : asset('images/manual/perfil/infor.png') }}" 
                    alt="Configuración del Perfil" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li><strong>Verificación de Cuenta:</strong> Sistema de reenvío de enlaces de verificación para asegurar la validez del correo electrónico registrado.</li>
        <li>
            <strong>Gestión de Credenciales:</strong> Cambio de contraseña con herramientas de visibilidad (mostrar/ocultar) para evitar errores durante la escritura.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/perfil/pass.png') : asset('images/manual/perfil/pass.png') }}" 
                    alt="Configuración del Perfil" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
        <li><strong>Seguridad de Datos:</strong> Es obligatorio el uso de contraseñas largas y complejas.</li>
        <li>
            <strong>Eliminación de Cuenta:</strong> Opción para la baja definitiva del sistema, que requiere confirmación mediante contraseña para proteger la integridad de los datos.
            <div class="manual-image-container my-6 text-center">
                <img src="{{ isset($is_pdf) ? public_path('images/manual/perfil/delete.png') : asset('images/manual/perfil/delete.png') }}" 
                    alt="Configuración del Perfil" 
                    class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
            </div>
        </li>
    </ul>

    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mt-4">
        <p class="text-sm text-red-700 dark:text-red-300">
            <strong>Advertencia:</strong> La eliminación de la cuenta es un proceso permanente. Una vez realizada, todos los recursos y datos asociados al usuario se borrarán de forma irreversible.
        </p>
    </div>
</section>

<section id="registro-actividades" class="scroll-mt-20 mb-12">
    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center mb-4">
        <span class="bg-emerald-600 text-white w-8 h-8 rounded-full flex items-center justify-center mr-3 text-sm font-bold">0{{ $i++ }}</span>
        Registro de Actividades (Auditoría)
    </h3>
    <p class="text-gray-700 dark:text-gray-400 mb-4">El sistema cuenta con un módulo de auditoría integral que registra cada acción relevante realizada en la plataforma, garantizando la transparencia y el seguimiento de los cambios:</p>
    
    <div class="manual-image-container my-6 text-center">
        <img src="{{ isset($is_pdf) ? public_path('images/manual/historial/index.png') : asset('images/manual/historial/index.png') }}" 
             alt="Registro de Actividades" 
             class="rounded-xl shadow-md border border-gray-200 mx-auto max-w-full">
        <p class="text-xs text-gray-500 mt-2 italic">Figura: Historial de auditoría para el seguimiento de operaciones en el sistema.</p>
    </div>

    <ul class="list-disc list-inside space-y-2 text-gray-600 dark:text-gray-400 ml-4 mb-6">
        <li><strong>Trazabilidad de Acciones:</strong> Registro detallado de la actividad realizada (creación, edición o eliminación) y el módulo afectado.</li>
        <li><strong>Identificación de Usuarios:</strong> Vinculación directa de cada registro con el usuario responsable, facilitando la supervisión técnica.</li>
        <li><strong>Filtrado de Eventos:</strong> Buscador dinámico que permite localizar actividades específicas filtrando por nombre de usuario o tipo de acción.</li>
        <li><strong>Cronología de Eventos:</strong> Visualización precisa de la fecha y hora exacta en la que se ejecutó cada movimiento en el sistema.</li>
        <li><strong>Navegación por Historial:</strong> Sistema de paginación optimizado para revisar registros antiguos sin comprometer el rendimiento de la aplicación.</li>
    </ul>

    <div class="bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500 p-4 mt-4">
        <p class="text-sm text-indigo-700 dark:text-indigo-300">
            <strong>Consejo Técnico:</strong> Utilice el botón de "Limpiar" en los filtros para restaurar la vista completa del historial de forma rápida tras una búsqueda específica.
        </p>
    </div>
</section>


# RN-001: Módulo de Análisis de Deforestación

## 1. Contexto / Problema que resuelve
El sistema permite importar archivos GeoJSON para calcular métricas de deforestación.
El problema original es que **el análisis geoespacial es pesado computacionalmente** (cálculo de áreas, superposición de capas, etc.). Si un usuario sube el mismo archivo dos veces por error, o si varios usuarios suben el mismo polígono, el sistema colapsaría o tardaría minutos innecesarios.

## 2. Decisión de Negocio 
- **Deduplicación automática:** El sistema **NO** ejecuta un nuevo análisis si detecta que el archivo GeoJSON ya fue importado anteriormente.
- **Mecanismo:** Se calcula un `hash` (SHA-256) del contenido crudo del archivo `.geojson`.
  - Si el `hash` NO existe en BD → Se ejecuta el análisis pesado y se guarda el resultado + el polígono.
  - Si el `hash` YA existe en BD → Se omite el análisis pesado y se **consultan** los resultados y polígonos guardados previamente.
- **Impacto visual:** El usuario siempre ve los resultados, pero si viene de caché, la interfaz debe mostrar un mensaje sutil (ej. "Resultados recuperados de análisis previo").

---

## 3. Validaciones Técnicas y de Negocio 
*Lista de control para cuando toque tocar este módulo:*

| ID | Validación | Comportamiento ante error |
| :--- | :--- | :--- |
| **V-001** | **Tamaño del archivo** | Límite máximo: **50 MB**. Si lo supera, el sistema rechaza la importación y muestra error. |
| **V-002** | **Estructura JSON** | Debe ser un JSON válido. Si no lo es, se captura la excepción y se notifica "Archivo corrupto o formato inválido". |
| **V-003** | **Tipo de geometría** | El sistema SOLO acepta geometrías tipo `Polygon` o `MultiPolygon`. Si trae `Point` o `LineString`, se rechaza. |
| **V-004** | **Sistema de Coordenadas (CRS)** | Se asume que el archivo viene en **EPSG:4326** (WGS84). Si viene en otro, el sistema debe transformarlo ANTES de guardar (o rechazar si no se puede transformar). |
| **V-005** | **Polígonos válidos** | Los polígonos no deben autointersectarse. Se usa la validación nativa de la librería geoespacial (ej. `shapely.is_valid`). Si es inválido, se rechaza el archivo completo. |
| **V-006** | **Campos mínimos** | El archivo debe contener al menos 1 polígono. Si viene vacío, se notifica "No se encontraron polígonos para analizar". |
| **V-007** | **Hash duplicado** | Si ya existe, se salta el procesamiento. **OJO:** Esto aplica aunque el archivo se llame diferente. La detección es por contenido, no por nombre. |

---

## 4. Flujo de Guardado 
Cuando se selecciona "Guardar" después del análisis:
1. Se guarda el registro del **Análisis** (fecha, usuario, resultado métricas).
2. Se guardan los **Polígonos** asociados a ese análisis en la tabla de geometrías.
3. **Regla crítica:** Si el análisis fue recuperado por caché (hash existente), al hacer clic en "Guardar" NO se duplican los polígonos en BD. Solo se crea un nuevo registro de "Análisis" que apunta al ID del polígono ya existente (relación Many-to-One o reutilización de FK).

---

## 5. Excepciones y Casos Borde
- **¿Qué pasa si el hash existe pero el polígono en BD fue borrado?** 
  - El sistema debe forzar la re-ejecución del análisis (regenerar el polígono) porque la integridad referencial estaría rota.
- **¿Qué pasa si subo el mismo archivo pero con diferente encoding (UTF-8 vs ANSI)?**
  - El hash cambiará, por lo que se procesará de nuevo. **Decisión:** Lo asumimos como un archivo diferente para evitar corrupción de datos.
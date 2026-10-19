import './bootstrap';
import Swal from 'sweetalert2';

// Exportación global de SweetAlert2 para habilitar su uso en scripts inline de las vistas Blade
window.Swal = Swal;

/**
 * ====================================================================================================
 * UTILIDADES DE INTERFAZ Y MODALES: app.js
 * ====================================================================================================
 * 
 * Centraliza la capa de interacción dinámica con el usuario:
 * 1. Notificaciones flotantes (Toast) y confirmaciones modales con SweetAlert2 adaptadas a Modo Oscuro/Claro.
 * 2. Diálogo interactivo de configuración y lanzamiento de análisis satelital de deforestación.
 * 3. Operaciones asíncronas (AJAX / Fetch API) para gestión de usuarios y roles sin recarga de página:
 *    - Deshabilitación y habilitación de usuarios con eliminación visual de filas de tabla en tiempo real.
 *    - Cambio de roles con doble confirmación preventiva cuando un administrador reduce sus propios privilegios.
 * ====================================================================================================
 */

/**
 * Despliega una alerta tipo Toast (notificación emergente compacta en la esquina superior derecha):
 * - Temporizador automático de 1.6 segundos con barra de progreso animada.
 * - Soporta pausa del temporizador al pasar el puntero del ratón por encima (mouseenter/mouseleave).
 * - Integrado con clases de Tailwind CSS para compatibilidad completa con temas claro y oscuro.
 * 
 * @param {string} icon  Tipo de icono ('success', 'error', 'warning', 'info')
 * @param {string} title Título principal de la notificación
 * @param {string} text  Mensaje descriptivo secundario
 */
window.showCustomAlert = function (icon, title, text) {
    Swal.fire({
        position: "top-end",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 1600,
        timerProgressBar: true,
        toast: true,
        width: '380px',
        padding: '1rem',
        html: `<div class="text-center">
                 <p class="text-sm text-gray-700 dark:text-gray-300">${text}</p>
               </div>`,
        customClass: {
            popup: 'rounded-xl shadow-2xl dark:shadow-[0_0_25px_rgba(000,000,000,0.90)] bg-stone-100/95 dark:bg-custom-gray border border-gray-200 dark:border-gray-300',
            title: 'text-lg font-semibold text-gray-900 dark:text-white mb-1',
            htmlContainer: 'text-sm text-gray-600 dark:text-gray-300',
            timerProgressBar: 'bg-gradient-to-r from-emerald-400 to-emerald-600'
        },
        showClass: {
            popup: 'animate__animated animate__fadeInRight animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutRight animate__faster'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
};

/**
 * Muestra una ventana modal de confirmación previa para acciones críticas:
 * - Evita ejecuciones accidentales (ej. eliminación o deshabilitación de cuentas).
 * - Devuelve una Promesa para encadenar la lógica con `.then()` o `await`.
 * 
 * @param {boolean} isEnable      Indica si la acción es positiva (verde) o destructiva (amarillo/rojo)
 * @param {string|null} customMessage Mensaje personalizado para el cuerpo de la ventana
 * @returns {Promise<SweetAlertResult>}
 */
window.showCustomConfirmation = function (isEnable = false, customMessage = null) {
    const title = '¿Estás seguro?';
    const message = customMessage || (isEnable
        ? '¡Esta acción habilitará al usuario!'
        : '¡Esta acción deshabilitará al usuario!');
    const confirmText = isEnable ? 'Sí, habilitar' : 'Sí, deshabilitar';
    const iconColor = '#f59f0bea';

    return Swal.fire({
        title: title,
        html: `<div class="text-center">
                 <p class="text-gray-700 dark:text-gray-300 mb-4">${message}</p>
               </div>`,
        icon: 'warning',
        iconColor: iconColor,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-2xl shadow-2xl bg-stone-100/90 dark:bg-custom-gray border border-gray-200 dark:border-gray-700',
            title: 'text-2xl font-bold text-gray-900 dark:text-white',
            htmlContainer: 'text-gray-600 dark:text-gray-300',
            actions: 'gap-4 mt-6',
            confirmButton: isEnable
                ? 'px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out border border-green-400/30'
                : 'px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out border border-emerald-400/30',
            cancelButton: 'px-6 py-3 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out border border-rose-400/30'
        },
        showClass: {
            popup: 'animate__animated animate__zoomIn animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut animate__faster'
        }
    });
};

/**
 * Modal Interactivo de Configuración de Análisis de Deforestación:
 * 
 * Flujo de ejecución:
 * 1. Genera dinámicamente un rango de opciones de años (desde 2001 hasta 2024 según el dataset de GFW).
 * 2. Renderiza dos desplegables en pantalla: Año de Inicio y Año de Fin.
 * 3. Incluye un interruptor (checkbox) para que el usuario elija si desea guardar los resultados en base de datos.
 * 4. Valida en el cliente (`preConfirm`) que el año de inicio no sea superior al año de fin.
 * 5. Si se confirma:
 *    - Escribe los valores en los inputs ocultos del formulario (`start-{id}`, `end-{id}`, `save-{id}`).
 *    - Activa una pantalla de carga (overlay) con barra de progreso simulada para dar retroalimentación visual inmediata.
 *    - Envía el formulario POST para su procesamiento en el servidor.
 * 
 * @param {number|string} polygonId   ID de la Parcela a evaluar
 * @param {string}        polygonName Nombre visible de la parcela
 */
window.confirmAnalysis = function (polygonId, polygonName) {
    const startYear = 2001; // Primer año disponible en el dataset UMD Hansen de GFW
    const endYear = 2024; // Último año procesado
    const iconColor = '#f59f0bea';

    // Generar arreglo secuencial de años para los selectores HTML
    const yearOptions = Array.from(
        { length: endYear - startYear + 1 },
        (_, i) => startYear + i
    );

    return Swal.fire({
        title: `Analizar deforestación`,
        html: `
            <div class="text-left px-4">
                <p class="text-gray-700 dark:text-gray-300 mb-6 text-center">
                    Configura los parámetros para el Parcela: <br>
                    <strong class="text-emerald-600 dark:text-emerald-400">${polygonName}</strong>
                </p>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Año Inicio</label>
                        <select id="swal-start" class="w-full p-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500">
                            ${yearOptions.map(y => `<option value="${y}">${y}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Año Fin</label>
                        <select id="swal-end" class="w-full p-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500">
                            ${yearOptions.map(y =>
            `<option value="${y}" ${y === endYear ? 'selected' : ''}>${y}</option>`
        ).join('')}
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-3 p-3 bg-gray-200/50 dark:bg-gray-700/50 rounded-2xl border border-dashed border-gray-400 dark:border-gray-500">
                    <input type="checkbox" id="swal-save" class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                    <label for="swal-save" class="text-sm font-semibold text-gray-700 dark:text-gray-200 cursor-pointer">¿Guardar resultados del análisis?</label>
                </div>
            </div>`,
        icon: 'info',
        iconColor: iconColor,
        showCancelButton: true,
        confirmButtonText: 'Iniciar Análisis',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-2xl shadow-2xl bg-stone-100/90 dark:bg-custom-gray border border-gray-200 dark:border-gray-700',
            title: 'text-2xl font-bold text-gray-900 dark:text-white',
            htmlContainer: 'text-gray-600 dark:text-gray-300',
            actions: 'gap-4 mt-6',
            confirmButton: 'px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out border border-emerald-400/30',
            cancelButton: 'px-6 py-3 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out border border-rose-400/30'
        },
        preConfirm: () => {
            const start = document.getElementById('swal-start').value;
            const end = document.getElementById('swal-end').value;
            const save = document.getElementById('swal-save').checked;

            // Validación lógica de fechas
            if (parseInt(start) > parseInt(end)) {
                Swal.showValidationMessage('El año de inicio no puede ser mayor al de fin');
                return false;
            }
            return { start, end, save };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`start-${polygonId}`).value = result.value.start;
            document.getElementById(`end-${polygonId}`).value = result.value.end;
            document.getElementById(`save-${polygonId}`).value = result.value.save ? '1' : '0';

            const loaderOverlay = document.getElementById('loader-overlay');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressPercentage = document.getElementById('progress-percentage');

            // Feedback visual: simulación de avance de consulta satelital mientras responde el backend
            if (loaderOverlay) {
                loaderOverlay.classList.remove('hidden');
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) {
                        progress = 95;
                        clearInterval(interval);
                    }
                    if (progressBar) progressBar.style.width = `${progress}%`;
                    if (progressPercentage) progressPercentage.innerText = `${Math.round(progress)}%`;
                    if (progressText) progressText.innerText = 'Consultando satélites GFW...';
                }, 400);
            }

            document.getElementById(`analyze-form-${polygonId}`).submit();
        }
    });
};

// Asegurar que la cortina de carga se oculte si el usuario presiona "Atrás" en el navegador
window.addEventListener('pageshow', function (event) {
    const loader = document.getElementById('loader-overlay');
    if (loader) loader.classList.add('hidden');
});

// Interceptor para formularios con confirmación previa vía clases CSS
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sweet-confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const action = form.getAttribute('data-action');
            const isEnable = action === 'habilitar';

            showCustomConfirmation(isEnable).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

/**
 * Deshabilita un usuario mediante petición HTTP DELETE asíncrona:
 * - Solicita confirmación previa.
 * - Envía la petición con el encabezado de protección CSRF (`X-CSRF-TOKEN`).
 * - Al responder exitosamente, elimina la fila de la tabla del DOM (`row.remove()`)
 *   sin requerir recarga total de la página web.
 * 
 * @param {number|string} userId   ID del usuario
 * @param {string}        userName Nombre del usuario a deshabilitar
 */
window.handleUserDisable = async function (userId, userName) {
    const result = await window.showCustomConfirmation(false, `Vas a deshabilitar al usuario: ${userName}`);

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const row = document.getElementById(`user-row-${userId}`);
                if (row) {
                    row.remove();
                }
                showCustomAlert('success', '¡Éxito!', data.message);
            } else {
                showCustomAlert('error', 'Error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showCustomAlert('error', 'Error', 'Ocurrió un error al deshabilitar el usuario.');
        }
    }
};

/**
 * Modifica el rol de un usuario en el sistema mediante petición AJAX PATCH:
 * 
 * Medidas de seguridad y prevención:
 * 1. Comprueba si el rol seleccionado es idéntico al actual para evitar peticiones redundantes.
 * 2. Si el usuario actual está modificando SU PROPIO ROL degradándose de 'Administrador' a 'Básico':
 *    - Presenta un primer aviso de riesgo.
 *    - Presenta un SEGUNDO diálogo modal obligatorio detallando la pérdida de acceso y permisos.
 *    - Si cancela en cualquier paso, restablece el desplegable al valor original.
 * 3. En caso de éxito, actualiza el atributo `data-original-role` con el nuevo estado.
 * 4. En caso de fallo de red, revierte el desplegable visualmente al valor original.
 * 
 * @param {HTMLSelectElement} selectElement Elemento <select> que disparó el evento
 * @param {number|string}    userId        ID del usuario modificado
 * @param {string}           userName      Nombre del usuario
 * @param {boolean}          isSelf        Verdadero si el usuario autenticado se está editando a sí mismo
 * @param {string|null}      originalRole  Rol anterior antes del cambio
 */
window.handleRoleChange = async function (selectElement, userId, userName, isSelf, originalRole = null) {
    const currentOriginalRole = originalRole || selectElement.getAttribute('data-original-role');
    const newRole = selectElement.value;
    const roleText = (newRole === 'administrador') ? 'Administrador' : 'Básico';

    // Si no hubo cambio real, salir
    if (newRole === currentOriginalRole) {
        return;
    }

    let customMessage;
    let customConfirmText = 'Cambiar';

    if (isSelf) {
        customMessage = `<b>¡ATENCIÓN!</b> Al cambiar tu propio rol, tu <b>acceso</b> y <b>permisos</b>
            dentro del sistema se verán <b>afectados</b>. <b>¿Estás seguro de que quieres continuar?</b>`;
        customConfirmText = 'Cambiar';
    } else {
        customMessage = `¿Estás seguro de que quieres cambiar el rol de 
            <b>${userName}</b> a <b>${roleText}</b>?`;
    }

    const result = await window.showCustomConfirmation(false, customMessage, customConfirmText);

    if (result.isConfirmed) {
        // Salvaguarda especial: autodemoción de administrador a básico
        if (isSelf && newRole === 'basico' && currentOriginalRole === 'administrador') {
            const permissions = {
                'administrador': 'Acceso total a la administración de usuarios, auditoría, y gestión completa de datos.',
                'basico': 'Acceso restringido a la visualización de datos y reportes personales.'
            };

            const lostAccess = permissions['administrador'];

            const secondMessage = `
                <b>ADVERTENCIA FINAL:</b> Estás cambiando tu rol de <b>Administrador</b> a <b>Básico</b>.
                Estás a punto de <b>perder el siguiente acceso</b>:
                
                <p class='mt-2 p-2 bg-red-100 dark:bg-red-900/50 rounded-lg text-sm'>
                    ${lostAccess}
                </p>
                
                <b>¿CONFIRMAS BAJAR TUS PROPIOS PERMISOS?</b>
            `;

            const secondResult = await window.showCustomConfirmation(true, secondMessage, 'Cambiar');

            if (!secondResult.isConfirmed) {
                selectElement.value = currentOriginalRole;
                return;
            }
        }

        // Petición HTTP PATCH asíncrona
        try {
            const url = `/admin/users/${userId}/update-role`;

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    role: newRole
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Error ${response.status}: ${response.statusText}. ${errorText}`);
            }

            const data = await response.json();

            if (data.success) {
                // Actualizar el atributo de respaldo para futuras modificaciones
                selectElement.setAttribute('data-original-role', newRole);
                showCustomAlert('success', '¡Éxito!', data.message);
            } else {
                throw new Error(data.message || 'Error al actualizar el rol');
            }

        } catch (error) {
            console.error('Error en la petición:', error);
            // Revertir en la interfaz si ocurrió un fallo
            selectElement.value = currentOriginalRole;
            showCustomAlert('error', 'Error', error.message || 'Ocurrió un error al actualizar el rol.');
        }

    } else {
        // Revertir si el usuario canceló la ventana de confirmación
        selectElement.value = currentOriginalRole;
    }
};

/**
 * Rehabilita un usuario previamente dado de baja mediante petición HTTP POST asíncrona:
 * Remueve la fila de la vista de usuarios deshabilitados tras la respuesta positiva.
 */
window.handleUserEnable = async function (userId, userName) {
    const result = await window.showCustomConfirmation(true, `Vas a habilitar al usuario: ${userName}`);

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/admin/users/${userId}/enable`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const row = document.getElementById(`disabled-user-row-${userId}`);
                if (row) {
                    row.remove();
                }
                showCustomAlert('success', '¡Éxito!', data.message);
            } else {
                showCustomAlert('error', 'Error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showCustomAlert('error', 'Error', 'Ocurrió un error al habilitar el usuario.');
        }
    }
};

/**
 * ====================================================================================================
 * UTILIDADES DE SANITIZACIÓN DE INPUTS
 * ====================================================================================================
 * Funciones que mutan `input.value` preservando la posición del cursor.
 * Se exponen en `window` para poder usarlas desde atributos `oninput` inline
 * en los componentes Blade (`<x-text-input>`, `<textarea>`, etc.).
 * ====================================================================================================
 */

/**
 * Helper interno: aplica una transformación al value del input y restaura el cursor.
 * Evita repetir lógica en cada función.
 *
 * @param {HTMLInputElement|HTMLTextAreaElement} input
 * @param {(value: string) => string} transform
 */
function applyTransformPreservingCursor(input, transform) {
    const original = input.value;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const transformed = transform(original);

    if (transformed === original) return; // sin cambios, no tocamos el DOM

    input.value = transformed;

    // setSelectionRange falla en inputs de tipo number/email/etc.
    try {
        // Ajustamos el cursor si el texto se acortó por el filtro
        const delta = original.length - transformed.length;
        const newStart = Math.max(0, start - delta);
        const newEnd = Math.max(0, end - delta);
        input.setSelectionRange(newStart, newEnd);
    } catch (_) {
        // Silencioso: algunos tipos de input no soportan setSelectionRange
    }
}

/**
 * Convierte todo el valor a MAYÚSCULAS preservando el cursor.
 * @param {HTMLInputElement|HTMLTextAreaElement} input
 */
window.toUppercasePreservingCursor = function (input) {
    applyTransformPreservingCursor(input, v => v.toUpperCase());
};

/**
 * Filtra el valor para dejar solo letras (con acentos), espacios y apóstrofes.
 * No convierte a mayúsculas; combínala con `toUppercasePreservingCursor` si quieres ambas.
 * @param {HTMLInputElement|HTMLTextAreaElement} input
 */
window.toLettersPreservingCursor = function (input) {
    applyTransformPreservingCursor(input, v =>
        v.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ\s']/g, '')
    );
};

/**
 * Filtra el valor para dejar solo dígitos.
 * @param {HTMLInputElement|HTMLTextAreaElement} input
 */
window.toDigitsPreservingCursor = function (input) {
    applyTransformPreservingCursor(input, v => v.replace(/\D/g, ''));
};
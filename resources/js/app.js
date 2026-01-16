import './bootstrap';


/* import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
 */

/* import $ from 'jquery';   ESTA IMPLE
import 'datatables.net';
import 'datatables.net-dt/css/dataTables.dataTables.css';

// Opcional: Si quieres los estilos por defecto de DataTables
import 'datatables.net-dt/js/dataTables.dataTables'; */

import Swal from 'sweetalert2';

window.Swal = Swal; // Así puedes usarlo en scripts inline

// Función para mostrar alertas personalizadas (como las de app.blade.php)
window.showCustomAlert = function(icon, title, text) {
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

// Hacer la función de confirmación disponible globalmente
window.showCustomConfirmation = function(isEnable = false, customMessage = null) {
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

// Mantener el código existente para formularios (pero usar la nueva función)
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sweet-confirm-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
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

// FUNCIÓN PARA DESHABILITAR USUARIOS
window.handleUserDisable = async function(userId, userName) {
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
                // Eliminar la fila de la tabla
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

// FUNCIÓN CORREGIDA PARA MANEJAR CAMBIOS DE ROL CON AJAX
window.handleRoleChange = async function(selectElement, userId, userName, isSelf, originalRole = null) {
    // Obtener el rol original del atributo data-original-role (más confiable)
    const currentOriginalRole = originalRole || selectElement.getAttribute('data-original-role');
    const newRole = selectElement.value;
    const roleText = (newRole === 'administrador') ? 'Administrador' : 'Básico';
    
    // Si el nuevo rol es igual al original, no hacer nada
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
                // Restablecer al valor original
                selectElement.value = currentOriginalRole;
                return;
            }
        }
        
        // ENVÍO CON AJAX PARA EVITAR RECARGA - USANDO MÉTODO PATCH
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
                // ✅ ACTUALIZAR EL ATRIBUTO data-original-role con el nuevo valor
                selectElement.setAttribute('data-original-role', newRole);
                
                showCustomAlert('success', '¡Éxito!', data.message);
            } else {
                throw new Error(data.message || 'Error al actualizar el rol');
            }
            
        } catch (error) {
            console.error('Error en la petición:', error);
            // Revertir el select al valor original en caso de error
            selectElement.value = currentOriginalRole;
            showCustomAlert('error', 'Error', error.message || 'Ocurrió un error al actualizar el rol.');
        }
        
    } else {
        // Si cancela la primera modal, restablece el valor del select al original
        selectElement.value = currentOriginalRole;
    }
};

// FUNCIÓN PARA HABILITAR USUARIOS DESHABILITADOS
window.handleUserEnable = async function(userId, userName) {
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
                // Eliminar la fila de la tabla
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
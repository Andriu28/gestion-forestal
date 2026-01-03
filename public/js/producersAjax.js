// producers-ajax.js - Manejo asíncrono de estados de productores

document.addEventListener('DOMContentLoaded', function() {
    initProducerAjaxHandlers();
});

function initProducerAjaxHandlers() {
    // Manejar cambio de estado (activo/inactivo)
    const toggleStatusForms = document.querySelectorAll('form[action*="toggle-status"]');
    toggleStatusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleToggleStatus(this);
        });
    });
    
    // Manejar deshabilitar (soft delete)
    const deleteForms = document.querySelectorAll('form[action*="destroy"]:not([action*="force"])');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleDisableProducer(this);
        });
    });
    
    // Manejar habilitar (restore)
    const restoreButtons = document.querySelectorAll('button[onclick*="handleProducerEnable"]');
    restoreButtons.forEach(button => {
        const onclickAttr = button.getAttribute('onclick');
        const producerIdMatch = onclickAttr.match(/handleProducerEnable\((\d+)/);
        if (producerIdMatch) {
            const producerId = producerIdMatch[1];
            const producerName = onclickAttr.match(/,\s*'([^']+)'/)?.[1] || 'este productor';
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                handleProducerEnable(producerId, producerName);
            });
            
            // Remover el atributo onclick para evitar duplicados
            button.removeAttribute('onclick');
        }
    });
}

function handleToggleStatus(form) {
    const formData = new FormData(form);
    const url = form.action;
    const method = form.method;
    const producerId = form.closest('tr').id.split('-').pop();
    
    Swal.fire({
        title: '¿Cambiar estado?',
        text: '¿Estás seguro de que deseas cambiar el estado de este productor?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sendAjaxRequest(url, method, formData, function(response) {
                if (response.success) {
                    // Actualizar la interfaz
                    const row = document.getElementById(`producer-row-${producerId}`);
                    if (row) {
                        const statusCell = row.querySelector('td:nth-child(4)');
                        if (statusCell) {
                            if (response.is_active) {
                                statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-green-600 text-white rounded-full">Activo</span>';
                            } else {
                                statusCell.innerHTML = '<span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-500 text-white rounded-full">Inactivo</span>';
                            }
                        }
                    }
                    
                    showSwalAlert('success', 'Éxito', response.message);
                }
            });
        }
    });
}

function handleDisableProducer(form) {
    const formData = new FormData(form);
    const url = form.action;
    const method = form.method;
    const producerRow = form.closest('tr');
    const producerId = producerRow.id.split('-').pop();
    const producerName = producerRow.querySelector('td:nth-child(1)').textContent.trim();
    
    Swal.fire({
        title: '¿Deshabilitar productor?',
        html: `¿Estás seguro de que deseas deshabilitar al productor <strong>${producerName}</strong>?<br><br>
               <small>El productor será movido a la lista de deshabilitados y podrás restaurarlo más tarde.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, deshabilitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sendAjaxRequest(url, method, formData, function(response) {
                if (response.success) {
                    // Remover la fila de la tabla
                    producerRow.remove();
                    
                    showSwalAlert('success', 'Éxito', response.message);
                    
                    // Mostrar enlace para ver deshabilitados si no hay filas
                    checkEmptyTable();
                }
            });
        }
    });
}

function handleProducerEnable(producerId, producerName) {
    const url = `/producers/${producerId}/restore`;
    
    Swal.fire({
        title: '¿Habilitar productor?',
        html: `¿Estás seguro de que deseas habilitar al productor <strong>${producerName}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, habilitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sendAjaxRequest(url, 'POST', {}, function(response) {
                if (response.success) {
                    // Remover la fila de la tabla de deshabilitados
                    const row = document.getElementById(`disabled-producer-row-${producerId}`);
                    if (row) {
                        row.remove();
                    }
                    
                    showSwalAlert('success', 'Éxito', response.message);
                    
                    // Mostrar mensaje si no hay más filas
                    checkEmptyTable();
                }
            });
        }
    });
}

function handleProducerForceDelete(producerId, producerName) {
    Swal.fire({
        title: '¿Eliminar permanentemente?',
        html: `¿Estás seguro de que deseas eliminar permanentemente al productor <strong>${producerName}</strong>?<br><br>
               <small class="text-red-600">¡Esta acción no se puede deshacer!</small>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = `/producers/${producerId}/force-delete`;
            sendAjaxRequest(url, 'POST', {}, function(response) {
                if (response.success) {
                    // Remover la fila
                    const row = document.getElementById(`disabled-producer-row-${producerId}`);
                    if (row) {
                        row.remove();
                    }
                    
                    showSwalAlert('success', 'Éxito', response.message);
                    checkEmptyTable();
                }
            });
        }
    });
}

function sendAjaxRequest(url, method, formData, callback) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        callback(data);
    })
    .catch(error => {
        console.error('Error:', error);
        showSwalAlert('error', 'Error', 'Hubo un problema con la solicitud.');
    });
}

function showSwalAlert(icon, title, text) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

function checkEmptyTable() {
    const table = document.querySelector('#producers-table tbody, #disabled-producers-table tbody');
    if (table && table.children.length === 0) {
        const emptyMessage = document.createElement('tr');
        emptyMessage.innerHTML = `
            <td colspan="5" class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400">No se encontraron productores.</p>
            </td>
        `;
        table.appendChild(emptyMessage);
    }
}

// Exportar funciones para uso global
window.handleProducerEnable = handleProducerEnable;
window.handleProducerForceDelete = handleProducerForceDelete;
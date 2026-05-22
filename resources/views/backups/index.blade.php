<x-app-layout>
    <div class="mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Respaldos de Base de Datos
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gestiona los backups y restauraciones de PostgreSQL
            </p>
        </div>

        <!-- Botones de Acción -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <!-- Crear Backup -->
            <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6">
                <button onclick="createBackup()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Crear Nuevo Backup
                </button>
            </div>

            <!-- Restaurar Backup -->
            <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6">
                <button onclick="showRestoreModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Restaurar Base de Datos
                </button>
            </div>

            <!-- Importar SQL -->
            <div class="bg-stone-100/90 dark:bg-custom-gray rounded-2xl shadow-lg p-6">
                <button onclick="document.getElementById('importFile').click()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Importar Archivo SQL
                </button>
                <input type="file" id="importFile" accept=".sql,.txt" class="hidden" onchange="importSQL(this)">
            </div>
        </div>

        <!-- Lista de Backups -->
        <div class="bg-stone-100/90 dark:bg-custom-gray rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                Backups Disponibles
            </h3>

            @if($backups->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-lg">No hay backups disponibles</p>
                    <p class="text-sm mt-2">Crea tu primer backup usando el botón "Crear Nuevo Backup"</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Archivo</th>
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Tamaño</th>
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300">Fecha</th>
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-gray-300 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                            <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="py-3">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $backup['filename'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $backup['date'] }}
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('backups.download', $backup['filename']) }}" 
                                           class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors"
                                           title="Descargar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        <button onclick="deleteBackup('{{ $backup['filename'] }}')"
                                                class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para Restaurar -->
    <div id="restoreModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white dark:bg-custom-gray rounded-2xl shadow-2xl p-8 max-w-lg w-full mx-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Restaurar Base de Datos</h3>
            
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <p class="text-red-600 dark:text-red-400 font-semibold mb-2">⚠️ ¡ADVERTENCIA!</p>
                <p class="text-red-600 dark:text-red-400 text-sm">
                    Esta acción reemplazará TODOS los datos actuales. Se creará un backup de seguridad automáticamente antes de restaurar.
                </p>
            </div>

            <form id="restoreForm" onsubmit="restoreBackup(event)">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Seleccionar Backup
                    </label>
                    <select name="backup_file" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($backups as $backup)
                            <option value="{{ $backup['filename'] }}">
                                {{ $backup['filename'] }} ({{ $backup['size'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Escribe "YES" para confirmar
                    </label>
                    <input type="text" name="confirm_restore" 
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                           placeholder="YES" required>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRestoreModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Restaurar
                    </button>
                </div>
            </form>
        </div>
    </div>

    </x-app-layout>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Crear Backup
        function createBackup() {
            Swal.fire({
                title: 'Crear Backup',
                text: '¿Deseas crear un nuevo backup de la base de datos?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar',
                input: 'text',
                inputPlaceholder: 'Nombre opcional (sin espacios)',
                inputAttributes: {
                    pattern: '[a-zA-Z0-9_-]+'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Creando backup...');
                    
                    fetch('{{ route("backups.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            backup_name: result.value || null
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Backup Creado',
                                text: `Archivo: ${data.filename} (${data.size})`,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error al crear el backup', 'error');
                    });
                }
            });
        }

        // Restaurar Backup
        function showRestoreModal() {
            document.getElementById('restoreModal').classList.remove('hidden');
        }

        function closeRestoreModal() {
            document.getElementById('restoreModal').classList.add('hidden');
        }

        function restoreBackup(event) {
            event.preventDefault();
            
            const form = document.getElementById('restoreForm');
            const formData = new FormData(form);
            
            if (formData.get('confirm_restore') !== 'YES') {
                Swal.fire('Error', 'Debes escribir YES para confirmar', 'error');
                return;
            }
            
            showLoading('Restaurando base de datos...');
            
            fetch('{{ route("backups.restore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    backup_file: formData.get('backup_file'),
                    confirm_restore: formData.get('confirm_restore')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restauración Exitosa',
                        text: data.message + ' Se recomienda cerrar sesión.',
                        showCancelButton: true,
                        confirmButtonText: 'Cerrar Sesión',
                        cancelButtonText: 'Permanecer'
                    }).then((result) => {
                        closeRestoreModal();
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("logout") }}';
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error al restaurar la base de datos', 'error');
            });
        }

        // Eliminar Backup
        function deleteBackup(filename) {
            Swal.fire({
                title: '¿Eliminar Backup?',
                text: `¿Estás seguro de eliminar "${filename}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ route("backups.destroy", ":filename") }}`.replace(':filename', filename), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminado', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        }

        // Importar SQL
        function importSQL(input) {
            if (!input.files.length) return;
            
            Swal.fire({
                title: 'Importar Archivo SQL',
                text: 'Esta acción modificará la base de datos. Se creará un backup de seguridad.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Importar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('sql_file', input.files[0]);
                    
                    showLoading('Importando archivo SQL...');
                    
                    fetch('{{ route("backups.import") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Éxito', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error al importar', 'error');
                    });
                }
                
                input.value = '';
            });
        }

        function showLoading(message) {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    </script>

<div>
    <form wire:submit="store" id="producer-form">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre del productor *')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" 
                wire:model.live.debounce.250ms="name" 
                autofocus
                oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="lastname" :value="__('Apellido *')" />
            <x-text-input id="lastname" class="block mt-1 w-full" type="text" 
                wire:model.live.debounce.250ms="lastname"
                oninput="this.value = this.value.replace(/[^A-Za-záéíóúÁÉÍÓÚüÜñÑ]/g, ''); if(this.value.length === 1) this.value = this.value.toUpperCase();" />
            <x-input-error :messages="$errors->first('lastname')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="description" :value="__('Descripción *')" />
            <textarea id="description" wire:model.live="description" rows="3" 
                class="w-full px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70"
                placeholder="Descripción del productor..."
                oninput="if(this.value.length === 1) this.value = this.value.toUpperCase();"></textarea>
            <x-input-error :messages="$errors->first('description')" class="mt-2" />
        </div>

        <!-- ===== MAPA DE UBICACIÓN ===== -->
        <div class="mt-6" wire:ignore>
            <x-input-label :value="__('Ubicación')" />

            <!-- Contenedor del mapa -->
            <div id="map" style="height: 60vh; border: 1px solid #dededeff; border-radius: 0.5rem; margin-top: 0.5rem;"></div>

            <!-- Coordenadas (solo lectura) -->
            <div class="grid grid-cols-2 gap-4 mt-3">
                <div>
                    <x-input-label for="latitude" :value="__('Latitud')" />
                    <x-text-input id="latitude" type="text" wire:model.live="latitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" readonly />
                    <x-input-error :messages="$errors->first('latitude')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="longitude" :value="__('Longitud')" />
                    <x-text-input id="longitude" type="text" wire:model.live="longitude" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" readonly />
                    <x-input-error :messages="$errors->first('longitude')" class="mt-1" />
                </div>
            </div>

            <!-- Dirección -->
            <div class="mt-3">
                <x-input-label for="address" :value="__('Dirección')" />
                <x-text-input id="address" type="text" wire:model.live="address" class="block mt-1 w-full" placeholder="Dirección obtenida automáticamente..." />
                <x-input-error :messages="$errors->first('address')" class="mt-1" />
            </div>

            <!-- Botón para usar ubicación actual -->
            <button type="button" id="locate-user"
                class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Usar mi ubicación
            </button>
        </div>
        <!-- ===== FIN MAPA ===== -->

        <div class="mt-4 flex items-center">
            <input type="checkbox" id="is_active" wire:model="is_active" class="border border-stone-400/80 dark:border-gray-600 !bg-stone-50 dark:!bg-gray-800/50 text-custom-gray dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-custom-gold-dark dark:focus:ring-custom-gold-medium/70 focus:border-custom-gold-dark dark:focus:border-custom-gold-medium/70">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">Productor activo</label>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <x-go-back-button />
            <x-primary-button>
                {{ __('Guardar productor') }}
            </x-primary-button>
        </div>
    </form>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // 1. INICIALIZAR MAPA
            // ============================================
            const initialLon = -63.1729;
            const initialLat = 10.5556;
    
            const map = new ol.Map({
                target: 'map',
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.OSM()
                    })
                ],
                view: new ol.View({
                    center: ol.proj.fromLonLat([initialLon, initialLat]),
                    zoom: 13
                })
            });
    
            // ============================================
            // 2. CAPA DE MARCADOR
            // ============================================
            const markerLayer = new ol.layer.Vector({
                source: new ol.source.Vector(),
                style: new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 8,
                        fill: new ol.style.Fill({ color: '#dc2626' }),
                        stroke: new ol.style.Stroke({ color: '#ffffff', width: 2 })
                    })
                })
            });
            map.addLayer(markerLayer);
    
            function placeMarker(coordinate) {
                markerLayer.getSource().clear();
                const feature = new ol.Feature({
                    geometry: new ol.geom.Point(coordinate)
                });
                markerLayer.getSource().addFeature(feature);
            }
    
            // ============================================
            // 3. CLICK EN EL MAPA
            // ============================================
            map.on('click', function(evt) {
                const coord = evt.coordinate;
                const lonLat = ol.proj.toLonLat(coord);
                const lat = lonLat[1];
                const lng = lonLat[0];
    
                placeMarker(coord);
    
                @this.set('latitude', lat);
                @this.set('longitude', lng);
    
                reverseGeocode(lat, lng);
            });
    
            // ============================================
            // 4. GEOCODIFICACIÓN INVERSA (Nominatim)
            // ============================================
            function reverseGeocode(lat, lng) {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
    
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            @this.set('address', data.display_name);
                        } else {
                            @this.set('address', 'Dirección no encontrada');
                        }
                    })
                    .catch(error => {
                        console.error('Error en geocodificación:', error);
                        @this.set('address', 'Error al obtener dirección');
                    });
            }
    
            // ============================================
            // 5. BOTÓN "USAR MI UBICACIÓN"
            // ============================================
            document.getElementById('locate-user')?.addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const coord = ol.proj.fromLonLat([lng, lat]);
    
                        placeMarker(coord);
                        map.getView().setCenter(coord);
                        map.getView().setZoom(15);
    
                        @this.set('latitude', lat);
                        @this.set('longitude', lng);
                        reverseGeocode(lat, lng);
                    }, function(error) {
                        alert('No se pudo obtener tu ubicación: ' + error.message);
                    });
                } else {
                    alert('Tu navegador no soporta geolocalización.');
                }
            });
    
            // ============================================
            // 6. CARGAR UBICACIÓN EXISTENTE (si edición)
            // ============================================
            const existingLat = @json($latitude ?? null);
            const existingLng = @json($longitude ?? null);
    
            if (existingLat !== null && existingLng !== null) {
                const coord = ol.proj.fromLonLat([parseFloat(existingLng), parseFloat(existingLat)]);
                placeMarker(coord);
                map.getView().setCenter(coord);
                map.getView().setZoom(15);
            } else {
                // Opcional: colocar marcador en ubicación inicial por defecto
                const defaultCoord = ol.proj.fromLonLat([initialLon, initialLat]);
                placeMarker(defaultCoord);
                // Si quieres obtener la dirección por defecto, descomenta:
                // reverseGeocode(initialLat, initialLon);
            }
        });
    </script>
</div>


<script>
class FormValidator {
    static fields = [
        {
            id: 'name',
            rules: [
                { 
                    type: 'pattern', 
                    pattern: /^[A-ZÁÉÍÓÚÜÑ][A-Za-záéíóúÁÉÍÓÚüÜñÑ]*$/, 
                    message: 'Solo letras, sin espacios, números ni caracteres especiales. Debe empezar con mayúscula.' 
                }
            ]
        },
        {
            id: 'lastname',
            rules: [
                { 
                    type: 'pattern', 
                    pattern: /^[A-ZÁÉÍÓÚÜÑ][A-Za-záéíóúÁÉÍÓÚüÜñÑ]*$/, 
                    message: 'Solo letras, sin espacios, números ni caracteres especiales. Debe empezar con mayúscula.' 
                }
            ]
        }
    ];

    static initializeFields() {
        this.fields.forEach(field => {
            const input = document.getElementById(field.id);
            if (input) {
                input.addEventListener('blur', () => this.validateField(field.id));
                input.addEventListener('input', () => this.clearError(field.id));
                
                // Convertir primera letra a mayúscula
                input.addEventListener('input', (e) => {
                    const value = e.target.value;
                    if (value.length === 1) {
                        e.target.value = value.toUpperCase();
                    }
                });
            }
        });
    }

    static validateField(fieldId) {
        const fieldConfig = this.fields.find(f => f.id === fieldId);
        if (!fieldConfig) return true;

        const input = document.getElementById(fieldId);
        const value = input.value.trim();
        let isValid = true;

        // Limpiar error previo
        this.clearError(fieldId);

        for (const rule of fieldConfig.rules) {
            switch (rule.type) {
                case 'required':
                    if (!value) {
                        this.showError(fieldId, rule.message);
                        isValid = false;
                    }
                    break;
                case 'pattern':
                    if (value && !rule.pattern.test(value)) {
                        this.showError(fieldId, rule.message);
                        isValid = false;
                    }
                    break;
            }
            if (!isValid) break;
        }

        return isValid;
    }

    static showError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const errorDiv = document.getElementById(`${fieldId}-error`);
        
        // Agregar clase de animación shake
        input.classList.add('shake-animation');
        setTimeout(() => input.classList.remove('shake-animation'), 500);
        
        // Mostrar mensaje de error
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        } else {
            // Crear elemento de error si no existe
            const div = document.createElement('div');
            div.id = `${fieldId}-error`;
            div.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
            div.textContent = message;
            input.parentNode.appendChild(div);
        }
        
        // Enfocar el campo
        input.focus();
    }

    static clearError(fieldId) {
        const input = document.getElementById(fieldId);
        const errorDiv = document.getElementById(`${fieldId}-error`);
        
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
    }

    static validateForm() {
        let isValid = true;
        this.fields.forEach(field => {
            if (!this.validateField(field.id)) {
                isValid = false;
            }
        });
        return isValid;
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Agregar estilos para animación shake
    if (!document.getElementById('form-validator-styles')) {
        const style = document.createElement('style');
        style.id = 'form-validator-styles';
        style.textContent = `
            .shake-animation {
                animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            }
            @keyframes shake {
                10%, 90% { transform: translateX(-2px); }
                20%, 80% { transform: translateX(3px); }
                30%, 50%, 70% { transform: translateX(-3px); }
                40%, 60% { transform: translateX(3px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Inicializar campos
    FormValidator.initializeFields();
    
    // Configurar Livewire si existe
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('request', ({ fail }) => {
            if (!FormValidator.validateForm()) {
                fail();
            }
        });
    }
});
</script>
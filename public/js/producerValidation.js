// producer-validation.js - Validaciones en tiempo real para campos de nombre y apellido

document.addEventListener('DOMContentLoaded', function() {
    initProducerValidations();
});

function initProducerValidations() {
    // Seleccionar todos los inputs de nombre y apellido en forms Livewire
    const nameInputs = document.querySelectorAll('input[name="name"], input[name="lastname"]');
    
    nameInputs.forEach(input => {
        // Validar mientras se escribe
        input.addEventListener('input', function() {
            validateProducerInput(this);
        });
        
        // Capitalizar al perder foco
        input.addEventListener('blur', function() {
            if (this.value && !this.classList.contains('is-invalid')) {
                this.value = formatProducerName(this.value);
            }
        });
        
        // Prevenir entrada de números y caracteres no válidos
        input.addEventListener('keydown', function(e) {
            // Permitir teclas de control
            if (e.ctrlKey || e.metaKey || 
                [8, 9, 13, 27, 32, 37, 38, 39, 40, 46].includes(e.keyCode)) {
                return;
            }
            
            // Bloquear números
            if (e.keyCode >= 48 && e.keyCode <= 57) {
                e.preventDefault();
                showInputError(this, 'No se permiten números en este campo');
                return;
            }
            
            // Bloquear símbolos no permitidos (excepto apóstrofe, guión y espacio)
            const allowedSpecialChars = [32, 39, 45, 189]; // espacio, apóstrofe, guión
            const isLetter = (e.keyCode >= 65 && e.keyCode <= 90) || 
                           (e.keyCode >= 97 && e.keyCode <= 122);
            const isSpecialChar = allowedSpecialChars.includes(e.keyCode);
            
            if (!isLetter && !isSpecialChar) {
                e.preventDefault();
                showInputError(this, 'Carácter no permitido');
            }
        });
        
        // Limpiar error al empezar a escribir
        input.addEventListener('focus', function() {
            this.classList.remove('is-invalid');
            const errorElement = this.nextElementSibling?.querySelector('.input-error');
            if (errorElement) {
                errorElement.textContent = '';
            }
        });
    });
}

function validateProducerInput(input) {
    const value = input.value.trim();
    const fieldName = input.name;
    const isRequired = fieldName === 'name';
    
    // Limpiar clases anteriores
    input.classList.remove('is-invalid', 'is-valid');
    
    // Si está vacío y no es requerido, está bien
    if (!value && !isRequired) {
        return true;
    }
    
    // Si está vacío y es requerido, mostrar error
    if (!value && isRequired) {
        showInputError(input, 'Este campo es obligatorio');
        return false;
    }
    
    // Validar que no contenga números
    const hasNumbers = /\d/.test(value);
    if (hasNumbers) {
        showInputError(input, 'No se permiten números');
        return false;
    }
    
    // Validar que empiece con mayúscula
    const startsWithUppercase = /^[A-ZÁÉÍÓÚÑ]/.test(value);
    if (!startsWithUppercase) {
        showInputError(input, 'Debe comenzar con mayúscula');
        return false;
    }
    
    // Validar formato completo
    const isValidFormat = /^[A-ZÁÉÍÓÚÑ][a-záéíóúñ\s\'-]+$/.test(value);
    if (!isValidFormat) {
        showInputError(input, 'Formato inválido. Use solo letras, espacios, apóstrofes o guiones');
        return false;
    }
    
    // Validar longitud mínima para nombre
    if (fieldName === 'name' && value.length < 3) {
        showInputError(input, 'El nombre debe tener al menos 3 caracteres');
        return false;
    }
    
    // Todo está bien
    input.classList.add('is-valid');
    return true;
}

function formatProducerName(name) {
    return name.toLowerCase()
        .split(' ')
        .map(word => {
            if (!word) return '';
            
            // Manejar apóstrofes (como O'Connor)
            if (word.includes("'")) {
                return word.split("'")
                    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
                    .join("'");
            }
            
            // Manejar guiones (como García-Márquez)
            if (word.includes("-")) {
                return word.split("-")
                    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
                    .join("-");
            }
            
            return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(' ');
}

function showInputError(input, message) {
    input.classList.add('is-invalid');
    
    // Buscar elemento de error de Livewire
    let errorElement = input.nextElementSibling;
    
    if (!errorElement || !errorElement.classList.contains('input-error')) {
        // Intentar encontrar el contenedor de error
        const parent = input.parentElement;
        errorElement = parent.querySelector('.input-error');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Interceptar envío de formularios Livewire para validación adicional
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('request', ({ fail, succeed }) => {
        // Validar todos los campos antes del envío
        const nameInputs = document.querySelectorAll('input[name="name"], input[name="lastname"]');
        let isValid = true;
        
        nameInputs.forEach(input => {
            if (!validateProducerInput(input)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            // Mostrar alerta si hay errores
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Por favor, corrija los errores en el formulario antes de enviar.',
                confirmButtonText: 'Entendido'
            });
            
            // Prevenir el envío
            return false;
        }
    });
});
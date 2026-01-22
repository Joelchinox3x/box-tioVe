// ============================================
// SISTEMA DE VALIDACIÓN MODERNO
// ============================================

// Detectar estado de proteccion global (definido en la vista)
// const protegido = ... (global)

const validaciones = {
    nombre: { validado: false, requerido: true },
    precio: { validado: false, requerido: true }
};

document.addEventListener('DOMContentLoaded', () => {
    initValidation();
});

function initValidation() {
    // Si está protegido, no activamos validaciones visuales de edición
    if (typeof protegido !== 'undefined' && protegido) return;

    setupNombreValidation();
    setupPrecioValidation();
}

function setupNombreValidation() {
    const nombreInput = document.getElementById('nombre');
    const nombreCheck = document.getElementById('nombre-check');
    const nombreError = document.getElementById('nombre-error');

    if (!nombreInput) return;

    // Validar valor inicial
    const valorInicial = nombreInput.value.trim();
    if (valorInicial && valorInicial.length >= 3) {
        validaciones.nombre.validado = true;
        nombreCheck?.classList.remove('hidden');
        nombreInput.classList.add('border-green-500');
        nombreInput.classList.remove('border-slate-200');
    }

    nombreInput.addEventListener('input', function () {
        const value = this.value.trim();

        if (value.length === 0) {
            nombreCheck?.classList.add('hidden');
            nombreError?.classList.add('hidden');
            this.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4');
            this.classList.add('border-slate-200');
            validaciones.nombre.validado = false;
        } else if (value.length >= 3) {
            nombreCheck?.classList.remove('hidden');
            nombreError?.classList.add('hidden');
            this.classList.add('border-green-500');
            this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
            validaciones.nombre.validado = true;
        } else {
            nombreCheck?.classList.add('hidden');
            nombreError?.classList.remove('hidden');
            this.classList.add('border-red-500');
            this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
            validaciones.nombre.validado = false;
        }
    });

    nombreInput.addEventListener('focus', function () {
        const value = this.value.trim();
        if (value.length === 0) {
            this.classList.add('border-blue-500', 'ring-4', 'ring-blue-500/20');
            this.classList.remove('border-slate-200');
        } else if (validaciones.nombre.validado) {
            this.classList.add('ring-4', 'ring-green-500/20');
        } else {
            this.classList.add('ring-4', 'ring-red-500/20');
        }
    });

    nombreInput.addEventListener('blur', function () {
        this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
        if (this.value.trim().length === 0) {
            this.classList.remove('border-blue-500');
            this.classList.add('border-slate-200');
        }
    });
}

function setupPrecioValidation() {
    const precioInput = document.getElementById('precio');
    const precioCheck = document.getElementById('precio-check');
    const precioError = document.getElementById('precio-error');
    const precioHint = document.getElementById('precio-hint');

    if (!precioInput) return;

    // Validar valor inicial
    const valorInicial = parseFloat(precioInput.value);
    if (valorInicial > 0) {
        validaciones.precio.validado = true;
        precioCheck?.classList.remove('hidden');
        precioInput.classList.add('border-green-500');
        precioInput.classList.remove('border-slate-200');
        precioHint?.classList.add('text-green-600');
        precioHint?.classList.remove('text-slate-500');
        if (precioHint) precioHint.textContent = 'Precio válido ✓';
    }

    precioInput.addEventListener('input', function () {
        const value = parseFloat(this.value);

        if (this.value.trim() === '') {
            precioCheck?.classList.add('hidden');
            precioError?.classList.add('hidden');
            this.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4');
            this.classList.add('border-slate-200');
            if (precioHint) {
                precioHint.classList.remove('text-green-600', 'text-red-600');
                precioHint.classList.add('text-slate-500');
                precioHint.textContent = 'Debe ser mayor a 0';
            }
            validaciones.precio.validado = false;
        } else if (value > 0) {
            precioCheck?.classList.remove('hidden');
            precioError?.classList.add('hidden');
            this.classList.add('border-green-500');
            this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
            if (precioHint) {
                precioHint.classList.add('text-green-600');
                precioHint.classList.remove('text-slate-500', 'text-red-600');
                precioHint.textContent = 'Precio válido ✓';
            }
            validaciones.precio.validado = true;
        } else {
            precioCheck?.classList.add('hidden');
            precioError?.classList.remove('hidden');
            this.classList.add('border-red-500');
            this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
            if (precioHint) {
                precioHint.classList.add('text-red-600');
                precioHint.classList.remove('text-slate-500', 'text-green-600');
                precioHint.textContent = 'Debe ser mayor a 0';
            }
            validaciones.precio.validado = false;
        }
    });

    precioInput.addEventListener('focus', function () {
        const value = this.value.trim();
        if (value.length === 0) {
            this.classList.add('border-blue-500', 'ring-4', 'ring-blue-500/20');
            this.classList.remove('border-slate-200');
        } else if (validaciones.precio.validado) {
            this.classList.add('ring-4', 'ring-green-500/20');
        } else {
            this.classList.add('ring-4', 'ring-red-500/20');
        }
    });

    precioInput.addEventListener('blur', function () {
        this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
        if (this.value.trim().length === 0) {
            this.classList.remove('border-blue-500');
            this.classList.add('border-slate-200');
        }
    });
}

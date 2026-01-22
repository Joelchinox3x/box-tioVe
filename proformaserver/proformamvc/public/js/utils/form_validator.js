/**
 * FormValidator - Validador Unificado de Formularios
 * Centraliza la lógica de validación visual y funcional para mantener consistencia en todo el sistema.
 * 
 * Dependencias:
 * - toast.js (global mostrarToast) para notificaciones de error en submit.
 */
class FormValidator {
    /**
     * Valida nombres: Cuenta solo letras (a-z, áéíóú, ñ). Mínimo 4 letras.
     * Regla extraída de create.php: const letras = texto.match(/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/g);
     */
    static validateName(value) {
        const letters = value.match(/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/g);
        return letters && letters.length >= 4;
    }

    /**
     * Configura la validación completa para un input de nombre.
     * Incluye: Input, Focus, Blur, Keydown (Enter).
     * 
     * @param {string} inputId - ID del input
     * @param {Object} options - { checkId, errorId, nextInputId, isProtected, maxLength }
     */
    static setupNameInput(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const { checkId, errorId, nextInputId, isProtected, maxLength, hintId } = options;

        if (isProtected) return;

        // Limite de caracteres (default 70)
        // Eliminamos el atributo nativo si existe para controlar el toast manualmente
        const limit = maxLength || (input.hasAttribute('maxlength') ? parseInt(input.getAttribute('maxlength')) : 100);
        if (input.hasAttribute('maxlength')) {
            input.removeAttribute('maxlength');
        }

        const checkIcon = checkId ? document.getElementById(checkId) : null;
        const errorIcon = errorId ? document.getElementById(errorId) : null;
        const hintElement = hintId ? document.getElementById(hintId) : null;
        let isValid = false;

        const showSuccess = () => {
            if (checkIcon) {
                checkIcon.classList.remove('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.play();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-slate-500', 'text-red-500');
                hintElement.classList.add('text-green-500');
            }
        };

        const showNeutral = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-green-500', 'text-red-500');
                hintElement.classList.add('text-slate-500');
            }
        };

        const showError = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.remove('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-slate-500', 'text-green-500');
                hintElement.classList.add('text-red-500');
            }
        };

        const updateState = () => {
            const value = input.value.trim();

            if (value.length === 0) {
                input.classList.remove('border-green-500', 'border-red-500', 'ring-green-500/20', 'ring-red-500/20');
                input.classList.add('border-slate-200');
                showNeutral();
                isValid = false;
            } else if (this.validateName(value)) {
                input.classList.add('border-green-500');
                input.classList.remove('border-slate-200', 'border-red-500', 'ring-red-500/20');
                if (document.activeElement === input) input.classList.add('ring-green-500/20');
                showSuccess();
                isValid = true;
            } else {
                input.classList.add('border-red-500');
                input.classList.remove('border-slate-200', 'border-green-500', 'ring-green-500/20');
                if (document.activeElement === input) input.classList.add('ring-red-500/20');
                showError();
                isValid = false;
            }
        };

        input.addEventListener('input', () => {
            if (input.value.length > limit) {
                input.value = input.value.slice(0, limit);
                if (typeof mostrarToast === 'function') {
                    mostrarToast(`Límite de ${limit} caracteres alcanzado`, 'error');
                }
            }
            updateState();
        });

        input.addEventListener('focus', () => {
            input.classList.add('ring-4');
            const value = input.value.trim();
            if (value.length === 0) {
                input.classList.add('border-blue-500', 'ring-blue-500/20');
                input.classList.remove('border-slate-200');
            } else {
                updateState();
            }
        });

        input.addEventListener('blur', () => {
            input.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
            if (input.value.trim().length === 0) {
                input.classList.remove('border-blue-500');
                input.classList.add('border-slate-200');
            }
        });

        if (nextInputId) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && isValid) {
                    e.preventDefault();
                    const nextInput = document.getElementById(nextInputId);
                    if (nextInput) nextInput.focus();
                }
            });
        }

        if (input.value.trim()) updateState();
    }

    /**
     * Valida DNI: 8 dígitos numéricos
     */
    static validateDni(value) {
        return /^\d{8}$/.test(value);
    }

    /**
     * Valida RUC: 11 dígitos numéricos
     */
    static validateRuc(value) {
        return /^\d{11}$/.test(value);
    }

    /**
     * Configura input para DNI (8 dígitos)
     */
    static setupDniInput(inputId, options = {}) {
        this._setupNumericInput(inputId, { ...options, expectedLength: 8, name: 'DNI' });
    }

    /**
     * Configura input para RUC (11 dígitos)
     */
    static setupRucInput(inputId, options = {}) {
        this._setupNumericInput(inputId, { ...options, expectedLength: 11, name: 'RUC' });
    }

    /**
     * Método interno reutilizable para inputs numéricos de longitud fija (DNI/RUC)
     */
    static _setupNumericInput(inputId, options) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const { checkId, errorId, nextInputId, isProtected, expectedLength, name, isDniRuc, hintId } = options;

        if (isProtected) return;

        // Eliminar atributo nativo y usar JS
        if (input.hasAttribute('maxlength')) input.removeAttribute('maxlength');

        const checkIcon = checkId ? document.getElementById(checkId) : null;
        const errorIcon = errorId ? document.getElementById(errorId) : null;
        const hintElement = hintId ? document.getElementById(hintId) : null;
        let isValid = false;

        const showSuccess = () => {
            if (checkIcon) {
                checkIcon.classList.remove('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.play();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-slate-500', 'text-red-500');
                hintElement.classList.add('text-green-500');
            }
        };

        const showNeutral = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-green-500', 'text-red-500');
                hintElement.classList.add('text-slate-500');
            }
        };

        const showError = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.remove('hidden');

            if (hintElement) {
                hintElement.classList.remove('text-slate-500', 'text-green-500');
                hintElement.classList.add('text-red-500');
            }
        };

        const updateState = () => {
            const value = input.value.trim();
            let isCorrectFormat = false;

            if (isDniRuc) {
                isCorrectFormat = this.validateDniRuc(value);
            } else {
                isCorrectFormat = (name === 'DNI' ? this.validateDni(value) : this.validateRuc(value));
            }

            if (value.length === 0) {
                input.classList.remove('border-green-500', 'border-red-500', 'ring-green-500/20', 'ring-red-500/20');
                input.classList.add('border-slate-200');
                showNeutral();
                isValid = false;
            } else if (isCorrectFormat) {
                input.classList.add('border-green-500');
                input.classList.remove('border-slate-200', 'border-red-500', 'ring-red-500/20');
                if (document.activeElement === input) input.classList.add('ring-green-500/20');
                showSuccess();
                isValid = true;
            } else {
                input.classList.add('border-red-500');
                input.classList.remove('border-slate-200', 'border-green-500', 'ring-green-500/20');
                if (document.activeElement === input) input.classList.add('ring-red-500/20');
                showError();
                isValid = false;
            }

            // Text logic specifically for DNI/RUC
            if (isDniRuc && hintElement && value.length > 0) {
                if (value.length === 8) hintElement.textContent = '8 dígitos (DNI)';
                else if (value.length === 11) hintElement.textContent = '11 dígitos (RUC)';
                else hintElement.textContent = '8 u 11 dígitos';
            }
        };

        input.addEventListener('input', () => {
            // Filtrar no numéricos
            input.value = input.value.replace(/\D/g, '');

            // Si es DNI/RUC, limitamos a 11. Si es específico (8 o 11), limitamos a ese valor.
            const limit = isDniRuc ? 11 : expectedLength;

            if (input.value.length > limit) {
                input.value = input.value.slice(0, limit);

            }
            updateState();
        });

        input.addEventListener('focus', () => {
            input.classList.add('ring-4');
            const value = input.value.trim();
            if (value.length === 0) {
                input.classList.add('border-blue-500', 'ring-blue-500/20');
                input.classList.remove('border-slate-200');
            } else {
                updateState();
            }
        });

        input.addEventListener('blur', () => {
            input.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
            if (input.value.trim().length === 0) {
                input.classList.remove('border-blue-500');
                input.classList.add('border-slate-200');
            }
        });

        if (nextInputId) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && isValid) {
                    e.preventDefault();
                    const nextInput = document.getElementById(nextInputId);
                    if (nextInput) nextInput.focus();
                }
            });
        }
        if (input.value.trim()) updateState();
    }

    /**
     * Valida DNI (8) o RUC (11)
     */
    static validateDniRuc(value) {
        return /^\d{8}$/.test(value) || /^\d{11}$/.test(value);
    }

    /**
     * Configura input para DNI o RUC (único campo)
     */
    static setupDniRucInput(inputId, options = {}) {
        this._setupNumericInput(inputId, { ...options, expectedLength: 11, name: 'DNI/RUC', isDniRuc: true });
    }

    /**
     * Valida formato de Email
     */
    static validateEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    /**
     * Configura input para Email
     */
    static setupEmailInput(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const { checkId, errorId, isProtected } = options;
        if (isProtected) return;

        const checkIcon = checkId ? document.getElementById(checkId) : null;
        const errorIcon = errorId ? document.getElementById(errorId) : null;

        const showSuccess = () => {
            if (checkIcon) {
                checkIcon.classList.remove('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.play();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');
        };

        const showNeutral = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');
        };

        const showError = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.remove('hidden');
        };

        const updateState = () => {
            const value = input.value.trim();
            if (value.length === 0) {
                input.classList.remove('border-green-500', 'border-red-500', 'ring-green-500/20', 'ring-red-500/20');
                input.classList.add('border-slate-200');
                showNeutral();
            } else if (this.validateEmail(value)) {
                input.classList.add('border-green-500');
                input.classList.remove('border-slate-200', 'border-red-500', 'ring-red-500/20');
                if (document.activeElement === input) input.classList.add('ring-green-500/20');
                showSuccess();
            } else {
                input.classList.add('border-red-500');
                input.classList.remove('border-slate-200', 'border-green-500', 'ring-green-500/20');
                if (document.activeElement === input) input.classList.add('ring-red-500/20');
                showError();
            }
        };

        input.addEventListener('input', updateState);

        input.addEventListener('focus', () => {
            input.classList.add('ring-4');
            const value = input.value.trim();
            if (value.length === 0) {
                input.classList.add('border-blue-500', 'ring-blue-500/20');
                input.classList.remove('border-slate-200');
            } else {
                updateState();
            }
        });

        input.addEventListener('blur', () => {
            input.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
            if (input.value.trim().length === 0) {
                input.classList.remove('border-blue-500');
                input.classList.add('border-slate-200');
            }
        });

        if (input.value.trim()) updateState();
    }

    /**
     * Valida que el precio sea mayor a 0.
     */
    static validatePrice(value) {
        const num = parseFloat(value);
        return !isNaN(num) && num > 0;
    }

    /**
     * Configura la validación para un input de precio.
     * @param {string} inputId - ID del input
     * @param {Object} options - { checkId, errorId, hintId, isProtected }
     */
    static setupPriceInput(inputId, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const { checkId, errorId, hintId, isProtected } = options;
        if (isProtected) return;

        const checkIcon = checkId ? document.getElementById(checkId) : null;
        const errorIcon = errorId ? document.getElementById(errorId) : null;
        const hintText = hintId ? document.getElementById(hintId) : null;
        let isValid = false;

        // Helper para manejar Lottie o Icono (Copiar referencia de setupNameInput o duplicar si es necesario, 
        // pero mejor definir helpers a nivel de clase o duplicar para evitar problemas de scope sin instanciación)
        // Dado que son métodos estáticos, duplicaré los helpers internos por simplicidad o los haría estáticos privados si JS lo soportara bien aquí.
        const showSuccess = () => {
            if (checkIcon) {
                checkIcon.classList.remove('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.play();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');
            if (hintText) {
                hintText.classList.remove('text-slate-500', 'text-red-600');
                hintText.classList.add('text-green-600');
                hintText.textContent = 'Precio válido ✓';
            }
        };

        const showNeutral = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.add('hidden');
            if (hintText) {
                hintText.classList.remove('text-green-600', 'text-red-600');
                hintText.classList.add('text-slate-500');
                hintText.textContent = 'Debe ser mayor a 0';
            }
        };

        const showError = () => {
            if (checkIcon) {
                checkIcon.classList.add('hidden');
                if (checkIcon.tagName.toLowerCase() === 'dotlottie-player') {
                    checkIcon.stop();
                }
            }
            if (errorIcon) errorIcon.classList.remove('hidden');
            if (hintText) {
                hintText.classList.remove('text-slate-500', 'text-green-600');
                hintText.classList.add('text-red-600');
                hintText.textContent = 'Debe ser mayor a 0';
            }
        };

        const updateState = () => {
            const value = input.value.trim();
            if (value === '') {
                input.classList.remove('border-green-500', 'border-red-500', 'ring-green-500/20', 'ring-red-500/20');
                input.classList.add('border-slate-200');
                showNeutral();
                isValid = false;
            } else if (this.validatePrice(value)) {
                input.classList.add('border-green-500');
                input.classList.remove('border-slate-200', 'border-red-500', 'ring-red-500/20');
                if (document.activeElement === input) input.classList.add('ring-green-500/20');
                showSuccess();
                isValid = true;
            } else {
                input.classList.add('border-red-500');
                input.classList.remove('border-slate-200', 'border-green-500', 'ring-green-500/20');
                if (document.activeElement === input) input.classList.add('ring-red-500/20');
                showError();
                isValid = false;
            }
        };

        input.addEventListener('input', () => {
            updateState();

            // Check max length
            if (input.maxLength > 0 && input.value.length >= input.maxLength) {
                if (typeof mostrarToast === 'function') {
                    // Debounce simple: solo si no se ha mostrado recientemente (opcional, pero mejor direct)
                    // O simplemente mostrarlo. El toast.js suele manejar colas o reemplazos.
                    // Para evitar spam, verficamos si el último toast fue este mismo hace poco?
                    // Por simplicidad, mostrarlo.
                    mostrarToast(`Límite de ${input.maxLength} caracteres alcanzado`, 'error');
                }
            }
        });

        input.addEventListener('focus', () => {
            input.classList.add('ring-4');
            const value = input.value.trim();
            if (value.length === 0) {
                input.classList.add('border-blue-500', 'ring-blue-500/20');
                input.classList.remove('border-slate-200');
            } else {
                updateState();
            }
        });

        input.addEventListener('blur', () => {
            input.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
            if (input.value.trim().length === 0) {
                input.classList.remove('border-blue-500');
                input.classList.add('border-slate-200');
            }
        });

        if (input.value.trim()) updateState();
    }


    /**
     * Valida todo el formulario al hacer submit.
     * Muestra Toast si hay error y hace scroll al campo.
     * 
     * @param {Event} e - Evento de submit
     * @param {string} inputId - ID del input a validar
     * @param {string} errorMessage - Mensaje para el Toast
     * @param {string} validationType - 'name' | 'price' (default: 'name')
     * @returns {boolean} - True si es válido, False si falló (y previno envío)
     */
    static validateOnSubmit(e, inputId, errorMessage, validationType = 'name') {
        const input = document.getElementById(inputId);
        if (!input) return true;

        const value = input.value.trim();
        let isValid = false;

        if (validationType === 'price') {
            isValid = this.validatePrice(value);
        } else if (validationType === 'dni_ruc') {
            isValid = this.validateDniRuc(value);
        } else {
            isValid = this.validateName(value);
        }

        if (!isValid) {
            if (e) e.preventDefault(); // Prevenir envío del form

            // Determinar mensaje a mostrar
            let msg = 'Error de validación';
            if (typeof errorMessage === 'string') {
                msg = errorMessage;
            } else if (errorMessage && typeof errorMessage === 'object') {
                msg = (value.length === 0) ? (errorMessage.empty || 'Este campo es requerido') : (errorMessage.invalid || 'Formato incorrecto');
            }

            // Visuales de Error (Estilo Flash)
            input.classList.add('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
            input.classList.remove('border-slate-200');

            // Toast Global (toast.js)
            if (typeof mostrarToast === 'function') {
                mostrarToast(msg, 'error');
            } else {
                console.error('mostrarToast no está definido. Asegúrate de incluir toast.js.');
                alert(msg);
            }

            // Scroll y Focus
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input.focus();

            // Auto-limpieza en el próximo input (One-time listener)
            input.addEventListener('input', () => {
                input.classList.remove('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
                input.classList.add('border-slate-200');
            }, { once: true });

            return false;
        }
        return true;
    }
}

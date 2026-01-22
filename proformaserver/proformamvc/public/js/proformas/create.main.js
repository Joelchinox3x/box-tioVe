/**
 * Módulo Principal de Creación de Proformas
 * Maneja la validación del formulario e inicialización general.
 */

document.addEventListener('DOMContentLoaded', function () {

    // Validación del formulario
    const form = document.getElementById('proformaForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            let hasErrors = false;

            // Validar cliente
            const clienteId = document.getElementById('clienteIdHidden') ? document.getElementById('clienteIdHidden').value : '';
            const clienteInput = document.getElementById('clienteSearchInput');

            if (!clienteId && clienteInput) {
                e.preventDefault();
                hasErrors = true;

                // Pintar de rojo el campo de cliente
                clienteInput.classList.add('border-red-500', 'ring-4', 'ring-red-200', 'bg-red-50');
                clienteInput.classList.remove('border-slate-200');

                // Mostrar notificación
                if (window.mostrarNotificacion) window.mostrarNotificacion('Cliente requerido', 'Debes seleccionar un cliente', 'error');

                // Scroll al campo
                clienteInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Remover el error cuando haga click
                clienteInput.addEventListener('click', function () {
                    clienteInput.classList.remove('border-red-500', 'ring-4', 'ring-red-200', 'bg-red-50');
                    clienteInput.classList.add('border-slate-200');
                }, { once: true });
            }

            // Validar items
            const itemsCount = document.querySelectorAll('.item-row').length;
            const productoInput = document.getElementById('productoSearchInput');

            if (itemsCount === 0 && productoInput) {
                e.preventDefault();
                hasErrors = true;

                // Pintar de rojo el campo de producto
                productoInput.classList.add('border-red-500', 'ring-4', 'ring-red-200', 'bg-red-50');
                productoInput.classList.remove('border-slate-200');

                // Mostrar notificación
                if (window.mostrarNotificacion) window.mostrarNotificacion('Items requeridos', 'Debes agregar al menos un ítem a la proforma', 'error');

                // Scroll al campo si no hubo error en cliente
                if (clienteId) {
                    productoInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Remover el error cuando haga click
                productoInput.addEventListener('click', function () {
                    productoInput.classList.remove('border-red-500', 'ring-4', 'ring-red-200', 'bg-red-50');
                    productoInput.classList.add('border-slate-200');
                }, { once: true });
            }

            if (hasErrors) {
                return false;
            }
        });
    }
});

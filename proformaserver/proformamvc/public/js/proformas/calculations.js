/**
 * Módulo de Cálculos
 * Maneja la lógica de precios, impuestos y totales.
 */

// Se asume que IGV_PERCENT está definido globalmente desde PHP

window.calcularTotales = function () {
    let total = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.item-cantidad').value) || 0;
        const precio = parseFloat(row.querySelector('.item-precio').value) || 0;
        const itemTotal = cantidad * precio;

        // Update hidden subtotal field for this item
        const subtotalInput = row.querySelector('.item-subtotal');
        if (subtotalInput) {
            subtotalInput.value = itemTotal.toFixed(2);
        }

        total += itemTotal;
    });

    // El precio ya incluye IGV, así que calculamos el subtotal (base imponible)
    // Formula: subtotal = total / (1 + IGV_PERCENT/100)
    const subtotal = total / (1 + (window.IGV_PERCENT || 18) / 100);
    const igv = total - subtotal;

    // Mostrar total en la UI
    const displayTotal = document.getElementById('displayTotal');
    if (displayTotal) {
        displayTotal.textContent = total.toFixed(2);
    }

    // Update main form hidden fields
    const formSubtotal = document.getElementById('formSubtotal');
    const formIgv = document.getElementById('formIgv');
    const formTotal = document.getElementById('formTotal');

    if (formSubtotal) formSubtotal.value = subtotal.toFixed(2);
    if (formIgv) formIgv.value = igv.toFixed(2);
    if (formTotal) formTotal.value = total.toFixed(2);
};

window.actualizarMoneda = function (moneda) {
    const simbolo = moneda === 'USD' ? '$' : 'S/.';
    const displayTotal = document.getElementById('displayTotal');
    if (displayTotal && displayTotal.previousElementSibling) {
        displayTotal.previousElementSibling.textContent = simbolo;
    }

    // Actualizar input hidden de moneda
    const monedaInput = document.querySelector('input[name="moneda"]');
    if (monedaInput) {
        monedaInput.value = moneda;
    }
};

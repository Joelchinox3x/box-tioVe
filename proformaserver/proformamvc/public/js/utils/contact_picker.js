/**
 * Utility for Contact Picker API
 * Allows importing contacts from the device's address book.
 * 
 * Usage:
 * 1. Include this script: <script src="<?= asset('js/utils/contact_picker.js') ?>"></script>
 * 2. Ensure your view has:
 *    - A button with id="btnImportarContacto" (initially hidden)
 *    - Inputs with ids: "nombre", "telefonoInput"
 *    - Optional input: "telefono_full"
 */

document.addEventListener('DOMContentLoaded', () => {
    const btnImportarContacto = document.getElementById('btnImportarContacto');

    // Si no existe el botón, no hacemos nada
    if (!btnImportarContacto) return;

    // Verificar si el navegador soporta Contact Picker API
    const isContactPickerSupported = ('contacts' in navigator && 'ContactsManager' in window);

    if (isContactPickerSupported) {
        // Mostrar el botón si hay soporte
        btnImportarContacto.classList.remove('hidden');

        // Agregar evento
        btnImportarContacto.addEventListener('click', importarContacto);
    }

    async function importarContacto() {
        const props = ['name', 'tel'];
        const opts = { multiple: false };

        try {
            const contacts = await navigator.contacts.select(props, opts);

            if (contacts.length) {
                const contacto = contacts[0];

                // 1. Llenar Nombre
                if (contacto.name && contacto.name[0]) {
                    const nombreInput = document.getElementById('nombre');
                    if (nombreInput) {
                        nombreInput.value = contacto.name[0];
                        nombreInput.dispatchEvent(new Event('input'));
                    }

                    // Usar toast global si existe, si no console
                    if (typeof mostrarToast === 'function') {
                        mostrarToast('Contacto importado correctamente', 'success');
                    }
                }

                // 2. Llenar Teléfono
                if (contacto.tel && contacto.tel[0]) {
                    const telefonoInput = document.getElementById('telefonoInput');
                    const telefonoFullInput = document.getElementById('telefono_full');

                    // Limpiar número (quitar espacios, guiones, etc)
                    let rawNumber = contacto.tel[0].replace(/\s+/g, '').replace(/-/g, '');

                    // Si empieza con +51, quitarlo para el input visual
                    if (rawNumber.startsWith('+51')) {
                        rawNumber = rawNumber.substring(3);
                    } else if (rawNumber.startsWith('51') && rawNumber.length === 11) {
                        rawNumber = rawNumber.substring(2);
                    }

                    // Asignar al input visible
                    if (telefonoInput) {
                        telefonoInput.value = rawNumber;
                        telefonoInput.dispatchEvent(new Event('input'));
                    }

                    // Asignar al input hidden con formato completo (si existe)
                    if (telefonoFullInput) {
                        telefonoFullInput.value = '+51' + rawNumber;
                    }
                }
            }
        } catch (error) {
            console.log('Importación cancelada o error:', error);
        }
    }
});

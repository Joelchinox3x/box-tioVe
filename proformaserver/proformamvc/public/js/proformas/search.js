/**
 * Módulo de Búsqueda
 * Maneja los selectores con búsqueda integrada (Clientes y Productos).
 */

document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // SELECTOR DE CLIENTES
    // ========================================
    const clienteSearchInput = document.getElementById('clienteSearchInput');
    const clienteSearchField = document.getElementById('clienteSearchField');
    const clienteDropdown = document.getElementById('clienteDropdown');
    const clienteIdHidden = document.getElementById('clienteIdHidden');
    const clienteOptions = document.querySelectorAll('.cliente-option');

    if (clienteSearchInput) {
        clienteSearchInput.addEventListener('click', function (e) {
            // Reducimos timeout para mejorar respuesta
            setTimeout(() => {
                if (clienteDropdown.classList.contains('hidden')) {
                    clienteDropdown.classList.remove('hidden');
                    if (clienteSearchField) clienteSearchField.value = '';
                    mostrarTodasLasOpcionesCliente();

                    // PINTAR SECCIÓN DE AZUL y ELEVAR A PRIMER PLANO (ELIMINADO - COLOR RESET)
                    /*
                    const section = document.getElementById('clienteSection');
                    if (section) {
                        section.classList.remove('bg-white', 'border-slate-200');
                        section.classList.add('bg-blue-50', 'border-blue-200', 'border-l-4', 'border-l-blue-600');
                        section.style.position = 'relative';
                        section.style.zIndex = '100'; 
                    }
                    clienteSearchInput.classList.remove('border-slate-200');
                    clienteSearchInput.classList.add('border-blue-400');
                    */

                } else {
                    clienteDropdown.classList.add('hidden');

                    // AL CERRAR, RESTAURAR NIVEL (ELIMINADO - COLOR RESET)
                    /*
                    const section = document.getElementById('clienteSection');
                    if (section) {
                        if (!section.classList.contains('maquinaria-attention')) {
                            section.classList.add('bg-white', 'border-slate-200');
                            section.classList.remove('bg-blue-50', 'border-blue-200', 'border-l-4', 'border-l-blue-600');
                        }
                        section.style.zIndex = '';
                    }
                    clienteSearchInput.classList.add('border-slate-200');
                    clienteSearchInput.classList.remove('border-blue-400');
                    */
                }
            }, 50);
        });
    }

    if (clienteSearchField) {
        clienteSearchField.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            clienteOptions.forEach(option => {
                const nombre = option.getAttribute('data-nombre')?.toLowerCase() || '';
                const dni = option.getAttribute('data-dni')?.toLowerCase() || '';
                if (nombre.includes(searchTerm) || dni.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // FIX: Eliminamos la lógica de bloqueo de foco que impedía escribir en PC.
        // El navegador manejará el foco nativamente.
    }

    clienteOptions.forEach(option => {
        option.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const dni = this.getAttribute('data-dni');
            const telefono = this.getAttribute('data-telefono');

            window.selectedClientPhone = telefono; // Guardar teléfono globalmente

            if (clienteIdHidden) clienteIdHidden.value = id;
            if (clienteSearchInput) clienteSearchInput.value = `${nombre} - ${dni}`;

            if (clienteDropdown) clienteDropdown.classList.add('hidden');

            // ACTUALIZAR VISUALES (Sistema 3 Colores)
            updateClientVisuals(true);

            // if (window.activarAtencionMaquinaria) window.activarAtencionMaquinaria(); // YA NO SE USA, REEMPLAZADO POR updateMachineryVisuals dentro de updateClientVisuals
        });
    });

    // ========================================
    // SELECTOR DE PRODUCTOS
    // ========================================
    const productoSearchInput = document.getElementById('productoSearchInput');
    const productoSearchField = document.getElementById('productoSearchField');
    const productoDropdown = document.getElementById('productoDropdown');
    const productoOptions = document.querySelectorAll('.producto-option');

    if (productoSearchInput) {
        productoSearchInput.addEventListener('click', function (e) {
            // VALIDACIÓN: Verificar si hay cliente seleccionado
            const clienteId = document.getElementById('clienteIdHidden')?.value;
            if (!clienteId) {
                // Blur para quitar foco y teclado
                this.blur();

                if (window.mostrarNotificacion) {
                    window.mostrarNotificacion('Atención', 'Primero debes seleccionar un cliente para continuar', 'warning');
                } else {
                    alert('Primero debes seleccionar un cliente para continuar');
                }

                // Enfocar el buscador de clientes para guiar al usuario
                const clienteInput = document.getElementById('clienteSearchInput');
                if (clienteInput) {
                    setTimeout(() => clienteInput.focus(), 100);
                }

                return; // DETENER
            }

            this.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => {
                if (productoDropdown.classList.contains('hidden')) {
                    productoDropdown.classList.remove('hidden');
                    if (productoSearchField) productoSearchField.value = '';

                    if (window.monedaSeleccionada !== null && window.filtrarProductosPorMonedaDropdown) {
                        window.filtrarProductosPorMonedaDropdown(window.monedaSeleccionada);
                    } else {
                        mostrarTodasLasOpcionesProducto();
                    }
                } else {
                    productoDropdown.classList.add('hidden');
                }
            }, 300);
        });
    }

    if (productoSearchField) {
        productoSearchField.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const productosConFolleto = window.folletosDisponibles || {};

            productoOptions.forEach(option => {
                const nombre = option.getAttribute('data-nombre')?.toLowerCase() || '';
                const monedaOpcion = option.getAttribute('data-moneda');

                // Extraer ID para verificar brochure
                const dataProducto = JSON.parse(option.getAttribute('data-producto'));
                const productoId = dataProducto.id;

                const coincideBusqueda = nombre.includes(searchTerm);
                const coincideMoneda = window.monedaSeleccionada === null || monedaOpcion === window.monedaSeleccionada;

                // NUEVA VALIDACIÓN: Si estamos en modo folleto, debe tener folleto
                const coincideFolleto = !window.isBrochureMode || (productosConFolleto[productoId] && productosConFolleto[productoId].length > 0);

                if (coincideBusqueda && coincideMoneda && coincideFolleto) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });

        // FIX: Eliminamos la lógica de bloqueo de foco que impedía escribir en PC.
        // El navegador manejará el foco nativamente.
    }

    productoOptions.forEach(option => {
        option.addEventListener('click', function () {
            const productoData = this.getAttribute('data-producto');
            const producto = JSON.parse(productoData);

            if (window.monedaSeleccionada !== null && producto.moneda !== window.monedaSeleccionada) {
                return;
            }

            // MODIFICACION: MODO FOLLETO
            if (window.isBrochureMode) {
                if (window.renderBrochurePreview) {
                    window.renderBrochurePreview(producto);
                }
                if (productoDropdown) productoDropdown.classList.add('hidden');
                if (productoSearchInput) productoSearchInput.value = ''; // Limpiar buscador para estética
                return; // DETENER AQUI
            }

            if (window.agregarItemDesdeProducto) window.agregarItemDesdeProducto(producto);

            if (productoDropdown) productoDropdown.classList.add('hidden');
            if (productoSearchInput) productoSearchInput.value = '';
        });
    });

    // Cerrar dropdowns al click fuera
    document.addEventListener('click', function (e) {
        if (clienteSearchInput && clienteDropdown && !e.target.closest('#clienteSearchInput') && !e.target.closest('#clienteDropdown')) {
            clienteDropdown.classList.add('hidden');
        }
        if (productoSearchInput && productoDropdown && !e.target.closest('#productoSearchInput') && !e.target.closest('#productoDropdown')) {
            productoDropdown.classList.add('hidden');
        }
    });

    // Funciones helpers internas pero expuestas para items.js si es necesario
    function mostrarTodasLasOpcionesCliente() {
        clienteOptions.forEach(option => { option.style.display = ''; });
    }

    function mostrarTodasLasOpcionesProducto() {
        if (window.isBrochureMode && typeof filtrarDropdownPorFolleto === 'function') {
            filtrarDropdownPorFolleto(true);
            return;
        }
        productoOptions.forEach(option => { option.style.display = ''; });
    }

    // Exponer globalmente para que items.js pueda resetear filtros
    window.mostrarTodosLosProductos = mostrarTodasLasOpcionesProducto;
    window.mostrarTodasLasOpcionesProducto = mostrarTodasLasOpcionesProducto; // Alias

    window.filtrarProductosPorMonedaDropdown = function (moneda) {
        const productosConFolleto = window.folletosDisponibles || {};

        productoOptions.forEach(option => {
            const monedaOpcion = option.getAttribute('data-moneda');
            const dataProducto = JSON.parse(option.getAttribute('data-producto'));
            const productoId = dataProducto.id;

            const coincideMoneda = monedaOpcion === moneda;
            const coincideFolleto = !window.isBrochureMode || (productosConFolleto[productoId] && productosConFolleto[productoId].length > 0);

            if (coincideMoneda && coincideFolleto) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    };

    window.filtrarProductosPorMoneda = function (moneda) {
        window.filtrarProductosPorMonedaDropdown(moneda);
    };

    // ========================================
    // LOGICA DE COLORES (SISTEMA DE 3 COLORES)
    // ========================================

    function updateClientVisuals(hasClient) {
        const section = document.getElementById('clienteSection');
        const input = document.getElementById('clienteSearchInput');

        if (hasClient) {
            // ACTIVAR AZUL (CLIENTE)
            if (section) {
                section.classList.remove('bg-white', 'border-slate-200');
                section.classList.add('section-active-blue'); // Define fondo y borde
            }
            if (input) {
                input.classList.remove('border-slate-200');
                input.classList.add('border-blue-500', 'ring-1', 'ring-blue-500');
            }
        } else {
            // DESACTIVAR (RESET A SLATE)
            if (section) {
                section.classList.remove('section-active-blue');
                section.classList.add('bg-white', 'border-slate-200');
            }
            if (input) {
                input.classList.remove('border-blue-500', 'ring-1', 'ring-blue-500', 'bg-blue-50');
                input.classList.add('border-slate-200');
            }
        }

        // Actualizar maquinaria en cascada
        updateMachineryVisuals();
    }

    function updateMachineryVisuals() {
        const clienteId = document.getElementById('clienteIdHidden')?.value;
        const section = document.getElementById('maquinariaSection');
        const input = document.getElementById('productoSearchInput');
        const dropdown = document.getElementById('productoDropdown');
        const internalInput = document.getElementById('productoSearchField');

        // LIMPIEZA PREVIA (Siempre)
        if (section) section.classList.remove('section-active-indigo', 'section-active-purple', 'border-slate-200', 'bg-white');
        if (input) input.classList.remove(
            'border-indigo-500', 'ring-1', 'ring-indigo-500', 'bg-indigo-50',
            'border-purple-500', 'ring-1', 'ring-purple-500', 'bg-purple-50',
            'border-slate-200'
        );
        if (dropdown) dropdown.classList.remove('border-indigo-500', 'border-purple-500', 'border-slate-300');
        if (internalInput) internalInput.classList.remove(
            'focus:ring-indigo-400', 'focus:border-indigo-400',
            'focus:ring-purple-400', 'focus:border-purple-400',
            'focus:ring-slate-300', 'focus:border-slate-400'
        );

        // DECIDIR COLOR
        if (!clienteId) {
            // NEUTRO (SLATE)
            if (section) section.classList.add('border-slate-200', 'bg-white');
            if (input) input.classList.add('border-slate-200');
            if (dropdown) dropdown.classList.add('border-slate-300');
            if (internalInput) internalInput.classList.add('focus:ring-slate-300', 'focus:border-slate-400');
            return;
        }

        if (window.isBrochureMode) {
            // PURPLE (FOLLETO)
            if (section) section.classList.add('section-active-purple');
            if (input) input.classList.add('border-purple-500', 'ring-1', 'ring-purple-500'); // Input morado SIN fondo
            if (dropdown) dropdown.classList.add('border-purple-500');
            if (internalInput) internalInput.classList.add('focus:ring-purple-400', 'focus:border-purple-400');
        } else {
            // INDIGO (COTIZAR)
            if (section) section.classList.add('section-active-indigo');
            if (input) input.classList.add('border-indigo-500', 'ring-1', 'ring-indigo-500'); // Input indigo SIN fondo
            if (dropdown) dropdown.classList.add('border-indigo-500');
            if (internalInput) internalInput.classList.add('focus:ring-indigo-400', 'focus:border-indigo-400');
        }
    }

    // ========================================
    // LOGICA DE BROCHURES (FOLLETOS) - MODO INTEGRADO
    // ========================================

    window.isBrochureMode = false;
    window.selectedBrochureProduct = null;
    window.selectedBrochurePdf = null;

    window.toggleBrochureMode = function (active) {
        window.isBrochureMode = active;
        const btnQuote = document.getElementById('btnModeQuote');
        const btnBrochure = document.getElementById('btnModeBrochure');

        const modeQuoteContainer = document.getElementById('modeQuoteContainer');
        const itemsContainer = document.getElementById('itemsContainer');
        const btnManualItem = document.getElementById('btnManualItem');
        const brochurePreviewPanel = document.getElementById('brochurePreviewPanel');

        const iconContainer = document.getElementById('iconContainer');
        const sectionIcon = document.getElementById('sectionIcon');
        const maquiSection = document.getElementById('maquinariaSection');
        const productoSearchInput = document.getElementById('productoSearchInput');
        const floatingTotal = document.getElementById('floatingTotalContainer');
        const designSection = document.getElementById('designSection'); // Sección de diseño PDF

        // Resetear selección
        window.selectedBrochureProduct = null;
        window.selectedBrochurePdf = null;
        if (brochurePreviewPanel) brochurePreviewPanel.classList.add('hidden');
        if (document.getElementById('brochureCoverBase')) document.getElementById('brochureCoverBase').innerHTML = `<i class="ph-duotone ph-image text-3xl text-slate-300"></i>`;
        if (document.getElementById('brochureProductName')) document.getElementById('brochureProductName').textContent = 'Selecciona un producto';
        if (document.getElementById('brochureFileName')) document.getElementById('brochureFileName').textContent = '...';

        // LIMPIEZA TOTAL DE COLORES DINÁMICOS (RESET)
        // Solo manejamos visibilidad y cambios de texto/iconos básicos sin afectar colores de diseño base.

        if (active) {
            // MODO FOLLETO ACTIVO

            // Ocultar sección de diseño PDF
            if (designSection) designSection.classList.add('hidden');

            // Botones: Folleto PINTADO (Purple), Cotizar DESPINTADO (Slate)
            if (btnBrochure) {
                btnBrochure.classList.remove('text-slate-500', 'bg-white');
                btnBrochure.classList.add('bg-purple-100', 'text-purple-600', 'shadow-sm');
            }
            if (btnQuote) {
                btnQuote.classList.remove('bg-indigo-100', 'text-indigo-600', 'shadow-sm', 'bg-white', 'text-slate-700');
                btnQuote.classList.add('text-slate-500', 'hover:text-indigo-600');
            }

            // 1. Visibilidad
            if (itemsContainer) itemsContainer.classList.add('hidden');
            if (btnManualItem) btnManualItem.classList.add('hidden');
            if (floatingTotal) floatingTotal.classList.add('hidden');

            // Boton WhatsApp (Reset - oculto hasta elegir producto)
            const btnSendBrochure = document.getElementById('btnSendBrochure');
            if (btnSendBrochure) btnSendBrochure.classList.add('hidden');

            // 2. Iconos y Textos (Sin colores)
            if (productoSearchInput) {
                productoSearchInput.placeholder = "Buscar folleto...";
            }
            if (sectionIcon) {
                sectionIcon.classList.remove('ph-list-dashes');
                sectionIcon.classList.add('ph-file-pdf');
            }

            // 3. Filtrar Dropdown
            if (typeof filtrarDropdownPorFolleto === 'function') filtrarDropdownPorFolleto(true);

        } else {
            // MODO COTIZAR ACTIVO

            // Botones: Cotizar PINTADO (Indigo), Folleto DESPINTADO (Slate)
            if (btnQuote) {
                btnQuote.classList.remove('text-slate-500', 'bg-white', 'text-slate-700', 'hover:text-indigo-600');
                btnQuote.classList.add('bg-indigo-100', 'text-indigo-600', 'shadow-sm');
            }
            if (btnBrochure) {
                btnBrochure.classList.remove('bg-purple-100', 'text-purple-600', 'shadow-sm', 'bg-white');
                btnBrochure.classList.add('text-slate-500', 'hover:text-purple-600');
            }

            // 1. Visibilidad
            if (itemsContainer) itemsContainer.classList.remove('hidden');
            if (btnManualItem) btnManualItem.classList.remove('hidden');
            if (floatingTotal) floatingTotal.classList.remove('hidden');

            // Mostrar sección de diseño PDF
            if (designSection) designSection.classList.remove('hidden');

            const btnSendBrochure = document.getElementById('btnSendBrochure');
            if (btnSendBrochure) btnSendBrochure.classList.add('hidden');

            // 2. Iconos y Textos (Sin colores)
            if (productoSearchInput) {
                productoSearchInput.placeholder = "+ Agregar producto del inventario...";
            }
            if (sectionIcon) {
                sectionIcon.classList.remove('ph-file-pdf');
                sectionIcon.classList.add('ph-list-dashes');
            }

            // 3. Restaurar Dropdown
            if (typeof filtrarDropdownPorFolleto === 'function') filtrarDropdownPorFolleto(false);
        }

        // ACTUALIZAR VISUALES (Sistema 3 Colores - Reaccionar al cambio de modo)
        updateMachineryVisuals(); // <-- HOOK IMPORTANTE
    };

    function filtrarDropdownPorFolleto(filtrar) {
        const options = document.querySelectorAll('.producto-option');
        const productosConFolleto = window.folletosDisponibles || {};

        options.forEach(option => {
            if (!filtrar) {
                option.style.display = ''; // Mostrar todo
                return;
            }

            const dataProducto = JSON.parse(option.getAttribute('data-producto'));
            const productoId = dataProducto.id;

            if (productosConFolleto[productoId] && productosConFolleto[productoId].length > 0) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    }

    window.renderBrochurePreview = function (dataProducto) {
        const brochurePreviewPanel = document.getElementById('brochurePreviewPanel');
        const gridContainer = document.getElementById('brochureGridContainer');
        const txtName = document.getElementById('brochureProductName');
        const badgeCount = document.getElementById('brochureCountBadge');

        const folletos = window.folletosDisponibles[dataProducto.id];
        const btnSendBrochure = document.getElementById('btnSendBrochure');

        // Reset
        gridContainer.innerHTML = '';
        brochurePreviewPanel.classList.remove('hidden');
        txtName.textContent = dataProducto.nombre;

        // Guardar referencia actual
        window.selectedBrochureProduct = dataProducto;

        if (!folletos || folletos.length === 0) {
            badgeCount.textContent = '0 folletos';
            gridContainer.innerHTML = `<div class="col-span-3 text-center py-6 text-slate-400 text-xs italic">Este producto no tiene folletos disponibles.</div>`;
            if (btnSendBrochure) btnSendBrochure.classList.add('hidden');
            return;
        }

        // Mostrar botón principal
        if (btnSendBrochure) btnSendBrochure.classList.remove('hidden');

        badgeCount.textContent = `${folletos.length} folletos`;

        folletos.forEach(folleto => {
            // Parsear imagenes
            let coverImage = '';
            let imagenesFuente = [];
            try {
                imagenesFuente = (typeof folleto.imagenes_fuente === 'string')
                    ? JSON.parse(folleto.imagenes_fuente)
                    : folleto.imagenes_fuente;
            } catch (e) { }

            // Determinar imagen de portada
            let imgHtml = '';
            if (imagenesFuente && imagenesFuente.length > 0) {
                // Usamos la primera imagen
                imgHtml = `<img src="/${imagenesFuente[0]}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`;
            } else {
                imgHtml = `<div class="w-full h-full flex items-center justify-center bg-purple-100 text-purple-300"><i class="ph-duotone ph-file-pdf text-4xl"></i></div>`;
            }

            // Crear Card HTML
            const card = document.createElement('div');
            card.className = 'relative group rounded-xl overflow-hidden shadow-md bg-white border border-slate-100 hover:shadow-lg transition-all duration-300';

            // Botón Compartir (WhatsApp)
            const pdfUrl = folleto.ruta_pdf;

            card.innerHTML = `
                <!-- Contenedor Imagen -->
                <div class="relative aspect-[3/4] w-full bg-slate-50 overflow-hidden">
                    ${imgHtml}
                    
                    <!-- Overlay Top: Nombre del Folleto -->
                    <div class="absolute top-0 left-0 right-0 bg-black/60 backdrop-blur-[2px] p-1.5 text-center transition-transform hover:bg-black/70">
                         <span class="text-[10px] text-white font-bold block leading-tight truncate px-1">${folleto.nombre}</span>
                    </div>

                    <!-- Overlay Bottom: Categoría (Dentro de la foto) -->
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent p-2 pt-6 text-center">
                         <span class="text-[9px] text-purple-200 uppercase tracking-widest font-bold drop-shadow-sm">${folleto.categoria || 'GENERAL'}</span>
                    </div>
                </div>
                
                <!-- Botón Acción (Fuera de la foto) -->
                <button type="button" 
                        onclick="window.compartirFolletoEspecifico('${pdfUrl}', '${dataProducto.nombre}', '${folleto.nombre}')"
                        class="w-full py-2 bg-green-500 hover:bg-green-600 text-white text-[10px] font-bold flex items-center justify-center gap-1.5 transition-colors">
                    <i class="ph-bold ph-whatsapp-logo text-sm"></i> Compartir
                </button>
            `;

            gridContainer.appendChild(card);
        });
    };

    window.compartirFolletoEspecifico = function (pdfPath, productName, brochureName) {
        if (!pdfPath) return;

        // Obtener teléfono del cliente seleccionado
        const clienteId = document.getElementById('clienteIdHidden')?.value;
        const clienteInput = document.getElementById('clienteSearchInput');

        let nombreCliente = 'Cliente';
        let telefono = '';

        if (clienteId) {
            const option = document.querySelector(`.cliente-option[data-id="${clienteId}"]`);
            if (option) {
                telefono = option.getAttribute('data-telefono');
                nombreCliente = option.getAttribute('data-nombre');
            }
        }

        // Limpiar teléfono
        if (telefono) telefono = telefono.replace(/\D/g, '');

        // Si no hay teléfono válido, pedirlo
        if (!telefono || telefono.length < 9) {
            if (!clienteId) {
                // Focus en cliente para ayudar
                if (window.mostrarNotificacion) window.mostrarNotificacion('Selecciona Cliente', 'Primero selecciona un cliente o ingresa el número manual.', 'warning');
                clienteInput.focus();
                return;
            }

            // Abrir Modal
            const modal = document.getElementById('modalPhoneUpdate');
            const msg = document.getElementById('phoneModalMessage');

            if (modal) {
                // Guardar intento para reanudar después
                window.pendingShareData = { pdfPath, productName, brochureName };

                if (msg) msg.textContent = `El cliente ${nombreCliente} no tiene celular registrado.`;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    const input = document.getElementById('phoneUpdateInput');
                    if (input) input.focus();
                }, 100);
            } else {
                // Fallback si no existe el modal por alguna razon (aunque ya lo incluimos)
                alert("Error: Modal no encontrado");
            }
            return;
        }

        const urlPdfCompleta = window.location.origin + '/' + pdfPath;

        // Mensaje personalizado (Codificado para soportar caracteres como &)
        const saludo = `Hola ${nombreCliente}, aquí te comparto el folleto *${brochureName}* del equipo *${productName}*:`;
        const mensajeCompleto = `${saludo}\n${urlPdfCompleta}`;

        const mensajeCodificado = encodeURIComponent(mensajeCompleto);
        const urlWa = `https://wa.me/51${telefono}?text=${mensajeCodificado}`;
        window.open(urlWa, '_blank');
    };


    // Función para manejar el guardado desde el modal
    window.saveClientPhone = function () {
        const input = document.getElementById('phoneUpdateInput');
        const phone = input.value.replace(/\D/g, '');
        const clienteId = document.getElementById('clienteIdHidden')?.value;

        if (phone.length < 9) {
            if (window.mostrarNotificacion) window.mostrarNotificacion('Error', 'Ingresa un número de celular válido (9 dígitos).', 'error');
            return;
        }

        if (!clienteId) return;

        // AJAX Update
        const formData = new FormData();
        formData.append('id', clienteId);
        formData.append('telefono', phone);

        // Simulamos fetch a endpoint (pendiente crear)
        fetch('/clientes/update-phone', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 1. Actualizar DOM
                    const option = document.querySelector(`.cliente-option[data-id="${clienteId}"]`);
                    if (option) option.setAttribute('data-telefono', phone);
                    window.selectedClientPhone = phone;

                    // 2. Cerrar Modal
                    closePhoneModal();
                    if (window.mostrarNotificacion) window.mostrarNotificacion('Éxito', 'Celular actualizado correctamente.', 'success');

                    // 3. Reanudar acción compartida
                    if (window.pendingShareData) {
                        const { pdfPath, productName, brochureName } = window.pendingShareData;
                        window.compartirFolletoEspecifico(pdfPath, productName, brochureName);
                        window.pendingShareData = null; // Limpiar
                    }
                } else {
                    if (window.mostrarNotificacion) window.mostrarNotificacion('Error', data.message || 'Error al guardar.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                if (window.mostrarNotificacion) window.mostrarNotificacion('Error', 'Error de conexión.' + err, 'error');
            });
    };

    // Configurar validación del modal (Adaptado de create.php para usar IDs del modal)
    function setupPhoneModalValidation() {
        const input = document.getElementById('phoneUpdateInput');
        const container = document.getElementById('telefonoContainerModal');
        const hint = document.getElementById('telefonoHintModal');
        const hintText = document.getElementById('telefonoHintTextModal');
        const icon = document.getElementById('whatsappIconStatic');

        if (!input) return;
        if (input.dataset.validationAttached) return;
        input.dataset.validationAttached = "true";

        // ============================================
        // LOGICA CONTACT PICKER (SOLO NUMERO)
        // ============================================
        const btnImport = document.getElementById('btnImportarContactoModal');
        const isSupported = ('contacts' in navigator && 'ContactsManager' in window);

        if (btnImport && isSupported) {
            btnImport.classList.remove('hidden');
            btnImport.onclick = async () => {
                try {
                    const props = ['tel']; // Solo pedimos teléfono
                    const opts = { multiple: false };

                    const contacts = await navigator.contacts.select(props, opts);

                    if (contacts.length && contacts[0].tel && contacts[0].tel[0]) {
                        // Limpiar y setear valor
                        let number = contacts[0].tel[0];
                        input.value = number;

                        // Disparar evento para que corra la validación visual
                        input.dispatchEvent(new Event('input'));

                        if (window.mostrarToast) window.mostrarToast('Número importado', 'success');
                    }
                } catch (ex) {
                    // Ignorar error de cancelación de usuario
                }
            };
        }

        let primerNumeroIncorrecto = false;

        input.addEventListener('input', function (e) {
            let value = this.value; // Valor original sin limpiar aún

            // 1. Detección de pegado masivo (+51...)
            // Si tiene +51 lo limpiamos primero para evaluar el número real
            if (value.includes('+51')) {
                value = value.replace('+51', '');
            }

            // Limpiar no numéricos
            value = value.replace(/\D/g, '');

            // 2. Validación diferenciada (Paste vs Typing)
            // DETECTAR SI ES PEGADO para aplicar reglas estrictas
            if (e.inputType === 'insertFromPaste' || e.inputType === 'insertFromDrop') {
                // Reglas estrictas:
                // A) Longitud mayor a 9
                // B) No empieza con 9
                if (value.length > 9 || (value.length > 0 && !value.startsWith('9'))) {
                    this.value = ''; // Borrar todo

                    // Resetear estados visuales
                    container.classList.add('border-red-500');
                    container.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500', 'border-blue-300'); // Limpiar todos

                    hint.classList.add('text-red-600');
                    hint.classList.remove('text-slate-500', 'text-green-600');

                    let msg = 'Número inválido.';
                    if (value.length > 9) msg = 'Número demasiado largo. Solo 9 dígitos.';
                    else if (!value.startsWith('9')) msg = 'El número debe empezar con 9.';

                    if (hintText) hintText.innerHTML = `<i class="ph-bold ph-warning"></i> ${msg}`;

                    if (icon) {
                        icon.classList.add('opacity-50', 'text-slate-400');
                        icon.classList.remove('text-green-500', 'opacity-100');
                    }

                    // Toast solicitado
                    if (typeof mostrarToast === 'function') {
                        mostrarToast(msg, 'error');
                    }
                    return;
                }
            }

            // Si no es pegado (escritura normal), mantenemos la lógica suave de truncado
            if (value.length > 9) {
                value = value.substring(0, 9);
            }

            // Corrección automática de primer dígito
            if (primerNumeroIncorrecto && value.length > 1) {
                value = '9';
                primerNumeroIncorrecto = false;
            }

            // Validar primer dígito (Debe ser 9)
            if (value.length > 0 && !value.startsWith('9')) {
                primerNumeroIncorrecto = true;

                // Feedback Error Inmediato
                container.classList.add('border-red-500');
                container.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500', 'border-blue-300');

                hint.classList.add('text-red-600');
                hint.classList.remove('text-slate-500', 'text-green-600');

                if (hintText) hintText.innerHTML = '<i class="ph-bold ph-warning"></i> El primer número debe ser 9';

                if (icon) {
                    icon.classList.add('opacity-50', 'text-slate-400');
                    icon.classList.remove('text-green-500', 'opacity-100');
                }

                this.value = value;
                return;
            } else {
                primerNumeroIncorrecto = false;
            }

            // Formatear (999 999 999)
            if (value.length > 6) {
                value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + ' ' + value.substring(3);
            }

            this.value = value;

            // Validación Final
            const digitsOnly = value.replace(/\s/g, '');

            if (digitsOnly.length === 9) {
                // Válido
                container.classList.add('border-green-500');
                container.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500', 'border-blue-300');

                hint.classList.add('text-green-600');
                hint.classList.remove('text-slate-500', 'text-red-600');

                if (hintText) hintText.innerHTML = '<i class="ph-bold ph-check-circle"></i> Número correcto';

                if (icon) {
                    icon.classList.remove('opacity-50', 'text-slate-400');
                    icon.classList.add('text-green-500', 'opacity-100');
                }
            } else if (digitsOnly.length > 0) {
                // Incompleto
                container.classList.add('border-blue-300');
                container.classList.remove('border-slate-200', 'border-green-500', 'border-red-500');

                hint.classList.remove('text-green-600', 'text-red-600');
                hint.classList.add('text-slate-500');

                const faltan = 9 - digitsOnly.length;
                if (hintText) hintText.textContent = `Faltan ${faltan} dígitos`;

                if (icon) {
                    icon.classList.add('opacity-50', 'text-slate-400');
                    icon.classList.remove('text-green-500', 'opacity-100');
                }
            } else {
                // Vacío
                container.classList.remove('border-green-500', 'border-red-500', 'border-blue-300', 'border-blue-500');
                container.classList.add('border-slate-200');

                hint.classList.remove('text-green-600', 'text-red-600');
                hint.classList.add('text-slate-500');

                if (hintText) hintText.textContent = 'Ingresa 9 dígitos sin el +51';

                if (icon) {
                    icon.classList.add('opacity-50', 'text-slate-400');
                    icon.classList.remove('text-green-500', 'opacity-100');
                }
            }
        });
    }

    // Inicializar la validación del modal si existe en el DOM
    setupPhoneModalValidation();

});

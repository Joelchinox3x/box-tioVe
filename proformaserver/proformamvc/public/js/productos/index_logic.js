/**
 * Logic for Product Index (List) Page
 * Handles:
 * 1. Toast Notifications (from PHP session)
 * 2. Real-time Search
 * 3. Delete Confirmation Modal
 */

// ============================================
// 1. SISTEMA DE NOTIFICACIONES (PHP Session)
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    // Check for global notification variables injected from PHP
    if (window.PHP_NOTIFICATION) {
        mostrarNotificacion(
            window.PHP_NOTIFICATION.titulo,
            window.PHP_NOTIFICATION.mensaje,
            window.PHP_NOTIFICATION.tipo
        );
    }
});

// ============================================
// 2. BÚSQUEDA EN TIEMPO REAL
// ============================================
const searchInput = document.getElementById('headerSearchInput');
const clearBtn = document.getElementById('headerSearchClear');
let timeoutId;

// Theme Check (Defined in global window.COLOR_TEMA)
const esGreen = window.COLOR_TEMA === 'green';
const esOrange = window.COLOR_TEMA === 'orange';

searchInput?.addEventListener('input', function () {
    clearTimeout(timeoutId);
    const term = this.value.trim();

    // Toggle clear button
    if (this.value.length > 0) {
        clearBtn?.classList.remove('hidden');
    } else {
        clearBtn?.classList.add('hidden');
    }

    if (term.length === 0) {
        location.reload();
        return;
    }

    // Debounce search
    timeoutId = setTimeout(() => {
        fetch(`${window.SEARCH_URL}?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(productos => {
                const container = document.getElementById('productosList');

                if (productos.length === 0) {
                    container.innerHTML = `
            <div class="text-center py-12">
              <p class="text-slate-500">No se encontraron resultados para "${term}"</p>
            </div>
          `;
                    return;
                }

                container.innerHTML = productos.map(producto => {
                    const imagenes = producto.imagenes ? JSON.parse(producto.imagenes) : [];
                    const primeraImagen = imagenes.length > 0 ? imagenes[0] : null;
                    const protegido = producto.protegido || false;

                    // Asegurar que el ID sea numérico/string válido
                    const pId = producto.id;

                    // Colores dinámicos según tema
                    const borderTema = esGreen ?
                        'border-green-200 hover:border-green-300' :
                        'border-orange-200 hover:border-orange-300';

                    const borderImgTema = esGreen ?
                        'border-green-100 group-hover:border-green-300' :
                        'border-orange-100 group-hover:border-orange-300';

                    const bgGradTema = esGreen ?
                        'from-emerald-100 to-green-200' :
                        'from-orange-100 to-amber-200';

                    const textIconTema = esGreen ?
                        'text-green-600' :
                        'text-orange-600';

                    const textHoverTema = esGreen ?
                        'text-slate-600 group-hover:text-green-600' :
                        'text-slate-600 group-hover:text-orange-600';

                    const precioTema = esGreen ?
                        'text-green-600' :
                        'text-orange-600';

                    const btnTema = esGreen ?
                        'bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700' :
                        'bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700';

                    // URL Base para assets
                    const assetBase = window.ASSET_URL || '/';

                    return `
            <div class="group bg-white rounded-xl shadow-sm border ${protegido ? 'border-red-200 hover:border-red-300' : borderTema} hover:shadow-md transition-all overflow-hidden">
              <!-- Header -->
              <div class="p-3">
                <div class="flex items-center space-x-3">
                  <!-- Imagen -->
                  <div class="w-16 h-16 flex-shrink-0 rounded-lg border-2 transition-all
                      ${protegido
                            ? 'border-red-100 group-hover:border-red-300'
                            : borderImgTema}
                    ">
                    ${primeraImagen
                            ? `<img src="${assetBase}${primeraImagen}" class="w-full h-full rounded-lg object-cover">`
                            : `<div class="relative w-full h-full bg-gradient-to-br ${protegido ? 'from-red-100 to-rose-200' : bgGradTema} rounded-lg flex items-center justify-center">
                       <i class="ph-bold ph-package ${protegido ? 'text-red-600 group-hover:opacity-0' : textIconTema} text-xl transition-opacity duration-200"></i>

                        ${protegido ? `
                          <div class="absolute inset-0 flex items-center justify-center
                            bg-red-600/10 opacity-0 group-hover:opacity-100 transition-all rounded-lg">
                            <i class="ph-bold ph-lock text-red-600 text-2xl"></i>
                          </div>
                        ` : ''}
                      </div>`
                        }
                  </div>

                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold ${protegido ? 'text-slate-600 group-hover:text-red-600' : textHoverTema} text-sm mb-0.5 truncate">
                      ${producto.nombre}
                    </h3>
                    <div class="flex items-center gap-2 divide-x divide-slate-200 text-[11px] text-slate-500 mb-0.5 flex-wrap">
                      ${producto.modelo ? `
                        <span class="flex items-center gap-1">
                          <i class="ph ph-cube"></i>
                          <span>${producto.modelo}</span>
                        </span>
                      ` : ''}
                      
                      ${producto.sku ? `
                        <span class="flex items-center gap-1 pl-2"> <i class="ph ph-barcode"></i>
                          <span class="text-slate-400">${producto.sku}</span>
                        </span>
                      ` : ''}
                    </div>
                    <p class="text-lg font-bold ${protegido ? 'text-red-600' : precioTema}">
                      ${producto.moneda || 'PEN'} ${parseFloat(producto.precio).toFixed(2)}
                      ${protegido ? '<i class="ph-bold ph-lock text-red-400 group-hover:text-red-600 transition-colors duration-200 cursor-pointer"></i>' : ''}
                    </p>
                  </div>

                  <!-- Botón Cotizar -->
                  <div class="flex items-center">
                    <a href="${window.PROFORMA_URL}?producto_id=${pId}" class="flex items-center justify-center gap-1.5 px-3 py-2 ${protegido ? 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700' : btnTema} text-white rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-lg text-xs whitespace-nowrap">
                      <i class="ph-bold ph-file-text text-sm"></i>
                      <span>Cotizar</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          `;
                }).join('');
            });
    }, 300);
});

clearBtn?.addEventListener('click', function () {
    searchInput.value = '';
    this.classList.add('hidden');
    searchInput.focus();
    location.reload();
});

// ============================================
// 3. SISTEMA DE ELIMINACIÓN CON MODAL
// ============================================
let productoAEliminarData = null;

// Mostrar modal
function confirmarEliminar(id, nombre) {
    productoAEliminarData = {
        id,
        nombre
    };
    document.getElementById('deleteProductName').textContent = nombre;
    const modal = document.getElementById('confirmDeleteModal');
    modal.classList.remove('hidden');

    if ('vibrate' in navigator) {
        navigator.vibrate(50);
    }
}

// Cerrar modal
function cerrarModalEliminar() {
    const modal = document.getElementById('confirmDeleteModal');
    modal.classList.add('hidden');
    productoAEliminarData = null;
}

// Ejecutar
function ejecutarEliminacion() {
    if (!productoAEliminarData) return;
    window.location.href = `${window.DELETE_URL_BASE}/${productoAEliminarData.id}`;
}

// Close on ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('confirmDeleteModal');
        if (!modal.classList.contains('hidden')) {
            cerrarModalEliminar();
        }
    }
});

// ============================================
// 4. OTROS
// ============================================
function mostrarToastProtegido() {
    mostrarToast('Producto protegido', 'warning');
}

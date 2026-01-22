/**
 * Módulo de Items
 * Maneja la creación, eliminación y gestión de items en la proforma.
 */

// Variables globales del módulo
window.itemCounter = 0;
window.monedaSeleccionada = null; // null, 'PEN', o 'USD'

window.agregarItemDesdeProducto = function (producto) {
  // Si es el primer producto, establecer la moneda
  if (window.monedaSeleccionada === null) {
    window.monedaSeleccionada = producto.moneda || 'PEN';

    // Función de calculations.js
    if (window.actualizarMoneda) window.actualizarMoneda(window.monedaSeleccionada);

    // Función de search.js (será creada)
    if (window.filtrarProductosPorMoneda) window.filtrarProductosPorMoneda(window.monedaSeleccionada);
  }

  // Verificar si el producto ya existe en los items
  const itemExistente = window.buscarItemPorProductoId(producto.id);

  if (itemExistente) {
    // Si existe, incrementar la cantidad
    window.incrementarCantidadItem(itemExistente);
    window.mostrarNotificacion(`${producto.nombre}`, 'Cantidad incrementada +1', 'info');
  } else {
    // Si no existe, agregar nuevo item
    window.itemCounter++;
    const html = window.crearItemHTML(window.itemCounter, producto.nombre, producto.precio, producto.id);
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    window.mostrarNotificacion(`${producto.nombre}`, 'Agregado a la proforma', 'success');
  }

  if (window.calcularTotales) window.calcularTotales();
};

window.agregarItemManual = function () {
  window.itemCounter++;
  const html = window.crearItemHTML(window.itemCounter, '', '0.00', 0);
  const container = document.getElementById('itemsContainer');
  container.insertAdjacentHTML('beforeend', html);

  // Scroll automático hacia el nuevo item
  const newItem = container.lastElementChild;
  if (newItem) {
    setTimeout(() => {
      newItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
  }
};

window.crearItemHTML = function (idx, nombre = '', precio = '0.00', productoId = 0) {
  const simboloMoneda = window.monedaSeleccionada === 'USD' ? '$' : 'S/.';
  const esItemManual = productoId === 0;

  // Opciones diferentes según si es item manual o de producto
  let opcionesHTML = '';

  if (esItemManual) {
    // Item manual: Solo opción de foto, desactivada por defecto
    opcionesHTML = `
      <div class="w-full space-y-2">
        <!-- Checkbox y file input en la misma línea -->
        <div class="flex items-center gap-2">
          <label class="flex items-center gap-1.5 cursor-pointer group/check flex-shrink-0">
            <div class="relative flex items-center">
              <input type="checkbox" name="incluir_fotos[]" value="1" id="check_img_${idx}" onchange="toggleImageUpload(${idx})" class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-indigo-500 checked:bg-indigo-500">
              <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                <i class="ph-bold ph-check text-[9px]"></i>
              </span>
            </div>
            <span class="text-[11px] font-bold text-slate-500 group-hover/check:text-indigo-600 transition whitespace-nowrap">Incluir Fotos</span>
          </label>

          <div id="upload_container_${idx}" class="hidden flex-1">
            <input type="file" name="items[${idx}][imagenes][]" id="imagenesInput_${idx}" accept="image/*" multiple onchange="handleImagePreview(event, ${idx})" class="w-full px-3 py-2 border-2 border-indigo-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white transition-all duration-300 hover:border-indigo-300">
          </div>
        </div>

        <!-- Contenedor de previews -->
        <div id="imagePreview_${idx}" class="grid grid-cols-2 gap-2"></div>

        <p id="imageText_${idx}" class="text-xs text-slate-500 text-center italic hidden">Máximo 2 imágenes</p>
      </div>
    `;
  } else {
    // Item de producto: Todas las opciones activadas por defecto
    opcionesHTML = `
      <label class="flex items-center gap-1.5 cursor-pointer group/check">
        <div class="relative flex items-center">
          <input type="checkbox" name="incluir_ficha[]" value="1" checked class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-blue-500 checked:bg-blue-500">
          <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <i class="ph-bold ph-check text-[9px]"></i>
          </span>
        </div>
        <span class="text-[11px] font-bold text-slate-500 group-hover/check:text-blue-600 transition">Incluir Ficha</span>
      </label>

      <label class="flex items-center gap-1.5 cursor-pointer group/check">
        <div class="relative flex items-center">
          <input type="checkbox" name="incluir_fotos[]" value="1" checked class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-indigo-500 checked:bg-indigo-500">
          <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <i class="ph-bold ph-check text-[9px]"></i>
          </span>
        </div>
        <span class="text-[11px] font-bold text-slate-500 group-hover/check:text-indigo-600 transition">Incluir Fotos</span>
      </label>

      <label class="flex items-center gap-1.5 cursor-pointer group/check bg-white p-2 rounded-lg border-2 border-slate-200 hover:border-purple-300 transition-all duration-300">
        <div class="relative flex items-center">
          <input type="checkbox" name="incluir_galeria[]" value="1" class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-slate-300 transition-all checked:border-purple-500 checked:bg-purple-500">
          <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <i class="ph-bold ph-check text-[9px]"></i>
          </span>
        </div>
        <span class="text-[11px] font-bold text-slate-500 group-hover/check:text-purple-600 transition block">Galería Extra</span>
      </label>
    `;
  }

  // Estilos diferenciados (Manual: Orange / Producto: White-Blue)
  const cardClasses = esItemManual
    ? 'bg-orange-50 p-2 rounded-xl border border-orange-200 shadow-sm relative animate-fade-in-up group transition-all duration-300 hover:shadow-md hover:border-orange-300 ring-1 ring-orange-500/10'
    : 'bg-white p-2 rounded-xl border border-slate-200 shadow-sm relative animate-fade-in-up group transition-all duration-300 hover:shadow-md hover:border-blue-300 ring-1 ring-black/5';

  return `
    <div class="item-row ${cardClasses}">

      <button type="button" onclick="eliminarItem(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:scale-110 hover:bg-red-600 transition-all duration-200 z-10">
        <i class="ph-bold ph-x text-xs"></i>
      </button>

      <input type="hidden" name="producto_id[]" value="${productoId}">

      <div class="mb-1.5">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wide ml-0.5 mb-0.5 block">Producto</label>
        <input type="text" name="descripcion[]" value="${nombre}" required class="w-full bg-white border-2 border-slate-200 rounded-lg p-1.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-all duration-300 hover:border-slate-300" placeholder="Nombre del producto">
      </div>

      <div class="flex gap-3">
        <div class="w-24">
          <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wide ml-0.5 mb-0.5 block">Cant.</label>
          <input type="number" name="cantidad[]" value="1" min="1" required oninput="calcularTotales()" class="item-cantidad w-full bg-white border-2 border-slate-200 rounded-lg p-1.5 text-center text-sm font-bold text-slate-800 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-all duration-300 hover:border-slate-300">
        </div>
        <div class="flex-1">
          <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wide ml-0.5 mb-0.5 block">Precio Unit.</label>
          <div class="relative">
            <span class="absolute left-3 top-2 text-slate-500 text-xs font-bold">${simboloMoneda}</span>
            <input type="number" step="0.01" name="precio_unitario[]" value="${precio}" min="0" required oninput="calcularTotales()" class="item-precio w-full bg-white border-2 border-slate-200 rounded-lg p-1.5 pl-9 text-right text-sm font-bold text-slate-800 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition-all duration-300 hover:border-slate-300">
          </div>
        </div>
      </div>

      <!-- Campo hidden para subtotal del item -->
      <input type="hidden" name="item_subtotal[]" value="0" class="item-subtotal">

      <div class="${esItemManual ? 'pt-1 mt-1 border-t border-slate-200' : 'flex gap-2 pt-1 mt-1 border-t border-slate-200'}">
        ${opcionesHTML}
      </div>
    </div>
  `;
};

window.eliminarItem = function (btn) {
  btn.closest('.item-row').remove();
  if (window.calcularTotales) window.calcularTotales();

  // Si no quedan items, resetear la moneda
  if (document.querySelectorAll('.item-row').length === 0) {
    window.monedaSeleccionada = null;
    if (window.actualizarMoneda) window.actualizarMoneda('PEN'); // Volver a PEN por defecto
    if (window.mostrarTodosLosProductos) window.mostrarTodosLosProductos();
  }
};

window.buscarItemPorProductoId = function (productoId) {
  // Si productoId es 0, es un item manual, no buscar duplicados
  if (productoId === 0) return null;

  const items = document.querySelectorAll('.item-row');
  for (const item of items) {
    const hiddenInput = item.querySelector('input[name="producto_id[]"]');
    if (hiddenInput && parseInt(hiddenInput.value) === parseInt(productoId)) {
      return item;
    }
  }
  return null;
};

window.incrementarCantidadItem = function (itemElement) {
  const cantidadInput = itemElement.querySelector('.item-cantidad');
  const cantidadActual = parseInt(cantidadInput.value) || 1;
  cantidadInput.value = cantidadActual + 1;

  // Animar la tarjeta para indicar que se incrementó
  itemElement.classList.remove('animate-pulse-once');
  void itemElement.offsetWidth; // Forzar reflow
  itemElement.classList.add('animate-pulse-once');

  // Scroll suave hacia el item
  itemElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

// Mostrar/Ocultar carga de imágenes
window.toggleImageUpload = function (idx) {
  const container = document.getElementById(`upload_container_${idx}`);
  const checkbox = document.getElementById(`check_img_${idx}`);
  const preview = document.getElementById(`imagePreview_${idx}`);
  const text = document.getElementById(`imageText_${idx}`);

  if (checkbox.checked) {
    container.classList.remove('hidden');
    text.classList.remove('hidden');
  } else {
    container.classList.add('hidden');
    preview.innerHTML = '';
    text.classList.add('hidden');
    // Limpiar input
    const input = document.getElementById(`imagenesInput_${idx}`);
    if (input) input.value = '';
  }
};

window.handleImagePreview = async function (e, idx) {
  const MAX_FOTOS = 2;
  const preview = document.getElementById(`imagePreview_${idx}`);
  const input = e.target;
  let files = Array.from(input.files);

  // Validar cantidad máxima
  if (files.length > MAX_FOTOS) {
    window.mostrarNotificacion('Límite excedido', `Máximo ${MAX_FOTOS} imágenes permitidas`, 'error');
    input.value = '';
    preview.innerHTML = '';
    return;
  }

  // Mostrar indicador de carga en el preview
  preview.innerHTML = '<div class="col-span-2 text-center py-2 text-xs text-blue-600 font-bold animate-pulse"><i class="ph-bold ph-spinner animate-spin mr-1"></i>Optimizando...</div>';

  const newDataTransfer = new DataTransfer();
  const finalFiles = [];

  try {
    for (let i = 0; i < files.length; i++) {
      try {
        // COMPRIMIR A WEBP
        const compressedFile = await ImageOptimizer.compress(files[i], 0.8, 1200);
        newDataTransfer.items.add(compressedFile);
        finalFiles.push(compressedFile);
        // window.mostrarNotificacion('Optimizado', `${((files[i].size/1024).toFixed(0))}KB ➜ ${(compressedFile.size/1024).toFixed(0)}KB`, 'success');
      } catch (err) {
        console.error("Error optimizando:", err);
        newDataTransfer.items.add(files[i]); // Fallback original
        finalFiles.push(files[i]);
      }
    }

    // Reemplazar archivos en el input
    input.files = newDataTransfer.files;

  } catch (error) {
    console.error("Error general:", error);
  }

  // Renderizar Preview
  preview.innerHTML = '';

  finalFiles.forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function (event) {
      const div = document.createElement('div');
      div.className = 'aspect-square rounded-xl overflow-hidden border-2 border-indigo-300 relative group shadow-md hover:shadow-lg transition-all duration-300';
      div.innerHTML = `
        <img src="${event.target.result}" class="w-full h-full object-cover">
        <div class="absolute bottom-2 left-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-[9px] px-2 py-1 rounded-lg font-bold shadow-lg">
          ${index + 1}
        </div>
      `;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
};

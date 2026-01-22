<?php
$title = 'Nuevo Producto';
$back_url = url('/inventario');
$section = 'inventario';

include __DIR__ . '/../partials/load_header.php';
?>
<!-- Utilidad de compresión -->
<script src="<?= asset('js/utils/image-optimizer.js') ?>"></script>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Modal vista previa imagen -->
<div id="imagePreviewModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" onclick="closeImagePreview()">
  <img id="previewModalImage" src="" class="max-w-full max-h-full rounded-2xl shadow-2xl">
</div>

<!-- Contenido principal -->
<main class="max-w-md mx-auto pt-1 px-4 pb-24">

  <form id="productForm" action="<?= url("/inventario/store") ?>" method="POST" enctype="multipart/form-data" class="space-y-4 animate-fade-in-up" novalidate>

    <!-- Información General -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
      <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
        <i class="ph-fill ph-info text-blue-500"></i> Información General
      </h2>

      <div class="space-y-3">
        <!-- Nombre del Producto -->
        <div>
          <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block flex items-center gap-1">
            Nombre del Producto
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              type="text"
              id="nombre"
              name="nombre"
              required
              class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-10 text-sm text-slate-700 focus:outline-none font-medium transition-all duration-300"
              placeholder="Ej: RetroExcavadora"
            >
            <div class="absolute right-3 top-1/2 -translate-y-1/2 transition-all">
              <i class="ph-bold ph-check-circle text-green-500 text-xl hidden" id="nombre-check"></i>
              <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="nombre-error"></i>
            </div>
          </div>
        </div>

        <!-- Moneda y Precio -->
        <div class="grid grid-cols-3 gap-3">
          <div class="col-span-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Moneda</label>
            <div class="relative">
              <select name="moneda" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 appearance-none focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-bold">
                <option value="PEN">S/.</option>
                <option value="USD">$</option>
              </select>
              <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
            </div>
          </div>
          <div class="col-span-2">
            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block flex items-center gap-1">
              Precio
              <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                type="number"
                id="precio"
                step="0.01"
                name="precio"
                required
                min="0.01"
                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-10 text-sm text-slate-700 focus:outline-none font-bold text-right transition-all duration-300"
                placeholder="0.00"
              >
              <div class="absolute right-3 top-1/2 -translate-y-1/2 transition-all">
                <i class="ph-bold ph-check-circle text-green-500 text-xl hidden" id="precio-check"></i>
                <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="precio-error"></i>
              </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 ml-1" id="precio-hint">Debe ser mayor a 0</p>
          </div>
        </div>

        <!-- Modelo y SKU -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Modelo</label>
            <input type="text" name="modelo" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium" placeholder="Ej: 320D">
          </div>
          <div>
            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">SKU / Código</label>
            <input type="text" name="sku" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium" placeholder="Ej: PROD-001">
          </div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Descripción</label>
          <textarea name="descripcion" rows="3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium resize-none"></textarea>
        </div>
      </div>
    </div>

    <!-- Imágenes -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
      <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
        <i class="ph-fill ph-image text-blue-500"></i> Imágenes (Max 4)
      </h2>

      <div id="upload-progress" class="hidden mb-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold text-slate-600">Preparando imágenes...</span>
          <span id="upload-percent" class="text-xs font-bold text-blue-600">0%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
          <div id="upload-bar" class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
      </div>

      <div class="relative border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-4 text-center hover:bg-slate-100 transition cursor-pointer group mb-3">
        <input type="file" id="input-fotos" name="imagenes[]" multiple accept="image/*"
          class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
          onchange="previewImages(this)">

        <div class="text-slate-400 group-hover:text-blue-500 transition">
          <i class="ph-duotone ph-upload-simple text-2xl mb-1"></i>
          <p class="text-xs font-bold">Toca para subir fotos</p>
        </div>
      </div>

      <div id="preview-container" class="grid grid-cols-4 gap-2"></div>
    </div>

    <!-- Especificaciones Técnicas -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
          <i class="ph-fill ph-list-dashes text-blue-500"></i> Ficha Técnica
        </h2>
        <div class="flex gap-2">
          <button type="button" onclick="document.getElementById('modalParser').classList.remove('hidden')" class="text-gray-600 text-xs font-bold hover:bg-gray-100 px-3 py-1.5 rounded-lg transition border border-gray-200 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Pegar Tabla
          </button>
          <button type="button" onclick="agregarFila()" class="text-[10px] bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 transition inline-flex items-center gap-1">
            <i class="ph-bold ph-plus"></i> Añadir
          </button>
        </div>
      </div>

      <div id="contenedor-specs" class="space-y-2"></div>

      <p class="text-[12px] text-slate-400 mt-3 text-center italic">Ctrl+S: Guardar | Ctrl+Enter: Guardar y crear otro | Esc: Cancelar</p>
    </div>

  </form>

</main>

<!-- Botones de acción -->
<div class="fixed bottom-20 left-0 right-0 z-20 animate-fade-in-up px-4" style="animation-delay: 0.4s">
  <div class="flex gap-3 bg-white/95 backdrop-blur-xl p-3 rounded-2xl shadow-2xl border-2 border-slate-200/50 ring-4 ring-slate-100/50 max-w-lg mx-auto">
    <button
      type="button"
      id="cancelBtn"
       class="flex-1 px-5 py-3 bg-slate-200 border-2 border-slate-400 text-slate-700 rounded-xl font-semibold text-center hover:bg-slate-400 hover:border-slate-600 transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2 text-sm"
    >
      <i class="ph-bold ph-x"></i>
      <span>Cancelar</span>
    </button>
    <button
      type="submit"
      form="productForm"
      id="submitBtn"
      class="flex-1 px-5 py-3 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
      <i class="ph-bold ph-check relative z-10" id="submitIcon"></i>
      <span class="relative z-10" id="submitText">Guardar</span>
    </button>
  </div>
</div>

<!-- Modal Parser -->
<div id="modalParser" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-2">Pegar Datos Técnicos</h3>
    <p class="text-xs text-gray-500 mb-3">Copia tu tabla (Excel, PDF o Web) y pégala aquí.</p>

    <textarea id="textoPegado" class="w-full h-40 border border-gray-300 rounded-xl p-3 text-xs focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Modelo    HQ-YL1000
Motor     Honda GX160"></textarea>

    <div class="flex justify-end gap-3">
      <button type="button" onclick="document.getElementById('modalParser').classList.add('hidden')" class="text-gray-500 font-semibold text-sm hover:bg-gray-100 px-4 py-2 rounded-lg">Cancelar</button>
      <button type="button" onclick="procesarPegado()" class="bg-blue-600 text-white font-bold text-sm px-6 py-2 rounded-lg hover:bg-blue-700">Procesar</button>
    </div>
  </div>
</div>

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let contador = 0;
let draggedElement = null;
const dataTransfer = new DataTransfer();

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  agregarFila('', '');
  initKeyboardShortcuts();
  initValidation();
});

// ============================================
// SISTEMA DE VALIDACIÓN MODERNO
// ============================================
const validaciones = {
  nombre: { validado: false, requerido: true },
  precio: { validado: false, requerido: true }
};

function initValidation() {
  // Nombre
  const nombreInput = document.getElementById('nombre');
  const nombreCheck = document.getElementById('nombre-check');
  const nombreError = document.getElementById('nombre-error');

  nombreInput?.addEventListener('input', function() {
    const value = this.value.trim();

    if (value.length === 0) {
      // Vacío
      nombreCheck.classList.add('hidden');
      nombreError.classList.add('hidden');
      this.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4');
      this.classList.add('border-slate-200');
      validaciones.nombre.validado = false;
    } else if (value.length >= 3) {
      // Válido
      nombreCheck.classList.remove('hidden');
      nombreError.classList.add('hidden');
      this.classList.add('border-green-500');
      this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
      validaciones.nombre.validado = true;
    } else {
      // Inválido
      nombreCheck.classList.add('hidden');
      nombreError.classList.remove('hidden');
      this.classList.add('border-red-500');
      this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
      validaciones.nombre.validado = false;
    }
  });

  nombreInput?.addEventListener('focus', function() {
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

  nombreInput?.addEventListener('blur', function() {
    const value = this.value.trim();
    this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
    if (value.length === 0) {
      this.classList.remove('border-blue-500');
      this.classList.add('border-slate-200');
    }
  });

  // Precio
  const precioInput = document.getElementById('precio');
  const precioCheck = document.getElementById('precio-check');
  const precioError = document.getElementById('precio-error');
  const precioHint = document.getElementById('precio-hint');

  precioInput?.addEventListener('input', function() {
    const value = parseFloat(this.value);

    if (this.value.trim() === '') {
      // Vacío
      precioCheck.classList.add('hidden');
      precioError.classList.add('hidden');
      this.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4');
      this.classList.add('border-slate-200');
      precioHint.classList.remove('text-green-600', 'text-red-600');
      precioHint.classList.add('text-slate-500');
      precioHint.textContent = 'Debe ser mayor a 0';
      validaciones.precio.validado = false;
    } else if (value > 0) {
      // Válido
      precioCheck.classList.remove('hidden');
      precioError.classList.add('hidden');
      this.classList.add('border-green-500');
      this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
      precioHint.classList.add('text-green-600');
      precioHint.classList.remove('text-slate-500', 'text-red-600');
      precioHint.textContent = 'Precio válido ✓';
      validaciones.precio.validado = true;
    } else {
      // Inválido
      precioCheck.classList.add('hidden');
      precioError.classList.remove('hidden');
      this.classList.add('border-red-500');
      this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
      precioHint.classList.add('text-red-600');
      precioHint.classList.remove('text-slate-500', 'text-green-600');
      precioHint.textContent = 'Debe ser mayor a 0';
      validaciones.precio.validado = false;
    }
  });

  precioInput?.addEventListener('focus', function() {
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

  precioInput?.addEventListener('blur', function() {
    const value = this.value.trim();
    this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');
    if (value.length === 0) {
      this.classList.remove('border-blue-500');
      this.classList.add('border-slate-200');
    }
  });
}

// ============================================
// ATAJOS DE TECLADO
// ============================================
function initKeyboardShortcuts() {
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      document.getElementById('productForm').requestSubmit();
    }

    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'create_another';
      input.value = '1';
      document.getElementById('productForm').appendChild(input);
      document.getElementById('productForm').requestSubmit();
    }

    if (e.key === 'Escape') {
      if (!document.getElementById('modalParser').classList.contains('hidden')) {
        document.getElementById('modalParser').classList.add('hidden');
      } else if (!document.getElementById('imagePreviewModal').classList.contains('hidden')) {
        closeImagePreview();
      } else {
        document.getElementById('cancelBtn').click();
      }
    }
  });

  document.getElementById('cancelBtn').addEventListener('click', () => {
      mostrarToast('Cancelando y regresando al inventario...', 'info');
      
      // Damos un pequeño margen para que el usuario vea el Toast antes de irse
      setTimeout(() => {
          window.location.href = '<?= url("/inventario") ?>';
      }, 800); 
  });
}

// ============================================
// VALIDACIÓN Y ENVÍO DEL FORMULARIO
// ============================================
document.getElementById('productForm').addEventListener('submit', (e) => {
  e.preventDefault();

  const nombreInput = document.getElementById('nombre');
  const precioInput = document.getElementById('precio');
  let hasErrors = false;

  // Validar nombre
  if (!nombreInput.value.trim() || nombreInput.value.trim().length < 3) {
    hasErrors = true;
    nombreInput.classList.add('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
    nombreInput.classList.remove('border-slate-200');
    mostrarToast('El nombre debe tener al menos 3 caracteres', 'error');
    nombreInput.scrollIntoView({ behavior: 'smooth', block: 'center' });

    nombreInput.addEventListener('input', function() {
      nombreInput.classList.remove('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
      nombreInput.classList.add('border-slate-200');
    }, { once: true });
  }

  // Validar precio
  if (!precioInput.value.trim() || parseFloat(precioInput.value) <= 0) {
    hasErrors = true;
    precioInput.classList.add('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
    precioInput.classList.remove('border-slate-200');
    mostrarToast('El precio debe ser mayor a 0', 'error');

    if (!nombreInput.value.trim()) {
      precioInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    precioInput.addEventListener('input', function() {
      precioInput.classList.remove('border-red-500', 'ring-2', 'ring-red-200', 'bg-red-50');
      precioInput.classList.add('border-slate-200');
    }, { once: true });
  }

  if (hasErrors) {
    return;
  }

  // Mostrar loading
  const submitBtn = document.getElementById('submitBtn');
  const submitIcon = document.getElementById('submitIcon');
  const submitText = document.getElementById('submitText');

  submitBtn.disabled = true;
  submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
  submitIcon.className = 'ph-bold ph-circle-notch animate-spin relative z-10';
  submitText.textContent = 'Guardando...';

  if (dataTransfer.files.length > 0) {
    simulateUploadProgress(() => {
      e.target.submit();
    });
  } else {
    e.target.submit();
  }
});

function simulateUploadProgress(callback) {
  const progressEl = document.getElementById('upload-progress');
  const barEl = document.getElementById('upload-bar');
  const percentEl = document.getElementById('upload-percent');

  progressEl.classList.remove('hidden');

  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.random() * 30;
    if (progress > 100) progress = 100;

    barEl.style.width = progress + '%';
    percentEl.textContent = Math.round(progress) + '%';

    if (progress >= 100) {
      clearInterval(interval);
      setTimeout(callback, 300);
    }
  }, 200);
}

// ============================================
// SISTEMA DE NOTIFICACIONES
// ============================================
function mostrarToast(mensaje, tipo = 'success') {
  const titulos = {
    success: 'Éxito',
    error: 'Error',
    info: 'Información',
    warning: 'Advertencia'
  };

  mostrarNotificacion(titulos[tipo] || titulos.info, mensaje, tipo, 3000);
}

// ============================================
// DRAG & DROP IMÁGENES
// ============================================
function renderPreviews(container, input) {
  container.innerHTML = '';

  Array.from(dataTransfer.files).forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function(e) {
      const div = document.createElement('div');
      div.className = "relative aspect-square rounded-xl overflow-hidden border-2 border-slate-200 shadow-sm animate-fade-in-up group cursor-move";
      div.draggable = true;
      div.dataset.index = index;

      div.addEventListener('dragstart', handleDragStart);
      div.addEventListener('dragover', handleDragOver);
      div.addEventListener('drop', handleDrop);
      div.addEventListener('dragend', handleDragEnd);

      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = "w-full h-full object-cover cursor-pointer";
      img.onclick = () => showImagePreview(e.target.result);

      const badge = document.createElement('div');
      badge.className = "absolute top-1 left-1 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full";
      badge.textContent = index + 1;

      const removeBtn = document.createElement('button');
      removeBtn.innerHTML = '<i class="ph-bold ph-x"></i>';
      removeBtn.className = "absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity text-xs";
      removeBtn.onclick = function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        removeFile(index, input, container);
      };

      div.appendChild(img);
      div.appendChild(badge);
      div.appendChild(removeBtn);
      container.appendChild(div);
    }
    reader.readAsDataURL(file);
  });
}

function handleDragStart(e) {
  draggedElement = e.target;
  e.target.style.opacity = '0.4';
}

function handleDragOver(e) {
  if (e.preventDefault) e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  return false;
}

function handleDrop(e) {
  if (e.stopPropagation) e.stopPropagation();

  if (draggedElement !== e.currentTarget) {
    const draggedIndex = parseInt(draggedElement.dataset.index);
    const targetIndex = parseInt(e.currentTarget.dataset.index);

    reorderFiles(draggedIndex, targetIndex);
  }

  return false;
}

function handleDragEnd(e) {
  e.target.style.opacity = '1';
}

function reorderFiles(fromIndex, toIndex) {
  const newDataTransfer = new DataTransfer();
  const files = Array.from(dataTransfer.files);

  const [movedFile] = files.splice(fromIndex, 1);
  files.splice(toIndex, 0, movedFile);

  dataTransfer.items.clear();
  files.forEach(file => {
    dataTransfer.items.add(file);
  });

  document.getElementById('input-fotos').files = dataTransfer.files;
  renderPreviews(document.getElementById('preview-container'), document.getElementById('input-fotos'));
}

// ============================================
// VISTA PREVIA
// ============================================
function showImagePreview(src) {
  document.getElementById('previewModalImage').src = src;
  document.getElementById('imagePreviewModal').classList.remove('hidden');
}

function closeImagePreview() {
  document.getElementById('imagePreviewModal').classList.add('hidden');
}

// ============================================
// FUNCIONES DE IMÁGENES
// ============================================
// ============================================
// FUNCIONES DE IMÁGENES
// ============================================
async function previewImages(input) {
  const container = document.getElementById('preview-container');
  const maxFiles = 4;
  const newFiles = input.files;

  if (dataTransfer.files.length + newFiles.length > maxFiles) {
    mostrarToast(`Solo puedes subir un máximo de ${maxFiles} fotos`, 'error');
    input.files = dataTransfer.files;
    return;
  }

  // Mostrar indicador de carga
  let loadingIndicator = document.getElementById('optimization-loading');
  if (!loadingIndicator) {
    loadingIndicator = document.createElement('div');
    loadingIndicator.id = 'optimization-loading';
    loadingIndicator.className = 'col-span-4 text-center py-4 text-sm text-blue-600 font-bold animate-pulse';
    loadingIndicator.innerHTML = '<i class="ph-bold ph-spinner animate-spin mr-2"></i>Optimizando imágenes...';
    container.appendChild(loadingIndicator);
  }

  try {
    for (let i = 0; i < newFiles.length; i++) {
        let fileExists = false;
        // Check duplicados por nombre (ignorando extensión si ya fue convertido)
        const baseName = newFiles[i].name.replace(/\.[^/.]+$/, "");
        
        for (let j = 0; j < dataTransfer.files.length; j++) {
            const dtBaseName = dataTransfer.files[j].name.replace(/\.[^/.]+$/, "");
            if (dtBaseName === baseName) {
                fileExists = true;
                break;
            }
        }

        if (!fileExists) {
            // COMPRESIÓN CLIENT-SIDE (WebP)
            try {
                const compressedFile = await ImageOptimizer.compress(newFiles[i], 0.8, 1200);
                dataTransfer.items.add(compressedFile);
                mostrarToast(`Imagen optimizada: ${((newFiles[i].size/1024).toFixed(0))}KB ➜ ${(compressedFile.size/1024).toFixed(0)}KB`, 'success');
            } catch (err) {
                console.error("Error optimizando:", err);
                mostrarToast(`Error al optimizar ${newFiles[i].name}`, 'error');
                // Fallback: agregar original si falla
                dataTransfer.items.add(newFiles[i]);
            }
        }
    }
  } finally {
    // Remover indicador
    if (loadingIndicator && loadingIndicator.parentNode) {
        loadingIndicator.parentNode.removeChild(loadingIndicator);
    }
  }

  input.files = dataTransfer.files;
  renderPreviews(container, input);
}

function removeFile(index, input, container) {
  const newDataTransfer = new DataTransfer();
  const currentFiles = dataTransfer.files;

  for (let i = 0; i < currentFiles.length; i++) {
    if (i !== index) {
      newDataTransfer.items.add(currentFiles[i]);
    }
  }

  dataTransfer.items.clear();
  for (let i = 0; i < newDataTransfer.files.length; i++) {
    dataTransfer.items.add(newDataTransfer.files[i]);
  }
  input.files = dataTransfer.files;

  renderPreviews(container, input);
}

// ============================================
// ESPECIFICACIONES TÉCNICAS
// ============================================
function agregarFila(attr = '', val = '') {
  const c = document.getElementById('contenedor-specs');
  const div = document.createElement('div');
  div.className = 'flex gap-2 items-center group animate-fade-in-up';
  div.innerHTML = `
    <div class="relative w-1/2">
      <input type="text" name="specs[${contador}][attr]" value="${attr}" placeholder="Atributo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-blue-200 outline-none">
    </div>
    <div class="relative w-1/2">
      <input type="text" name="specs[${contador}][val]" value="${val}" placeholder="Valor" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-blue-200 outline-none">
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition">
      <i class="ph-bold ph-x text-xs"></i>
    </button>
  `;
  c.appendChild(div);
  contador++;
}

function procesarPegado() {
  const texto = document.getElementById('textoPegado').value;
  if(!texto.trim()) return;

  const filas = texto.split('\n');
  const contenedor = document.getElementById('contenedor-specs');

  contenedor.innerHTML = '';

  filas.forEach(fila => {
    let cols = fila.split(/\t+/).map(c => c.trim()).filter(c => c !== '');

    if(cols.length < 2) {
      cols = fila.split(/\s{2,}/).map(c => c.trim()).filter(c => c !== '');
    }

    if (cols.length === 2) {
      agregarFila(cols[0], cols[1]);
    }
    else if (cols.length >= 4) {
      agregarFila(cols[0], cols[1]);
      agregarFila(cols[2], cols[3]);
    }
    else if(cols.length === 3) {
      agregarFila(cols[0], cols[1] + ' ' + cols[2]);
    }
  });

  document.getElementById('modalParser').classList.add('hidden');
  document.getElementById('textoPegado').value = '';
}

// ============================================
// ANIMACIONES
// ============================================
const style = document.createElement('style');
style.textContent = `
  @keyframes fade-in-up {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out forwards;
    opacity: 0;
  }
`;
document.head.appendChild(style);
</script>

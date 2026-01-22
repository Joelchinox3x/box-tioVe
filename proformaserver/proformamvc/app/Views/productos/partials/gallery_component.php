<!-- Imágenes actuales (Solo visible si hay imágenes) -->
<?php if (!empty($producto['imagenes'])): ?>
  <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
    <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
      <i class="ph-fill ph-images text-blue-500"></i> Imágenes Actuales
    </h2>

    <!-- Imágenes Actuales REFACTORIZADO para Star/Lock -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3" id="current-images-grid">
      <?php foreach ($producto['imagenes'] as $index => $imagen): ?>
        <div class="relative group aspect-square bg-slate-50 rounded-2xl">
          
          <!-- Wrapper de la Imagen (Con overflow hidden para los bordes redondeados) -->
          <div class="absolute inset-0 rounded-2xl overflow-hidden border-2 border-slate-200 shadow-sm transition-all group-hover:shadow-md">
              <!-- Input oculto para el orden -->
              <input type="hidden" name="imagenes_viejas[]" value="<?= htmlspecialchars($imagen) ?>">

              <!-- Imagen -->
              <img src="<?= asset(htmlspecialchars($imagen)) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 cursor-pointer" onclick="showImagePreview('<?= asset(htmlspecialchars($imagen)) ?>')">
              
              <!-- Overlay degradado -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
          </div>

          <?php if (!($producto['protegido'] ?? false)): ?>
              <!-- Botones Star/Lock (Dentro del wrapper de imagen) -->
              <button type="button" class="btn-star absolute top-2 left-2 z-20 bg-white/90 text-amber-400 p-1.5 rounded-lg text-xs hover:bg-white hover:text-amber-500 shadow-sm ring-1 ring-black/5" 
                      onclick="priorizarImagen(this)" title="Mover al inicio libre">
                <i class="ph-fill ph-star"></i>
              </button>

              <button type="button" class="btn-lock hidden absolute top-2 left-2 z-20 bg-white/90 text-slate-400 p-1.5 rounded-lg transition-all text-xs hover:bg-white hover:text-red-500 shadow-sm ring-1 ring-black/5" 
                      onclick="toggleLock(this)" title="Bloquear posición">
                <i class="ph-bold ph-lock-open"></i>
              </button>
              
              <!-- Botón Eliminar (FUERA del wrapper para sobresalir) -->
              <button type="button" 
                      class="absolute -top-2 -right-2 z-30 bg-red-500 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-md ring-2 ring-white hover:bg-red-600 hover:scale-110 transition-all duration-200"
                      onclick="eliminarImagenExistente(this)"
                      title="Eliminar imagen">
                <i class="ph-bold ph-trash text-sm"></i>
              </button>
          <?php endif; ?>

        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-[10px] text-slate-400 mt-2 text-center flex items-center justify-center gap-1">
        <i class="ph-bold ph-info"></i>
        Las imágenes bloqueadas <i class="ph-bold ph-lock-key text-xs"></i> mantienen su posición al reordenar.
    </p>
  </div>
<?php endif; ?>

<!-- Nuevas imágenes (Subida) -->
<div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
  <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
    <i class="ph-fill ph-image text-blue-500"></i> Agregar Imágenes
  </h2>

  <?php if ($producto['protegido'] ?? false): ?>
    <div 
      onclick="mostrarToast('Producto bloqueado. Desbloquea primero para agregar imágenes.', 'error')"
      class="relative border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-4 text-center cursor-pointer mb-3"
    >
      <div class="text-slate-400">
        <i class="ph-duotone ph-lock text-2xl mb-1"></i>
        <p class="text-xs font-bold">Subida bloqueada</p>
      </div>
    </div>
  <?php else: ?>
    <div class="relative border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-4 text-center hover:bg-slate-100 transition cursor-pointer group mb-3">
      <input
        type="file"
        id="input-fotos"
        name="imagenes_nuevas[]"
        multiple
        accept="image/*"
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
        onchange="previewImages(this)"
      >
      <div class="text-slate-400 group-hover:text-blue-500 transition">
        <i class="ph-duotone ph-upload-simple text-2xl mb-1"></i>
        <p class="text-xs font-bold">Toca para subir fotos</p>
      </div>
    </div>
  <?php endif; ?>

  <div id="preview-container" class="grid grid-cols-4 gap-2"></div>
</div>

<!-- Modal vista previa imagen (si no está ya en el layout principal) -->
<!-- Modal vista previa imagen (si no está ya en el layout principal) -->
<div id="imagePreviewModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black/90 backdrop-blur-sm select-none" tabindex="-1">
  
  <!-- Close Button -->
  <button type="button" onclick="closeImagePreview()" class="absolute top-4 right-4 text-white/50 hover:text-white z-[80] p-2">
    <i class="ph-bold ph-x text-2xl"></i>
  </button>

  <!-- Prev Button -->
  <button type="button" onclick="event.stopPropagation(); prevImage()" class="absolute left-2 md:left-8 text-white/50 hover:text-white z-[75] p-4 bg-black/20 hover:bg-black/40 rounded-full transition-all">
    <i class="ph-bold ph-caret-left text-3xl"></i>
  </button>

  <!-- Image Container -->
  <div class="relative w-full h-full flex items-center justify-center p-4" onclick="closeImagePreview()"> 
      <img id="previewModalImage" src="" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl transition-opacity duration-300" onclick="event.stopPropagation()">
  </div>

  <!-- Next Button -->
  <button type="button" onclick="event.stopPropagation(); nextImage()" class="absolute right-2 md:right-8 text-white/50 hover:text-white z-[75] p-4 bg-black/20 hover:bg-black/40 rounded-full transition-all">
    <i class="ph-bold ph-caret-right text-3xl"></i>
  </button>

</div>

<!-- Modal de Confirmación de Eliminación de Imagen -->
<div id="deleteImageModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all scale-95 duration-300">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph-duotone ph-trash text-3xl"></i>
            </div>
            
            <h3 class="text-lg font-bold text-slate-800 mb-2">¿Eliminar imagen?</h3>
            <p class="text-sm text-slate-500 mb-6">Esta acción la quitará de la vista, pero debes guardar los cambios abajo para que sea permanente.</p>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteImageModal()" class="flex-1 py-3 px-4 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                    Cancelar
                </button>
                <button type="button" id="confirmDeleteImageBtn" class="flex-1 py-3 px-4 bg-red-600 text-white font-medium rounded-xl shadow-lg shadow-red-200 hover:bg-red-700 transition-all">
                    Si, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

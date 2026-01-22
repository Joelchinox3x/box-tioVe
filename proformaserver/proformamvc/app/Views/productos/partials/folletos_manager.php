<!-- Gestor de Folletos PDF -->
<div class="bg-white p-3 rounded-3xl shadow-sm border border-slate-100">
  <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
    <i class="ph-fill ph-file-pdf text-red-500"></i> Folletos del Producto
  </h2>

  <?php if ($producto['protegido'] ?? false): ?>
    <!-- Mensaje de bloqueado -->
    <div class="bg-slate-50 rounded-xl p-3 text-center mb-3">
      <i class="ph-duotone ph-lock text-slate-400 text-2xl mb-1"></i>
      <p class="text-xs font-bold text-slate-500">Producto bloqueado</p>
      <p class="text-[10px] text-slate-400">Desbloquea para gestionar folletos</p>
    </div>
  <?php else: ?>

    <!-- Folletos Existentes -->
    <?php if (!empty($folletos)): ?>
      <div class="space-y-3 mb-3" id="folletos-existentes">
        <?php foreach ($folletos as $folleto): ?>
          <?php 
            $cardBg = $folleto['activo'] ? 'bg-orange-50 hover:border-orange-200' : 'bg-slate-50 hover:border-slate-300';
            $iconColor = $folleto['tipo'] == 'generado' ? 'from-blue-500 to-indigo-600 shadow-blue-200' : 'from-purple-500 to-fuchsia-600 shadow-purple-200';
            
            // Logic for onclick vs href
            $isGenerado = ($folleto['tipo'] == 'generado' && !empty($folleto['imagenes_fuente']));
            $clickAttr = '';
            $hrefAttr = 'href="' . asset($folleto['ruta_pdf']) . '" target="_blank"';
            
            if ($isGenerado) {
                // For generated brochures, we open the Option Modal
                $jsonFotos = htmlspecialchars(json_encode($folleto['imagenes_fuente']), ENT_QUOTES, 'UTF-8');
                $pdfUrl = asset($folleto['ruta_pdf']);
                $clickAttr = "onclick=\"abrirOpcionesFolleto('$pdfUrl', '$jsonFotos')\"";
                $hrefAttr = 'href="javascript:void(0)"'; // Prevent default link behavior
            }
          ?>
          <div class="<?= $cardBg ?> rounded-2xl p-3 border-2 border-slate-200 transition-all folleto-item group" data-id="<?= $folleto['id'] ?>">
            <div class="flex items-start gap-3">
              <!-- Icono y badge de tipo -->
              <a <?= $hrefAttr ?> <?= $clickAttr ?> class="flex-shrink-0 relative">
                <div class="w-14 h-14 bg-gradient-to-br <?= $iconColor ?> text-white rounded-xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-105">
                  <!-- Icono PDF (Default) -->
                  <i class="ph-fill ph-file-pdf text-2xl group-hover:hidden"></i>
                  <!-- Icono Ver (Hover) -->
                  <i class="ph-bold ph-eye text-2xl hidden group-hover:block animate-in fade-in zoom-in duration-200"></i>
                </div>
              </a>

              <!-- Info del folleto -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-1 mb-1">
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-slate-800 truncate"><?= htmlspecialchars($folleto['nombre']) ?></h3>
                  </div>

                  <!-- Badge de categoría -->
                  <span class="px-2 py-1 bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg">
                    <?= ucfirst($folleto['categoria']) ?>
                  </span>
                </div>

                <!-- Metadatos (Peso, Fotos y Descargas) -->
                <div class="flex items-center justify-between text-[10px] text-slate-500 mb-3">
                  
                  <!-- Izquierda: Peso y Fotos -->
                  <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                      <i class="ph ph-hard-drives"></i>
                      <?php
                        $bytes = $folleto['tamanio'] ?? 0;
                        if ($bytes >= 1048576) {
                          echo number_format($bytes / 1048576, 2) . ' MB';
                        } elseif ($bytes >= 1024) {
                          echo number_format($bytes / 1024, 2) . ' KB';
                        } else {
                          echo $bytes . ' B';
                        }
                      ?>
                    </span>

                    <?php if ($folleto['tipo'] == 'generado' && !empty($folleto['imagenes_fuente'])): ?>
                      <span class="flex items-center gap-1"><i class="ph ph-images"></i> <?= count($folleto['imagenes_fuente']) ?> fotos</span>
                    <?php endif; ?>
                  </div>

                  <!-- Derecha: Burbuja de Descargas -->
                  <div class="bg-slate-100 text-slate-600 rounded-full h-5 min-w-[20px] px-1.5 flex items-center justify-center font-bold" title="Descargas">
                     <?= $folleto['descargas'] ?? 0 ?>
                  </div>

                </div>

               </div> <!-- Cierre Info -->

               <!-- Acciones Verticales (Fuera del contenedor de texto) -->
               <div class="flex flex-col gap-1">
                  <!-- Activar/Desactivar -->
                  <button type="button"
                          onclick="toggleActivoFolleto(<?= $folleto['id'] ?>, <?= $folleto['activo'] ? 0 : 1 ?>)"
                          class="w-7 h-7 bg-gradient-to-br <?= $folleto['activo'] ? 'from-emerald-500 to-green-600' : 'from-slate-400 to-slate-500' ?> hover:brightness-110 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                    <i class="ph-bold ph-<?= $folleto['activo'] ? 'check' : 'x' ?> text-white text-xs"></i>
                  </button>

                  <!-- Eliminar -->
                  <button type="button"
                          onclick="eliminarFolleto(<?= $folleto['id'] ?>)"
                          class="w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                    <i class="ph-bold ph-trash text-white text-xs"></i>
                  </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="bg-slate-50 rounded-xl p-6 text-center mb-4">
        <i class="ph-duotone ph-file-dashed text-slate-300 text-4xl mb-2"></i>
        <p class="text-xs font-bold text-slate-500">No hay folletos aún</p>
        <p class="text-[10px] text-slate-400">Sube un PDF o crea uno desde fotos</p>
      </div>
    <?php endif; ?>

    <!-- Selector de modo -->
    <div class="mb-4">
      <p class="text-xs font-bold text-slate-700 mb-2">Agregar nuevo folleto:</p>
      <div class="flex gap-2">
        <button type="button"
                onclick="cambiarModoFolleto('pdf')"
                id="btn-modo-pdf"
                class="flex-1 py-3 px-4 bg-red-500 text-white text-xs font-bold rounded-xl hover:bg-red-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-red-200">
          <i class="ph-bold ph-file-arrow-up"></i> Subir PDF
        </button>
        <button type="button"
                onclick="cambiarModoFolleto('fotos')"
                id="btn-modo-fotos"
                class="flex-1 py-3 px-4 bg-blue-500 text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
          <i class="ph-bold ph-images"></i> Crear desde Fotos
        </button>
      </div>
    </div>

    <!-- Modo: Subir PDF -->
    <div id="modo-subir-pdf" class="hidden">
      <div class="bg-red-50/50 rounded-xl border-2 border-dashed border-red-200 p-4 mb-3">
        <input type="file"
               id="input-pdf-directo"
               accept="application/pdf,.pdf"
               class="hidden"
               onchange="previewPdfDirecto(this)">
        <label for="input-pdf-directo"
               class="block text-center cursor-pointer">
          <i class="ph-duotone ph-upload-simple text-red-400 text-3xl mb-2"></i>
          <p class="text-xs font-bold text-red-700">Seleccionar archivo PDF</p>
          <p class="text-[10px] text-red-500">Máximo 15MB</p>
        </label>
      </div>
      <div id="preview-pdf-directo"></div>
    </div>

    <!-- Modo: Crear desde Fotos -->
    <div id="modo-crear-fotos" class="hidden">
      <div class="bg-blue-50/50 rounded-xl border-2 border-dashed border-blue-200 p-4 mb-3">
        <input type="file"
               id="input-fotos-folleto"
               accept="image/*"
               multiple
               class="hidden"
               onchange="previewFotosFolleto(this)">
        <label for="input-fotos-folleto"
               class="block text-center cursor-pointer">
          <i class="ph-duotone ph-upload-simple text-blue-400 text-3xl mb-2"></i>
          <p class="text-xs font-bold text-blue-700">Seleccionar fotos</p>
          <p class="text-[10px] text-blue-500">Se generará un PDF automáticamente</p>
        </label>
      </div>
      <div id="preview-fotos-folleto" class="grid grid-cols-3 gap-2"></div>
    </div>

    <!-- Campos comunes -->
    <div id="campos-folleto" class="hidden space-y-3 mt-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del folleto</label>
        <input type="text"
               id="folleto-nombre"
               placeholder="Ej: Catálogo Principal 2026"
               class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-xs focus:border-blue-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1">Categoría</label>
        <select id="folleto-categoria"
                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-xs focus:border-blue-500 focus:outline-none">
          <option value="general">General</option>
          <option value="tecnico">Técnico</option>
          <option value="comercial">Comercial</option>
        </select>
      </div>

      <button type="button"
              onclick="guardarFolleto()"
              id="btn-guardar-folleto"
              class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-xs font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg shadow-green-200">
        <i class="ph-bold ph-check-circle"></i> Guardar Folleto
      </button>
    </div>

  <?php endif; ?>
</div>

<script>
let modoActual = null;
let archivoSeleccionado = null;
let folletoIdToDelete = null;

function cambiarModoFolleto(modo) {
  // Resetear
  document.getElementById('modo-subir-pdf').classList.add('hidden');
  document.getElementById('modo-crear-fotos').classList.add('hidden');
  document.getElementById('campos-folleto').classList.add('hidden');
  document.getElementById('btn-modo-pdf').classList.remove('ring-4', 'ring-red-200');
  document.getElementById('btn-modo-fotos').classList.remove('ring-4', 'ring-blue-200');

  modoActual = modo;
  archivoSeleccionado = null;

  if (modo === 'pdf') {
    document.getElementById('modo-subir-pdf').classList.remove('hidden');
    document.getElementById('btn-modo-pdf').classList.add('ring-4', 'ring-red-200');
  } else {
    document.getElementById('modo-crear-fotos').classList.remove('hidden');
    document.getElementById('btn-modo-fotos').classList.add('ring-4', 'ring-blue-200');
  }
}

function previewPdfDirecto(input) {
  const file = input.files[0];
  const preview = document.getElementById('preview-pdf-directo');

  if (!file) {
    preview.innerHTML = '';
    document.getElementById('campos-folleto').classList.add('hidden');
    return;
  }

  // Validar tipo
  if (file.type !== 'application/pdf') {
    mostrarToast('Solo se permiten archivos PDF', 'error');
    input.value = '';
    return;
  }

  // Validar tamaño
  if (file.size > 15 * 1024 * 1024) {
    mostrarToast('El archivo excede los 15MB', 'error');
    input.value = '';
    return;
  }

  archivoSeleccionado = file;

  // Mostrar preview
  const sizeMB = (file.size / 1048576).toFixed(2);
  preview.innerHTML = `
    <div class="bg-white rounded-xl p-3 border-2 border-red-200 flex items-center gap-3">
      <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
        <i class="ph-fill ph-file-pdf text-xl"></i>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-bold text-slate-800 truncate">${file.name}</p>
        <p class="text-[10px] text-slate-500">${sizeMB} MB</p>
      </div>
      <button type="button" onclick="document.getElementById('input-pdf-directo').value=''; previewPdfDirecto(document.getElementById('input-pdf-directo'))"
              class="text-red-500 hover:text-red-700">
        <i class="ph-bold ph-x text-lg"></i>
      </button>
    </div>
  `;

  document.getElementById('campos-folleto').classList.remove('hidden');
  document.getElementById('folleto-nombre').value = file.name.replace('.pdf', '');
}


async function previewFotosFolleto(input) {
  const files = Array.from(input.files);
  const preview = document.getElementById('preview-fotos-folleto');

  if (files.length === 0) {
    preview.innerHTML = '';
    document.getElementById('campos-folleto').classList.add('hidden');
    return;
  }

  // Limpiar y preparar UI
  preview.innerHTML = '';
  document.getElementById('campos-folleto').classList.remove('hidden');
  document.getElementById('folleto-nombre').value = 'Folleto generado - ' + new Date().toLocaleDateString();
  
  // Procesar imágenes
  const optimizedFiles = [];
  mostrarToast(`Procesando ${files.length} imágenes...`, 'info');

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (!file.type.startsWith('image/')) continue;

    try {
        let processedFile = file;
        
        // Optimizar si existe la librería
        if (typeof ImageOptimizer !== 'undefined') {
            processedFile = await ImageOptimizer.optimize(file, {
                maxWidth: 1200, 
                quality: 0.8, 
                maxSizeMB: 1
            });
            
            // Feedback de reducción (Igual que en gallery_logic.js)
            if (processedFile.size < file.size) {
                const originalKB = (file.size / 1024).toFixed(0);
                const newKB = (processedFile.size / 1024).toFixed(0);
                mostrarToast(`Optimizada: ${originalKB}KB ➜ ${newKB}KB`, 'success');
            } else {
                mostrarToast(`Procesada: ${(file.size / 1024).toFixed(0)}KB`, 'info');
            }
        }
        optimizedFiles.push(processedFile);

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
          const div = document.createElement('div');
          div.className = 'relative aspect-square bg-slate-100 rounded-lg overflow-hidden animate-fade-in shadow-sm border border-slate-200';
          div.innerHTML = `
            <img src="${e.target.result}" class="w-full h-full object-cover">
            <div class="absolute top-1 left-1 bg-blue-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-md">${i + 1}</div>
            <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-0.5 text-center">
                <span class="text-[9px] text-white font-mono">WEBP ${(processedFile.size/1024).toFixed(0)}KB</span>
            </div>
          `;
          preview.appendChild(div);
        };
        reader.readAsDataURL(processedFile);

    } catch (err) {
        console.error(err);
        mostrarToast(`Error al procesar ${file.name}`, 'error');
    }
  }

  archivoSeleccionado = optimizedFiles;
  mostrarToast('Imágenes optimizadas y listas', 'success');
}

async function guardarFolleto() {
  const nombre = document.getElementById('folleto-nombre').value.trim();
  const categoria = document.getElementById('folleto-categoria').value;

  if (!nombre) {
    mostrarToast('Ingresa un nombre para el folleto', 'error');
    return;
  }

  if (!archivoSeleccionado) {
    mostrarToast('Selecciona un archivo', 'error');
    return;
  }

  const btn = document.getElementById('btn-guardar-folleto');
  btn.disabled = true;
  btn.innerHTML = '<i class="ph-bold ph-circle-notch animate-spin"></i> Guardando...';

  try {
    const formData = new FormData();
    formData.append('producto_id', <?= $producto['id'] ?>);
    formData.append('nombre', nombre);
    formData.append('categoria', categoria);
    formData.append('tipo', modoActual === 'pdf' ? 'subido' : 'generado');

    if (modoActual === 'pdf') {
      formData.append('pdf', archivoSeleccionado);
    } else {
      archivoSeleccionado.forEach((file, index) => {
        formData.append(`fotos[]`, file);
      });
    }

    const response = await fetch('<?= url("/inventario/folleto/crear") ?>', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      mostrarToast('Folleto guardado correctamente', 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      mostrarToast(result.error || 'Error al guardar', 'error');
    }
  } catch (error) {
    mostrarToast('Error de conexión', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="ph-bold ph-check-circle"></i> Guardar Folleto';
  }
}

function eliminarFolleto(id) {
  folletoIdToDelete = id;
  const modal = document.getElementById('deleteFolletoModal');
  modal.classList.remove('hidden');
  // Forzar reflow
  void modal.offsetWidth;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.querySelector('div').classList.remove('scale-95');
  modal.querySelector('div').classList.add('scale-100');
}

function closeDeleteFolletoModal() {
  const modal = document.getElementById('deleteFolletoModal');
  modal.classList.add('opacity-0', 'pointer-events-none');
  modal.querySelector('div').classList.remove('scale-100');
  modal.querySelector('div').classList.add('scale-95');
  setTimeout(() => {
    modal.classList.add('hidden');
    folletoIdToDelete = null;
  }, 300);
}

// Inicializar listener
document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('confirmDeleteFolletoBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
             if (folletoIdToDelete) {
                  const id = folletoIdToDelete;
                  closeDeleteFolletoModal();
                  
                  try {
                    const response = await fetch(`<?= url("/inventario/folleto/eliminar/") ?>${id}`, {
                      method: 'POST',
                      headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });

                    // Si el servidor responde OK (200), asumimos éxito y recargamos
                    // igual que en index.php para garantizar sincronía
                    if (response.ok) {
                         mostrarToast('Folleto eliminado', 'success');
                         setTimeout(() => location.reload(), 500);
                    } else {
                         mostrarToast('Error al eliminar', 'error');
                    }
                  } catch (error) {
                    console.error(error);
                    // Si falló el fetch pero el usuario dice que "si se borra",
                    // forzamos recarga tras un delay por si acaso fue un error de red espurio
                    mostrarToast('Verificando...', 'info');
                    setTimeout(() => location.reload(), 1000);
                  }
             }
        });
    }
});

async function toggleActivoFolleto(id, activo) {
  try {
    const response = await fetch(`<?= url("/inventario/folleto/toggle/") ?>${id}`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({activo})
    });

    const result = await response.json();

    if (result.success) {
      mostrarToast(activo ? 'Folleto activado' : 'Folleto desactivado', 'success');
      setTimeout(() => location.reload(), 500);
    } else {
      mostrarToast(result.error || 'Error', 'error');
    }
  } catch (error) {
    mostrarToast('Error de conexión', 'error');
  }
}
</script>


<!-- Modal de Confirmación de Eliminación de Folleto -->
<div id="deleteFolletoModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all scale-95 duration-300">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph-duotone ph-trash text-3xl"></i>
            </div>
            
            <h3 class="text-lg font-bold text-slate-800 mb-2">¿Eliminar folleto?</h3>
            <p class="text-sm text-slate-500 mb-6">Esta acción borrará el archivo PDF permanentemente. ¿Estás seguro?</p>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteFolletoModal()" class="flex-1 py-3 px-4 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                    Cancelar
                </button>
                <button type="button" id="confirmDeleteFolletoBtn" class="flex-1 py-3 px-4 bg-red-600 text-white font-medium rounded-xl shadow-lg shadow-red-200 hover:bg-red-700 transition-all">
                    Si, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Parcial de Modals -->
<?php include __DIR__ . '/folletos_gallery_modal.php'; ?>

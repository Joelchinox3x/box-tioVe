<?php
// Configuración del header
$title = 'Clientes';
$subtitle = ($total_clientes ?? 0) . ' registrados';

// Botones de acción (Exportar, Importar, Nuevo)
$action_buttons = [
    [
        'url' => '#',
        'icon' => 'ph-download-simple', // Export
        'label' => 'Exportar',
        'onclick' => 'openExportModal(); return false;',
        'target' => '_self'
    ],
    [
        'url' => '#',
        'icon' => 'ph-upload-simple', // Import
        'label' => 'Importar',
        'onclick' => 'openImportModal(); return false;',
        'target' => '_self'
    ],
    [
        'url' => url('/clientes/create'),
        'icon' => 'ph-plus',
        'label' => 'Nuevo'
    ]
];

$search = true;
$selection_mode_enabled = true;
$section = 'clientes';
$show_home = true;

// ============================================
// 🎨 MOSTRAR DIFERENCIACIÓN VISUAL
// ============================================
// true  = Cards bloqueados se ven ROJOS, desbloqueados VERDES (diferenciación visual)
// false = Todos los cards se ven VERDES, solo el hover revela si está bloqueado
$MOSTRAR_DIFERENCIACION = false;
// ============================================

include __DIR__ . '/../partials/load_header.php';
include __DIR__ . '/partials/export_import_modals.php';
?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="pt-10 px-4 pb-6 max-w-md mx-auto">

  <!-- Lista de clientes -->
  <div id="clientesList" class="space-y-3">
    <?php if (empty($clientes)): ?>
      <div class="text-center py-12">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ph-bold ph-users text-slate-400 text-4xl"></i>
        </div>
        <p class="text-slate-500 mb-4">No hay clientes registrados</p>
        <div class="flex flex-col gap-2 max-w-xs mx-auto">
             <a href="<?= url('/clientes/create') ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors">
              Agregar Primer Cliente
            </a>
             <button onclick="openImportModal()" class="inline-block px-6 py-3 bg-white border border-slate-300 text-slate-600 rounded-xl font-medium hover:bg-slate-50 transition-colors">
              <i class="ph-bold ph-upload-simple mr-2"></i> Importar Backup
            </button>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($clientes as $cliente): ?>

              <?php
                // Colores según configuración
                if ($MOSTRAR_DIFERENCIACION) {
                  // MODO DIFERENCIADO: Bloqueados = Rojo, Normales = Verde (visible desde el inicio)
                  $textColor     = $cliente['protegido'] ? 'group-hover:text-red-600' : 'group-hover:text-green-600';
                  $bgIcon        = $cliente['protegido'] ? 'bg-rose-50 group-hover:bg-rose-100' : 'bg-emerald-50 group-hover:bg-emerald-100';
                  $textIcon      = $cliente['protegido'] ? 'text-red-500 group-hover:text-red-600' : 'text-green-500 group-hover:text-green-600';
                  $barraVertical = $cliente['protegido'] ? 'from-rose-500 to-red-500' : 'from-emerald-500 to-green-500';
                  $bgCard        = $cliente['protegido'] ? 'from-rose-50 to-red-50 hover:from-rose-100 hover:to-red-100'  : 'from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100';
                  $borderCard    = $cliente['protegido'] ? 'border-red-200 hover:border-red-400 shadow-red-100/50' : 'border-green-200 hover:border-green-400 shadow-green-100/50';
                  $bgBoton       = $cliente['protegido'] ? 'from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700' : 'from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700';
                  $imagenGrad    = $cliente['protegido'] ? 'from-red-500 to-rose-600' : 'from-green-500 to-emerald-600';
                  $overlayGrad   = $cliente['protegido'] ? 'bg-gradient-to-r from-rose-600 to-red-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-rose-700 group-hover/foto:to-red-700' : 'bg-gradient-to-r from-emerald-600 to-green-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-emerald-700 group-hover/foto:to-green-700';
                  $iconOverlayHover = 'group-hover/foto:opacity-100';
                } else {
                  // MODO UNIFORME: Todos verdes, hover revela bloqueados en ROJO
                  $textColor     = $cliente['protegido'] ? 'group-hover:text-red-600' : 'group-hover:text-green-600';
                  $bgIcon        = $cliente['protegido'] ? 'bg-emerald-50 group-hover:bg-rose-100' : 'bg-emerald-50 group-hover:bg-emerald-100';
                  $textIcon      = $cliente['protegido'] ? 'text-green-500 group-hover:text-red-600' : 'text-green-500 group-hover:text-green-600';
                  $barraVertical = $cliente['protegido'] ? 'from-emerald-500 to-green-500 group-hover:from-rose-500 group-hover:to-red-500' : 'from-emerald-500 to-green-500';
                  $bgCard        = $cliente['protegido'] ? 'from-emerald-50 to-green-50 hover:from-rose-100 hover:to-red-100' : 'from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100';
                  $borderCard    = $cliente['protegido'] ? 'border-green-200 hover:border-red-400 shadow-green-100/50' : 'border-green-200 hover:border-green-400 shadow-green-100/50';
                  $bgBoton       = $cliente['protegido'] ? 'from-green-600 to-emerald-600 group-hover:from-rose-700 group-hover:to-red-700' : 'from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700';
                  $imagenGrad    = $cliente['protegido'] ? 'from-green-500 to-emerald-600' : 'from-green-500 to-emerald-600';
                  $overlayGrad   = $cliente['protegido'] ? 'bg-gradient-to-r from-emerald-600 to-green-600 opacity-0 group-hover:opacity-90 group-hover:from-rose-700 group-hover:to-red-700' : 'bg-gradient-to-r from-emerald-600 to-green-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-emerald-700 group-hover/foto:to-green-700';
                  $iconOverlayHover = $cliente['protegido'] ? 'group-hover:opacity-100' : 'group-hover/foto:opacity-100';
                }
                ?>
     
      <div class="group relative bg-gradient-to-br <?= $bgCard ?> rounded-2xl shadow-sm border <?= $borderCard ?> hover:shadow-lg hover:scale-[1.02] transition-all duration-300 animate-fade-in-up overflow-hidden">

       <!-- Barra vertical de color según estado -->
          <div class="absolute top-0 left-0 bottom-0 w-1 bg-gradient-to-br <?= $barraVertical ?> group-hover:w-1.5 transition-all"></div>

          <!-- Header con info del cliente -->
          <div class="pt-3 pr-3 pl-4 pb-1">
            <div class="flex items-center gap-3">

              <!-- Contenedor de foto con íconos -->
              <div class="flex flex-col items-center gap-1">
              

                <!-- Foto con efecto clickeable -->
                <?php if ($cliente['protegido']): ?>
                  <!-- Cliente protegido - muestra mensaje al hacer click -->
                  <button onclick="mostrarToastProtegido()" class="relative w-16 h-16 flex-shrink-0 group/foto cursor-pointer">
                    <?php if ($cliente['foto_url']): ?>
                      <img src="<?= asset( htmlspecialchars($cliente['foto_url'])) ?>" alt="" class="w-full h-full rounded-xl object-cover ring-2 ring-white shadow-md">
                    <?php else: ?>
                      <div class="w-full h-full bg-gradient-to-br <?= $imagenGrad ?> rounded-xl flex items-center justify-center shadow-md transition-transform duration-300">
                        <i class="ph-bold ph-user text-white text-2xl"></i>
                      </div>
                    <?php endif; ?>
                    <!-- Overlay con icono de candado en hover -->
                     <div class="absolute inset-0 <?= $overlayGrad ?> rounded-xl flex items-center justify-center transition-all duration-300">
                     <i class="ph-bold ph-lock text-white text-2xl opacity-0 <?= $iconOverlayHover ?> transition-opacity duration-300"></i>
                    </div>
                  </button>
                <?php else: ?>
                  <!-- Cliente desbloqueado - va a editar -->
                  <a href="<?= url('/clientes/edit/' . $cliente['id']) ?>" class="relative w-16 h-16 flex-shrink-0 group/foto cursor-pointer">
                    <?php if ($cliente['foto_url']): ?>
                      <img src="<?= asset( htmlspecialchars($cliente['foto_url'])) ?>" alt="" class="w-full h-full rounded-xl object-cover ring-2 ring-white shadow-md">
                    <?php else: ?>
                      <div class="w-full h-full bg-gradient-to-br <?= $imagenGrad ?> rounded-xl flex items-center justify-center shadow-md transition-transform duration-300">
                        <i class="ph-bold ph-user text-white text-2xl"></i>
                      </div>
                    <?php endif; ?>
                    <!-- Overlay con icono de editar en hover -->
                      <div class="absolute inset-0 <?= $overlayGrad ?> rounded-xl flex items-center justify-center transition-all duration-300">
                      <i class="ph-bold ph-pencil text-white text-2xl opacity-0 group-hover/foto:opacity-100 transition-opacity duration-300"></i>
                    </div>
                  </a>
                <?php endif; ?>

                <!-- Íconos abajo de la foto: Solo teléfono y WhatsApp -->
                <div class="flex items-center justify-center gap-1">
                  <?php if ($cliente['telefono']): ?>
                    <a href="tel:<?= htmlspecialchars($cliente['telefono']) ?>"
                       class="w-6 h-6 flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-full shadow-sm hover:scale-110 transition-all duration-300 border border-blue-100"
                       title="Llamar">
                      <i class="ph-bold ph-phone text-[11px]"></i>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $cliente['telefono']) ?>"
                       target="_blank"
                       class="w-6 h-6 flex items-center justify-center bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded-full shadow-sm hover:scale-110 transition-all duration-300 border border-green-100"
                       title="WhatsApp">
                      <i class="ph-bold ph-whatsapp-logo text-[11px]"></i>
                    </a>
                  <?php endif; ?>

                  <?php if (!empty($cliente['latitud']) && !empty($cliente['longitud'])): ?>
                    <a href="https://www.google.com/maps?q=<?= $cliente['latitud'] ?>,<?= $cliente['longitud'] ?>"
                       target="_blank"
                       class="w-6 h-6 flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-full shadow-sm hover:scale-110 transition-all duration-300 border border-red-100"
                       title="Ver ubicación">
                      <i class="ph-bold ph-map-pin text-[11px]"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
          
              <!-- Info -->
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-900 text-base mb-1.5 truncate <?= $textColor ?> transition-colors">
                  <?= htmlspecialchars($cliente['nombre']) ?>
                </h3>

                <!-- DNI/RUC y Teléfono (izquierda) + Botón Cotizar (derecha) -->
                <div class="flex items-center justify-between gap-2">
                  <!-- DNI/RUC y Teléfono apilados a la izquierda -->
                  <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                    <?php if ($cliente['dni_ruc']): ?>
                      <div class="flex items-center gap-1">
                        <span class="w-5 h-5 <?= $bgIcon ?> rounded-md flex items-center justify-center flex-shrink-0">
                          <i class="ph ph-identification-card text-[12px] <?= $textIcon ?>"></i>
                        </span>
                        <span class="text-xs font-medium text-slate-700 truncate"><?= htmlspecialchars($cliente['dni_ruc']) ?></span>
                      </div>
                    <?php endif; ?>

                    <?php if ($cliente['telefono']): ?>
                      <div class="flex items-center gap-1">
                        <span class="w-5 h-5 <?= $bgIcon ?> rounded-md flex items-center justify-center flex-shrink-0">
                          <i class="ph ph-phone text-[12px] <?= $textIcon ?>"></i>
                        </span>
                        <span class="text-xs font-bold text-slate-600 truncate"><?= htmlspecialchars($cliente['telefono']) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- Botón Cotizar a la derecha -->
                  <a href="<?= url('/proformas/create?cliente_id=' . $cliente['id']) ?>"
                     class="flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gradient-to-r <?= $bgBoton ?> text-white rounded-xl font-medium transition-all duration-300 shadow-md hover:shadow-lg text-xs whitespace-nowrap self-start">
                    <i class="ph-bold ph-file-text text-sm"></i>
                    <span>Cotizar</span>
                  </a>
                </div>
              </div>

              <!-- Acciones superiores -->
              <div class="flex flex-col gap-1">
                <!-- Botón Editar -->
                <a href="<?= url('/clientes/edit/' . $cliente['id']) ?>" class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                  <i class="ph-bold ph-pencil text-white text-xs"></i>
                </a>
                <!-- Botón Eliminar o Candado -->
                <?php if ($cliente['protegido']): ?>
                  <button onclick="mostrarToastProtegido(<?= $cliente['id'] ?>)" class="w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110 cursor-pointer">
                    
                    <i class="ph-bold ph-lock text-white text-xs"></i>
                  </button>
                <?php else: ?>
                  <button onclick="confirmarEliminar(<?= $cliente['id'] ?>, '<?= htmlspecialchars($cliente['nombre']) ?>')" class="w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                    <i class="ph-bold ph-trash text-white text-xs"></i>
                  </button>
                <?php endif; ?>
              </div>

            </div>
          </div>



        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>

<!-- Modal de Confirmación de Eliminación ULTRA PREMIUM -->
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in overflow-hidden">
    <!-- Header con gradiente rojo peligro -->
    <div class="bg-gradient-to-r from-red-500 to-rose-600 p-5 relative overflow-hidden">
      <!-- Patrón decorativo de fondo -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full -ml-12 -mb-12"></div>
      </div>

      <div class="flex items-center gap-3 relative z-10">
        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
          <i class="ph-bold ph-warning text-white text-2xl animate-pulse"></i>
        </div>
        <div>
          <h3 class="text-white font-bold text-lg">¡Advertencia!</h3>
          <p class="text-white/90 text-xs">Esta acción no se puede deshacer</p>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="p-6">
      <p class="text-slate-700 text-sm mb-1">¿Estás seguro de eliminar a:</p>
      <p class="text-slate-900 font-bold text-lg mb-3" id="deleteClientName">Cliente</p>

      <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg mb-4">
        <div class="flex items-start gap-2">
          <i class="ph-bold ph-warning-circle text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
          <div>
            <p class="text-amber-800 text-xs font-semibold mb-1">Se eliminarán también:</p>
            <ul class="text-amber-700 text-xs space-y-0.5">
              <li>• Todas sus proformas</li>
              <li>• Su foto de perfil</li>
              <li>• Todo su historial</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="flex gap-3">
        <button
          onclick="cerrarModalEliminarCliente()"
          class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all active:scale-95"
        >
          Cancelar
        </button>
        <button
          onclick="ejecutarEliminacion()"
          class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold transition-all shadow-md hover:shadow-lg active:scale-95"
        >
          Sí, eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes scale-in {
    from {
      opacity: 0;
      transform: scale(0.9);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }

  .animate-scale-in {
    animation: scale-in 0.3s ease-out forwards;
  }

  .animate-shake {
    animation: shake 0.3s ease-out;
  }
</style>

<script>
// Mostrar notificaciones basadas en mensajes del servidor
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($mensaje)): ?>
    <?php
    $notificaciones = [
      'created' => ['titulo' => '¡Cliente Creado!', 'mensaje' => 'El cliente se guardó exitosamente', 'tipo' => 'success'],
      'updated' => ['titulo' => 'Actualizado', 'mensaje' => 'Cliente actualizado exitosamente', 'tipo' => 'success'],
      'deleted' => ['titulo' => 'Eliminado', 'mensaje' => 'Cliente eliminado correctamente', 'tipo' => 'success'],
      'locked' => ['titulo' => 'Protegido', 'mensaje' => 'Este cliente está protegido', 'tipo' => 'warning'],
      'not_found' => ['titulo' => 'Error', 'mensaje' => 'Cliente no encontrado', 'tipo' => 'error']
    ];
    $notif = $notificaciones[$mensaje] ?? null;
    ?>
    <?php if ($notif): ?>
      mostrarNotificacion(
        '<?= $notif['titulo'] ?>',
        '<?= $notif['mensaje'] ?>',
        '<?= $notif['tipo'] ?>'
      );
    <?php endif; ?>
  <?php endif; ?>
});

// Búsqueda en tiempo real
const searchInput = document.getElementById('headerSearchInput');
const clearBtn = document.getElementById('headerSearchClear');
let timeoutId;

// Configuración desde PHP
const MOSTRAR_DIFERENCIACION = <?= $MOSTRAR_DIFERENCIACION ? 'true' : 'false' ?>;

// ============================================
// LOGICA DE EXPORTACIÓN / IMPORTACIÓN
// ============================================

// Abrir/Cerrar Modales
function openExportModal() {
    document.getElementById('exportClientModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportClientModal').classList.add('hidden');
}

function confirmExport() {
    window.location.href = '<?= url('/clientes/export') ?>';
    closeExportModal();
    mostrarToast('Descarga iniciada...', 'info');
}

function openImportModal() {
    document.getElementById('importClientModal').classList.remove('hidden');
    resetImportModal();
}

function closeImportModal() {
    document.getElementById('importClientModal').classList.add('hidden');
}

function resetImportModal() {
    document.getElementById('importStep1').classList.remove('hidden');
    document.getElementById('importStep2').classList.add('hidden');
    document.getElementById('importFileInput').value = '';
    document.getElementById('importPreviewList').innerHTML = '';
    document.getElementById('btnConfirmImport').disabled = true;
    window.clientsToImport = [];
}

// LOGICA IMPORTACIÓN
const fileInput = document.getElementById('importFileInput');
if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const json = JSON.parse(e.target.result);
                if (Array.isArray(json)) {
                    renderImportPreview(json);
                } else {
                    mostrarToast('El archivo no tiene el formato correcto (debe ser una lista)', 'error');
                }
            } catch (err) {
                mostrarToast('Error al leer el JSON: ' + err.message, 'error');
            }
        };
        reader.readAsText(file);
    });
}

function renderImportPreview(clients) {
    window.clientsToImport = clients; // Guardar referencia global
    
    const container = document.getElementById('importPreviewList');
    container.innerHTML = '';
    
    // Cambiar de paso
    document.getElementById('importStep1').classList.add('hidden');
    document.getElementById('importStep2').classList.remove('hidden');
    
    // Actualizar contador visual
    document.getElementById('importCountInfo').textContent = `${clients.length} clientes encontrados`;
    
    // Renderizar lista
    clients.forEach((c, index) => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors border border-slate-100';
        
        let info = `<div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-700 truncate">${c.nombre || 'Sin Nombre'}</p>
                        <p class="text-xs text-slate-500 truncate">
                            ${c.dni_ruc ? `<span class="mr-2"><i class="ph ph-identification-card"></i> ${c.dni_ruc}</span>` : ''}
                            ${c.telefono ? `<span><i class="ph ph-phone"></i> ${c.telefono}</span>` : ''}
                        </p>
                    </div>`;

        div.innerHTML = `
            <input type="checkbox" class="import-check w-5 h-5 rounded text-green-600 border-slate-300 focus:ring-green-500" checked data-index="${index}">
            ${info}
            ${c.id ? '<span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded font-bold uppercase">ID:'+c.id+'</span>' : '<span class="text-[10px] px-1.5 py-0.5 bg-green-100 text-green-600 rounded font-bold uppercase">NUEVO</span>'}
        `;
        
        container.appendChild(div);
    });

    updateImportButton();

    // Event listeners para los nuevos checkboxes
    document.querySelectorAll('.import-check').forEach(chk => {
        chk.addEventListener('change', updateImportButton);
    });
}

// Select All Logic
const selectAllChk = document.getElementById('selectAllImport');
if (selectAllChk) {
    selectAllChk.addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.import-check').forEach(chk => {
            chk.checked = checked;
        });
        updateImportButton();
    });
}

function updateImportButton() {
    const checkedCount = document.querySelectorAll('.import-check:checked').length;
    const btn = document.getElementById('btnConfirmImport');
    
    if (checkedCount > 0) {
        btn.disabled = false;
        btn.innerHTML = `Importar (${checkedCount})`;
    } else {
        btn.disabled = true;
        btn.innerHTML = `Importar`;
    }
}

function confirmImport() {
    // Filtrar los seleccionados
    const selectedClients = [];
    document.querySelectorAll('.import-check:checked').forEach(chk => {
        const idx = chk.getAttribute('data-index');
        selectedClients.push(window.clientsToImport[idx]);
    });

    if (selectedClients.length === 0) return;

    // Enviar al servidor
    const btn = document.getElementById('btnConfirmImport');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i> Procesando...';

    fetch('<?= url('/clientes/import') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(selectedClients)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(data.summary, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarToast(data.message || 'Error al importar', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        console.error(err);
        mostrarToast('Error de red al importar', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
// ============================================

// ============================================

searchInput?.addEventListener('input', function() {
  clearTimeout(timeoutId);
  const term = this.value.trim();

  // Mostrar/ocultar botón de limpiar
  if (this.value.length > 0) {
    clearBtn?.classList.remove('hidden');
  } else {
    clearBtn?.classList.add('hidden');
  }

  if (term.length === 0) {
    location.reload();
    return;
  }

  timeoutId = setTimeout(() => {
    fetch(`<?= url('/clientes/search') ?>?q=${encodeURIComponent(term)}`)
      .then(res => res.json())
      .then(clientes => {
        const container = document.getElementById('clientesList');

        if (clientes.length === 0) {
          container.innerHTML = `
            <div class="text-center py-12">
              <p class="text-slate-500">No se encontraron resultados para "${term}"</p>
            </div>
          `;
          return;
        }

        container.innerHTML = clientes.map(cliente => {
          const telefono = cliente.telefono || '';
          const telefonoLimpio = telefono.replace(/[^0-9]/g, '');

          // Determinar colores según modo
          let borderClass, borderHover, bgGradient, iconColor, iconHoverOpacity, lockOverlay, hoverTextColor, btnGradient, overlayConFoto, iconOverlay, lockIconColor;

          if (MOSTRAR_DIFERENCIACION) {
            // MODO DIFERENCIADO: Bloqueados = Rojo, Normales = Verde
            borderClass = cliente.protegido ? 'border-red-200' : 'border-green-200';
            borderHover = cliente.protegido ? 'hover:border-red-300' : 'hover:border-green-300';
            bgGradient = cliente.protegido ? 'from-red-100 to-rose-200' : 'from-green-100 to-emerald-200';
            iconColor = cliente.protegido ? 'text-red-600' : 'text-green-600';
            hoverTextColor = cliente.protegido ? 'group-hover:text-red-600' : 'group-hover:text-green-600';
            btnGradient = cliente.protegido ? 'from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700' : 'from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700';
            lockIconColor = 'text-red-400 group-hover:text-red-600';
            overlayConFoto = '';
            iconOverlay = '';
          } else {
            // MODO UNIFORME: Todos verdes, hover revela bloqueados en ROJO
            borderClass = cliente.protegido ? 'border-green-200' : 'border-green-200';
            borderHover = cliente.protegido ? 'hover:border-red-300' : 'hover:border-green-300';
            bgGradient = 'from-green-100 to-emerald-200';
            iconColor = 'text-green-600';
            hoverTextColor = cliente.protegido ? 'group-hover:text-red-600' : 'group-hover:text-green-600';
            btnGradient = cliente.protegido ? 'from-emerald-600 to-green-600 group-hover:from-rose-700 group-hover:to-red-700' : 'from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700';
            lockIconColor = 'text-green-400 group-hover:text-red-600';

            // Overlay cuando hay foto (bloqueados)
            overlayConFoto = cliente.protegido ? `
              <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-green-600 group-hover:from-rose-700 group-hover:to-red-700 opacity-0 group-hover:opacity-90 rounded-lg flex items-center justify-center transition-all duration-300">
                <i class="ph-bold ph-lock text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
              </div>
            ` : '';

            // Icono overlay para card pequeño
            iconOverlay = cliente.protegido ? 'group-hover:opacity-100' : '';
          }

          iconHoverOpacity = cliente.protegido ? 'group-hover:opacity-0' : '';

          // Overlay sin foto - debe cambiar de verde a rojo en modo uniforme
          if (MOSTRAR_DIFERENCIACION) {
            lockOverlay = cliente.protegido ? `
              <div class="absolute inset-0 flex items-center justify-center
                bg-red-600/10 opacity-0 group-hover:opacity-100 transition-all rounded-lg">
                <i class="ph-bold ph-lock text-red-600 text-2xl"></i>
              </div>
            ` : '';
          } else {
            lockOverlay = cliente.protegido ? `
              <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-green-600 group-hover:from-rose-700 group-hover:to-red-700 opacity-0 group-hover:opacity-90 rounded-lg flex items-center justify-center transition-all duration-300">
                <i class="ph-bold ph-lock text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
              </div>
            ` : '';
          }

          return `
            <div class="group bg-white rounded-xl shadow-sm border ${borderClass} ${borderHover} hover:shadow-md transition-all overflow-hidden">
              <!-- Header con info del cliente -->
              <div class="p-3">
                <div class="flex items-start space-x-3">
                  <div class="relative w-12 h-12 flex-shrink-0 rounded-lg border-2 transition-all ${borderClass} ${borderHover}">
                    ${cliente.foto_url
                      ? `<img src="<?= asset('/') ?>${cliente.foto_url}" class="w-full h-full rounded-lg object-cover">
                         ${overlayConFoto}`
                      : `<div class="relative w-full h-full bg-gradient-to-br ${bgGradient} rounded-lg flex items-center justify-center">
                       <i class="ph-bold ph-user ${iconColor} ${iconHoverOpacity} text-xl transition-opacity duration-200"></i>
                        ${lockOverlay}
                      </div>`
                    }
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-600 ${hoverTextColor} text-sm mb-0.5 truncate flex items-center gap-1.5">
                      <span>${cliente.nombre}</span>
                    </h3>
                    ${cliente.dni_ruc ? `<p class="text-sm text-slate-500 mb-0.5 flex items-center gap-1">
                      <i class="ph ph-identification-card text-sm"></i>
                      <span>${cliente.dni_ruc}</span>
                      ${cliente.protegido ? `<i class="ph-bold ph-lock ${lockIconColor} transition-colors duration-200 text-base cursor-pointer"></i>` : ''}
                    </p>` : ''}
                  </div>
                  <div class="flex items-center">
                    <a href="<?= url('/proformas/create?cliente_id=') ?>${cliente.id}" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r ${btnGradient} text-white rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-lg text-xs">
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

// Botón de limpiar búsqueda
clearBtn?.addEventListener('click', function() {
  searchInput.value = '';
  this.classList.add('hidden');
  searchInput.focus();
  location.reload();
});

// ============================================
// SISTEMA DE ELIMINACIÓN CON MODAL PREMIUM
// ============================================
let clienteAEliminarData = null;

// Mostrar modal de confirmación de eliminación
function confirmarEliminar(id, nombre) {
  clienteAEliminarData = { id, nombre };

  // Actualizar nombre en el modal
  document.getElementById('deleteClientName').textContent = nombre;

  // Mostrar modal con animación
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.remove('hidden');

  // Pequeña vibración háptica si está disponible
  if ('vibrate' in navigator) {
    navigator.vibrate(50);
  }
}

// Cerrar modal de eliminación
function cerrarModalEliminarCliente() {
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.add('hidden');
  clienteAEliminarData = null;
}

// Ejecutar eliminación
function ejecutarEliminacion() {
  if (!clienteAEliminarData) return;

  // Mostrar feedback visual
  mostrarToast('Eliminando cliente...', 'info');

  // Redirigir a eliminar
  window.location.href = `<?= url('/clientes/delete/') ?>${clienteAEliminarData.id}`;
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modal = document.getElementById('confirmDeleteModal');
    if (!modal.classList.contains('hidden')) {
      cerrarModalEliminarCliente();
    }
  }
});

// Función genérica para mostrar toasts (usa el sistema moderno de notificaciones)
function mostrarToast(mensaje, tipo = 'info') {
  const titulos = {
    info: 'Información',
    success: 'Éxito',
    warning: 'Advertencia',
    error: 'Error'
  };

  mostrarNotificacion(titulos[tipo] || titulos.info, mensaje, tipo, 2500);
}

// Mostrar toast cuando cliente está protegido
function mostrarToastProtegido() {
  mostrarToast('Cliente protegido. Desbloquea con su PIN', 'warning');
}
</script>

<?php
$title = 'Detalle Proforma';
$back_url = url('/proformas');
$action_button = [
    'url' => url("/proformas/viewPdf/{$proforma['id']}"),
    'target' => '_blank',
    'icon' => 'ph-file-pdf',
    'label' => 'PDF'
    
];
$section = 'proformas';

include __DIR__ . '/../partials/load_header.php';
?>

<!-- Contenido principal -->
<main class="max-w-md mx-auto pt-18 px-6 pb-6 animate-fade-in-up">

  <!-- Información de la Proforma -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
    <div class="flex items-start justify-between mb-4">
      <div>
        <div class="text-sm text-slate-500 mb-1">Proforma</div>
        <div class="text-2xl font-bold text-purple-600">TRA000<?= htmlspecialchars($proforma['id']) ?></div>
      </div>
      <div class="text-right">
        <div class="text-sm text-slate-500 mb-1">Fecha</div>
        <div class="font-semibold text-slate-800">
          <?= date('d/m/Y', strtotime($proforma['fecha_creacion'])) ?>
        </div>
      </div>
    </div>

    <div class="border-t border-slate-100 pt-4">
      <h3 class="font-semibold text-slate-800 mb-3">Cliente</h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-slate-500">Nombre:</span>
          <span class="font-medium text-slate-800"><?= htmlspecialchars($proforma['cliente_nombre']) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">DNI/RUC:</span>
          <span class="font-medium text-slate-800"><?= htmlspecialchars($proforma['dni_ruc']) ?></span>
        </div>
        <?php if (!empty($proforma['direccion'])): ?>
          <div class="flex justify-between">
            <span class="text-slate-500">Dirección:</span>
            <span class="font-medium text-slate-800 text-right"><?= htmlspecialchars($proforma['direccion']) ?></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($proforma['telefono'])): ?>
          <div class="flex justify-between">
            <span class="text-slate-500">Teléfono:</span>
            <span class="font-medium text-slate-800"><?= htmlspecialchars($proforma['telefono']) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Items -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
    <h3 class="font-semibold text-slate-800 mb-4">Items</h3>

    <div class="space-y-3">
      <?php if (!empty($proforma['items'])): ?>
        <?php foreach ($proforma['items'] as $index => $item): ?>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1">
                <div class="font-semibold text-slate-800 mb-1">
                  <?= htmlspecialchars($item['descripcion']) ?>
                </div>
                <?php if (!empty($item['producto_nombre'])): ?>
                  <div class="text-xs text-slate-500">
                    Producto: <?= htmlspecialchars($item['producto_nombre']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-3 text-sm">
              <div>
                <span class="text-slate-500">Cantidad:</span>
                <span class="font-semibold text-slate-800 ml-1"><?= $item['cantidad'] ?></span>
              </div>
              <div>
                <span class="text-slate-500">P. Unit.:</span>
                <span class="font-semibold text-slate-800 ml-1">
                  <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($item['precio_unitario'], 2) ?>
                </span>
              </div>
              <div class="text-right">
                <span class="text-slate-500">Subtotal:</span>
                <span class="font-bold text-purple-600 ml-1">
                  <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($item['subtotal'], 2) ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-slate-500 py-4">No hay items en esta proforma</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Totales -->
  <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 shadow-sm border border-purple-200 mb-5">
    <h3 class="font-semibold text-slate-800 mb-4">Totales</h3>

    <div class="space-y-3">
      <div class="flex justify-between items-center">
        <span class="text-slate-600">Subtotal:</span>
        <span class="font-semibold text-slate-800">
          <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['subtotal'], 2) ?>
        </span>
      </div>

      <?php if ($proforma['descuento'] > 0): ?>
        <div class="flex justify-between items-center">
          <span class="text-slate-600">Descuento:</span>
          <span class="font-semibold text-red-600">
            -<?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['descuento'], 2) ?>
          </span>
        </div>
      <?php endif; ?>

      <div class="flex justify-between items-center">
        <span class="text-slate-600">IGV (18%):</span>
        <span class="font-semibold text-slate-800">
          <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['igv'], 2) ?>
        </span>
      </div>

      <div class="pt-3 border-t border-purple-200 flex justify-between items-center">
        <span class="text-lg font-bold text-slate-800">Total:</span>
        <span class="text-2xl font-bold text-purple-600">
          <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['total'], 2) ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Observaciones y Condiciones -->
  <?php if (!empty($proforma['observaciones']) || !empty($proforma['condiciones'])): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
      <h3 class="font-semibold text-slate-800 mb-4">Información Adicional</h3>

      <?php if (!empty($proforma['observaciones'])): ?>
        <div class="mb-4">
          <div class="text-sm font-medium text-slate-700 mb-2">Observaciones</div>
          <div class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3">
            <?= nl2br(htmlspecialchars($proforma['observaciones'])) ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($proforma['condiciones'])): ?>
        <div>
          <div class="text-sm font-medium text-slate-700 mb-2">Condiciones Comerciales</div>
          <div class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3">
            <?= nl2br(htmlspecialchars($proforma['condiciones'])) ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Información del Registro -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
    <h3 class="font-semibold text-slate-800 mb-4">Información del Registro</h3>

    <div class="space-y-2 text-sm">
      <div class="flex justify-between">
        <span class="text-slate-500">Vigencia:</span>
        <span class="text-slate-800 font-medium">
          <?= $proforma['vigencia_dias'] ?? 30 ?> días
        </span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Fecha de creación:</span>
        <span class="text-slate-800 font-medium">
          <?= date('d/m/Y H:i', strtotime($proforma['fecha_creacion'])) ?>
        </span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">ID:</span>
        <span class="text-slate-800 font-medium">#<?= $proforma['id'] ?></span>
      </div>
    </div>
  </div>

  <!-- Acciones Mejoradas -->
  <div class="grid grid-cols-2 gap-3">
    <a
      href="<?= url("/proformas/edit/{$proforma['id']}") ?>"
      class="btn-primary text-center flex items-center justify-center gap-2 hover:scale-105 transform transition-all"
    >
      <i class="ph-bold ph-pencil text-lg"></i>
      <span>Editar</span>
    </a>

    <a
      href="<?= url("/proformas/viewPdf/{$proforma['id']}") ?>"
      target="_blank"
      class="flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-purple-800 transition-all shadow-lg hover:shadow-xl hover:scale-105 transform active:scale-95"
    >
      <i class="ph-bold ph-file-pdf text-lg"></i>
      <span>PDF</span>
    </a>
  </div>

  <!-- Botón Eliminar Destacado -->
  <button
    onclick="confirmarEliminar()"
    class="w-full mt-3 px-4 py-3 bg-red-50 text-red-600 border-2 border-red-200 rounded-xl font-semibold hover:bg-red-600 hover:text-white transition-all flex items-center justify-center gap-2 hover:scale-105 transform active:scale-95"
  >
    <i class="ph-bold ph-trash text-lg"></i>
    <span>Eliminar Proforma</span>
  </button>

</main>

<!-- Modal de Confirmación de Eliminación -->
<div id="confirmDeleteModal" class="modal-overlay hidden">
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

    <!-- Body del modal -->
    <div class="p-5 space-y-4">
      <!-- Información de la proforma -->
      <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
        <p class="text-slate-600 text-sm mb-2">¿Estás seguro de eliminar esta proforma?</p>
        <div class="flex items-center gap-2">
          <i class="ph-bold ph-file-text text-purple-600 text-xl"></i>
          <p class="font-bold text-slate-800">Proforma TRA000<?= $proforma['id'] ?></p>
        </div>
        <p class="text-xs text-slate-500 mt-2">
          Cliente: <?= htmlspecialchars($proforma['cliente_nombre']) ?>
        </p>
        <p class="text-xs text-purple-600 font-bold mt-1">
          Total: <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['total'], 2) ?>
        </p>
      </div>

      <!-- Mensaje de advertencia con efecto -->
      <div class="flex items-start gap-2 text-sm text-slate-600 bg-amber-50 p-3 rounded-lg border border-amber-200">
        <i class="ph-bold ph-info text-amber-500 mt-0.5 text-lg animate-pulse"></i>
        <p class="flex-1">Se eliminarán <strong>todos los datos</strong> relacionados con esta proforma de forma <strong>permanente</strong>.</p>
      </div>

      <!-- Botones con efectos mejorados -->
      <div class="flex gap-3">
        <button
          onclick="cerrarModalEliminar()"
          class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all active:scale-95 hover:scale-105 transform"
        >
          <i class="ph-bold ph-x mr-1"></i>
          Cancelar
        </button>
        <button
          onclick="ejecutarEliminacion()"
          class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold transition-all shadow-md hover:shadow-lg active:scale-95 hover:scale-105 transform"
        >
          <i class="ph-bold ph-trash mr-1"></i>
          Sí, eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Mostrar notificaciones si hay mensajes
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const msg = urlParams.get('msg');

  if (msg === 'updated') {
    notifySuccess('Actualizada', 'Proforma actualizada exitosamente');
  }
});

// Variable para guardar confirmación
let modalAbierto = false;

function confirmarEliminar() {
  // Mostrar modal
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.remove('hidden');
  modalAbierto = true;

  // Vibración háptica si está disponible
  if ('vibrate' in navigator) {
    navigator.vibrate(50);
  }
}

function cerrarModalEliminar() {
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.add('hidden');
  modalAbierto = false;
}

function ejecutarEliminacion() {
  // Mostrar notificación de eliminando
  notifyInfo('Eliminando', 'Procesando solicitud...', 2000);

  // Animación de salida
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.add('animate-fade-out');

  // Redirigir después de un breve delay
  setTimeout(() => {
    window.location.href = "<?= url("/proformas/delete/{$proforma['id']}") ?>";
  }, 300);
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && modalAbierto) {
    cerrarModalEliminar();
  }
});

// Cerrar modal al hacer clic fuera
document.getElementById('confirmDeleteModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    cerrarModalEliminar();
  }
});

// Efecto de hover en las cards
document.querySelectorAll('.bg-white, .bg-gradient-to-br').forEach(card => {
  card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';

  card.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-2px)';
  });

  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
  });
});
</script>
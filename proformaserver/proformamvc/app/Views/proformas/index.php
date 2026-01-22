<?php
// Configuración del header
$title = 'Proformas';
$subtitle = ($total_proformas ?? 0) . ' generadas';
$action_button = [
    'url' => url('/proformas/create'),
    'icon' => 'ph-plus',
    'label' => 'Nuevo'
];
$section = 'proformas';
$show_home = true;

include __DIR__ . '/../partials/load_header.php';
?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="pt-18 px-6 pb-6 max-w-md mx-auto">

  <!-- Lista de proformas -->
  <div class="space-y-3">
      <h3 class="mt-3 font-bold text-gray-800 text-sm mb-3 uppercase tracking-wide opacity-80 flex items-center justify-between">
            Todas las Proformas
            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= $total_proformas ?></span>
      </h3>
    <?php if (empty($proformas)): ?>
      <div class="text-center py-12">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ph-bold ph-file-text text-slate-400 text-4xl"></i>
        </div>
        <p class="text-slate-500 mb-4">No hay proformas generadas</p>
        <a href="<?= url("/proformas/create" ) ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors">
          Crear Primera Proforma
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($proformas as $proforma): ?>
        <?php 
        // Decodificar imagen (Para uso en toda la card)
        $primerImagen = '';
        if (!empty($proforma['primer_imagen_json'])) {
            $imgs = json_decode($proforma['primer_imagen_json'], true);
            if (!empty($imgs) && is_array($imgs)) {
                $primerImagen = $imgs[0];
            }
        }
        ?>
        <!-- Card Compacta Mejorada -->
        <div class="group relative bg-gradient-to-br from-blue-50 to-cyan-50 hover:from-cyan-50 hover:to-blue-50 p-3 rounded-2xl shadow-sm border border-blue-100 hover:border-blue-200 hover:shadow-md hover:scale-[1.02] transition-all duration-300 relative animate-fade-in-up">
       
          <!-- Imagen Flotante al Hover de la CARD (Top Right) -->
          <?php include __DIR__ . '/partials/hover_image.php'; ?>

        <!-- Barra lateral de color -->
          <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-cyan-500 to-blue-500 group-hover:w-1.5 transition-all rounded-l-2xl"></div>

          <div class="pl-3">
            <!-- Header: ID y Fecha -->
            <div class="flex justify-between items-center mb-1">
              <span class="font-bold text-slate-400 text-[10px] tracking-wider flex items-center">
                TRA<?= str_pad($proforma['id'], 5, '0', STR_PAD_LEFT) ?>
                
                <?php if (!empty($proforma['primer_sku'])): ?>
                    <span class="ml-2 px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[9px] font-bold border border-indigo-100 uppercase tracking-normal">
                        <?= htmlspecialchars($proforma['primer_sku']) ?>
                    </span>
                <?php endif; ?>
              </span>
              <span class="text-[10px] text-slate-400">
                <?= date('d/m/Y', strtotime($proforma['fecha_creacion'])) ?>
              </span>
            </div>

            <!-- Cliente -->
            <div >
              <h3 class="font-bold text-slate-800 text-sm leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors">
                <?= htmlspecialchars($proforma['cliente_nombre'] ?? 'Cliente') ?>
              </h3>
            </div>
            
            <?php
            $textColor  = 'group-hover:text-purple-600';
            $bgIcon     = 'bg-purple-50 group-hover:bg-purple-100';
            $textIcon   = 'text-purple-500 group-hover:text-purple-600';
            ?>
            <!-- Footer: Total y Acciones -->
            <div class="flex justify-between  pt-2">
              <!-- Total -->
              <div>
                <span class="block text-[11px] text-slate-400 uppercase font-bold tracking-wide">Total</span>
                <span class="block text-lg font-bold text-blue-600 leading-none">
                  <?= $proforma['moneda'] ?? 'PEN' ?> <?= number_format($proforma['total'], 0) ?>
                </span>
             
              </div>

              <!-- Botones de Acción Compactos -->
              <div class="flex gap-1.5 items-center">
                <!-- Ver -->
                <a
                  href="<?= url("/proformas/show/{$proforma['id']}") ?>"
                  class="px-2 py-1 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold hover:bg-purple-100 transition flex items-center gap-1 hover:scale-105 transform active:scale-95"
                  title="Ver detalle">
                  <i class="ph-bold ph-eye text-lg"></i>
                </a>

                <!-- Ver PDF -->
                <a
                  href="<?= url("/proformas/viewPdf/{$proforma['id']}") ?>"
                  target="_blank"
                  class="px-2 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition flex items-center gap-1 hover:scale-105 transform active:scale-95"
                  title="Ver PDF">
                  <i class="ph-bold ph-file-pdf text-lg"></i>
                </a>

                <!-- Descargar PDF -->
                <a
                  href="<?= url("/proformas/downloadPdf/{$proforma['id']}") ?>"
                  class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition flex items-center gap-1 hover:scale-105 transform active:scale-95"
                  title="Descargar PDF"
                  download>
                  <i class="ph-bold ph-download-simple text-lg"></i>
                </a>

                <!-- WhatsApp con PDF -->
                <button
                  onclick="compartirWhatsApp(<?= $proforma['id'] ?>, '<?= htmlspecialchars($proforma['cliente_nombre'] ?? 'Cliente') ?>', '<?= htmlspecialchars($proforma['telefono'] ?? '') ?>', '<?= $proforma['token'] ?? '' ?>')"
                  class="px-2 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-bold hover:bg-green-100 transition flex items-center gap-1 hover:scale-105 transform active:scale-95"
                  title="Compartir por WhatsApp">
                  <i class="ph-bold ph-whatsapp-logo text-lg"></i>
                </a>

                <!-- Eliminar -->
                <button
                  onclick="confirmarEliminar(<?= $proforma['id'] ?>)"
                  class="h-8 w-8 flex items-center justify-center bg-gray-50 text-gray-400 rounded-lg hover:bg-red-50 hover:text-red-500 transition border border-gray-100 hover:scale-105 transform active:scale-95"
                  title="Eliminar">
                  <i class="ph-bold ph-trash text-lg"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>

<!-- Modal de Confirmación de Eliminación -->
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

    <!-- Body del modal -->
    <div class="p-5 space-y-4">
      <!-- Información de la proforma -->
      <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
        <p class="text-slate-600 text-sm mb-2">¿Estás seguro de eliminar esta proforma?</p>
        <div class="flex items-center gap-2">
          <i class="ph-bold ph-file-text text-purple-600"></i>
          <p class="font-bold text-slate-800" id="proformaToDeleteName">Proforma #00001</p>
        </div>
      </div>

      <!-- Mensaje de advertencia -->
      <div class="flex items-start gap-2 text-sm text-slate-600">
        <i class="ph-bold ph-info text-amber-500 mt-0.5"></i>
        <p>Se eliminarán todos los datos relacionados con esta proforma de forma permanente.</p>
      </div>

      <!-- Botones -->
      <div class="flex gap-3">
        <button
          onclick="cerrarModalEliminar()"
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

<script>
// Mostrar notificaciones basadas en mensajes del servidor
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($mensaje)): ?>
    <?php
    $notificaciones = [
      'created' => ['titulo' => '¡Proforma Creada!', 'mensaje' => 'La proforma se guardó exitosamente', 'tipo' => 'success'],
      'deleted' => ['titulo' => 'Eliminada', 'mensaje' => 'Proforma eliminada correctamente', 'tipo' => 'success'],
      'not_found' => ['titulo' => 'Error', 'mensaje' => 'Proforma no encontrada', 'tipo' => 'error'],
      'updated' => ['titulo' => 'Actualizada', 'mensaje' => 'Proforma actualizada exitosamente', 'tipo' => 'success'],
      'error' => ['titulo' => 'Error', 'mensaje' => 'Ocurrió un error al procesar la solicitud', 'tipo' => 'error']
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

// Variable para guardar el ID de la proforma a eliminar
let proformaAEliminar = null;

function confirmarEliminar(id) {
  // Guardar ID
  proformaAEliminar = id;

  // Actualizar nombre en el modal
  const proformaNumero = String(id).padStart(5, '0');
  document.getElementById('proformaToDeleteName').textContent = `Proforma TRA${proformaNumero}`;

  // Mostrar modal
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.remove('hidden');

  // Vibración háptica si está disponible
  if ('vibrate' in navigator) {
    navigator.vibrate(50);
  }
}

function cerrarModalEliminar() {
  const modal = document.getElementById('confirmDeleteModal');
  modal.classList.add('hidden');
  proformaAEliminar = null;
}

function ejecutarEliminacion() {
  if (!proformaAEliminar) return;

  // Mostrar notificación
  notifyInfo('Eliminando', 'Procesando solicitud...', 2000);

  // Redirigir a eliminar
  window.location.href = "<?= url('/proformas/delete/') ?>" + proformaAEliminar;
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modal = document.getElementById('confirmDeleteModal');
    if (!modal.classList.contains('hidden')) {
      cerrarModalEliminar();
    }
  }
});

// Nueva función mejorada para compartir por WhatsApp con PDF
function compartirWhatsApp(id, cliente, telefono, token) {
  // Formatear ID con padding
  const proformaId = String(id).padStart(5, '0');

  // Construir URL pública del PDF (si tiene token, usamos la pública, si no, la privada como fallback)
  let pdfPublicUrl;
  let pdfDownloadUrl;
  
  if (token) {
     pdfPublicUrl = window.location.origin + "<?= url('/p/ver/') ?>" + token;
     pdfDownloadUrl = window.location.origin + "<?= url('/p/dl/') ?>" + token;
  } else {
     pdfPublicUrl = window.location.origin + "<?= url('/proformas/viewPdf/') ?>" + id;
     pdfDownloadUrl = window.location.origin + "<?= url('/proformas/downloadPdf/') ?>" + id;
  }

  // Mensaje personalizado con instrucciones (FORMATO DETALLADO)
  const mensaje = `Hola, te comparto la *Proforma TRA${proformaId}* de *${cliente}*\n\n` +
                  `📄 Ver PDF: ${pdfPublicUrl}\n\n` +
                  `💾 Descargar: ${pdfDownloadUrl}\n\n` +
                  `_Generado con Sistema Proforma Tradimacova_`;

  // Codificar mensaje para URL
  const mensajeCodificado = encodeURIComponent(mensaje);

  // Limpiar teléfono (solo números)
  const telefonoLimpio = telefono.replace(/[^0-9]/g, '');

  // URL de WhatsApp
  let whatsappUrl;
  if (telefonoLimpio && telefonoLimpio.length >= 9) {
    // Si tiene teléfono, ir directo a ese número
    // Agregar código de país si no lo tiene (51 para Perú)
    const numeroCompleto = telefonoLimpio.startsWith('51') ? telefonoLimpio : '51' + telefonoLimpio;
    whatsappUrl = `https://wa.me/${numeroCompleto}?text=${mensajeCodificado}`;
  } else {
    // Si no tiene teléfono, dejar que el usuario elija
    whatsappUrl = `https://wa.me/?text=${mensajeCodificado}`;
  }

  // Notificación
  notifyInfo('WhatsApp', 'Abriendo WhatsApp con link de descarga...', 2000);

  // Abrir WhatsApp
  window.open(whatsappUrl, '_blank');
}

</script>

<?php
$title = 'Nueva Proforma';
$back_url = url('/proformas');
$section = 'proformas';

// Cargar helper para obtener el porcentaje de IGV
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
$igvPercent = App\Helpers\SettingsHelper::getIgvPercent();

include __DIR__ . '/../partials/load_header.php';
?>

<style>
  /* ============================================ */
  /* ANIMACIONES GLOBALES */
  /* ============================================ */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }

  @keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  .animate-slide-in { animation: slideInRight 0.3s ease-out; }
  .animate-slide-out { animation: slideOutRight 0.3s ease-out; }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }
  .animate-pulse-once { animation: pulse 0.4s ease-in-out; }

  /* ANIMACIÓN PRO: ELEVACIÓN ELEGANTE (VERSIÓN AZUL) */
  @keyframes proElevation {
    0% {
      transform: translateY(0);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
      border-color: #e2e8f0;
    }
    100% {
      transform: translateY(-4px);
      box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.15), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
      border-color: #3b82f6; /* Blue-500 */
    }
  }

  @keyframes pulseSubtle {
    0%, 100% { border-color: #3b82f6; } /* Blue-500 */
    50% { border-color: #60a5fa; } /* Blue-400 */
  }

  /* ============================================ */
  /* SISTEMA DE 3 COLORES (NUEVO) */
  /* ============================================ */

  /* 1. Cliente: BLUE (Azul) */
  @keyframes proElevationBlue {
    0% { transform: translateY(0); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-color: #e2e8f0; }
    100% { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.15), 0 8px 10px -6px rgba(37, 99, 235, 0.1); border-color: #3b82f6; }
  }
  .section-active-blue {
    animation: proElevationBlue 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    background-color: #eff6ff !important; /* Blue-50 */
    border-left: 4px solid #2563eb !important; /* Blue-600 */
    position: relative;
    z-index: 30; /* SUPERIOR A MAQUINARIA */
  }

  /* 2. Cotizar: INDIGO (Índigo) */
  @keyframes proElevationIndigo {
    0% { transform: translateY(0); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-color: #e2e8f0; }
    100% { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.15), 0 8px 10px -6px rgba(79, 70, 229, 0.1); border-color: #6366f1; }
  }
  .section-active-indigo {
    animation: proElevationIndigo 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    background-color: #eef2ff !important; /* Indigo-50 */
    border-left: 4px solid #4f46e5 !important; /* Indigo-600 */
    position: relative;
    z-index: 20;
  }

  /* 3. Folleto: PURPLE (Morado) */
  @keyframes proElevationPurple {
    0% { transform: translateY(0); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-color: #e2e8f0; }
    100% { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(147, 51, 234, 0.15), 0 8px 10px -6px rgba(147, 51, 234, 0.1); border-color: #a855f7; }
  }
  .section-active-purple {
    animation: proElevationPurple 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    background-color: #faf5ff !important; /* Purple-50 */
    border-left: 4px solid #9333ea !important; /* Purple-600 */
    position: relative;
    z-index: 20;
  }

  /* Ocultar flechas en inputs numéricos */
  input[type=number]::-webkit-inner-spin-button,
  input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* Hacer las opciones del select más pequeñas */
  .select-compact option {
    font-size: 10px;
    padding: 4px 8px;
  }

  #productSelect option {
    font-size: 10px;
    padding: 4px 8px;
  }
</style>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="max-w-md mx-auto pt-0 px-4 pb-16">

  <form action="<?= url("/proformas/store") ?>" method="POST" enctype="multipart/form-data" id="proformaForm" class="space-y-3 animate-fade-in-up">

    <!-- Hidden fields con valores por defecto -->
    <input type="hidden" name="moneda" value="PEN">
    <input type="hidden" name="subtotal" id="formSubtotal" value="0">
    <input type="hidden" name="igv" id="formIgv" value="0">
    <input type="hidden" name="total" id="formTotal" value="0">

    <?php include __DIR__ . '/partials/section_cliente.php'; ?>
    <?php include __DIR__ . '/partials/section_maquinaria.php'; ?>
    <?php include __DIR__ . '/partials/section_design.php'; ?>
    <?php include __DIR__ . '/partials/modal_phone.php'; ?>

  </form>

  <?php include __DIR__ . '/partials/floating_total.php'; ?>

</main>

<!-- Configuración Global -->
<script>
  window.IGV_PERCENT = <?= $igvPercent ?>;
  window.clientesData = <?= json_encode($clientes) ?>;
  window.folletosDisponibles = <?= $folletos_json ?? '{}' ?>;
</script>

<!-- Módulos JS Refactorizados -->
<!-- Utilidad de compresión -->
<script src="<?= url('/js/utils/image-optimizer.js') ?>"></script>
<!-- NOTE: Using manual timestamp cached busting for dev -->
<script src="<?= url('/js/proformas/ui.js?v=' . time()) ?>"></script>
<script src="<?= url('/js/proformas/calculations.js?v=' . time()) ?>"></script>
<script src="<?= url('/js/proformas/items.js?v=' . time()) ?>"></script>
<script src="<?= url('/js/proformas/search.js?v=' . time()) ?>"></script>
<script src="<?= url('/js/proformas/create.main.js?v=' . time()) ?>"></script>

<script>
// Lógica de Preselección (Dependiente de PHP)
document.addEventListener('DOMContentLoaded', function() {
  // Preseleccionar cliente si viene desde el botón "Cotizar"
  <?php if (isset($cliente_id_preseleccionado) && $cliente_id_preseleccionado): ?>
    const clientePreseleccionadoId = '<?= $cliente_id_preseleccionado ?>';
    const clienteOption = document.querySelector(`.cliente-option[data-id="${clientePreseleccionadoId}"]`);

    if (clienteOption) {
      const nombre = clienteOption.getAttribute('data-nombre');
      const dni = clienteOption.getAttribute('data-dni');

      // Actualizar los campos
      const clienteIdHidden = document.getElementById('clienteIdHidden');
      const clienteSearchInput = document.getElementById('clienteSearchInput');

      if (clienteIdHidden) clienteIdHidden.value = clientePreseleccionadoId;
      if (clienteSearchInput) clienteSearchInput.value = `${nombre} - ${dni}`;

      // Mostrar notificación
      setTimeout(() => {
        if (window.mostrarNotificacion) window.mostrarNotificacion('Cliente Preseleccionado', `${nombre} listo para cotizar`, 'success');
      }, 500);
    }
  <?php endif; ?>

  // Solo agregar si hay producto preseleccionado desde URL
  <?php if (isset($producto_preseleccionado) && $producto_preseleccionado): ?>
    if (window.agregarItemDesdeProducto) {
        window.agregarItemDesdeProducto(<?= json_encode([
          'id' => $producto_preseleccionado['id'],
          'nombre' => $producto_preseleccionado['nombre'],
          'precio' => $producto_preseleccionado['precio'],
          'moneda' => $producto_preseleccionado['moneda'] ?? 'PEN'
        ]) ?>);
    }
  <?php endif; ?>
});
</script>

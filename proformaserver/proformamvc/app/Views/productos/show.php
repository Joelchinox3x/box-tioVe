<?php
$title = 'Detalle del Producto';
$back_url = url('/inventario');
$action_button = [
    'url' => url("/inventario/edit/" . $producto['id']),
    'icon' => 'ph-pencil',
    'label' => 'Editar'
];
// Helper de Configuración
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

$section = 'inventario';

include __DIR__ . '/../partials/load_header.php';
?>
 
<!-- Utilidad de compresión -->
<!-- Utilidad de compresión (Ahora en main.php) -->


<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="max-w-md mx-auto pt-6 px-6 pb-6">

  <!-- Galería de imágenes -->
  <?php if (!empty($producto['imagenes'])): ?>
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 mb-5">
      <div class="aspect-square rounded-xl overflow-hidden mb-3" id="mainImage">
        
        <img src="<?= asset(htmlspecialchars($producto['imagenes'][0] ?? '')) ?>" alt="" class="w-full h-full object-cover">
      </div>

      <?php if (count($producto['imagenes']) > 1): ?>
        <div class="grid grid-cols-4 gap-2">
          <?php foreach ($producto['imagenes'] as $index => $imagen): ?>
            <button type="button" onclick="cambiarImagen('<?= asset(htmlspecialchars($imagen)) ?>')" class="aspect-square rounded-lg overflow-hidden border-2 border-slate-200 hover:border-green-500 transition-colors">
              <img src="<?= asset(htmlspecialchars($imagen)) ?>" alt="" class="w-full h-full object-cover">
            </button>
          <?php endforeach; ?>
          
          <!-- Botón AGREGAR FOTO RÁPIDA -->
        
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Información básica -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
    <div class="flex items-start justify-between mb-4">
      <div class="flex-1">
        <h2 class="text-2xl font-bold text-slate-800 mb-2">
          <?= htmlspecialchars($producto['nombre']) ?>
        </h2>

        <?php if ($producto['modelo']): ?>
          <p class="text-slate-600 mb-1">
            <span class="font-medium">Modelo:</span> <?= htmlspecialchars($producto['modelo']) ?>
          </p>
        <?php endif; ?>

        <?php if ($producto['sku']): ?>
          <p class="text-slate-500 text-sm">
            <span class="font-medium">SKU:</span> <?= htmlspecialchars($producto['sku']) ?>
          </p>
        <?php endif; ?>
      </div>

      <?php if ($producto['protegido']): ?>
        <div class="px-3 py-1 bg-amber-50 border border-amber-200 rounded-lg">
          <i class="ph-bold ph-lock text-amber-600"></i>
        </div>
      <?php endif; ?>
    </div>

    <div class="pt-4 border-t border-slate-100">
      <p class="text-sm text-slate-500 mb-1">Precio</p>
      <p class="text-3xl font-bold text-green-600">
        <?= $producto['moneda'] ?? 'PEN' ?> <?= number_format($producto['precio'], 2) ?>
      </p>
    </div>
  </div>

  <!-- Descripción -->
  <?php if ($producto['descripcion']): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
      <h3 class="font-semibold text-slate-800 mb-3">Descripción</h3>
      <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
    </div>
  <?php endif; ?>

  <!-- Especificaciones técnicas -->
  <?php if (!empty($producto['specs'])): ?>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-5">
      <h3 class="font-semibold text-slate-800 mb-4">Especificaciones Técnicas</h3>

      <div class="space-y-3">
        <?php foreach ($producto['specs'] as $spec): ?>
          <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
            <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($spec['atributo']) ?></span>
            <span class="text-sm text-slate-800 font-semibold"><?= htmlspecialchars($spec['valor']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Información adicional -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-semibold text-slate-800 mb-4">Información del Registro</h3>

    <div class="space-y-2 text-sm">
      <div class="flex justify-between">
        <span class="text-slate-500">Fecha de creación:</span>
        <span class="text-slate-800 font-medium">
          <?= isset($producto['fecha_creacion']) ? date('d/m/Y H:i', strtotime($producto['fecha_creacion'])) : 'N/A' ?>
        </span>
      </div>

      <div class="flex justify-between">
        <span class="text-slate-500">Última modificación:</span>
        <span class="text-slate-800 font-medium">
          <?= isset($producto['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($producto['fecha_modificacion'])) : 'N/A' ?>
        </span>
      </div>

      <div class="flex justify-between">
        <span class="text-slate-500">ID del producto:</span>
        <span class="text-slate-800 font-medium">#<?= $producto['id'] ?></span>
      </div>
    </div>
  </div>

  <!-- Acciones -->
  <div class="mt-6 flex space-x-3">
    <a href="<?= url("/inventario/edit/" . $producto['id']) ?>" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-medium text-center hover:bg-blue-700 transition-colors">
      <i class="ph-bold ph-pencil"></i> Editar
    </a>
    

    <!-- Botón EXPORTAR PAQUETE -->
    <button onclick="openExportModal()" class="px-4 py-3 bg-violet-600 text-white rounded-xl font-medium text-center hover:bg-violet-700 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-violet-200" title="Exportar Paquete">
      <i class="ph-bold ph-export"></i>
    </button>

    <!-- Botón COMPARTIR -->
    <button onclick="compartirProducto()" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium text-center hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
      <i class="ph-bold ph-share-network"></i> Compartir
    </button>

     
      <button onclick="confirmarEliminar()" class="px-4 py-3 bg-red-50 text-red-600 rounded-xl font-medium hover:bg-red-100 transition-colors">
        <i class="ph-bold ph-trash"></i>
      </button>
     
  </div>

</main>

<script>
    // Variables Globales para JS (show_admin.js)
    window.DELETE_URL = "<?= url('/inventario/delete/' . $producto['id']) ?>";
    window.UPLOAD_URL = "<?= url('/inventario/add-photos/' . $producto['id']) ?>";
    window.SHARE_URL = "<?= url('/p/producto/') . ($producto['token'] ?? '') ?>";
    window.SHARE_TITLE = "<?= addslashes($producto['nombre']) ?>";
    window.MANAGER_NAME = "<?= addslashes(SettingsHelper::getManagerName()) ?>";
</script>

<!-- Lógica de Admin (Subida rápida, compartir, borrar) -->
<script src="<?= asset('js/productos/show_admin.js') ?>"></script>

<!-- Modal de Confirmación de Eliminación -->
<?php include __DIR__ . '/partials/delete_modal.php'; ?>

<!-- Modal de Exportación -->
<?php include __DIR__ . '/partials/export_modal.php'; ?>

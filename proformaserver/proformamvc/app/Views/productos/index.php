<?php
$title = 'Inventario';
$subtitle = ($total_equipos ?? 0) . ' equipos';
$action_buttons = [
    [
        'url' => 'javascript:void(0)',
        'onclick' => 'openImportModal()',
        'icon' => 'ph-download-simple', // Icono de importación
        'label' => 'Importar'
    ],
    [
        'url' => url('/inventario/create'),
        'icon' => 'ph-plus',
        'label' => 'Nuevo'
    ]
];
$search = true;
$selection_mode_enabled = false;
$section = 'inventario';
$show_home = true;

// ============================================
// 🎨 COLOR PREDOMINANTE - CAMBIAR AQUÍ
// ============================================
$COLOR_TEMA = 'orange';

include __DIR__ . '/../partials/load_header.php';
?>



<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="pt-6 px-4 pb-6 max-w-md mx-auto">

  <!-- Lista de productos -->
  <div id="productosList" class="space-y-2">
    <?php if (empty($productos)): ?>
      <div class="text-center py-12">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ph-bold ph-package text-slate-400 text-4xl"></i>
        </div>
        <p class="text-slate-500 mb-4">No hay productos en el inventario</p>
        <a href="<?= url('/inventario/create') ?>" class="inline-block px-6 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
          Agregar Primer Producto
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($productos as $producto): ?>
        <?php
          $imagenes = json_decode($producto['imagenes'], true) ?? [];
          $primeraImagen = !empty($imagenes) ? $imagenes[0] : null;
        ?>

      <?php
        // Paleta de colores dinámica según $COLOR_TEMA
        $paletas = [
          'green'  => ['bg' => 'from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100', 'border' => 'border-green-200 hover:border-green-400 shadow-green-100/50', 'barra' => 'from-emerald-500 to-green-500', 'text' => 'group-hover:text-green-600', 'bgIcon' => 'bg-emerald-50 group-hover:bg-emerald-100', 'textIcon' => 'text-green-500 group-hover:text-green-600', 'precio' => 'text-green-600', 'imagen' => 'from-green-500 to-emerald-600', 'overlay' => 'bg-gradient-to-r from-emerald-600 to-green-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-emerald-700 group-hover/foto:to-green-700', 'botonCotizar' => 'from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700'],
          'orange' => ['bg' => 'from-orange-50 to-amber-50 hover:from-orange-100 hover:to-amber-100', 'border' => 'border-orange-200 hover:border-orange-400 shadow-orange-100/50', 'barra' => 'from-orange-500 to-amber-500', 'text' => 'group-hover:text-orange-600', 'bgIcon' => 'bg-orange-50 group-hover:bg-orange-100', 'textIcon' => 'text-orange-500 group-hover:text-orange-600', 'precio' => 'text-orange-600', 'imagen' => 'from-orange-500 to-amber-600', 'overlay' => 'bg-gradient-to-r from-orange-600 to-amber-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-orange-700 group-hover/foto:to-amber-700', 'botonCotizar' => 'from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700'],
          'red'    => ['bg' => 'from-rose-50 to-red-50 hover:from-rose-100 hover:to-red-100', 'border' => 'border-red-200 hover:border-red-400 shadow-red-100/50', 'barra' => 'from-rose-500 to-red-500', 'text' => 'group-hover:text-red-600', 'bgIcon' => 'bg-rose-50 group-hover:bg-rose-100', 'textIcon' => 'text-red-500 group-hover:text-red-600', 'precio' => 'text-red-600', 'imagen' => 'from-red-500 to-rose-600', 'overlay' => 'bg-gradient-to-r from-rose-600 to-red-600 opacity-0 group-hover/foto:opacity-90 group-hover/foto:from-red-700 group-hover/foto:to-rose-700', 'botonCotizar' => 'from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700'],
        ];

        $protegido = $producto['protegido'] ?? false;

        // Si está protegido: rojo. Si no: color del tema
        $textColor  = $protegido ? $paletas['orange']['text'] : $paletas[$COLOR_TEMA]['text'];
        $bgIcon     = $protegido ? $paletas['orange']['bgIcon'] : $paletas[$COLOR_TEMA]['bgIcon'];
        $textIcon   = $protegido ? $paletas['orange']['textIcon'] : $paletas[$COLOR_TEMA]['textIcon'];
        $barraVertical = $protegido ? $paletas['orange']['barra'] : $paletas[$COLOR_TEMA]['barra'];
        $bgCard     = $protegido ? $paletas['orange']['bg'] : $paletas[$COLOR_TEMA]['bg'];
        $borderCard = $protegido ? $paletas['orange']['border'] : $paletas[$COLOR_TEMA]['border'];
        $precioColor = $protegido ? $paletas['orange']['precio'] : $paletas[$COLOR_TEMA]['precio'];
        $imagenGrad = $protegido ? $paletas['orange']['imagen'] : $paletas[$COLOR_TEMA]['imagen'];
        $overlayGrad = $protegido ? $paletas['red']['overlay'] : $paletas[$COLOR_TEMA]['overlay'];
        $botonCotizar = $protegido ? $paletas['orange']['botonCotizar'] : $paletas[$COLOR_TEMA]['botonCotizar'];
      ?>

      <div class="group relative bg-gradient-to-br <?= $bgCard ?> rounded-2xl shadow-sm border <?= $borderCard ?> hover:shadow-lg hover:scale-[1.02] transition-all duration-300 animate-fade-in-up overflow-hidden">

       <!-- Barra vertical de color según estado -->
          <div class="absolute top-0 left-0 bottom-0 w-1 bg-gradient-to-b <?= $barraVertical ?> group-hover:w-1.5 transition-all"></div>

          <!-- Header con info del producto -->
          <div class="pt-3 pr-3 pl-4 pb-1">
            <div class="flex items-center gap-3">

              <!-- Imagen con efecto clickeable -->
              <?php if ($protegido): ?>
                <!-- Producto protegido - muestra mensaje al hacer click -->
                <button onclick="mostrarToastProtegido()" class="relative w-16 h-16 flex-shrink-0 group/foto cursor-pointer">
                  <?php if ($primeraImagen): ?>
                    <img src="<?= asset(htmlspecialchars($primeraImagen)) ?>" alt="" class="w-full h-full rounded-xl object-cover ring-2 ring-white shadow-md">
                  <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br <?= $imagenGrad ?> rounded-xl flex items-center justify-center shadow-md transition-transform duration-300">
                      <i class="ph-bold ph-package text-white text-2xl"></i>
                    </div>
                  <?php endif; ?>
                  <!-- Overlay con icono de candado en hover -->
                  <div class="absolute inset-0 <?= $overlayGrad ?> rounded-xl flex items-center justify-center transition-all duration-300">
                    <i class="ph-bold ph-lock text-white text-2xl opacity-0 group-hover/foto:opacity-100 transition-opacity duration-300"></i>
                  </div>
                </button>
              <?php else: ?>
                <!-- Producto desbloqueado - va a editar -->
                <a href="<?= url('/inventario/edit/' . $producto['id']) ?>" class="relative w-16 h-16 flex-shrink-0 group/foto cursor-pointer">
                  <?php if ($primeraImagen): ?>
                    <img src="<?= asset(htmlspecialchars($primeraImagen)) ?>" alt="" class="w-full h-full rounded-xl object-cover ring-2 ring-white shadow-md">
                  <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br <?= $imagenGrad ?> rounded-xl flex items-center justify-center shadow-md transition-transform duration-300">
                      <i class="ph-bold ph-package text-white text-2xl"></i>
                    </div>
                  <?php endif; ?>
                  <!-- Overlay con icono de editar en hover -->
                  <div class="absolute inset-0 <?= $overlayGrad ?> rounded-xl flex items-center justify-center transition-all duration-300">
                    <i class="ph-bold ph-pencil text-white text-2xl opacity-0 group-hover/foto:opacity-100 transition-opacity duration-300"></i>
                  </div>
                </a>
              <?php endif; ?>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-900 text-base mb-1 truncate <?= $textColor ?> transition-colors">
                  <?= htmlspecialchars($producto['nombre']) ?>
                </h3>

                <div class="space-y-0.5">
                  <div class="flex items-center gap-2 divide-x divide-slate-200 text-xs text-slate-500">
                      <?php if ($producto['modelo']): ?>
                          <div class="flex items-center gap-1.5">
                              <span class="w-4 h-4 <?= $bgIcon ?> rounded flex items-center justify-center flex-shrink-0">
                                  <i class="ph ph-cube text-[16px] <?= $textIcon ?>"></i>
                              </span>
                              <span class="font-medium text-slate-700"><?= htmlspecialchars($producto['modelo']) ?></span>
                          </div>
                      <?php endif; ?>

                      <?php if ($producto['sku']): ?>
                          <div class="flex items-center gap-1.5 <?= $producto['modelo'] ? 'pl-2' : '' ?>">
                              <span class="w-4 h-4 <?= $bgIcon ?> rounded flex items-center justify-center flex-shrink-0">
                                  <i class="ph ph-barcode text-[16px] <?= $textIcon ?>"></i>
                              </span>
                              <span class="font-medium text-slate-700"><?= htmlspecialchars($producto['sku']) ?></span>
                          </div>
                      <?php endif; ?>

                       <?php if ($protegido): ?>
                          <span class="flex items-center gap-1.5 w-8 h-8 rounded-full text-red-500"
                                title="Producto Protegido"
                                onclick="mostrarToast('Producto bloqueado', 'error')">
                              <i class="ph-bold ph-lock text-sm"></i>
                          </span>
                      <?php endif; ?>

                  </div>
                  <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                      <p class="text-xl font-extrabold <?= $precioColor ?> tracking-tight">
                          <?= $producto['moneda'] ?? 'PEN' ?> <?= number_format($producto['precio'], 0) ?>
                      </p>

                     
                    </div>

                    <!-- Botón Cotizar -->
                    <a href="<?= url('/proformas/create?producto_id=' . $producto['id']) ?>"
                       class="flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gradient-to-r <?= $botonCotizar ?> text-white rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-lg text-xs whitespace-nowrap">
                      
                       <i class="ph-bold ph-file-text text-sm"></i>
                      <span>Cotizar</span>
                    </a>
                  </div>

                  </p>
                </div>
              </div>

              <!-- Acciones superiores -->
              <div class="flex flex-col gap-1">
                <!-- Botón Ver (ojito) -->
                <a href="<?= url('/inventario/detalle/' . $producto['id']) ?>" class="w-7 h-7 bg-gradient-to-br from-purple-500 to-violet-600 hover:from-purple-600 hover:to-violet-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                  <i class="ph-bold ph-eye text-white text-xs"></i>
                </a>
                <!-- Botón Editar -->
                <a href="<?= url("/inventario/edit/" . $producto['id']) ?>" class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
                  <i class="ph-bold ph-pencil text-white text-xs"></i>
                </a>

                <!-- Botón Eliminar o Candado -->
                <?php if ($protegido): ?>
                  <button onclick="mostrarToastProtegido()" class="w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110 cursor-pointer">
                    <i class="ph-bold ph-lock text-white text-xs"></i>
                  </button>
                <?php else: ?>
                  <button onclick="confirmarEliminar(<?= $producto['id'] ?>, '<?= htmlspecialchars($producto['nombre']) ?>')" class="w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-lg flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md hover:scale-110">
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

<!-- Modal de Confirmación de Eliminación (Refactorizado) -->
<?php include __DIR__ . '/partials/delete_modal.php'; ?>
<!-- Modal de Importación -->
<?php include __DIR__ . '/partials/import_modal.php'; ?>

<!-- Configuración y Lógica Global -->
<script>
    // Configuración para index_logic.js
    window.COLOR_TEMA = "<?= $COLOR_TEMA ?>";
    window.SEARCH_URL = "<?= url('/inventario/search') ?>";
    window.DELETE_URL_BASE = "<?= url('/inventario/delete') ?>"; // Sin ID
    window.PROFORMA_URL = "<?= url('/proformas/create') ?>";
    window.ASSET_URL = "<?= asset('/') ?>";

    // Inyección de Notificaciones PHP
    <?php if ($mensaje ?? false): ?>
        <?php
        $notificaciones = [
            'created' => ['titulo' => 'Verificado', 'mensaje' => 'El producto se guardó exitosamente', 'tipo' => 'success'],
            'updated' => ['titulo' => 'Actualizado', 'mensaje' => 'Producto actualizado exitosamente', 'tipo' => 'success'],
            'deleted' => ['titulo' => 'Eliminado', 'mensaje' => 'Producto eliminado correctamente', 'tipo' => 'success'],
            'imported' => ['titulo' => 'Importación Exitosa', 'mensaje' => 'Producto importado y restaurado correctamente', 'tipo' => 'success'],
            'locked' => ['titulo' => 'Protegido', 'mensaje' => 'Este producto está protegido', 'tipo' => 'warning'],
            'not_found' => ['titulo' => 'Error', 'mensaje' => 'Producto no encontrado', 'tipo' => 'error']
        ];
        $notif = $notificaciones[$mensaje] ?? null;
        ?>
        <?php if ($notif): ?>
            window.PHP_NOTIFICATION = <?= json_encode($notif) ?>;
        <?php endif; ?>
    <?php endif; ?>
</script>

<script src="<?= asset('js/productos/index_logic.js') ?>"></script>

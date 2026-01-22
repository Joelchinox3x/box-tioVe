<?php
// navbar.php - BARRA DE NAVEGACIÓN CONTEXTUAL

// 1. Detectar en qué archivo estamos
$pag_actual = basename($_SERVER['PHP_SELF']);

// 2. Definir estados por defecto (Proforma)
$btn_central_url = 'agregar_proforma.php';
$btn_central_icon = 'ph-plus'; // Icono por defecto
$btn_color_class = 'from-blue-600 to-blue-500 shadow-blue-500/40'; // Color azul por defecto

// 3. Lógica para cambiar el botón según la página
switch ($pag_actual) {
    
    // CASO: ESTOY EN CLIENTES
    case 'clientes.php':
    case 'editar_cliente.php': // Si tuvieras esta página
        $btn_central_url = 'clientes.php?action=new';
        $btn_color_class = 'from-green-600 to-green-500 shadow-green-500/40'; // Se pone verde
        break;

    // CASO: ESTOY EN INVENTARIO
    case 'inventario.php':
    case 'agregar_equipos.php':
        $btn_central_url = 'agregar_equipos.php?action=new';
        $btn_color_class = 'from-orange-600 to-orange-500 shadow-orange-500/40'; // Se pone naranja
        break;

    // CASO: ESTOY EN PROFORMAS O INICIO (Mantenemos el default)
    default:
        $btn_central_url = 'agregar_proforma.php';
        $btn_color_class = 'from-blue-600 to-blue-500 shadow-blue-500/40'; // Azul
        break;
}

// Clases para los botones normales (Activo / Inactivo)
$clase_activo   = "bg-slate-900 text-white shadow-lg scale-110"; 
$clase_inactivo = "text-slate-400 transition-colors hover:scale-105";

// Lógica extra para mantener activo el botón de inventario si estamos agregando equipo
$es_inventario = ($pag_actual == 'inventario.php' || $pag_actual == 'agregar_equipos.php');
?>

<nav class="fixed bottom-0 left-0 w-full z-50 pointer-events-none">
  <div class="max-w-[90%] md:max-w-sm mx-auto bg-white/90 backdrop-blur-lg border border-white/40 shadow-2xl shadow-slate-300/50 rounded-full px-2 py-2 flex justify-between items-center pointer-events-auto">
      
      <a href="index.php" class="w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= ($pag_actual == 'index.php') ? $clase_activo : $clase_inactivo . ' hover:text-blue-600' ?>">
          <i class="<?= ($pag_actual == 'index.php') ? 'ph-fill' : 'ph-bold' ?> ph-house text-xl"></i>
      </a>

      <a href="proformas.php" class="w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= ($pag_actual == 'proformas.php') ? $clase_activo : $clase_inactivo . ' hover:text-blue-600' ?>">
          <i class="<?= ($pag_actual == 'proformas.php') ? 'ph-fill' : 'ph-bold' ?> ph-files text-xl"></i>
      </a>
      
      <a href="<?= $btn_central_url ?>" 
         class="relative -top-6 bg-gradient-to-r <?= $btn_color_class ?> text-white w-14 h-14 rounded-full flex items-center justify-center shadow-xl border-4 border-[#F8FAFC] transform transition hover:scale-105 active:scale-95 hover:rotate-90 duration-300 z-50">
          <i class="ph-bold <?= $btn_central_icon ?> text-2xl"></i>
      </a>
      
      <a href="clientes.php" class="w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= ($pag_actual == 'clientes.php') ? $clase_activo : $clase_inactivo . ' hover:text-green-600' ?>">
          <i class="<?= ($pag_actual == 'clientes.php') ? 'ph-fill' : 'ph-bold' ?> ph-users text-xl"></i>
      </a>

      <a href="inventario.php" class="w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= $es_inventario ? $clase_activo : $clase_inactivo . ' hover:text-orange-600' ?>">
          <i class="<?= $es_inventario ? 'ph-fill' : 'ph-bold' ?> ph-tractor text-xl"></i>
      </a>

  </div>
</nav>
<?php
// navbar.php - BARRA DE NAVEGACIÓN ULTRA PREMIUM CON IA SUPERIOR

// 1. Detectar en qué página estamos
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Configuración inteligente del botón central
$btn_central_url = url('/proformas/create');
$btn_central_icon = 'ph-plus';
$btn_color_class = 'from-slate-600 to-blue-600';
$btn_shadow = 'shadow-slate-500/50';
$btn_glow = 'hover:shadow-slate-500/80';
$nav_accent = 'slate';

// 3. Lógica contextual mejorada
if (strpos($currentPath, '/clientes') !== false) {
    $btn_central_url = url('/clientes/create');
    $btn_color_class = 'from-emerald-500 to-green-600';
    $btn_shadow = 'shadow-emerald-500/50';
    $btn_glow = 'hover:shadow-emerald-500/80';
    $nav_accent = 'emerald';
} elseif (strpos($currentPath, '/inventario') !== false) {
    $btn_central_url = url('/inventario/create');
    $btn_color_class = 'from-orange-500 to-amber-600';
    $btn_shadow = 'shadow-orange-500/50';
    $btn_glow = 'hover:shadow-orange-500/80';
    $nav_accent = 'orange';
} elseif (strpos($currentPath, '/proformas') !== false) {
    $btn_central_url = url('/proformas/create');
    $btn_color_class = 'from-blue-600 to-slate-600';
    $btn_shadow = 'shadow-blue-500/50';
    $btn_glow = 'hover:shadow-blue-500/80';
    $nav_accent = 'blue';
}

// Verificar secciones activas
$es_home = ($currentPath === url('/') || $currentPath === '/');
$es_proformas = (strpos($currentPath, '/proformas') !== false);
$es_clientes = (strpos($currentPath, '/clientes') !== false);
$es_inventario = (strpos($currentPath, '/inventario') !== false);
?>

<!-- Estilos CSS inline para animaciones premium -->
<style>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-4px); }
}

@keyframes pulse-ring {
  0% { transform: scale(0.95); opacity: 1; }
  100% { transform: scale(1.3); opacity: 0; }
}

@keyframes ripple {
  0% { transform: scale(0.8); opacity: 1; }
  100% { transform: scale(2.5); opacity: 0; }
}

.nav-btn-active {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  box-shadow: 0 8px 20px -4px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1);
}

.nav-btn {
  position: relative;
  overflow: hidden;
}

.nav-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.nav-btn:active::before {
  width: 120%;
  height: 120%;
}

.central-btn {
  animation: float 3s ease-in-out infinite;
  position: relative;
}

.central-btn::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: inherit;
  filter: blur(12px);
  opacity: 0.6;
  z-index: -1;
  animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.central-btn:hover {
  animation: none;
  transform: scale(1.15) rotate(90deg);
}

.central-btn:active {
  transform: scale(0.95) rotate(90deg);
}

/* Efecto de brillo en hover */
.nav-btn:hover .nav-icon {
  filter: drop-shadow(0 0 8px currentColor);
}

/* Indicador de notificación (para futuras features) */
.notification-dot {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border-radius: 50%;
  border: 2px solid white;
  animation: pulse-ring 2s infinite;
}

/* Glassmorphism mejorado */
.nav-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.08),
    0 2px 8px rgba(0, 0, 0, 0.04),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

/* Efecto de luz ambiental según la sección */
.nav-container.accent-violet { box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15), 0 0 0 1px rgba(139, 92, 246, 0.1); }
.nav-container.accent-emerald { box-shadow: 0 8px 32px rgba(16, 185, 129, 0.15), 0 0 0 1px rgba(16, 185, 129, 0.1); }
.nav-container.accent-orange { box-shadow: 0 8px 32px rgba(221, 2, 2, 0.15), 0 0 0 1px rgba(251, 146, 60, 0.1); }

/* Transición suave al cambiar de página */
.nav-transition {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<nav class="fixed bottom-0 left-0 w-full z-50 pointer-events-none pb-safe translate-y-4">
  <div class="max-w-[min(90%,440px)] mx-auto px-2 pb-4">
    <!-- Contenedor principal con glassmorphism -->
    <div class="nav-container accent-<?= $nav_accent ?> nav-transition rounded-full px-3 py-2.5 flex justify-between items-center pointer-events-auto relative">

      <!-- Indicador de sección activa (barra inferior) -->
      <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r <?= $btn_color_class ?> rounded-full opacity-50"></div>

      <!-- Home -->
      <a href="<?= url('/') ?>"
         class="nav-btn group relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= $es_home ? 'nav-btn-active scale-110' : 'hover:bg-slate-100/80 hover:scale-105' ?> active:scale-95"
         aria-label="Inicio">
        <i class="nav-icon <?= $es_home ? 'ph-fill' : 'ph-bold' ?> ph-house text-xl transition-all duration-300 <?= $es_home ? 'text-white' : 'text-slate-500 group-hover:text-blue-600' ?>"></i>
        <?php if ($es_home): ?>
          <span class="absolute -bottom-1 w-1 h-1 bg-white rounded-full"></span>
        <?php endif; ?>
      </a>

      <!-- Proformas -->
      <a href="<?= url('/proformas') ?>"
         class="nav-btn group relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= $es_proformas ? 'nav-btn-active scale-110' : 'hover:bg-slate-100/80 hover:scale-105' ?> active:scale-95"
         aria-label="Proformas">
        <i class="nav-icon <?= $es_proformas ? 'ph-fill' : 'ph-bold' ?> ph-file-text text-xl transition-all duration-300 <?= $es_proformas ? 'text-white' : 'text-slate-500 group-hover:text-blue-600' ?>"></i>
        <?php if ($es_proformas): ?>
          <span class="absolute -bottom-1 w-1 h-1 bg-white rounded-full"></span>
        <?php endif; ?>
        <!-- Ejemplo de badge de notificación (descomentado si hay nuevas proformas) -->
        <!-- <span class="notification-dot"></span> -->
      </a>

      <!-- BOTÓN CENTRAL FLOTANTE ÉPICO -->
      <a href="<?= $btn_central_url ?>"
         class="central-btn relative -top-7 bg-gradient-to-br <?= $btn_color_class ?> text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl <?= $btn_shadow ?> <?= $btn_glow ?> border-[3px] border-white/90 transform transition-all duration-500 group z-10"
         aria-label="Crear nuevo">
        <!-- Anillo exterior animado -->
        <div class="absolute inset-0 rounded-full bg-gradient-to-br <?= $btn_color_class ?> opacity-20 blur-md group-hover:opacity-40 transition-opacity duration-300"></div>

        <!-- Icono con efecto de rotación -->
        <i class="ph-bold <?= $btn_central_icon ?> text-3xl relative z-10 transition-transform duration-500 group-hover:rotate-90"></i>

        <!-- Efecto de resplandor en hover -->
        <div class="absolute inset-0 rounded-full bg-white/0 group-hover:bg-white/20 transition-all duration-300"></div>

        <!-- Mini pulso decorativo -->
        <div class="absolute -inset-1 rounded-full bg-gradient-to-br <?= $btn_color_class ?> opacity-0 group-hover:opacity-30 blur-lg transition-opacity duration-500"></div>
      </a>

      <!-- Clientes -->
      <a href="<?= url('/clientes') ?>"
         class="nav-btn group relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= $es_clientes ? 'nav-btn-active scale-110' : 'hover:bg-slate-100/80 hover:scale-105' ?> active:scale-95"
         aria-label="Clientes">
        <i class="nav-icon <?= $es_clientes ? 'ph-fill' : 'ph-bold' ?> ph-users text-xl transition-all duration-300 <?= $es_clientes ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' ?>"></i>
        <?php if ($es_clientes): ?>
          <span class="absolute -bottom-1 w-1 h-1 bg-white rounded-full"></span>
        <?php endif; ?>
      </a>

      <!-- Inventario -->
      <a href="<?= url('/inventario') ?>"
         class="nav-btn group relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?= $es_inventario ? 'nav-btn-active scale-110' : 'hover:bg-slate-100/80 hover:scale-105' ?> active:scale-95"
         aria-label="Inventario">
        <i class="nav-icon <?= $es_inventario ? 'ph-fill' : 'ph-bold' ?> ph-package text-xl transition-all duration-300 <?= $es_inventario ? 'text-white' : 'text-slate-500 group-hover:text-orange-600' ?>"></i>
        <?php if ($es_inventario): ?>
          <span class="absolute -bottom-1 w-1 h-1 bg-white rounded-full"></span>
        <?php endif; ?>
      </a>

    </div>

    <!-- Indicador de vibración háptica simulada (opcional) -->
    <script>
      // Feedback háptico mejorado al tocar botones
      document.querySelectorAll('.nav-btn, .central-btn').forEach(btn => {
        btn.addEventListener('touchstart', function() {
          if ('vibrate' in navigator) {
            navigator.vibrate(10);
          }
        });

        // Efecto ripple al hacer clic
        btn.addEventListener('click', function(e) {
          const ripple = document.createElement('div');
          ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            width: 20px;
            height: 20px;
            top: ${e.offsetY - 10}px;
            left: ${e.offsetX - 10}px;
            animation: ripple 0.6s ease-out;
            pointer-events: none;
          `;
          this.appendChild(ripple);
          setTimeout(() => ripple.remove(), 600);
        });
      });

      // Prevenir scroll accidental del navbar
      document.querySelector('nav').addEventListener('touchmove', function(e) {
        e.preventDefault();
      }, { passive: false });
    </script>

  </div>
</nav>
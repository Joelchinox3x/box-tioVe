<?php
// navbar_v2.php - NAVBAR MINIMALISTA CON BARRA DESLIZANTE
// Diseño alternativo con indicador animado y estilo moderno

// 1. Detectar en qué página estamos
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Configuración de secciones
$sections = [
    'home' => [
        'url' => url('/'),
        'icon' => 'ph-house',
        'label' => 'Inicio',
        'color' => 'slate',
        'gradient' => 'from-slate-600 to-slate-800',
        'active' => ($currentPath === url('/') || $currentPath === '/'),
    ],
    'proformas' => [
        'url' => url('/proformas'),
        'icon' => 'ph-file-text',
        'label' => 'Proformas',
        'color' => 'blue',
        'gradient' => 'from-blue-500 to-blue-700',
        'active' => (strpos($currentPath, '/proformas') !== false),
    ],
    'clientes' => [
        'url' => url('/clientes'),
        'icon' => 'ph-users',
        'label' => 'Clientes',
        'color' => 'emerald',
        'gradient' => 'from-emerald-500 to-emerald-700',
        'active' => (strpos($currentPath, '/clientes') !== false),
    ],
    'inventario' => [
        'url' => url('/inventario'),
        'icon' => 'ph-package',
        'label' => 'Inventario',
        'color' => 'orange',
        'gradient' => 'from-orange-500 to-orange-700',
        'active' => (strpos($currentPath, '/inventario') !== false),
    ],
];

// Encontrar sección activa
$activeSection = 'home';
$activeIndex = 0;
foreach ($sections as $key => $section) {
    if ($section['active']) {
        $activeSection = $key;
        break;
    }
    $activeIndex++;
}

// Configurar botón flotante según sección activa
$fab_configs = [
    'home' => ['url' => url('/proformas/create'), 'icon' => 'ph-plus', 'color' => 'from-blue-500 to-blue-700'],
    'proformas' => ['url' => url('/proformas/create'), 'icon' => 'ph-plus', 'color' => 'from-blue-500 to-blue-700'],
    'clientes' => ['url' => url('/clientes/create'), 'icon' => 'ph-user-plus', 'color' => 'from-emerald-500 to-emerald-700'],
    'inventario' => ['url' => url('/inventario/create'), 'icon' => 'ph-plus-circle', 'color' => 'from-orange-500 to-orange-700'],
];

$fab = $fab_configs[$activeSection];
?>

<!-- Estilos para navbar v2 -->
<style>
/* Barra deslizante animada */
@keyframes slide-indicator {
  from { transform: translateX(var(--from-position)); }
  to { transform: translateX(var(--to-position)); }
}

.nav-indicator {
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
              width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Animación de rebote para iconos activos */
@keyframes bounce-icon {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.nav-item-active .nav-icon {
  animation: bounce-icon 0.6s ease-out;
}

/* Efecto de onda al tocar */
@keyframes ripple-wave {
  0% {
    transform: scale(0);
    opacity: 0.8;
  }
  100% {
    transform: scale(2.5);
    opacity: 0;
  }
}

/* Glassmorphism suave */
.nav-glass {
  background: linear-gradient(135deg,
    rgba(255, 255, 255, 0.98) 0%,
    rgba(248, 250, 252, 0.95) 100%
  );
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  box-shadow:
    0 -8px 32px rgba(0, 0, 0, 0.08),
    0 -2px 8px rgba(0, 0, 0, 0.04),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
  border-top: 1px solid rgba(255, 255, 255, 0.8);
}

/* FAB con sombra dinámica */
.fab-button {
  box-shadow:
    0 12px 40px -8px currentColor,
    0 4px 12px rgba(0, 0, 0, 0.2);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.fab-button:hover {
  transform: scale(1.1) rotate(90deg);
  box-shadow:
    0 16px 48px -4px currentColor,
    0 8px 20px rgba(0, 0, 0, 0.25);
}

.fab-button:active {
  transform: scale(0.95) rotate(90deg);
}

/* Etiqueta tooltip */
.nav-label {
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  pointer-events: none;
}

.nav-item:hover .nav-label {
  opacity: 1;
  transform: translateY(0);
}

/* Pulso en botón activo */
@keyframes pulse-soft {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.2); }
}

.nav-pulse {
  animation: pulse-soft 2s ease-in-out infinite;
}

/* Contenedor centrado */
.nav-centered {
  max-width: min(92%, 480px);
}
</style>

<!-- NAVBAR V2 - MINIMALISTA CON BARRA DESLIZANTE -->
<div class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none flex justify-center translate-y-9">
  <nav class="nav-centered w-full px-4 pb-safe pointer-events-auto mb-4">

    <!-- Contenedor principal -->
    <div class="nav-glass rounded-3xl px-2 py-3 relative shadow-2xl">

      <!-- Barra indicadora deslizante -->
      <div class="absolute top-0 left-0 right-0 h-1 overflow-hidden rounded-t-3xl">
        <div class="nav-indicator absolute top-0 h-full w-1/4 bg-gradient-to-r <?= $sections[$activeSection]['gradient'] ?> rounded-full shadow-lg"
             style="left: calc(<?= $activeIndex * 25 ?>% + 0.5rem); width: calc(25% - 1rem);"></div>
      </div>

      <!-- Grid de navegación -->
      <div class="grid grid-cols-4 gap-1 relative mt-2">

        <?php foreach ($sections as $key => $section): ?>
          <a href="<?= $section['url'] ?>"
             class="nav-item group relative flex flex-col items-center justify-center py-2 px-2 rounded-2xl transition-all duration-300 <?= $section['active'] ? 'nav-item-active' : 'hover:bg-slate-100/60' ?>"
             data-section="<?= $key ?>">

            <!-- Icono -->
            <div class="relative mb-1">
              <?php if ($section['active']): ?>
                <!-- Pulso decorativo para activo -->
                <div class="absolute inset-0 bg-gradient-to-r <?= $section['gradient'] ?> rounded-full opacity-20 blur-md nav-pulse"></div>
              <?php endif; ?>

              <i class="nav-icon <?= $section['active'] ? 'ph-fill' : 'ph-bold' ?> <?= $section['icon'] ?> text-2xl transition-all duration-300 relative z-10 <?= $section['active'] ? 'text-'.$section['color'].'-600' : 'text-slate-500 group-hover:text-'.$section['color'].'-600' ?>"></i>
            </div>

            <!-- Label -->
            <span class="text-[10px] font-semibold transition-all duration-300 <?= $section['active'] ? 'text-'.$section['color'].'-600' : 'text-slate-500 group-hover:text-'.$section['color'].'-600' ?>">
              <?= $section['label'] ?>
            </span>

            <!-- Punto indicador activo -->
            <?php if ($section['active']): ?>
              <div class="absolute -bottom-1 w-1.5 h-1.5 bg-gradient-to-r <?= $section['gradient'] ?> rounded-full shadow-lg"></div>
            <?php endif; ?>

            <!-- Efecto ripple al hacer clic -->
            <div class="ripple-container absolute inset-0 rounded-2xl overflow-hidden pointer-events-none"></div>
          </a>
        <?php endforeach; ?>

      </div>

    </div>

  </nav>

  <!-- FLOATING ACTION BUTTON (FAB) -->
  <a href="<?= $fab['url'] ?>"
     class="fab-button fixed bottom-24 right-6 w-14 h-14 bg-gradient-to-br <?= $fab['color'] ?> rounded-full flex items-center justify-center pointer-events-auto group z-50"
     style="color: <?= $sections[$activeSection]['color'] === 'blue' ? 'rgba(59, 130, 246, 0.5)' : ($sections[$activeSection]['color'] === 'emerald' ? 'rgba(16, 185, 129, 0.5)' : 'rgba(251, 146, 60, 0.5)') ?>">

    <!-- Anillo exterior -->
    <div class="absolute inset-0 rounded-full border-4 border-white/30 group-hover:border-white/50 transition-all duration-300"></div>

    <!-- Icono -->
    <i class="ph-bold <?= $fab['icon'] ?> text-white text-2xl relative z-10 transition-transform duration-500 group-hover:rotate-180"></i>

    <!-- Sombra inferior -->
    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-10 h-2 bg-black/20 rounded-full blur-md"></div>
  </a>
</div>

<!-- Script para animaciones interactivas -->
<script>
(function() {
  const navItems = document.querySelectorAll('.nav-item');

  // Efecto ripple al hacer clic
  navItems.forEach(item => {
    item.addEventListener('click', function(e) {
      const rippleContainer = this.querySelector('.ripple-container');
      const ripple = document.createElement('div');

      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      ripple.style.cssText = `
        position: absolute;
        left: ${x}px;
        top: ${y}px;
        width: 10px;
        height: 10px;
        background: rgba(59, 130, 246, 0.4);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        animation: ripple-wave 0.6s ease-out;
        pointer-events: none;
      `;

      rippleContainer.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });

    // Vibración háptica en dispositivos móviles
    item.addEventListener('touchstart', function() {
      if ('vibrate' in navigator) {
        navigator.vibrate(8);
      }
    });
  });

  // Animación del FAB
  const fab = document.querySelector('.fab-button');
  if (fab) {
    fab.addEventListener('touchstart', function() {
      if ('vibrate' in navigator) {
        navigator.vibrate([10, 5, 10]);
      }
    });
  }

  // Actualizar indicador al cambiar de página (SPA-like)
  const updateIndicator = () => {
    const active = document.querySelector('.nav-item-active');
    const indicator = document.querySelector('.nav-indicator');

    if (active && indicator) {
      const parent = active.parentElement;
      const index = Array.from(parent.children).indexOf(active);
      indicator.style.left = `calc(${index * 25}% + 0.5rem)`;
    }
  };

  // Ejecutar al cargar
  updateIndicator();
})();
</script>

<style>
/* Safe area para dispositivos con notch */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .pb-safe {
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
  }
}

/* Prevenir scroll en el navbar */
nav {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  user-select: none;
}
</style>

<?php
// navbar_v3.php - NAVBAR DOCK ESTILO MACOS/IOS
// Diseño con iconos grandes, efecto de magnificación y blur intenso

// 1. Detectar en qué página estamos
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Configuración de elementos del dock
$dock_items = [
    'home' => [
        'url' => url('/'),
        'icon' => 'ph-house-line',
        'icon_fill' => 'ph-house',
        'label' => 'Inicio',
        'color_from' => '#64748b',
        'color_to' => '#475569',
        'bg_class' => 'from-slate-500 to-slate-700',
        'active' => ($currentPath === url('/') || $currentPath === '/'),
    ],
    'proformas' => [
        'url' => url('/proformas'),
        'icon' => 'ph-file-text',
        'icon_fill' => 'ph-file-text',
        'label' => 'Proformas',
        'color_from' => '#3b82f6',
        'color_to' => '#1d4ed8',
        'bg_class' => 'from-blue-500 to-blue-700',
        'active' => (strpos($currentPath, '/proformas') !== false),
    ],
    'clientes' => [
        'url' => url('/clientes'),
        'icon' => 'ph-users-three',
        'icon_fill' => 'ph-users-three',
        'label' => 'Clientes',
        'color_from' => '#10b981',
        'color_to' => '#059669',
        'bg_class' => 'from-emerald-500 to-emerald-700',
        'active' => (strpos($currentPath, '/clientes') !== false),
    ],
    'inventario' => [
        'url' => url('/inventario'),
        'icon' => 'ph-package',
        'icon_fill' => 'ph-package',
        'label' => 'Inventario',
        'color_from' => '#f97316',
        'color_to' => '#ea580c',
        'bg_class' => 'from-orange-500 to-orange-700',
        'active' => (strpos($currentPath, '/inventario') !== false),
    ],
    'divider' => 'divider', // Separador visual
    'new' => [
        'url' => url('/proformas/create'),
        'icon' => 'ph-plus-circle',
        'icon_fill' => 'ph-plus-circle',
        'label' => 'Nuevo',
        'color_from' => '#8b5cf6',
        'color_to' => '#7c3aed',
        'bg_class' => 'from-violet-500 to-violet-700',
        'active' => false,
        'special' => true,
    ],
];

// Ajustar URL del botón "Nuevo" según contexto
if (strpos($currentPath, '/clientes') !== false) {
    $dock_items['new']['url'] = url('/clientes/create');
    $dock_items['new']['icon'] = 'ph-user-plus';
} elseif (strpos($currentPath, '/inventario') !== false) {
    $dock_items['new']['url'] = url('/inventario/create');
    $dock_items['new']['icon'] = 'ph-plus-square';
}
?>

<!-- Estilos para navbar dock v3 -->
<style>
/* Dock container con glassmorphism extremo */
.dock-container {
  background: linear-gradient(135deg,
    rgba(255, 255, 255, 0.25) 0%,
    rgba(255, 255, 255, 0.15) 100%
  );
  backdrop-filter: blur(40px) saturate(200%);
  -webkit-backdrop-filter: blur(40px) saturate(200%);
  border: 1.5px solid rgba(255, 255, 255, 0.4);
  box-shadow:
    0 20px 60px -10px rgba(0, 0, 0, 0.3),
    0 10px 30px -5px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.6),
    inset 0 -1px 0 rgba(0, 0, 0, 0.1);
}

/* Efecto de magnificación al hover (estilo macOS Dock) */
.dock-item {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  transform-origin: bottom center;
}

.dock-item:hover {
  transform: scale(1.3) translateY(-10px);
  z-index: 10;
}

/* Magnify effect para items adyacentes */
.dock-items:hover .dock-item:not(:hover) {
  transform: scale(0.95);
  opacity: 0.8;
}

/* Iconos con gradiente y sombra */
.dock-icon {
  background: linear-gradient(135deg, var(--icon-color-from), var(--icon-color-to));
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  transition: all 0.3s ease;
}

.dock-item:hover .dock-icon {
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
}

/* Indicador activo con glow */
.active-indicator {
  background: linear-gradient(90deg,
    transparent 0%,
    currentColor 50%,
    transparent 100%
  );
  box-shadow: 0 0 20px currentColor;
  animation: glow-pulse 2s ease-in-out infinite;
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.6; box-shadow: 0 0 15px currentColor; }
  50% { opacity: 1; box-shadow: 0 0 25px currentColor; }
}

/* Label flotante estilo tooltip */
.dock-label {
  opacity: 0;
  transform: translateY(10px) scale(0.9);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  pointer-events: none;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(12px);
}

.dock-item:hover .dock-label {
  opacity: 1;
  transform: translateY(0) scale(1);
}

/* Separador vertical */
.dock-divider {
  width: 2px;
  height: 40px;
  background: linear-gradient(180deg,
    transparent 0%,
    rgba(255, 255, 255, 0.5) 20%,
    rgba(255, 255, 255, 0.5) 80%,
    transparent 100%
  );
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
  margin: 0 8px;
}

/* Efecto de reflejo inferior (espejo) */
.dock-reflection {
  position: absolute;
  bottom: -100%;
  left: 0;
  right: 0;
  height: 100%;
  background: inherit;
  opacity: 0.15;
  transform: scaleY(-1);
  filter: blur(8px);
  pointer-events: none;
  mask-image: linear-gradient(to bottom, transparent 0%, black 50%);
  -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 50%);
}

/* Botón especial (Nuevo) con brillo */
.dock-special {
  position: relative;
  overflow: visible;
}

.dock-special::before {
  content: '';
  position: absolute;
  inset: -2px;
  background: linear-gradient(135deg, #fbbf24, #f59e0b, #ec4899, #a855f7);
  border-radius: inherit;
  opacity: 0;
  filter: blur(12px);
  transition: opacity 0.3s ease;
  z-index: -1;
}

.dock-special:hover::before {
  opacity: 0.6;
}

/* Animación de entrada */
@keyframes dock-appear {
  from {
    opacity: 0;
    transform: translateY(100px) scale(0.8);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.dock-animate {
  animation: dock-appear 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Sombra dinámica del dock */
@keyframes dock-shadow-float {
  0%, 100% { transform: translateY(0) scaleX(1); opacity: 0.3; }
  50% { transform: translateY(4px) scaleX(1.05); opacity: 0.2; }
}

.dock-shadow {
  animation: dock-shadow-float 4s ease-in-out infinite;
}
</style>

<!-- NAVBAR DOCK V3 - ESTILO MACOS -->
<div class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none flex justify-center items-end pb-2">

  <!-- Sombra del dock -->
  <div class="absolute bottom-6 w-[90%] max-w-md h-4 bg-black/20 rounded-full blur-2xl dock-shadow"></div>

  <!-- Dock principal -->
  <div class="dock-container dock-animate rounded-[2rem] px-5 py-4 relative pointer-events-auto"
       style="max-width: min(90%, 420px);">

    <!-- Reflejo inferior (opcional) -->
    <div class="dock-reflection"></div>

    <!-- Items del dock -->
    <div class="dock-items flex items-end justify-center gap-3">

      <?php foreach ($dock_items as $key => $item): ?>
        <?php if ($item === 'divider'): ?>
          <!-- Separador -->
          <div class="dock-divider"></div>

        <?php else: ?>
          <!-- Item del dock -->
          <a href="<?= $item['url'] ?>"
             class="dock-item group relative flex flex-col items-center <?= isset($item['special']) && $item['special'] ? 'dock-special' : '' ?>"
             style="--icon-color-from: <?= $item['color_from'] ?>; --icon-color-to: <?= $item['color_to'] ?>;">

            <!-- Contenedor del icono -->
            <div class="relative">
              <!-- Fondo circular con gradiente -->
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?= $item['bg_class'] ?> p-[2px] shadow-xl">
                <div class="w-full h-full bg-white/90 backdrop-blur-sm rounded-[14px] flex items-center justify-center overflow-hidden">
                  <!-- Icono -->
                  <i class="dock-icon <?= $item['active'] ? 'ph-fill' : 'ph' ?> <?= $item['active'] ? $item['icon_fill'] : $item['icon'] ?> text-3xl"
                     style="--icon-color-from: <?= $item['color_from'] ?>; --icon-color-to: <?= $item['color_to'] ?>;"></i>

                  <!-- Brillo interior en hover -->
                  <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
              </div>

              <!-- Badge de notificación (ejemplo) -->
              <?php if ($key === 'proformas'): ?>
                <!-- <div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold border-2 border-white shadow-lg">3</div> -->
              <?php endif; ?>
            </div>

            <!-- Label flotante (tooltip) -->
            <div class="dock-label absolute -top-12 px-3 py-1.5 rounded-lg text-white text-xs font-semibold whitespace-nowrap shadow-2xl">
              <?= $item['label'] ?>
              <!-- Flecha del tooltip -->
              <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-black/85 rotate-45"></div>
            </div>

            <!-- Indicador de activo -->
            <?php if ($item['active']): ?>
              <div class="active-indicator absolute -bottom-2 w-1 h-1 rounded-full"
                   style="color: <?= $item['color_from'] ?>;"></div>
            <?php endif; ?>

          </a>
        <?php endif; ?>
      <?php endforeach; ?>

    </div>

  </div>
</div>

<!-- Script para interactividad avanzada -->
<script>
(function() {
  const dockItems = document.querySelectorAll('.dock-item');

  // Efecto de magnificación para items vecinos
  dockItems.forEach((item, index) => {
    item.addEventListener('mouseenter', function() {
      // Magnificar items vecinos con factor decreciente
      const prev = dockItems[index - 1];
      const next = dockItems[index + 1];

      if (prev) {
        prev.style.transform = 'scale(1.15) translateY(-5px)';
      }
      if (next) {
        next.style.transform = 'scale(1.15) translateY(-5px)';
      }
    });

    item.addEventListener('mouseleave', function() {
      const prev = dockItems[index - 1];
      const next = dockItems[index + 1];

      if (prev) {
        prev.style.transform = '';
      }
      if (next) {
        next.style.transform = '';
      }
    });

    // Vibración háptica
    item.addEventListener('touchstart', function() {
      if ('vibrate' in navigator) {
        navigator.vibrate(10);
      }
    });

    // Efecto de clic con escala
    item.addEventListener('mousedown', function() {
      this.style.transform = 'scale(1.2) translateY(-8px)';
    });

    item.addEventListener('mouseup', function() {
      this.style.transform = '';
    });
  });

  // Animación de ondas al hacer clic en el dock
  const dockContainer = document.querySelector('.dock-container');
  dockContainer.addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const ripple = document.createElement('div');
    ripple.style.cssText = `
      position: absolute;
      left: ${x}px;
      top: ${y}px;
      width: 20px;
      height: 20px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.6), transparent);
      border-radius: 50%;
      transform: translate(-50%, -50%) scale(0);
      animation: ripple-expand 0.6s ease-out;
      pointer-events: none;
    `;

    this.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  });
})();
</script>

<style>
@keyframes ripple-expand {
  to {
    transform: translate(-50%, -50%) scale(4);
    opacity: 0;
  }
}

/* Safe area para dispositivos con notch */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .pb-6 {
    padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
  }
}
</style>

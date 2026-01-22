<?php
/**
 * HEADER ULTRA PREMIUM
 *
 * Parámetros esperados:
 * - $title: Título principal del header
 * - $subtitle: Subtítulo opcional
 * - $back_url: URL para el botón de regresar (opcional)
 * - $action_button: Array con 'url', 'icon', 'label' (opcional)
 * - $badge: Texto para el badge superior (opcional)
 * - $badge_color: Color del badge: 'blue', 'green', 'red', 'amber', 'violet' (default: 'blue')
 * - $search: true/false para mostrar barra de búsqueda (opcional)
 * - $selection_mode_enabled: true/false para mostrar botón de selección múltiple (opcional)
 */

$title = $title ?? 'Tradimacova';
$subtitle = $subtitle ?? null;
$back_url = $back_url ?? null;
$action_button = $action_button ?? null;
$badge = $badge ?? null;
$badge_color = $badge_color ?? 'blue';
$search = $search ?? false;
$selection_mode_enabled = false; // Deshabilitado por configuración
$section = $section ?? 'inventario'; // clientes, inventario, etc
$show_home = $show_home ?? false;

// Colores por sección
$section_colors = [
        'home' => [
        'from' => 'from-slate-700',
        'via' => 'via-slate-800',
        'to' => 'to-slate-900',
        'hover_from' => 'hover:from-slate-600',
        'hover_via' => 'hover:via-slate-700',
        'hover_to' => 'hover:to-slate-800',
        'shadow' => 'shadow-slate-700/30',
        'hover_shadow' => 'hover:shadow-slate-600/40',

    ],
    'proformas' => [
        'from' => 'from-blue-600',
        'via' => 'via-blue-700',
        'to' => 'to-blue-800',
        'hover_from' => 'hover:from-blue-500',
        'hover_via' => 'hover:via-blue-600',
        'hover_to' => 'hover:to-blue-700',
        'shadow' => 'shadow-blue-600/30',
        'hover_shadow' => 'hover:shadow-blue-500/40',

    ],

    'clientes' => [
        'from' => 'from-emerald-500',
        'via' => 'via-green-500',
        'to' => 'to-teal-600',
        'hover_from' => 'hover:from-emerald-400',
        'hover_via' => 'hover:via-green-400',
        'hover_to' => 'hover:to-teal-500',
        'shadow' => 'shadow-emerald-500/30',
        'hover_shadow' => 'hover:shadow-emerald-400/40',


    ],
    'inventario' => [
        'from' => 'from-orange-500',
        'via' => 'via-amber-500',
        'to' => 'to-yellow-600',
        'hover_from' => 'hover:from-orange-400',
        'hover_via' => 'hover:via-amber-400',
        'hover_to' => 'hover:to-yellow-500',
        'shadow' => 'shadow-orange-500/30',
        'hover_shadow' => 'hover:shadow-orange-400/40',


    ],
];

$current_section = $section_colors[$section] ?? $section_colors['inventario'];

// Colores de badges
$badge_colors = [
    'blue' => 'from-blue-500 to-indigo-600',
    'green' => 'from-emerald-500 to-green-600',
    'red' => 'from-red-500 to-rose-600',
    'amber' => 'from-amber-500 to-orange-600',
    'violet' => 'from-violet-500 to-fuchsia-600',
    'orange' => 'from-orange-500 to-amber-600',
];

$gradient = $badge_colors[$badge_color] ?? $badge_colors['blue'];

// LOGICA DE NOTIFICACIONES (LEADS PENDIENTES)
// LOGICA DE NOTIFICACIONES (LEADS PENDIENTES)
$pendingCount = 0;
try {
    if (file_exists(__DIR__ . '/../../Models/ClientePendiente.php')) {
        require_once __DIR__ . '/../../Models/ClientePendiente.php';
        if (class_exists('\App\Models\ClientePendiente')) {
            $pendingModel = new \App\Models\ClientePendiente();
            $pendingCount = $pendingModel->countPending();
        }
    }
} catch (\Exception $e) {
    // Silenciar error totalmente en header para no bloquear la web
    error_log("Header Leads Error: " . $e->getMessage());
}
?>

<!-- Estilos CSS para el header premium -->
<style>
/* Glassmorphism mejorado para header */
.ultra-glass-header {
    background: linear-gradient(135deg,
        rgba(15, 23, 42, 0.95) 0%,
        rgba(30, 41, 59, 0.92) 50%,
        rgba(15, 23, 42, 0.95) 100%

    );
    backdrop-filter: blur(24px) saturate(200%);
    -webkit-backdrop-filter: blur(24px) saturate(200%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.2),
        0 0 0 1px rgba(255, 255, 255, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.15),
        inset 0 -1px 0 rgba(0, 0, 0, 0.1);
}

/* Animación de ola de fondo mejorada */
.ultra-glass-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(120deg,
        transparent 0%,
        rgba(147, 197, 253, 0.08) 40%,
        rgba(255, 255, 255, 0.05) 50%,
        rgba(147, 197, 253, 0.08) 60%,
        transparent 100%
    );
    background-size: 200% 100%;
    animation: wave 10s ease-in-out infinite;
    pointer-events: none;
}

@keyframes wave {
    0%, 100% {
        background-position: 0% 50%;
        opacity: 0.5;
    }
    50% {
        background-position: 100% 50%;
        opacity: 0.8;
    }
}

/* Partículas flotantes decorativas mejoradas */
.particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: radial-gradient(circle, rgba(147, 197, 253, 0.6), rgba(255, 255, 255, 0.3));
    border-radius: 50%;
    pointer-events: none;
    animation: float-particle 20s infinite ease-in-out;
    box-shadow: 0 0 8px rgba(147, 197, 253, 0.4);
}

@keyframes float-particle {
    0%, 100% {
        transform: translateY(0) translateX(0) scale(0.8) rotate(0deg);
        opacity: 0;
    }
    5% {
        opacity: 0.8;
    }
    95% {
        opacity: 0.8;
    }
    100% {
        transform: translateY(-120px) translateX(60px) scale(1.2) rotate(360deg);
        opacity: 0;
    }
}

/* Efecto shimmer mejorado en el título */
.shimmer-text {
    background: linear-gradient(120deg,
        #fff 0%,
        rgba(255,255,255,0.9) 40%,
        rgba(147, 197, 253, 1) 50%,
        rgba(255,255,255,0.9) 60%,
        #fff 100%
    );
    background-size: 300% 100%;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 4s ease-in-out infinite;
    text-shadow: 0 0 20px rgba(147, 197, 253, 0.3);
}

@keyframes shimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Botón con efecto neomórfico mejorado */
.neo-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.neo-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.neo-btn:hover::before {
    width: 300px;
    height: 300px;
}

.neo-btn:active {
    transform: scale(0.95);
}

/* Badge pulsante mejorado */
.pulse-badge {
    animation: pulse-glow 2.5s ease-in-out infinite;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7), 0 4px 12px rgba(59, 130, 246, 0.4);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(59, 130, 246, 0), 0 6px 16px rgba(59, 130, 246, 0.6);
        transform: scale(1.02);
    }
}

/* Barra de búsqueda premium mejorada */
.search-glass {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-glass:focus-within {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(147, 197, 253, 0.5);
    box-shadow:
        0 0 0 4px rgba(147, 197, 253, 0.15),
        0 8px 24px rgba(59, 130, 246, 0.2);
    transform: translateY(-1px);
}
</style>

<!-- HEADER ULTRA PREMIUM -->
<!-- Contenedor fijo que centra el header -->
<div class="fixed top-0 left-0 right-0 z-40 flex justify-center">
    <header class="ultra-glass-header w-full max-w-md px-4 pt-6 pb-3 overflow-hidden rounded-b-3xl shadow-lg relative">

        <!-- Partículas decorativas (3 partículas) -->
        <div class="particle" style="left: 20%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 5s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 10s;"></div>

        <div class="relative z-10">

        <!-- Fila superior: Navegación y Título -->
        <div class="flex items-center justify-between gap-3 <?= $search ? 'mb-2' : '' ?>">

            <!-- Botón de regresar/home + Título (alineados a la izquierda) -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <?php if ($back_url): ?>
                    <a href="<?= $back_url ?>"
                       class="neo-btn group w-9 h-9 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 backdrop-blur-md border border-white/10 flex-shrink-0">
                        <i class="ph-bold ph-arrow-left text-white text-lg group-hover:translate-x-[-2px] transition-transform duration-300"></i>
                    </a>
                <?php elseif ($show_home): ?>
                    <a href="<?= url('/') ?>"
                       class="neo-btn group w-9 h-9 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 backdrop-blur-md border border-white/10 flex-shrink-0">
                        <i class="ph-bold ph-house text-white text-lg group-hover:scale-110 transition-transform duration-300"></i>
                    </a>
                <?php endif; ?>

                <!-- Título y Badge -->
                <div class="flex-1 min-w-0">
                    <!-- Badge superior (si existe) -->
                    <?php if ($badge): ?>
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-gradient-to-r <?= $gradient ?> rounded-full text-white text-[9px] font-bold mb-0.5 shadow-md pulse-badge border border-white/20">
                            <span class="w-1 h-1 bg-white rounded-full animate-pulse"></span>
                            <?= htmlspecialchars($badge) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Título principal con mejor estilo -->
                    <h1 class="shimmer-text font-bold text-xl tracking-tight truncate leading-tight">
                        <?= htmlspecialchars($title) ?>
                    </h1>

                    <!-- Subtítulo (si existe) con mejor contraste -->
                    <?php if ($subtitle): ?>
                        <p class="text-slate-300 text-xs mt-0.5 font-medium truncate opacity-90">
                            <?= htmlspecialchars($subtitle) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de acción mejorados -->
            <div class="flex items-center gap-2">
                <?php 
                // Normalizar a array de botones
                $buttons = [];
                if (isset($action_buttons) && is_array($action_buttons)) {
                    $buttons = $action_buttons;
                } elseif (isset($action_button) && is_array($action_button)) {
                    $buttons = [$action_button];
                }

                foreach ($buttons as $btn): 
                ?>
                    <a href="<?= $btn['url'] ?? '#' ?>"
                       target="<?= $btn['target'] ?? '_self' ?>"
                       <?php if(isset($btn['onclick'])): ?>onclick="<?= $btn['onclick'] ?>"<?php endif; ?>
                       class="neo-btn group w-[42px] h-[42px] bg-gradient-to-br <?= $current_section['from'] ?> <?= $current_section['via'] ?> <?= $current_section['to'] ?> <?= $current_section['hover_from'] ?> <?= $current_section['hover_via'] ?> <?= $current_section['hover_to'] ?> rounded-2xl flex items-center justify-center transition-all duration-300 hover:scale-105 active:scale-95 shadow-xl <?= $current_section['shadow'] ?> <?= $current_section['hover_shadow'] ?> border border-white/20 hover:border-white/30 flex-shrink-0 relative overflow-hidden"
                       title="<?= $btn['label'] ?? '' ?>">
                        <div class="absolute inset-0 bg-gradient-to-t from-white/0 to-white/10"></div>
                        <i class="ph-bold <?= $btn['icon'] ?? 'ph-plus' ?> text-white text-xl relative z-10"></i>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Barra de búsqueda y selección (si está habilitada) - más compacta -->
        <?php if ($search): ?>
            <div class="flex items-center gap-2">
                <div class="search-glass rounded-xl px-3 py-2 flex items-center gap-2 transition-all duration-300 flex-1">
                    <i class="ph-bold ph-magnifying-glass text-white/60 text-base"></i>
                    <input
                        type="text"
                        id="headerSearchInput"
                        placeholder="Buscar..."
                        class="flex-1 bg-transparent text-white placeholder-white/50 outline-none text-xs font-medium"
                        autocomplete="off"
                    >
                    <button type="button" class="text-white/40 hover:text-white/80 transition-colors hidden" id="headerSearchClear">
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>
                </div>

          
            
            <!-- Notificación de Leads Pendientes -->
            <?php if ($pendingCount > 0): ?>
            <a href="<?= url('/leads') ?>" class="relative p-2.5 text-amber-400 hover:text-amber-300 rounded-xl hover:bg-amber-900/20 transition-colors tooltip-trigger animate-pulse" data-tooltip="<?= $pendingCount ?> Solicitudes Pendientes">
                <i class="ph-bold ph-bell-ringing text-xl"></i>
                <span class="absolute top-1.5 right-1.5 flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
            </a>
            <?php endif; ?>
                <!-- Botón de modo selección mejorado (si está habilitado) -->
                <?php if ($selection_mode_enabled): ?>
                    <button
                        onclick="if(typeof toggleSelectionMode === 'function') toggleSelectionMode(); else console.error('toggleSelectionMode no está definida');"
                        id="btnSelectMode"
                        class="neo-btn search-glass px-3.5 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/15 transition-all duration-300 border border-white/10 hover:border-blue-400/40 backdrop-blur-sm flex-shrink-0 group relative"
                        aria-label="Modo selección">
                        <i class="ph-bold ph-checks text-lg group-hover:scale-110 transition-transform duration-300 relative z-10"></i>
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl opacity-0 group-hover:opacity-20 blur transition-opacity duration-300 -z-10"></div>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

        <!-- Efecto de resplandor inferior mejorado con animación -->
        <div class="absolute bottom-0 left-0 right-0 h-[2px] overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-blue-400/40 to-transparent animate-shimmer-border"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
        </div>

    </header>
</div>

<style>
@keyframes shimmer-border {
    0%, 100% {
        transform: translateX(-100%);
        opacity: 0.5;
    }
    50% {
        transform: translateX(100%);
        opacity: 1;
    }
}

.animate-shimmer-border {
    animation: shimmer-border 6s ease-in-out infinite;
}
</style>

<!-- Espaciador para compensar el header fijo -->
<div class="h-[<?= $search ? '120px' : '90px' ?>]"></div>
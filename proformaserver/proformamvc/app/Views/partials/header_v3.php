<?php
/**
 * HEADER MODERN GRADIENT V3
 * Diseño moderno con gradiente animado y efectos visuales
 */

$title = $title ?? 'Tradimacova';
$subtitle = $subtitle ?? null;
$back_url = $back_url ?? null;
$action_button = $action_button ?? null;
$badge = $badge ?? null;
$search = $search ?? false;
$section = $section ?? 'inventario';
$show_home = $show_home ?? false;

// Gradientes por sección
$section_gradients = [
    'proformas' => 'from-blue-600 via-blue-700 to-indigo-700',
    'clientes' => 'from-emerald-600 via-teal-600 to-cyan-700',
    'inventario' => 'from-amber-600 via-orange-600 to-red-600',
    'home' => 'from-slate-700 via-slate-800 to-slate-900',
];

// Botones por sección
$section_buttons = [
    'proformas' => 'bg-blue-500 hover:bg-blue-600',
    'clientes' => 'bg-emerald-500 hover:bg-emerald-600',
    'inventario' => 'bg-amber-500 hover:bg-amber-600',
    'home' => 'bg-slate-600 hover:bg-slate-700',
];

$current_gradient = $section_gradients[$section] ?? $section_gradients['home'];
$current_button = $section_buttons[$section] ?? $section_buttons['home'];
?>

<style>
.modern-header {
    position: relative;
    overflow: hidden;
}

.modern-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.modern-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) translateX(0px); }
    50% { transform: translateY(-20px) translateX(10px); }
}

.modern-gradient {
    background: linear-gradient(135deg, var(--tw-gradient-stops));
    background-size: 200% 200%;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.glass-button {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.glass-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}
</style>

<!-- HEADER MODERN GRADIENT -->
<div class="fixed top-0 left-0 right-0 z-30 flex justify-center">
    <header class="modern-header modern-gradient bg-gradient-to-br <?= $current_gradient ?> w-full max-w-md px-5 py-4 relative">

        <div class="relative z-10 <?= $search ? 'mb-3' : '' ?>">
            <div class="flex items-center justify-between gap-3">

                <!-- Navegación + Título -->
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <?php if ($back_url): ?>
                        <a href="<?= $back_url ?>"
                           class="glass-button w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg">
                            <i class="ph-bold ph-arrow-left text-white text-lg"></i>
                        </a>
                    <?php elseif ($show_home): ?>
                        <a href="<?= url('/') ?>"
                           class="glass-button w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg">
                            <i class="ph-bold ph-house text-white text-lg"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Título -->
                    <div class="flex-1 min-w-0">
                        <?php if ($badge): ?>
                            <span class="inline-block px-2 py-0.5 bg-white/20 rounded-full text-[9px] text-white font-bold uppercase mb-1">
                                <?= htmlspecialchars($badge) ?>
                            </span>
                        <?php endif; ?>
                        <h1 class="font-bold text-lg text-white truncate drop-shadow-lg">
                            <?= htmlspecialchars($title) ?>
                        </h1>
                        <?php if ($subtitle): ?>
                            <p class="text-white/80 text-xs truncate"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botón de acción -->
                <?php if ($action_button): ?>
                    <a href="<?= $action_button['url'] ?? '#' ?>"
                    target="<?= $action_button['target'] ?? '_self' ?>"
                        class="<?= $current_button ?> px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 active:scale-95">
                        <i class="ph-bold <?= $action_button['icon'] ?? 'ph-plus' ?> text-white text-base"></i>
                        <span class="text-white text-sm font-bold"><?= $action_button['label'] ?? 'Nuevo' ?></span>
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <!-- Barra de búsqueda -->
        <?php if ($search): ?>
            <div class="relative z-10">
                <div class="glass-button flex items-center rounded-xl px-4 py-2.5 shadow-lg">
                    <i class="ph-bold ph-magnifying-glass text-white/80 text-base"></i>
                    <input
                        type="text"
                        id="headerSearchInput"
                        placeholder="Buscar..."
                        class="flex-1 bg-transparent text-white placeholder-white/60 outline-none text-sm ml-3 font-medium"
                        autocomplete="off"
                    >
                </div>
            </div>
        <?php endif; ?>

    </header>
</div>

<!-- Espaciador -->
<div class="h-[<?= $search ? '110px' : '68px' ?>]"></div>

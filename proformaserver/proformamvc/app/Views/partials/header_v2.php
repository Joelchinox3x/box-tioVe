<?php
/**
 * HEADER MINIMALISTA V2
 * Diseño limpio y compacto
 */

$title = $title ?? 'Tradimacova';
$subtitle = $subtitle ?? null;
$back_url = $back_url ?? null;
$action_button = $action_button ?? null;
$badge = $badge ?? null;
$search = $search ?? false;
$section = $section ?? 'inventario';
$show_home = $show_home ?? false;

// Colores por sección
$section_colors = [
    'proformas' => ['bg' => 'bg-blue-600', 'hover' => 'hover:bg-blue-700'],
    'clientes' => ['bg' => 'bg-emerald-600', 'hover' => 'hover:bg-emerald-700'],
    'inventario' => ['bg' => 'bg-amber-600', 'hover' => 'hover:bg-amber-700'],
    'home' => ['bg' => 'bg-slate-700', 'hover' => 'hover:bg-slate-800'],
];

$current_section = $section_colors[$section] ?? $section_colors['home'];
?>

<style>
.minimal-header {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}
</style>

<!-- HEADER MINIMALISTA -->
<div class="fixed top-0 left-0 right-0 z-30 flex justify-center">
    <header class="minimal-header w-full max-w-md px-4 py-3 relative">

        <div class="flex items-center justify-between gap-3 <?= $search ? 'mb-2' : '' ?>">

            <!-- Navegación + Título -->
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <?php if ($back_url): ?>
                    <a href="<?= $back_url ?>"
                       class="w-8 h-8 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center transition-all">
                        <i class="ph-bold ph-arrow-left text-white text-base"></i>
                    </a>
                <?php elseif ($show_home): ?>
                    <a href="<?= url('/') ?>"
                       class="w-8 h-8 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center transition-all">
                        <i class="ph-bold ph-house text-white text-base"></i>
                    </a>
                <?php endif; ?>

                <!-- Título -->
                <div class="flex-1 min-w-0">
                    <?php if ($badge): ?>
                        <span class="text-[10px] text-white/60 font-semibold uppercase"><?= htmlspecialchars($badge) ?></span>
                    <?php endif; ?>
                    <h1 class="font-bold text-base text-white truncate">
                        <?= htmlspecialchars($title) ?>
                    </h1>
                    <?php if ($subtitle): ?>
                        <p class="text-white/60 text-xs truncate"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón de acción -->
            <?php if ($action_button): ?>
                <a href="<?= $action_button['url'] ?? '#' ?>"
                target="<?= $action_button['target'] ?? '_self' ?>"
                    class="px-3 py-2 <?= $current_section['bg'] ?> <?= $current_section['hover'] ?> rounded-lg flex items-center gap-1.5 transition-all shadow-sm">
                    <i class="ph-bold <?= $action_button['icon'] ?? 'ph-plus' ?> text-white text-sm"></i>
                    <span class="text-white text-xs font-semibold"><?= $action_button['label'] ?? 'Nuevo' ?></span>
                </a>
            <?php endif; ?>

        </div>

        <!-- Barra de búsqueda -->
        <?php if ($search): ?>
            <div class="flex items-center bg-white/10 rounded-lg px-3 py-2">
                <i class="ph-bold ph-magnifying-glass text-white/60 text-sm"></i>
                <input
                    type="text"
                    id="headerSearchInput"
                    placeholder="Buscar..."
                    class="flex-1 bg-transparent text-white placeholder-white/50 outline-none text-sm ml-2"
                    autocomplete="off"
                >
            </div>
        <?php endif; ?>

    </header>
</div>

<!-- Espaciador -->
<div class="h-[<?= $search ? '100px' : '60px' ?>]"></div>

<?php
/**
 * Componente de botones flotantes para formularios (sticky footer)
 *
 * Uso:
 * <?php
 *   $floating_buttons = [
 *       'cancel_url' => url('/ruta/cancelar'),
 *       'submit_text' => 'Guardar Cambios',
 *       'submit_icon' => 'ph-floppy-disk',  // Opcional
 *       'submit_color' => 'purple',         // Opcional: purple, blue, green, orange, red
 *       'cancel_text' => 'Cancelar',        // Opcional
 *       'extra_buttons' => [                // Opcional: botones adicionales
 *           [
 *               'text' => 'Vista Previa',
 *               'icon' => 'ph-eye',
 *               'color' => 'green',
 *               'href' => url('/preview'),
 *               'target' => '_blank'        // Opcional
 *           ]
 *       ]
 *   ];
 *   include __DIR__ . '/../partials/floating_form_buttons.php';
 * ?>
 */

// Valores por defecto
$cancel_url = $floating_buttons['cancel_url'] ?? '#';
$cancel_text = $floating_buttons['cancel_text'] ?? 'Cancelar';
$submit_text = $floating_buttons['submit_text'] ?? 'Guardar';
$submit_icon = $floating_buttons['submit_icon'] ?? 'ph-check';
$submit_color = $floating_buttons['submit_color'] ?? 'purple';
$extra_buttons = $floating_buttons['extra_buttons'] ?? [];

// Colores disponibles
$color_classes = [
    'purple' => 'bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 shadow-purple-200',
    'blue' => 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-blue-200',
    'green' => 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-green-200',
    'orange' => 'bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 shadow-orange-200',
    'red' => 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 shadow-red-200',
];

$submit_classes = $color_classes[$submit_color] ?? $color_classes['purple'];
?>

<!-- Botones flotantes sticky footer -->
<div class="fixed bottom-20 left-0 right-0 z-20 animate-fade-in-up px-4" style="animation-delay: 0.2s">
    <div class="flex gap-3 bg-white/95 backdrop-blur-xl p-3 rounded-2xl shadow-2xl border-2 border-slate-200/50 ring-4 ring-slate-100/50 max-w-lg mx-auto">
        <!-- Botón Cancelar -->
        <a
            href="<?= $cancel_url ?>"
            class="flex-1 px-5 py-3 bg-slate-200 border-2 border-slate-400 text-slate-700 rounded-xl font-semibold text-center hover:bg-slate-400 hover:border-slate-600 transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2 text-sm"
        >
            <i class="ph-bold ph-x"></i>
            <span><?= $cancel_text ?></span>
        </a>

        <?php if (!empty($extra_buttons)): ?>
            <?php foreach ($extra_buttons as $btn): ?>
                <?php
                    $btn_color = $btn['color'] ?? 'blue';
                    $btn_classes = $color_classes[$btn_color] ?? $color_classes['blue'];
                    $btn_target = isset($btn['target']) ? 'target="' . htmlspecialchars($btn['target']) . '"' : '';
                ?>
                <a href="<?= $btn['href'] ?? '#' ?>"
                   <?= $btn_target ?>
                   class="flex-1 px-5 py-3 <?= $btn_classes ?> text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    <?php if (!empty($btn['icon'])): ?>
                        <i class="ph-bold <?= $btn['icon'] ?> relative z-10"></i>
                    <?php endif; ?>
                    <span class="relative z-10"><?= $btn['text'] ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Botón Submit -->
        <button
            type="submit"
            class="flex-1 px-5 py-3 <?= $submit_classes ?> text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm"
        >
            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
            <i class="ph-bold <?= $submit_icon ?> relative z-10"></i>
            <span class="relative z-10"><?= $submit_text ?></span>
        </button>
    </div>
</div>

<?php
/**
 * Componente de botones flotantes para formularios
 *
 * Uso:
 * <?php
 *   $form_buttons = [
 *       'cancel_url' => url('/ruta/cancelar'),
 *       'submit_text' => 'Guardar Cambios',
 *       'submit_icon' => 'ph-floppy-disk',  // Opcional
 *       'submit_color' => 'purple',         // Opcional: purple, blue, green, orange
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
 *   include __DIR__ . '/../partials/form_buttons.php';
 * ?>
 */

// Valores por defecto
$cancel_url = $form_buttons['cancel_url'] ?? '#';
$cancel_text = $form_buttons['cancel_text'] ?? 'Cancelar';
$submit_text = $form_buttons['submit_text'] ?? 'Guardar';
$submit_icon = $form_buttons['submit_icon'] ?? 'ph-check';
$submit_color = $form_buttons['submit_color'] ?? 'purple';
$extra_buttons = $form_buttons['extra_buttons'] ?? [];

// Colores disponibles
$color_classes = [
    'purple' => 'bg-purple-600 hover:bg-purple-700 shadow-purple-200',
    'blue' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-200',
    'green' => 'bg-green-600 hover:bg-green-700 shadow-green-200',
    'orange' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-200',
    'red' => 'bg-red-600 hover:bg-red-700 shadow-red-200',
];

$submit_classes = $color_classes[$submit_color] ?? $color_classes['purple'];
?>

<div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
    <!-- Botón Cancelar -->
    <a href="<?= $cancel_url ?>"
       class="px-5 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-colors">
        <?= $cancel_text ?>
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
               class="px-5 py-2.5 rounded-xl <?= $btn_classes ?> text-white font-bold shadow-lg transition-all flex items-center gap-2">
                <?php if (!empty($btn['icon'])): ?>
                    <i class="ph-bold <?= $btn['icon'] ?>"></i>
                <?php endif; ?>
                <?= $btn['text'] ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Botón Submit -->
    <button type="submit"
            class="px-6 py-2.5 rounded-xl <?= $submit_classes ?> text-white font-bold shadow-lg transition-all flex items-center gap-2">
        <i class="ph-bold <?= $submit_icon ?>"></i>
        <?= $submit_text ?>
    </button>
</div>

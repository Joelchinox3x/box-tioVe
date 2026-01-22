<?php
// Footer dinámico
$footer_img = $fullData['footer_img'] ?? $footer_img ?? '';
?>
<div style="width: 220mm; margin-left: -10mm; margin-right: -10mm; padding: 0;">
    <?php if (!empty($footer_img)): ?>
        <img src="<?= $footer_img ?>" style="width: 100%; height: auto; display: block; margin: 0;" />
    <?php endif; ?>
</div>
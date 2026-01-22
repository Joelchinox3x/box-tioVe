<style> <?php include __DIR__ . '/styles.php'; ?> </style>

<?php include __DIR__ . '/partials/header.php'; ?>

<div class="specs-title">
    GALERÍA ADICIONAL: <?= htmlspecialchars($item_name) ?>
</div>

<div style="margin-top: 20px;">

    <?php 
    // Recorremos el grupo de fotos (El motor ya envía máximo 2 por página)
    $contador = 1; 
    foreach($photos_group as $img): 
        if(file_exists($img)): 
    ?>
        
        <div class="photo-frame">
            <img src="<?= $img ?>" class="photo-img">
            <div class="photo-label">IMAGEN ADICIONAL <?= $contador ?></div>
        </div>

    <?php 
        $contador++;
        endif; 
    endforeach; 
    ?>

    <?php if(count($photos_group) === 0): ?>
        <div style="text-align:center; padding: 50px; color: #ccc;">
            Sin imágenes adicionales.
        </div>
    <?php endif; ?>

</div>

<div style="position: absolute; bottom: 0; width: 100%;">
    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>
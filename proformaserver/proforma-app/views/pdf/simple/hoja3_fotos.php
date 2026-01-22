<style> <?php include __DIR__ . '/styles.php'; ?> </style>

<?php include __DIR__ . '/partials/header.php'; ?>

<div class="specs-title">
    FOTOS PRINCIPALES: <?= htmlspecialchars($item_name) ?>
</div>

<div style="margin-top: 20px;">

    <?php 
    $contador = 1; 
    foreach($photos as $img): 
        if(file_exists($img)): 
    ?>
        
        <div class="photo-frame">
            <img src="<?= $img ?>" class="photo-img">
            <div class="photo-label">IMAGEN <?= $contador ?></div>
        </div>

    <?php 
        $contador++;
        endif; 
    endforeach; 
    ?>

</div>

<div style="position: absolute; bottom: 0; width: 100%;">
    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>
<style> <?php include 'styles.css'; ?> </style>

<h4 style="color: #666; border-bottom: 1px solid #ccc;">
    Galería Adicional: <?= $item_name ?>
</h4>

<table width="100%" style="margin-top: 20px;">
    <?php foreach($photos_group as $img): ?>
        <?php if(file_exists($img)): ?>
        <tr>
            <td align="center" style="padding-bottom: 20px;">
                <div class="img-container">
                    <img src="<?= $img ?>" style="max-width: 100%; max-height: 450px;">
                </div>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>
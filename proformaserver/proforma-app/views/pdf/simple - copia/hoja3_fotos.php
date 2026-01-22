<style> <?php include 'styles.css'; ?> </style>

<h3 style="color: #666; border-bottom: 2px solid #f37021; margin-bottom: 20px; padding-bottom: 5px;">
    Fotos Principales: <?= $item_name ?>
</h3>

<table width="100%" style="border-spacing: 10px;">
    <tr>
        <?php foreach($photos as $img): ?>
            <?php if(file_exists($img)): ?>
                <td align="center" width="50%" valign="middle" style="padding: 10px; border: 1px solid #eee; background-color: #fafafa;">
                    
                    <div style="margin-bottom: 10px;">
                        <img src="<?= $img ?>" style="width: 100%; max-height: 400px; object-fit: contain;">
                    </div>

                </td>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if(count($photos) < 2): ?>
            <td width="50%">&nbsp;</td>
        <?php endif; ?>
    </tr>
</table>
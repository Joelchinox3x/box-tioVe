<?php
// detalles_tecnicos.php

// 1. OBTENER DATOS (Igual que antes, conectamos a BD)
$prod_id = $detalle['id'];

// A. Info General del producto
$stmtP = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmtP->execute([$prod_id]);
$producto_db = $stmtP->fetch(PDO::FETCH_ASSOC);

// B. Especificaciones (Tabla producto_specs)
$stmtSpecs = $pdo->prepare("SELECT atributo, valor FROM producto_specs WHERE producto_id = ? ORDER BY orden ASC, id ASC");
$stmtSpecs->execute([$prod_id]);
$mis_specs = $stmtSpecs->fetchAll(PDO::FETCH_ASSOC);

// C. Imágenes
$imagenes = [];
if (!empty($producto_db['imagenes'])) {
    $imagenes = json_decode($producto_db['imagenes'], true);
}

// Aseguramos que la variable de color exista, si no, ponemos el naranja por defecto
if (!isset($color_brand)) $color_brand = "#f37021";
?>

<div style="font-family: sans-serif; padding-top: 10px;">

    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="100%" 
                style="border: 1px solid <?= $color_brand ?>; 
                       color: <?= $color_brand ?>; 
                       text-align: center; 
                       font-weight: bold; 
                       padding: 8px; 
                       font-size: 16px; 
                       text-transform: uppercase;">
                FICHA TÉCNICA: <?= htmlspecialchars($producto_db['nombre'] ?? $detalle['desc']) ?>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 20px; font-size: 12px;">
        <tr>
            <td width="15%"><b>CÓDIGO/SKU:</b></td>
            <td width="35%"><?= htmlspecialchars($producto_db['sku'] ?? '---') ?></td>
            <td width="15%"><b>MODELO:</b></td>
            <td width="35%"><?= htmlspecialchars($producto_db['modelo'] ?? '---') ?></td>
        </tr>
    </table>

    <?php if (!empty($mis_specs)): ?>
        <table width="100%" cellspacing="0" cellpadding="8" style="font-size: 12px; border-collapse: collapse; border: 1px solid #eee;">
            <thead>
                <tr>
                    <th width="40%" style="background-color: <?= $color_brand ?>; color: white; border: 1px solid <?= $color_brand ?>; text-align: left;">ATRIBUTO</th>
                    <th width="60%" style="background-color: #fcebe1; color: <?= $color_brand ?>; border: 1px solid <?= $color_brand ?>; text-align: left;">VALOR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($mis_specs as $spec): ?>
                <tr>
                    <td style="border: 1px solid #eee; font-weight: bold; color: #555;">
                        <?= htmlspecialchars($spec['atributo']) ?>
                    </td>
                    <td style="border: 1px solid #eee; color: #333;">
                        <?= htmlspecialchars($spec['valor']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 20px; text-align: center; color: #999; border: 1px dashed #ccc;">
            No se han registrado especificaciones técnicas detalladas.
        </div>
    <?php endif; ?>

    <?php if(!empty($producto_db['descripcion'])): ?>
        <br><br>
        <div style="border-bottom: 1px solid <?= $color_brand ?>; color: <?= $color_brand ?>; font-weight: bold; font-size: 12px; margin-bottom: 10px;">
            INFORMACIÓN ADICIONAL
        </div>
        <div style="font-size: 11px; color: #444; text-align: justify; line-height: 1.5;">
            <?= nl2br(htmlspecialchars($producto_db['descripcion'])) ?>
        </div>
    <?php endif; ?>

</div>

<?php if (!empty($imagenes)): ?>
    
    <pagebreak /> <div style="font-family: sans-serif; padding-top: 10px;">
        
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="100%" 
                    style="border: 1px solid <?= $color_brand ?>; 
                           color: <?= $color_brand ?>; 
                           text-align: center; 
                           font-weight: bold; 
                           padding: 8px; 
                           font-size: 16px; 
                           text-transform: uppercase;">
                    GALERÍA FOTOGRÁFICA
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 20px; border: 1px solid #eee; padding: 10px; border-radius: 5px;">
            <img src="<?= $imagenes[0] ?>" style="max-width: 100%; max-height: 400px; object-fit: contain;">
        </div>

        <?php if (count($imagenes) > 1): ?>
            <table width="100%" cellspacing="10">
                <tr>
                    <?php 
                    // Mostramos las siguientes imágenes (hasta 3 más para que quepan bien)
                    $max_imgs = 4; 
                    $count = 0;
                    for($k=1; $k < count($imagenes) && $count < $max_imgs; $k++): 
                    ?>
                        <td width="33%" valign="top">
                            <div style="border: 1px solid #eee; padding: 5px; text-align: center;">
                                <img src="<?= $imagenes[$k] ?>" style="width: 100%; height: 150px; object-fit: cover;">
                            </div>
                        </td>
                    <?php 
                        $count++;
                        // Si llegamos a 3 imagenes en la fila, cerramos y abrimos nueva (opcional, aquí lo dejo en una fila de 3)
                        if ($count % 3 == 0) { echo '</tr><tr>'; } 
                    endfor; 
                    ?>
                </tr>
            </table>
        <?php endif; ?>

    </div>
<?php endif; ?>
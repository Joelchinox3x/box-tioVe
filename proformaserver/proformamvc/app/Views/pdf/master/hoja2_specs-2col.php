<?php include __DIR__ . '/partials/' . ($fullData['header_view'] ?? 'header.php'); ?>

<style> <?php include __DIR__ . '/styles.php'; ?> </style>

<?php
// LÓGICA DE DATOS (Mantenemos esto aquí para separar General vs Motor)
$specs_general = [];
$specs_motor = [];
$detectado_motor = false;

if(!empty($item['db_data']['specs'])) {
    foreach($item['db_data']['specs'] as $spec) {
        $atributo_upper = mb_strtoupper($spec['atributo'], 'UTF-8');
        // Palabras clave para cortar la tabla
        if (strpos($atributo_upper, 'MODELO DE MOTOR') !== false || strpos($atributo_upper, 'MOTOR CUMMINS') !== false) {
            $detectado_motor = true;
        }
        if ($detectado_motor) {
            $specs_motor[] = $spec;
        } else {
            $specs_general[] = $spec;
        }
    }
}

// DIVIDIR SPECS GENERALES EN 2 COLUMNAS
$total_general = count($specs_general);
$half = ceil($total_general / 2);
$specs_col1 = array_slice($specs_general, 0, $half);
$specs_col2 = array_slice($specs_general, $half);
?>

<div class="specs-title">
    FICHA TÉCNICA: <?= htmlspecialchars($item['desc']) ?>
</div>

<div class="specs-box">

    <?php if(!empty($item['db_data']['descripcion'])): ?>
        <div class="specs-desc">
            <?= nl2br(htmlspecialchars($item['db_data']['descripcion'])) ?>
        </div>
    <?php endif; ?>

    <?php if($total_general > 0): ?>
        <!-- NUEVO LAYOUT REFACTORIZADO (2 Columnas Aislado) -->
        <div class="section-title-2col">DATOS TÉCNICOS</div>
        
        <table class="split-container-2col">
            <tr>
                <!-- COLUMNA IZQUIERDA (50% estricto) -->
                <td width="auto" valign="top" style="padding-right: 0;">
                    <table class="specs-table-2col-clean">
                        <thead>
                            <tr>
                                <th class="col-attr-2col">CARACTERÍSTICA</th>
                                <th class="col-val-2col">DETALLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($specs_col1 as $sg): ?>
                            <tr>
                                <td class="col-attr-2col"><?= htmlspecialchars($sg['atributo']) ?></td>
                                <td class="col-val-2col"><?= htmlspecialchars($sg['valor']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>

                <!-- COLUMNA DERECHA (50% estricto) -->
                <td width="auto" valign="top" style="padding-left: 0;">
                    <?php if(count($specs_col2) > 0): ?>
                    <table class="specs-table-2col-clean">
                        <thead>
                            <tr>
                                <th class="col-attr-2col">CARACTERÍSTICA</th>
                                <th class="col-val-2col">DETALLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($specs_col2 as $sg): ?>
                            <tr>
                                <td class="col-attr-2col"><?= htmlspecialchars($sg['atributo']) ?></td>
                                <td class="col-val-2col"><?= htmlspecialchars($sg['valor']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($specs_col1) > count($specs_col2)): ?>
                            <tr>
                                <td class="col-attr-2col">&nbsp;</td>
                                <td class="col-val-2col">&nbsp;</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if(count($specs_motor) > 0): ?>
        
        <div class="section-title-2col">ESPECIFICACIONES DEL MOTOR</div>

        <table class="specs-table-2col-clean" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="col-attr-2col">CARACTERISTICA</th>
                    <th class="col-val-2col">DETALLE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($specs_motor as $sm): ?>
                <tr>
                    <td class="col-attr-2col"><?= htmlspecialchars($sm['atributo']) ?></td>
                    <td class="col-val-2col"><?= htmlspecialchars($sm['valor']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(empty($specs_general) && empty($specs_motor)): ?>
        <p style="color: #999; font-style: italic; text-align: center;">No hay especificaciones técnicas detalladas.</p>
    <?php endif; ?>

</div>

<div style="position: absolute; bottom: 0; width: 100%;">
    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>

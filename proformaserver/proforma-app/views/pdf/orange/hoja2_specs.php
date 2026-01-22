<?php include __DIR__ . '/partials/header.php'; ?>

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

    <?php if(count($specs_general) > 0): ?>
        <div class="motor-header">DATOS TÉCNICOS</div>
        
        
        <table class="specs-table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="specs-attr">CARACTERÍSTICA</th>
                    <th class="specs-val">DETALLE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($specs_general as $sg): ?>
                <tr>
                    <td class="specs-attr"><?= htmlspecialchars($sg['atributo']) ?></td>
                    <td><?= htmlspecialchars($sg['valor']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(count($specs_motor) > 0): ?>
        
        <div class="motor-header">ESPECIFICACIONES DEL MOTOR</div>

        <table class="motor-table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="specs-attr">CARACTERISTICA</th>
                    <th class="specs-val">DETALLE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($specs_motor as $sm): ?>
                <tr>
                    <td class="specs-attr"><?= htmlspecialchars($sm['atributo']) ?></td>
                    <td><?= htmlspecialchars($sm['valor']) ?></td>
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
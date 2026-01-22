<style> <?php include 'styles.css'; ?> </style>

<div class="bg-brand" style="padding: 8px; font-weight: bold; border-radius: 5px 5px 0 0; font-size: 14px; margin-top: 20px;">
    FICHA TÉCNICA: <?= strtoupper($item['desc']) ?>
</div>

<div style="border: 1px solid #f37021; padding: 15px; border-radius: 0 0 5px 5px; margin-bottom: 20px;">

    <?php if(!empty($item['db_data']['descripcion'])): ?>
        <div style="text-align: justify; margin-bottom: 20px; color: #444; font-size: 11px;">
            <?= nl2br($item['db_data']['descripcion']) ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($item['db_data']['specs'])): ?>
        <table width="100%" cellspacing="0" cellpadding="5" style="font-size: 11px;">
            <thead>
                <tr>
                    <th width="40%" class="text-brand" style="border-bottom: 2px solid #f37021; text-align: left; padding: 8px;">CARACTERÍSTICA</th>
                    <th width="60%" class="text-brand" style="border-bottom: 2px solid #f37021; text-align: left; padding: 8px;">DETALLE</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                foreach($item['db_data']['specs'] as $spec): 
                    $bg = ($count % 2 == 0) ? '#fff' : '#fff5eb'; // Alternar color fila (blanco / naranja muy suave)
                ?>
                <tr style="background-color: <?= $bg ?>;">
                    <td style="border-bottom: 1px solid #eee; padding: 8px; font-weight: bold; color: #555;">
                        <?= htmlspecialchars($spec['atributo']) ?>
                    </td>
                    <td style="border-bottom: 1px solid #eee; padding: 8px; color: #333;">
                        <?= htmlspecialchars($spec['valor']) ?>
                    </td>
                </tr>
                <?php 
                $count++;
                endforeach; 
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #999; font-style: italic;">No hay especificaciones técnicas detalladas para este equipo.</p>
    <?php endif; ?>

</div>
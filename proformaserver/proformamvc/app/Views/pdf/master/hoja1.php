
<?php
$bcp_path  = PROJECT_ROOT . '/public/assets/img/bcp.png';
$bbva_path = PROJECT_ROOT . '/public/assets/img/bbva.png';

// Cargar helper de configuración
require_once PROJECT_ROOT . '/app/Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

// Determinar símbolo de moneda
$moneda = $fullData['moneda'] ?? 'PEN';
$simboloMoneda = $moneda === 'USD' ? '$' : 'S/.';
?>

<style> <?php include __DIR__ . '/styles.php'; ?> </style>

<?php 
    // Header dinámico según configuración de BD
    $headerFile = $fullData['header_view'] ?? 'header.php';
    include __DIR__ . '/partials/' . $headerFile; 
?>

<div class="client-box">
    <table class="client-table">
        <tr>
            <td width="9%"><b>Fecha</b></td>
            <td width="55%">: <?= date('d/m/Y', strtotime($fullData['fecha'])) ?></td>
            <td width="9%"><b>Vendedor</b></td>
            <td width="25%">: Edwin Vega</td>
        </tr>
        <tr>
            <td><b>Cliente</b></td>
            <td>: <?= htmlspecialchars($fullData['cliente']['nombre']) ?></td>
            <td><b>Almacen</b></td>
            <td>: Cajamarquilla</td>
        </tr>
        <tr>
            <td><b>RUC/DNI</b></td>
            <td>: <?= htmlspecialchars($fullData['cliente']['ruc_dni'] ?? '') ?></td>
            <td><b>Email</b></td>
            <td>: edwinvega@tradimacova.com</td>
        </tr>
        <tr>
            <td><b>Dirección</b></td>
            <td>: <?= htmlspecialchars(substr($fullData['cliente']['direccion'], 0, 50)) ?></td>
            <td><b>WhatsApp</b></td>
            <td>: 994279484</td>
        </tr>
    </table>
</div>

<div class="msj-respeto">
    <b>De mi mayor consideración:</b><br>
    Tengo el agrado de dirigirme a usted para comunicarle que, conforme a su solicitud, ponemos a su disposición la siguiente cotización.
</div>

<div class="summary-header">RESUMEN DE LA COTIZACIÓN</div>

<table class="items-table">
    <thead>
    <tr>
            <th width="10%">CANT.</th>
            <th width="60%">DESCRIPCIÓN</th>
            <th width="15%">P. UNIT.</th>
            <th width="15%" style="border-right: 1px solid  <?= $color_brand ?>;" >IMPORTE</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($fullData['items'] as $it): ?>
        <tr>
            <td align="center"><?= $it['qty'] ?></td>
            <td><b><?= htmlspecialchars($it['desc']) ?></b></td>
            <td align="center"><?= $simboloMoneda ?> <?= number_format($it['price'], 2) ?></td>
            <td align="center"><?= $simboloMoneda ?> <?= number_format($it['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>

        <?php 
            $minimo_filas = 3; 
            $productos_actuales = count($fullData['items']);
            $filas_a_agregar = $minimo_filas - $productos_actuales;
            if($filas_a_agregar < 0) $filas_a_agregar = 0;
        ?>

        <?php for($i = 0; $i < $filas_a_agregar; $i++): ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php endfor; ?>
        <tr>
            <td colspan="2" style="border:none;"></td>
            <td class="total-label" style="border-bottom: 1px solid white;">SUBTOTAL</td>
            <td class="total-value"><?= $simboloMoneda ?> <?= number_format($totals['subtotal'], 2) ?></td>
        </tr>
        <tr>
            <td colspan="2" style="border:none;"></td>
            <td class="total-label" style="border-bottom: 1px solid white;">IGV (<?= number_format(SettingsHelper::getIgvPercent(), 0) ?>%)</td>
            <td class="total-value"><?= $simboloMoneda ?> <?= number_format($totals['igv'], 2) ?></td>
        </tr>
        <tr>
            <td colspan="2" style="border:none;"></td>
            <td class="total-label">TOTAL</td>
            <td class="total-value"><?= $simboloMoneda ?> <?= number_format($totals['total'], 2) ?></td>
        </tr>
                </tbody>
</table>
 
<div class="terms-header">CONDICIONES COMERCIALES</div>
<div class="terms-body">
    <div style="padding: 10px;">
        <ul style="margin: 0; padding-left: 20px;">
            <li>Precios incluyen IGV.</li>
            <li>Forma de pago: A convenir.</li>
            <li>Tiempo de entrega: Inmediata (sujeto a disponibilidad).</li>
            <li>Validez de la cotización: 7 días hábiles.</li>
        </ul>
    </div>
</div>

<table class="contact-table">
    <tr>
        <td width="70%" class="contact-header" style="border-left: 1px solid <?= $color_brand ?>; border-right: 1px solid white;">CUENTAS BANCARIAS</td>
        <td width="30%" class="contact-header" style="border-right: 1px solid <?= $color_brand ?>; ">DATOS DE CONTACTO</td>
    </tr>
    
    <tr>
        <td class="contact-body" style="border-right: 1px solid  #ababab;">
            <table class="banco-tabla">
                <tr>
                    <td width="40" align="center"><img src="<?= $bcp_path ?>" width="60"></td>
                    <td class="banco-bcp"><b>BCP SOLES</b></td>
                    <td>1917232923026</td>
                    <td><b>| CCI:</b> 00219100723292302651</td>
                </tr>
                <tr>
                    <td align="center"><img src="<?= $bbva_path ?>" width="55"></td>
                    <td class="banco-bbva"><b>BBVA SOLES</b></td>
                    <td>0011-0750-0100039964</td>
                    <td><b>| CCI:</b> 0011750000100039964-70</td>
                </tr>
                <tr>
                    <td align="center">
                        <img src="<?= $bbva_path?>" width="55"></td>
                    <td class="banco-bbva" style="padding-right:4px;">BBVA DÓLARES</td>
                    <td>001107500100039980</td>
                    <td><b>| CCI:</b> 011750000100039980-76</td>
                </tr>
            </table>
        </td>

        <td class="contact-body" >
            <b>Nombre:</b> Edwin Vega<br>
            <b>Email:</b> tradimacovaimport@gmail.com<br>
            <b>Cel:</b> 994280191
        </td>
    </tr>
</table>

<div style="position: absolute; bottom: 0; width: 100%;">
    <?php include __DIR__ . '/partials/footer.php'; ?>
</div>
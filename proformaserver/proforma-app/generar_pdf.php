<?php
// Variables disponibles: $proforma (array datos), $items (array productos), etc.

$icon_tel_path = __DIR__ . '/assets/img/icono_tel.png';
$icon_web_path = __DIR__ . '/assets/img/icono_web.png';
$logo_path = __DIR__ . '/assets/img/logo.png';
$bcp_path  = __DIR__ . '/assets/img/bcp.png';
$bbva_path = __DIR__ . '/assets/img/bbva.png';
$color_brand = "#f37021"; 
?>

<?php include 'header_plantilla.php'; ?>


<div style="margin-top: 20px; border: 1px solid <?=$color_brand?>; padding: 5px; border-radius: 5px;">
    <table  width="100%">
        <tr>
            <td width="8%"><b>Fecha</b></td>
            <td width="57%">: <?= date('d/m/Y') ?></td>
            <td width="10%"><b>Vendedor</b></td>
            <td width="25%">: Edwin Vega</td>
        </tr>
        <tr>
            <td><b>Cliente</b></td>
            <td>: <?= htmlspecialchars($proforma['cliente']) ?></td>
            <td><b>Almacen</b></td>
            <td>: Cajamarquilla</td>
        </tr>
        <tr>
            <td><b>RUC/DNI</b></td>
            <td>: <?= htmlspecialchars($proforma['dni_ruc']) ?></td>
            <td><b>Email</b></td>
            <td>: edwinvega@tradimacova.com</td>
        </tr>
        <tr>
            <td><b>Dirección</b></td>
            <td>: <?= htmlspecialchars($proforma['direccion']) ?></td>
            <td><b>WhatsApp</b></td>
            <td>: 994279484</td>
            
        </tr>
    </table>
</div>



<div style="margin: 15px 0; font-size: 12px;">
    <b>De mi mayor consideración:</b><br>
    Tengo el agrado de dirigirme a usted para comunicarle que, conforme a su solicitud, ponemos a su disposición la siguiente cotización.
</div>

<table class="items-table">
<tr>
        <td colspan="4" 
            style="border: 1px solid <?=$color_brand?>; 
                   color: <?=$color_brand?>; 
                   text-align: center; 
                   font-weight: bold; 
                   padding: 6px; 
                   font-size: 16px; 
                   text-transform: uppercase;">
            RESUMEN DE LA COTIZACIÓN
        </td>
    </tr>
    <thead>
   
    
        <tr>
        
            <th width="8%" style=" border-right: 1px solid #eee; border-left: 1px solid #f37021;">CANT.</th>
            <th width="60%" style=" border-right: 1px solid #eee;">DESCRIPCIÓN</th>
            <th width="15%" style=" border-right: 1px solid #eee;">P. UNIT.</th>
            <th width="15%" style=" border-right:1px solid #f37021;">IMPORTE</td>
            
        </tr>
    </thead>
    <tbody>
        <?php foreach($items as $it): ?>
        <tr>
            <td align="center"><?= $it['qty'] ?></td>
            <td>
                <b><?= htmlspecialchars($it['desc']) ?></b>
                <?php if(!empty($it['long_desc'])): ?>
                    <br><span style="color:#555; font-size:10px;"><?= nl2br(htmlspecialchars($it['long_desc'])) ?></span>
                <?php endif; ?>
            </td>
            <td align="center">S/. <?= number_format($it['price'], 0, '.', ',') ?></td>
            <td align="center">S/. <?= number_format($it['subtotal'], 0, '.', ',') ?></td>
        </tr>
        <?php endforeach; ?>
        
        <tr>
    <td colspan="2" style="border:none;"></td>
    <td class="total-label" style="border-bottom: 1px solid white; ">SUBTOTAL</td>
    <td class="total-value">
        S/. <?= number_format($proforma['subtotal'], 0, '.', ',') ?>
    </td>
</tr>

<tr>
    <td colspan="2" style="border:none;  "></td>
    <td class="total-label" style="border-bottom: 1px solid white; ">IGV (18%)</td>
    <td class="total-value">
        S/. <?= number_format($proforma['igv'], 0, '.', ',') ?>
    </td>
</tr>

<tr>
    <td colspan="2" style="border:none;"></td>
    <td class="total-label">TOTAL</td>
    <td class="total-value">
        S/. <?= number_format($proforma['total'], 0, '.', ',') ?>
    </td>
</tr>

    </tbody>
</table>

<div style="border: 1px solid #f37021; background-color: <?=$color_brand?>; color: white; padding: 8px; margin-top: 20px; font-size: 12px; font-weight: bold; ">CONDICIONES COMERCIALES</div>
<div style="border: 1px solid #f37021; padding: 0px;">
    <ul>
        <li>Precios incluyen IGV.</li>
        <li>Forma de pago: A convenir.</li>
        <li>Tiempo de entrega: Inmediata (sujeto a disponibilidad).</li>
        <li>Validez de la cotización: 7 días hábiles.</li>
    </ul>
</div>


<table class="contact-table">
    <tr>
        
        <th width="70%" style="border-left:1px solid #f37021;">CUENTAS BANCARIAS</td>
        <th width="30%" style="border-left: 1px solid #eee; border-right: 1px solid #f37021;">DATOS DE CONTACTO</td>
    </tr>
    
    <tr>
        <td style="vertical-align: middle;">
            <table class="banco-tabla">
                <tr>
                    <td class="banco-logo"> <img src="<?=$bcp_path?>" width="60"></td>
                    <td class="banco-nombre"> BCP SOLES</td>
                    <td class="banco-numero"> 1917232923026</td>
                    <td class="banco-cci"><b>| CCI:</b> 00219100723292302651</td>
                </tr>

                <tr>
                    <td class="banco-logo"><img src="<?=$bbva_path?>" width="55"></td>
                    <td class="banco-nombre" style="color:#004481;">BBVA SOLES</td>
                    <td class="banco-numero">0011-0750-0100039964</td>
                    <td class="banco-cci"><b>| CCI:</b> 0011750000100039964-70</td>
                </tr>

                <tr>
                    <td class="banco-logo"><img src="<?=$bbva_path?>" width="55"></td>
                    <td class="banco-nombre" style="color:#004481;">BBVA DÓLARES</td>
                    <td class="banco-numero">001107500100039980</td>
                    <td class="banco-cci"><b>| CCI:</b> 011750000100039980-76</td>
                </tr>
            </table>
        </td>
        <td style="padding:8px; border-right:1px solid <?=$color_brand?>; font-size: 11px; vertical-align: middle;">
            <b>Nombre:</b> Edwin Vega<br>
            <b>Email:</b> tradimacovaimport@gmail.com<br>
            <b>Cel:</b> 994280191
        </td>
    </tr>
</table>

<?php 
// Recorremos cada ítem de la proforma
foreach($items as $detalle): 
    
    // 1. Verificar si tiene ID de producto (no es manual)
    if (empty($detalle['id']) || $detalle['id'] == 0) {
        continue; // Si es manual, saltamos
    }

    // 2. Verificar si el usuario marcó "Incluir Ficha" (guardado en el JSON)
    // Si no definiste la columna, asumimos true, pero mejor validar
    $incluir = isset($detalle['incluir_ficha']) ? $detalle['incluir_ficha'] : 1;
    
    if ($incluir): 
?>
    <pagebreak />
    
    <?php include 'detalles_tecnicos.php'; ?>

<?php 
    endif; 
endforeach; 
?>
</body>
</html>


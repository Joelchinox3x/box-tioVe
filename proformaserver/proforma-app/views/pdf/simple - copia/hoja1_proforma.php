<style> <?php include 'styles.css'; ?> </style>

<?php include 'partials/header.php'; ?>

<table width="100%" style="padding-bottom: 10px;">
    <tr>
        <td width="20%" valign="top">
            <img src="assets/img/logo.png" width="150">
        </td>
        
        <td width="55%" align="center" valign="top">
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 2px;">TRADIMACOVA & IMPORTMAQ S.A.C.</div>
            <div style="font-size: 10px; color: #555;">Equipos y maquinarias con toda seguridad</div>
            <div style="font-size: 10px; color: #555;">MZ E - LT 6 AV. CAJAMARQUILLA - CHOSICA LURIGANCHO</div>
            <div style="font-size: 10px; font-weight:bold; color: green;">994280191 - 994279484</div>
            <div style="font-size: 10px; color: #555;">www.tradimacova.com</div>
        </td>
        
        <td width="25%" valign="top">
            <div class="border-brand" style="border-radius: 8px; text-align: center; overflow: hidden;">
                <div style="font-size: 12px; font-weight: bold; padding: 5px;">20613938347</div>
                <div class="bg-brand" style="font-weight: bold; padding: 5px;">COTIZACION</div>
                <div style="font-size: 12px; padding: 5px;">
                    CT01- <?= str_pad($fullData['proforma_id'], 8, '0', STR_PAD_LEFT) ?>
                </div>
            </div>
        </td>
    </tr>
</table>

<div class="border-brand" style="border-radius: 8px; padding: 10px; margin-top: 10px; font-size: 10px;">
    <table width="100%">
        <tr>
            <td width="10%"><b>Fecha</b></td>
            <td width="50%">: <?= date('d/m/Y', strtotime($fullData['fecha'])) ?></td>
            <td width="15%"><b>Vendedor</b></td>
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

<div style="margin-top: 15px; font-size: 11px;">
    <b>De mi mayor consideración:</b><br>
    Tengo el agrado de dirigirme a usted para comunicarle que, conforme a su solicitud, ponemos a su disposición la siguiente cotización.
</div>

<div class="text-brand" style="text-align: center; font-weight: bold; padding: 5px; margin-top: 15px; border: 1px solid #f37021; border-bottom: 0;">
    RESUMEN DE LA COTIZACIÓN
</div>

<table width="100%" style="border-collapse: collapse;">
    <thead>
        <tr class="bg-brand">
            <th width="10%" style="padding: 8px; border: 1px solid #f37021;">CANT.</th>
            <th width="60%" style="padding: 8px; border: 1px solid #f37021;">DESCRIPCIÓN</th>
            <th width="15%" style="padding: 8px; border: 1px solid #f37021;">P. UNIT.</th>
            <th width="15%" style="padding: 8px; border: 1px solid #f37021;">IMPORTE</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($fullData['items'] as $it): ?>
        <tr>
            <td align="center" style="border: 1px solid #ddd; padding: 8px;"><?= $it['qty'] ?></td>
            <td style="border: 1px solid #ddd; padding: 8px;"><?= htmlspecialchars($it['desc']) ?></td>
            <td align="center" style="border: 1px solid #ddd; padding: 8px;">S/. <?= number_format($it['price'], 2) ?></td>
            <td align="center" style="border: 1px solid #ddd; padding: 8px;">S/. <?= number_format($it['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table width="100%" style="margin-top: 0;">
    <tr>
        <td width="60%"></td> <td width="40%">
            <table width="100%" cellspacing="0">
                <tr>
                    <td class="bg-brand" align="right" style="padding: 5px; font-weight:bold;">SUBTOTAL</td>
                    <td align="right" style="border: 1px solid #ccc; padding: 5px;">S/. <?= number_format($totals['subtotal'], 2) ?></td>
                </tr>
                <tr>
                    <td class="bg-brand" align="right" style="padding: 5px; font-weight:bold;">IGV (18%)</td>
                    <td align="right" style="border: 1px solid #ccc; padding: 5px;">S/. <?= number_format($totals['igv'], 2) ?></td>
                </tr>
                <tr>
                    <td class="bg-brand" align="right" style="padding: 5px; font-weight:bold;">TOTAL</td>
                    <td align="right" style="border: 1px solid #ccc; padding: 5px; font-weight:bold;">S/. <?= number_format($totals['total'], 2) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="bg-brand" style="padding: 5px; font-weight: bold; margin-top: 20px; font-size: 11px;">CONDICIONES COMERCIALES</div>
<div style="border: 1px solid #ddd; padding: 10px; font-size: 10px;">
    <ul style="margin: 0; padding-left: 20px;">
        <li>Precios incluyen IGV.</li>
        <li>Forma de pago: A convenir.</li>
        <li>Tiempo de entrega: Inmediata (sujeto a disponibilidad).</li>
        <li>Validez de la cotización: 7 días hábiles.</li>
    </ul>
</div>

<div style="margin-top: 30px; border-top: 2px solid #f37021; padding-top: 10px; font-size: 9px;">
    <table width="100%">
        <tr>
            <td width="65%" valign="top">
                <div class="text-brand" style="font-weight: bold; margin-bottom: 5px;">CUENTAS BANCARIAS</div>
                <table width="100%">
                    <tr>
                        <td width="20"><img src="assets/img/bcp.png" width="15"></td>
                        <td style="font-weight:bold;">BCP SOLES</td>
                        <td>1917232923026</td>
                        <td>CCI: 00219100723292302651</td>
                    </tr>
                    <tr>
                        <td width="20"><img src="assets/img/bbva.png" width="35"></td>
                        <td style="font-weight:bold; color:#004481;">BBVA SOLES</td>
                        <td>0011-0750-0100039964</td>
                        <td>CCI: 0011750000100039964-70</td>
                    </tr>
                </table>
            </td>
            <td width="35%" valign="top" style="border-left: 1px solid #ccc; padding-left: 10px;">
                <div class="text-brand" style="font-weight: bold; margin-bottom: 5px;">CONTACTO</div>
                <b>Nombre:</b> Edwin Vega<br>
                <b>Email:</b> tradimacovaimport@gmail.com<br>
                <b>Cel:</b> 994280191
            </td>
        </tr>
    </table>
</div>
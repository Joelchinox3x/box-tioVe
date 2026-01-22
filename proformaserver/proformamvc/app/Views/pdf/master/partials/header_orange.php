
<?php
$icon_tel_path = PROJECT_ROOT . '/public/assets/img/icono_tel.png';
$icon_web_path = PROJECT_ROOT . '/public/assets/img/icono_web.png';

// Obtener el logo configurado en ajustes
require_once PROJECT_ROOT . '/app/Helpers/SettingsHelper.php';
$logoFromSettings = App\Helpers\SettingsHelper::getAppLogo();
$logo_path = PROJECT_ROOT . '/public/' . ($logoFromSettings ?: 'assets/img/logo.png');
?>



<table width="100%" style="border-bottom: 0px solid #ccc; padding-bottom: 10 px;">
    <tr>
        <td width="15%" class="logo-cell">
            <img src="<?= $logo_path ?>" class="logo">
        </td>
        
        <td class="celda-centro">
            <div class="titulo-principal">TRADIMACOVA & IMPORTMAQ S.A.C.</div>

            <div class="info-empresa">
                <div class="linea-info-bold">Equipos y maquinarias con toda seguridad</div>
                <div class="linea-info">MZ E - LT 6 AV. CAJAMARQUILLA - CHOSICA LURIGANCHO</div>

                <div class="linea-info-top">
                    <img src="<?= $icon_tel_path ?>" width="14" class="v-middle" style="margin-right: 4px;" />
                    <span class="v-middle">994280191 - 994279484</span>
                </div>

                <div class="linea-info">
                    <img src="<?= $icon_web_path ?>" width="14" class="v-middle" style="margin-right: 4px;" />
                    <span class="v-middle">www.tradimacova.com</span>
                </div>
            </div>
        </td>

        <td width="25%"> </td>
    </tr>
</table>
 
<!-- CAJA RUC CON CSS ORDENADO -->
<div class="caja-ruc-div" style="margin-top: -104px;">
    <div class="ruc-div-top">20613938347</div>
    <div class="ruc-div-mid">COTIZACION</div>
    <div class="ruc-div-bot"> TRA<?= str_pad($fullData['proforma_id'] ?? '0', 5, '0', STR_PAD_LEFT) ?></div>
</div>
<div style="margin-bottom: 20px;"></div>

 
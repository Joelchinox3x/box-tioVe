
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
        <td width="19%"></td>
        
        <td class="celda-centro" > 
            <div class="titulo-principal">TRADIMACOVA & IMPORTMAQ S.A.C.</div>

            <div>
                <div class="linea-slogan" style="color: white;">Equipos y maquinarias con toda seguridad</div>
            </div>
        </td>

        <td width="25%"> </td>
    </tr>
</table>

<div class="caja-logo" style="margin-top: -145px; margin-left: 5px;">
    <div class="caja-logo-cont">
        <img src="<?= $logo_path ?>" class="logo2" style="margin-top: 23px;">
    </div>
    
</div>

<div class="caja-ruc-div" style="margin-top: -105px;">
    <div class="ruc-div-top">20613938347</div>
    <div class="ruc-div-mid">COTIZACION</div>
    <div class="ruc-div-bot"> TRA<?= str_pad($fullData['proforma_id'] ?? '0', 5, '0', STR_PAD_LEFT) ?></div>
</div>
<div style="margin-bottom: 70px;"></div>

 
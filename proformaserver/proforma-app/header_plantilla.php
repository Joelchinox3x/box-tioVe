<table>
    <tr>
         <td width="15%" class="logo-cell">
            <img src="<?= $logo_path ?>" class="logo" />
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
<div class="caja-ruc" style="margin-top: -100px; ">
    <table cellspacing="0" cellpadding="0">
        <tr>
            <td class="fila-ruc-numero">20613938347</td>
        </tr>

        <tr>
            <td class="fila-label">COTIZACION</td>
        </tr>

        <tr>
            <td class="fila-codigo">CT01- <?= str_pad($proforma['id'], 8, '0', STR_PAD_LEFT) ?></td>
        </tr>
    </table>
</div>

<div style="margin-bottom: 20px;"></div>
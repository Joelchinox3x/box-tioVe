<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php
        $estadoLabel = strtoupper((string)($inscripcion['estado'] ?? 'INSCRITO'));
        $estadoDetalle = trim((string)($inscripcion['estado_detalle'] ?? 'Inscripcion creada, falta registrar pago'));

        $badgeColor = (string)($inscripcion['badge_color'] ?? '#1F618D');
        $badgeBg = (string)($inscripcion['badge_bg'] ?? '#EBF5FB');

        $comprobanteId = trim((string)($comprobante_id ?? '000000'));
        $fechaEmision = trim((string)($fecha_emision ?? date('d/m/Y H:i')));
        $tokenRaw = trim((string)($token ?? ''));
        $tokenShort = $tokenRaw !== '' ? substr($tokenRaw, 0, 24) . '...' : 'NO-TOKEN';

        // Armar nombre: PrimerNombre "Apodo" PrimerApellido (apodo en dorado)
        $nombreRaw = trim((string)($peleador['nombre'] ?? 'No definido'));
        $apellidosRaw = trim((string)($peleador['apellidos'] ?? ''));
        $apodoPeleador = trim((string)($peleador['apodo'] ?? ''));
        $primerNombre = htmlspecialchars(explode(' ', $nombreRaw)[0]);
        $primerApellido = $apellidosRaw !== '' ? htmlspecialchars(explode(' ', $apellidosRaw)[0]) : '';
        $nombrePeleadorHtml = $primerNombre;
        if ($apodoPeleador !== '' && $apodoPeleador !== '-') {
            $nombrePeleadorHtml .= ' <span style="color: #d98721;">"' . htmlspecialchars(ucfirst(strtolower($apodoPeleador))) . '"</span>';
        }
        if ($primerApellido !== '') {
            $nombrePeleadorHtml .= ' ' . $primerApellido;
        }
        $dniPeleador = trim((string)($peleador['dni'] ?? 'No definido'));
        $telefonoRaw = trim((string)($peleador['telefono'] ?? 'No definido'));
        $telDigits = preg_replace('/\D/', '', $telefonoRaw);
        if (strlen($telDigits) === 11 && strpos($telDigits, '51') === 0) {
            $telDigits = substr($telDigits, 2);
        }
        $telefonoPeleador = strlen($telDigits) === 9
            ? substr($telDigits, 0, 3) . ' ' . substr($telDigits, 3, 3) . ' ' . substr($telDigits, 6, 3)
            : $telefonoRaw;

        $nombreEvento = trim((string)($evento['nombre'] ?? 'Evento no definido'));
        $lugarEvento = trim((string)($evento['direccion'] ?? 'Por confirmar'));

        $fechaEventoTexto = 'Por confirmar';
        if (!empty($evento['fecha'])) {
            $tsEvento = strtotime((string)$evento['fecha']);
            $fechaEventoTexto = $tsEvento !== false ? date('d/m/Y', $tsEvento) : (string)$evento['fecha'];
        }

        $horaEventoTexto = '';
        if (!empty($evento['hora'])) {
            $horaEventoTexto = substr((string)$evento['hora'], 0, 5);
        }
        $fechaHoraEvento = trim($fechaEventoTexto . ($horaEventoTexto !== '' ? ' ' . $horaEventoTexto : ''));

        $montoNum = (int)(float)($inscripcion['monto'] ?? 0);
        $montoTexto = 'S/. ' . $montoNum . ' Soles';

        $metodoPago = trim((string)($inscripcion['metodo'] ?? 'Por definir'));
        $fechaEstado = trim((string)($inscripcion['fecha'] ?? $fechaEmision));
        $estadoActual = strtoupper(trim((string)($inscripcion['estado'] ?? 'INSCRITO')));
        $mostrarQr = $estadoActual === 'PAGADO';
        $managerNombreRaw = trim((string)($manager['nombre'] ?? 'Por asignar'));
        // Quitar prefijos como "Prof.", "Lic.", "Ing.", etc.
        $managerNombreRaw = preg_replace('/^(Prof\.|Lic\.|Ing\.|Dr\.|Sr\.|Sra\.)\s*/i', '', $managerNombreRaw);
        // Tomar solo nombre + primer apellido (primeras 2 palabras)
        $partes = explode(' ', trim($managerNombreRaw));
        $managerNombre = count($partes) >= 2 ? $partes[0] . ' ' . $partes[1] : $partes[0];
        $managerTelefono = trim((string)($manager['telefono'] ?? ''));
        $managerContacto = $managerNombre . ($managerTelefono !== '' ? ' - ' . $managerTelefono : '');

        $qrSize = 60;

        $bgSrc = $template_background ?? null;
        if (empty($bgSrc)) {
            $fallbackBgPath = realpath(__DIR__ . '/../fondo_comprob_01.png');
            if ($fallbackBgPath && is_file($fallbackBgPath)) {
                $bgSrc = 'file://' . str_replace('\\', '/', $fallbackBgPath);
            }
        }
        if (!empty($bgSrc) && !preg_match('/^(https?:)?\/\//i', (string)$bgSrc) && strpos((string)$bgSrc, 'file://') !== 0) {
            $bgSrc = 'file://' . str_replace('\\', '/', (string)$bgSrc);
        }
    ?>
    <style>
        @page {
            margin: 0;
            <?php if (!empty($bgSrc)): ?>
            background-image: url('<?php echo htmlspecialchars($bgSrc); ?>');
            background-repeat: no-repeat;
            background-position: center center;
            background-image-resize: 6;
            <?php endif; ?>
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        body {
            <?php if (!empty($bgSrc)): ?>
            background-image: url('<?php echo htmlspecialchars($bgSrc); ?>');
            background-repeat: no-repeat;
            background-position: center center;
            background-image-resize: 6;
            <?php else: ?>
            background: #10151d;
            <?php endif; ?>
        }

        /* ========== PANELES ABSOLUTOS ========== */
        .left-panel {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 70%;
            bottom: 5px;
            background: transparent;
            border: none;
            border-radius: 8px;
            color: #122036;
            padding: 8px 9px;
        }

        .right-panel {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 27%;
            bottom: 5px;
            background: transparent;
            border: none;
            border-radius: 8px;
            color: #f0f5fb;
            padding: 8px;
            text-align: center;
        }

        /* ========== FOOTER IZQUIERDO (nivel página) ========== */
        .costo-box {
            position: absolute;
            top: 392px;
            left: 10px;
            width: 325px;
            background-color: rgba(0, 0, 0, 0.45);
            border: 2px solid  #d98721;
            border-radius: 7px;
            text-align: left;
            padding: 1px 5px;
            font-size: 25px;
            font-weight: 800;
            color: #da3636;
            text-transform: uppercase;
        }

        .costo-box span {
            color: #d98721;
            font-family: 'montserrat', sans-serif;
            font-weight: bold;
        }

        .voucher-line {
            position: absolute;
            top: 442px;
            left: 10px;
            width: 325px;
            font-size: 10px;
            color: #fafafa;
            letter-spacing: 0.2px;
            text-align: center;
        }

        .peleador-box {
            position: absolute;
            top: 326px;
            left: 510px;
            border: 2px solid #d98721;
            border-radius: 7px;
            text-align: left;
            background-color: rgba(0, 0, 0, 0.45);
        }

        .peleador-box-top {
            background-color: transparent;
            font-size: 26px;
            height: 33px;
            line-height: 33px;
            font-weight: 800;
            color: #da3636;
            padding-left: 8px;
            padding-right: 8px;
              padding-top: 2px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .peleador-box-mid {
            background-color: transparent;
            font-size: 26px;
            height: 36px;
            line-height: 36px;
            color: #da3636;
            font-weight: 700;
            padding-left: 8px;
            padding-top: 1px;
            padding-bottom: 1px;
        }

        .peleador-box-bot {
            background-color: transparent;
            font-size: 26px;
            height: 36px;
            line-height: 36px;
            color: #da3636;
            font-weight: 600;
            padding-left: 8px;
            padding-right: 8px;
            padding-bottom: 2px;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }

        /* ========== PANEL DERECHO ========== */
        .right-title {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 0.12px;
            color: #ffffff;
            text-align: center;
        }

        .right-subline {
            margin: 0 0 5px;
            font-size: 8px;
            color: #d2e3f7;
            text-align: center;
        }

        .right-event {
            margin: 0 0 6px;
            font-size: 28px;
            color: #d98721;
            font-family: 'oswald', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            word-break: break-word;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 4px;
            padding: 3px 5px;
        }

        /* --- Status Box (patron proformamvc) --- */
        .status-box {
            width: 90%;
            border: 2px solid #000408;
            border-radius: 7px;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        .status-box-top {
            background-color: #ffffff;
            font-size: 34px;
            height: 150px;
            line-height: 150px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            color: <?php echo htmlspecialchars($badgeColor); ?>;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }

        .status-box-middle {
            background-color: #f0f4fa;
            font-size: 14px;
            height: 50px;
            line-height: 50px;
            color: #42586f;
        }

        .status-box-bottom {
            background-color: #e4eaf3;
            font-size: 10px;
            height: 40px;
            line-height: 40px;
            color: #7a8da3;
            letter-spacing: 0.2px;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        /* --- QR --- */
        .qr-shell { margin-top: 6px; text-align: center; }

        .qr-box {
            display: inline-block;
            background: #ffffff;
            border: 2px solid #000408;
            border-radius: 7px;
            padding-top: 10px;
            padding-right: 0px;
            padding-bottom: 10px;
            padding-left: 0px;
        }

        .qr {
            width: <?php echo (int)$qrSize; ?>mm;
            height: <?php echo (int)$qrSize; ?>mm;
            display: block;
        }

        .qr-voucher {
            margin-top: 4px;
            font-size: 11px;
            color: #ffffff;
            text-align: center;
            letter-spacing: 0.2px;
        }

        .qr-estado {
            margin-top: 3px;
            font-size: 14px;
            font-family: 'oswald', sans-serif;
            font-weight: bold;
            text-align: center;
            color: #1E8449;
            background-color: #EAF7EF;
            border-radius: 4px;
            height: 22px;
            line-height: 22px;
        }

        /* --- Footer derecho --- */
        .right-foot {
            margin: 0 0 02px;
            padding: 0 0px;
            font-size: 13px;
            color: #bfd0e5;
            text-align: center;
        }

        .right-date {
            margin: 0 0 2px;
            font-size: 32px;
            font-weight: 900;
            color: #d98721;
            text-align: center;
            background-color: #000000;
            border-radius: 5px;
            padding: 4px 0;
        }

        .right-place {
            margin: 0 0 2px;
            font-size: 20px;
            color: #d6e3f4;
            word-break: break-word;
            text-align: center;
        }

        .right-manager {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #f1f6ff;
            word-break: break-word;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- ==================== FOOTER IZQUIERDO (cajas nivel página) ==================== -->
<div class="costo-box">COSTO: <span style="font-size: 32px; font-weight: 900; color: #d98721;"><?php echo htmlspecialchars($montoTexto); ?></span></div>
<div class="voucher-line">Voucher N° <?php echo htmlspecialchars($comprobanteId); ?> | Emision: <?php echo htmlspecialchars($fechaEmision); ?></div>

<div class="peleador-box">
     
    <div class="peleador-box-top"><?php echo $nombrePeleadorHtml; ?></div>
 
    <div class="peleador-box-mid">DNI: <?php echo htmlspecialchars($dniPeleador); ?></div>
    <div class="peleador-box-bot">TEL: <?php echo htmlspecialchars($telefonoPeleador); ?></div>
</div>

<!-- ==================== PANEL DERECHO ==================== -->
<div class="right-panel">

    <!-- ARRIBA: Titulo + Evento -->
    <div class="right-title">MI INSCRIPCION AL EVENTO</div>
    <div style="height: 8px;"></div>
    <div class="right-event"><?php echo htmlspecialchars($nombreEvento); ?></div>

    <!-- Espaciador arriba-medio -->
    <div style="height: 10px;"></div>

    <!-- MEDIO: Status o QR -->
    <?php if ($mostrarQr): ?>
        <div class="qr-shell">
            <div class="qr-box">
                <img class="qr" src="<?php echo htmlspecialchars($qr_code); ?>" alt="QR">
            </div>
        </div>
        <div class="qr-voucher">Voucher N° <?php echo htmlspecialchars($comprobanteId); ?> | Emision: <?php echo htmlspecialchars($fechaEmision); ?></div>
        <div class="qr-estado">Estado: <?php echo htmlspecialchars($estadoLabel); ?></div>
    <?php else: ?>
        <div class="status-box">
            <div class="status-box-top"><?php echo htmlspecialchars($estadoLabel); ?></div>
            <div class="status-box-middle"><?php echo htmlspecialchars($estadoDetalle); ?></div>
            <div class="status-box-bottom">Voucher N° <?php echo htmlspecialchars($comprobanteId); ?> | Emision: <?php echo htmlspecialchars($fechaEmision); ?></div>
        </div>
    <?php endif; ?>
    <div class="right-foot">Documento generado automaticamente para control interno del evento.</div>

    <!-- Espaciador medio-abajo -->
    <div style="height: 15px;"></div>

    <!-- ABAJO: Info evento -->
    
    <div class="right-date"><?php echo htmlspecialchars($fechaEventoTexto); ?></div>
    <div class="right-place"><?php echo htmlspecialchars($lugarEvento); ?></div>
    <div class="right-manager">Manager: <?php echo htmlspecialchars($managerContacto); ?></div>

</div>

</body>
</html>

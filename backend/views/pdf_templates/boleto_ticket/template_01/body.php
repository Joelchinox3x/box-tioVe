<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php
        $boletoId = str_pad((string)($boleto['id'] ?? '0'), 6, '0', STR_PAD_LEFT);
        $codigoQr = trim((string)($boleto['codigo_qr'] ?? 'SIN-CODIGO'));
        $fechaEmision = trim((string)($fecha_emision ?? date('d/m/Y H:i')));
        $tokenRaw = trim((string)($token ?? ''));

        $compradorNombre = trim((string)($comprador['nombre'] ?? 'No definido'));
        $compradorDni = trim((string)($comprador['dni'] ?? 'No definido'));
        $compradorTelRaw = trim((string)($comprador['telefono'] ?? ''));
        $telDigits = preg_replace('/\D/', '', $compradorTelRaw);
        if (strlen($telDigits) === 11 && strpos($telDigits, '51') === 0) {
            $telDigits = substr($telDigits, 2);
        }
        $compradorTelefono = strlen($telDigits) === 9
            ? substr($telDigits, 0, 3) . ' ' . substr($telDigits, 3, 3) . ' ' . substr($telDigits, 6, 3)
            : $compradorTelRaw;

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

        $tipoBoleto = trim((string)($boleto['tipo'] ?? 'General'));
        $cantidadBoletos = (int)($boleto['cantidad'] ?? 1);
        $precioTotal = (float)($boleto['precio_total'] ?? 0);
        $precioTexto = 'S/. ' . number_format($precioTotal, 0) . ' Soles';
        $colorAccent = trim((string)($boleto['color_hex'] ?? '#d98721'));

        $qrSize = 50;

        $bgSrc = $template_background ?? null;
        if (!empty($bgSrc) && !preg_match('/^(https?:)?\/\//i', (string)$bgSrc) && strpos((string)$bgSrc, 'file://') !== 0) {
            $bgSrc = 'file://' . str_replace('\\', '/', (string)$bgSrc);
        }
    ?>
    <style>
        @page {
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #e0e8f0;
            background: #0d1117;
        }

        /* ========== PANELES ========== */
        .left-panel {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 62%;
            bottom: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 12px 15px;
        }

        .right-panel {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30%;
            bottom: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        /* ========== PANEL IZQUIERDO ========== */
        .evento-title {
            font-size: 28px;
            font-weight: 900;
            color: <?php echo htmlspecialchars($colorAccent); ?>;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 3px;
            word-break: break-word;
        }

        .evento-subtitle {
            font-size: 11px;
            color: #8899aa;
            margin: 0 0 12px;
            letter-spacing: 0.3px;
        }

        .info-label {
            font-size: 9px;
            color: #6b7d8e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 2px;
        }

        .info-value {
            font-size: 14px;
            color: #e0e8f0;
            font-weight: 600;
            margin: 0 0 2px;
        }

        .tipo-badge {
            display: inline-block;
            background: <?php echo htmlspecialchars($colorAccent); ?>;
            color: #ffffff;
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 5px 15px;
            border-radius: 6px;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .cantidad-text {
            font-size: 13px;
            color: #aabbcc;
            margin-top: 4px;
        }

        .codigo-box {
            position: absolute;
            bottom: 15px;
            left: 15px;
            font-size: 12px;
            color: #556677;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .precio-box {
            position: absolute;
            bottom: 15px;
            right: 250px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid <?php echo htmlspecialchars($colorAccent); ?>;
            border-radius: 7px;
            padding: 4px 12px;
            font-size: 22px;
            font-weight: 900;
            color: <?php echo htmlspecialchars($colorAccent); ?>;
        }

        /* ========== PANEL DERECHO ========== */
        .right-header {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin: 0 0 8px;
        }

        .qr-container {
            background: #ffffff;
            border: 2px solid <?php echo htmlspecialchars($colorAccent); ?>;
            border-radius: 8px;
            padding: 8px;
            display: inline-block;
            margin: 5px 0;
        }

        .qr {
            width: <?php echo (int)$qrSize; ?>mm;
            height: <?php echo (int)$qrSize; ?>mm;
            display: block;
        }

        .estado-badge {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 900;
            color: #1E8449;
            background-color: #EAF7EF;
            border-radius: 5px;
            padding: 4px 10px;
            display: inline-block;
            text-transform: uppercase;
        }

        .right-info {
            margin-top: 8px;
            font-size: 10px;
            color: #8899aa;
        }

        .right-fecha {
            margin-top: 8px;
            font-size: 22px;
            font-weight: 900;
            color: <?php echo htmlspecialchars($colorAccent); ?>;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 5px;
            padding: 3px 0;
        }

        .right-lugar {
            margin-top: 4px;
            font-size: 12px;
            color: #aabbcc;
            word-break: break-word;
        }

        .right-foot {
            margin-top: 6px;
            font-size: 8px;
            color: #556677;
        }
    </style>
</head>
<body>

<!-- ==================== PANEL IZQUIERDO ==================== -->
<div class="left-panel">
    <div class="evento-title"><?php echo htmlspecialchars($nombreEvento); ?></div>
    <div class="evento-subtitle"><?php echo htmlspecialchars($fechaEventoTexto); ?> <?php echo htmlspecialchars($horaEventoTexto); ?> | <?php echo htmlspecialchars($lugarEvento); ?></div>

    <div class="info-label">Comprador</div>
    <div class="info-value"><?php echo htmlspecialchars($compradorNombre); ?></div>

    <div class="info-label">DNI</div>
    <div class="info-value"><?php echo htmlspecialchars($compradorDni); ?></div>

    <?php if ($compradorTelefono !== ''): ?>
    <div class="info-label">Telefono</div>
    <div class="info-value"><?php echo htmlspecialchars($compradorTelefono); ?></div>
    <?php endif; ?>

    <div class="tipo-badge"><?php echo htmlspecialchars($tipoBoleto); ?></div>
    <?php if ($cantidadBoletos > 1): ?>
    <div class="cantidad-text">Cantidad: <?php echo $cantidadBoletos; ?> boletos</div>
    <?php endif; ?>

    <div class="codigo-box"><?php echo htmlspecialchars($codigoQr); ?></div>
    <div class="precio-box"><?php echo htmlspecialchars($precioTexto); ?></div>
</div>

<!-- ==================== PANEL DERECHO ==================== -->
<div class="right-panel">
    <div class="right-header">Boleto de Entrada</div>

    <div class="qr-container">
        <img class="qr" src="<?php echo htmlspecialchars($qr_code); ?>" alt="QR">
    </div>

    <div class="estado-badge">VERIFICADO</div>

    <div class="right-info">
        Boleto N° <?php echo htmlspecialchars($boletoId); ?><br>
        Emision: <?php echo htmlspecialchars($fechaEmision); ?>
    </div>

    <div class="right-fecha"><?php echo htmlspecialchars($fechaEventoTexto); ?></div>
    <div class="right-lugar"><?php echo htmlspecialchars($lugarEvento); ?></div>

    <div class="right-foot">Documento generado automaticamente. Presentar QR en la entrada del evento.</div>
</div>

</body>
</html>

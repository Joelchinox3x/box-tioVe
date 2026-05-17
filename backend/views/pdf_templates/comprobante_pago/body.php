<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 4px solid #f05d4b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #f05d4b;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            color: #666;
            margin-top: 8px;
        }
        .comprobante-num {
            font-size: 13px;
            color: #999;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 25px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #f05d4b;
            border-bottom: 2px solid #f05d4b;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .info-row {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
            color: #555;
        }
        .value {
            color: #333;
            display: inline-block;
        }
        .qr-section {
            text-align: center;
            margin-top: 40px;
            padding: 25px;
            background: #fff;
            border: 2px dashed #ddd;
            border-radius: 10px;
        }
        .qr-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            color: #555;
        }
        .qr-code {
            margin: 15px auto;
        }
        .qr-hint {
            font-size: 11px;
            color: #999;
            margin-top: 10px;
        }
        .estado-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .highlight {
            color: #f05d4b;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="logo">🥊 BOXTIOVE</div>
        <div class="title">COMPROBANTE DE INSCRIPCIÓN Y PAGO</div>
        <div class="comprobante-num">Comprobante N°: <span class="highlight">#<?php echo $comprobante_id; ?></span></div>
    </div>

    <!-- EVENTO -->
    <div class="section">
        <div class="section-title">📅 Información del Evento</div>
        <div class="info-row">
            <span class="label">Evento:</span>
            <span class="value"><?php echo htmlspecialchars($evento['nombre']); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span class="value"><?php echo date('d/m/Y', strtotime($evento['fecha'])); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Hora:</span>
            <span class="value"><?php echo $evento['hora']; ?></span>
        </div>
        <div class="info-row">
            <span class="label">Lugar:</span>
            <span class="value"><?php echo htmlspecialchars($evento['direccion']); ?></span>
        </div>
    </div>

    <!-- PELEADOR -->
    <div class="section">
        <div class="section-title">👤 Datos del Peleador</div>
        <div class="info-row">
            <span class="label">Nombre:</span>
            <span class="value"><?php echo htmlspecialchars($peleador['nombre']); ?></span>
        </div>
        <?php if (!empty($peleador['apodo'])): ?>
        <div class="info-row">
            <span class="label">Apodo:</span>
            <span class="value">"<?php echo htmlspecialchars($peleador['apodo']); ?>"</span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="label">DNI:</span>
            <span class="value"><?php echo htmlspecialchars($peleador['dni']); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Teléfono:</span>
            <span class="value"><?php echo htmlspecialchars($peleador['telefono']); ?></span>
        </div>
    </div>

    <!-- PAGO -->
    <div class="section">
        <div class="section-title">💰 Detalles del Pago</div>
        <div class="info-row">
            <span class="label">Monto Pagado:</span>
            <span class="value highlight">S/ <?php echo $pago['monto']; ?></span>
        </div>
        <div class="info-row">
            <span class="label">Método de Pago:</span>
            <span class="value"><?php echo htmlspecialchars($pago['metodo']); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Fecha de Pago:</span>
            <span class="value"><?php echo $pago['fecha']; ?></span>
        </div>
        <div class="info-row">
            <span class="label">Estado:</span>
            <span class="estado-badge">✓ <?php echo $pago['estado']; ?></span>
        </div>
    </div>

    <!-- QR CODE -->
    <div class="qr-section">
        <div class="qr-title">🔍 Código de Verificación</div>
        <img src="<?php echo $qr_code; ?>" width="150" height="150" class="qr-code" alt="QR Code">
        <div class="qr-hint">
            Escanea el código QR para verificar la autenticidad de este comprobante
        </div>
        <div class="qr-hint" style="margin-top: 10px; font-size: 9px;">
            Token: <?php echo substr($token, 0, 20); ?>...
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Este documento fue generado electrónicamente y es válido sin firma.</p>
        <p><strong>BOXTIOVE.COM</strong> - Sistema de Gestión de Eventos de Boxeo</p>
        <p>Para consultas: contacto@boxtiove.com</p>
    </div>
</body>
</html>

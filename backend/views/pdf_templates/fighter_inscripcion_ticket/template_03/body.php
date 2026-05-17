<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #111827;
            background: #111827;
        }
        .sheet {
            margin: 0;
            padding: 14px 16px;
            border: 2px solid #60a5fa;
            border-radius: 10px;
            background: #f9fafb;
        }
        .header { border-bottom: 2px solid #60a5fa; padding-bottom: 8px; margin-bottom: 10px; }
        .brand { font-size: 17px; font-weight: bold; color: #1e3a8a; }
        .title { font-size: 12px; font-weight: bold; color: #1d4ed8; }
        .meta { font-size: 10px; color: #4b5563; margin-top: 3px; }
        .left { width: 100%; }
        .box { border: 1px solid #dbeafe; border-radius: 8px; padding: 8px; margin-top: 7px; background: #ffffff; }
        .section-title { font-size: 10px; font-weight: bold; color: #1e40af; text-transform: uppercase; margin-bottom: 5px; }
        .line { font-size: 10px; margin-bottom: 3px; }
        .label { width: 62px; display: inline-block; font-weight: bold; color: #6b7280; }
        .value { color: #111827; }
        .footer { clear: both; margin-top: 8px; text-align: center; font-size: 9px; color: #374151; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="brand">BOXTIOVE</div>
            <div class="title">TICKET DE INSCRIPCION - TEMPLATE 03</div>
            <div class="meta">Codigo #<?php echo htmlspecialchars($ticket_code); ?> | Emision <?php echo htmlspecialchars($fecha_emision); ?></div>
        </div>

        <div class="left">
            <div class="box">
                <div class="section-title">Peleador</div>
                <div class="line"><span class="label">Nombre:</span><span class="value"><?php echo htmlspecialchars($peleador['nombre'] ?? 'N/A'); ?></span></div>
                <div class="line"><span class="label">Apodo:</span><span class="value"><?php echo htmlspecialchars($peleador['apodo'] ?? '-'); ?></span></div>
                <div class="line"><span class="label">DNI:</span><span class="value"><?php echo htmlspecialchars($peleador['dni'] ?? 'N/A'); ?></span></div>
                <div class="line"><span class="label">Telefono:</span><span class="value"><?php echo htmlspecialchars($peleador['telefono'] ?? 'N/A'); ?></span></div>
            </div>
            <div class="box">
                <div class="section-title">Evento</div>
                <div class="line"><span class="label">Evento:</span><span class="value"><?php echo htmlspecialchars($evento['nombre'] ?? 'Por anunciar'); ?></span></div>
                <div class="line"><span class="label">Fecha:</span><span class="value"><?php echo !empty($evento['fecha']) ? date('d/m/Y', strtotime($evento['fecha'])) : 'Por confirmar'; ?></span></div>
                <div class="line"><span class="label">Hora:</span><span class="value"><?php echo htmlspecialchars($evento['hora'] ?? 'Por confirmar'); ?></span></div>
                <div class="line"><span class="label">Lugar:</span><span class="value"><?php echo htmlspecialchars($evento['direccion'] ?? 'Por confirmar'); ?></span></div>
                <div class="line"><span class="label">Monto:</span><span class="value">S/ <?php echo htmlspecialchars($inscripcion['monto'] ?? '0.00'); ?></span></div>
            </div>
        </div>

        <div class="footer">Documento generado automaticamente. BOXTIOVE.COM</div>
    </div>
</body>
</html>

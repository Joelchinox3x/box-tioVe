<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php
        $bgSrc = $template_background ?? null;
        if (!empty($bgSrc) && !preg_match('/^(https?:)?\/\//i', (string)$bgSrc) && strpos((string)$bgSrc, 'file://') !== 0) {
            $bgSrc = 'file://' . str_replace('\\', '/', (string)$bgSrc);
        }
    ?>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
            <?php if (!empty($bgSrc)): ?>
            background-image: url('<?php echo htmlspecialchars($bgSrc); ?>');
            background-repeat: no-repeat;
            background-position: center center;
            background-image-resize: 6;
            <?php else: ?>
            background: #0f1115;
            <?php endif; ?>
        }
        .sheet {
            margin: 0;
            padding: 16px 18px;
            border: 2px solid #f59e0b;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
        }
        .header {
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .brand {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        .title {
            font-size: 13px;
            font-weight: bold;
            color: #b45309;
        }
        .meta {
            font-size: 10px;
            color: #374151;
            margin-top: 3px;
        }
        .row {
            width: 100%;
        }
        .left {
            width: 100%;
        }
        .section {
            margin-top: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 7px 9px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .line {
            font-size: 10px;
            margin-bottom: 3px;
        }
        .label {
            color: #6b7280;
            font-weight: bold;
            display: inline-block;
            width: 62px;
        }
        .value {
            color: #111827;
        }
        .footer {
            clear: both;
            margin-top: 8px;
            font-size: 9px;
            color: #374151;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="brand">BOXTIOVE</div>
            <div class="title">TICKET DE INSCRIPCION - PELEADOR</div>
            <div class="meta">Codigo #<?php echo htmlspecialchars($ticket_code); ?> | Emision <?php echo htmlspecialchars($fecha_emision); ?></div>
        </div>

        <div class="row">
            <div class="left">
                <div class="section">
                    <div class="section-title">Datos del peleador</div>
                    <div class="line"><span class="label">Nombre:</span><span class="value"><?php echo htmlspecialchars($peleador['nombre'] ?? 'N/A'); ?></span></div>
                    <div class="line"><span class="label">Apodo:</span><span class="value"><?php echo htmlspecialchars($peleador['apodo'] ?? '-'); ?></span></div>
                    <div class="line"><span class="label">DNI:</span><span class="value"><?php echo htmlspecialchars($peleador['dni'] ?? 'N/A'); ?></span></div>
                    <div class="line"><span class="label">Telefono:</span><span class="value"><?php echo htmlspecialchars($peleador['telefono'] ?? 'N/A'); ?></span></div>
                </div>

                <div class="section">
                    <div class="section-title">Evento</div>
                    <div class="line"><span class="label">Evento:</span><span class="value"><?php echo htmlspecialchars($evento['nombre'] ?? 'Por anunciar'); ?></span></div>
                    <div class="line"><span class="label">Fecha:</span><span class="value"><?php echo !empty($evento['fecha']) ? date('d/m/Y', strtotime($evento['fecha'])) : 'Por confirmar'; ?></span></div>
                    <div class="line"><span class="label">Hora:</span><span class="value"><?php echo htmlspecialchars($evento['hora'] ?? 'Por confirmar'); ?></span></div>
                    <div class="line"><span class="label">Lugar:</span><span class="value"><?php echo htmlspecialchars($evento['direccion'] ?? 'Por confirmar'); ?></span></div>
                    <div class="line"><span class="label">Monto:</span><span class="value">S/ <?php echo htmlspecialchars($inscripcion['monto'] ?? '0.00'); ?></span></div>
                </div>
            </div>
        </div>

        <div class="footer">
            Documento generado automaticamente. BOXTIOVE.COM
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificacion de Boleto - BOXTIOVE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f13;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #1a1a24;
            border-radius: 20px;
            max-width: 420px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        .header {
            background: linear-gradient(135deg, #3498db 0%, #2471a3 100%);
            padding: 30px 24px;
            text-align: center;
        }
        .header.header-used {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
        }
        .header.header-invalid {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        .brand {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 6px;
        }
        .header-title {
            font-size: 20px;
            font-weight: 700;
        }

        .status-icon {
            text-align: center;
            margin-top: -20px;
            position: relative;
            z-index: 2;
        }
        .status-icon .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            border: 4px solid #1a1a24;
        }
        .icon-valid { background: #27ae60; }
        .icon-used { background: #e67e22; }
        .icon-invalid { background: #e74c3c; }

        .body { padding: 24px; }

        .status-text {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 24px;
        }
        .status-text h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .status-text p {
            font-size: 13px;
            color: #888;
        }
        .text-valid { color: #2ecc71; }
        .text-used { color: #e67e22; }
        .text-invalid { color: #e74c3c; }

        .info-group {
            background: #12121a;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .info-group-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #555;
            margin-bottom: 12px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-activo { background: rgba(46,204,113,0.15); color: #2ecc71; }
        .badge-usado { background: rgba(230,126,34,0.15); color: #e67e22; }
        .badge-cancelado { background: rgba(231,76,60,0.15); color: #e74c3c; }
        .badge-verificado { background: rgba(52,152,219,0.15); color: #3498db; }
        .badge-pendiente { background: rgba(241,196,15,0.15); color: #f1c40f; }

        .tipo-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .btn-usar {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #27ae60 0%, #219a52 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 16px;
            transition: opacity 0.3s, transform 0.1s;
        }
        .btn-usar:hover { opacity: 0.9; }
        .btn-usar:active { transform: scale(0.98); }
        .btn-usar:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-result {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            margin-top: 16px;
            text-align: center;
        }
        .btn-result-success { background: rgba(39,174,96,0.2); color: #2ecc71; }
        .btn-result-warning { background: rgba(230,126,34,0.2); color: #e67e22; }

        .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-right: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .footer {
            text-align: center;
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .footer p {
            font-size: 11px;
            color: #444;
        }
    </style>
</head>
<body>

<div class="card">
<?php if (!$valid): ?>
    <!-- ========== BOLETO NO ENCONTRADO ========== -->
    <div class="header header-invalid">
        <div class="brand">BOXTIOVE</div>
        <div class="header-title">Verificacion de Boleto</div>
    </div>
    <div class="status-icon">
        <div class="icon icon-invalid">&#10007;</div>
    </div>
    <div class="body">
        <div class="status-text">
            <h2 class="text-invalid">BOLETO NO ENCONTRADO</h2>
            <p><?php echo htmlspecialchars($errorMsg ?? 'Token invalido o boleto inexistente'); ?></p>
        </div>
    </div>

<?php elseif ($data['estado_boleto'] === 'usado'): ?>
    <!-- ========== BOLETO YA UTILIZADO ========== -->
    <div class="header header-used">
        <div class="brand">BOXTIOVE</div>
        <div class="header-title">Verificacion de Boleto</div>
    </div>
    <div class="status-icon">
        <div class="icon icon-used">&#9888;</div>
    </div>
    <div class="body">
        <div class="status-text">
            <h2 class="text-used">BOLETO YA UTILIZADO</h2>
            <p>Este boleto fue usado el <?php echo htmlspecialchars($data['fecha_uso'] ?? 'N/A'); ?></p>
        </div>

        <div class="info-group">
            <div class="info-group-title">Datos del Comprador</div>
            <div class="info-row">
                <span class="info-label">Nombre</span>
                <span class="info-value"><?php echo htmlspecialchars($data['comprador_nombres_apellidos']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DNI</span>
                <span class="info-value"><?php echo htmlspecialchars($data['comprador_dni']); ?></span>
            </div>
        </div>

        <div class="info-group">
            <div class="info-group-title">Evento</div>
            <div class="info-row">
                <span class="info-label">Evento</span>
                <span class="info-value"><?php echo htmlspecialchars($data['evento_nombre']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo</span>
                <span class="info-value">
                    <span class="tipo-badge" style="background: <?php echo htmlspecialchars($data['color_hex'] ?? '#3498db'); ?>;">
                        <?php echo htmlspecialchars($data['tipo_boleto']); ?>
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Cantidad</span>
                <span class="info-value"><?php echo (int)$data['cantidad']; ?></span>
            </div>
        </div>

        <div class="btn-result btn-result-warning">BOLETO YA UTILIZADO</div>
    </div>

<?php elseif ($data['estado_boleto'] === 'cancelado'): ?>
    <!-- ========== BOLETO CANCELADO ========== -->
    <div class="header header-invalid">
        <div class="brand">BOXTIOVE</div>
        <div class="header-title">Verificacion de Boleto</div>
    </div>
    <div class="status-icon">
        <div class="icon icon-invalid">&#10007;</div>
    </div>
    <div class="body">
        <div class="status-text">
            <h2 class="text-invalid">BOLETO CANCELADO</h2>
            <p>Este boleto ha sido cancelado y no es valido</p>
        </div>
    </div>

<?php else: ?>
    <!-- ========== BOLETO VALIDO ========== -->
    <div class="header">
        <div class="brand">BOXTIOVE</div>
        <div class="header-title">Verificacion de Boleto</div>
    </div>
    <div class="status-icon">
        <div class="icon icon-valid">&#10003;</div>
    </div>
    <div class="body">
        <div class="status-text">
            <h2 class="text-valid">BOLETO VALIDO</h2>
            <p>Pago verificado - Entrada autorizada</p>
        </div>

        <div class="info-group">
            <div class="info-group-title">Datos del Comprador</div>
            <div class="info-row">
                <span class="info-label">Nombre</span>
                <span class="info-value"><?php echo htmlspecialchars($data['comprador_nombres_apellidos']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DNI</span>
                <span class="info-value"><?php echo htmlspecialchars($data['comprador_dni']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefono</span>
                <span class="info-value"><?php echo htmlspecialchars($data['comprador_telefono'] ?? '-'); ?></span>
            </div>
        </div>

        <div class="info-group">
            <div class="info-group-title">Evento</div>
            <div class="info-row">
                <span class="info-label">Evento</span>
                <span class="info-value"><?php echo htmlspecialchars($data['evento_nombre']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha</span>
                <span class="info-value"><?php
                    $fechaEvt = $data['evento_fecha'] ?? null;
                    if ($fechaEvt) {
                        $ts = strtotime($fechaEvt);
                        echo $ts ? date('d/m/Y', $ts) : htmlspecialchars($fechaEvt);
                    } else {
                        echo 'Por confirmar';
                    }
                ?></span>
            </div>
            <?php if (!empty($data['evento_hora'])): ?>
            <div class="info-row">
                <span class="info-label">Hora</span>
                <span class="info-value"><?php echo htmlspecialchars(substr($data['evento_hora'], 0, 5)); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Lugar</span>
                <span class="info-value"><?php echo htmlspecialchars($data['evento_direccion'] ?? 'Por confirmar'); ?></span>
            </div>
        </div>

        <div class="info-group">
            <div class="info-group-title">Boleto</div>
            <div class="info-row">
                <span class="info-label">Tipo</span>
                <span class="info-value">
                    <span class="tipo-badge" style="background: <?php echo htmlspecialchars($data['color_hex'] ?? '#3498db'); ?>;">
                        <?php echo htmlspecialchars($data['tipo_boleto']); ?>
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Cantidad</span>
                <span class="info-value"><?php echo (int)$data['cantidad']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Precio</span>
                <span class="info-value" style="color: #2ecc71; font-size: 16px;">S/. <?php echo number_format((float)$data['precio_total'], 0); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Codigo</span>
                <span class="info-value" style="font-size: 12px; color: #888;"><?php echo htmlspecialchars($data['codigo_qr']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado Pago</span>
                <span class="info-value"><span class="badge badge-verificado">VERIFICADO</span></span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado Boleto</span>
                <span class="info-value"><span class="badge badge-activo">ACTIVO</span></span>
            </div>
        </div>

        <button class="btn-usar" id="btnUsar" onclick="marcarComoUsado()">
            MARCAR COMO USADO
        </button>
        <div id="resultMsg" style="display:none;"></div>
    </div>

    <script>
        function marcarComoUsado() {
            var btn = document.getElementById('btnUsar');
            var resultMsg = document.getElementById('resultMsg');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Procesando...';

            var codigoQr = '<?php echo addslashes($data['codigo_qr']); ?>';

            fetch('<?php echo rtrim(Config::API_URL, '/'); ?>/boletos/validar-qr', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ codigo_qr: codigoQr })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    btn.style.display = 'none';
                    resultMsg.style.display = 'block';
                    resultMsg.className = 'btn-result btn-result-success';
                    resultMsg.textContent = 'ENTRADA REGISTRADA';
                    // Actualizar badge de estado
                    var badges = document.querySelectorAll('.badge-activo');
                    badges.forEach(function(b) {
                        b.className = 'badge badge-usado';
                        b.textContent = 'USADO';
                    });
                } else {
                    btn.style.display = 'none';
                    resultMsg.style.display = 'block';
                    resultMsg.className = 'btn-result btn-result-warning';
                    resultMsg.textContent = res.message || 'No se pudo procesar';
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.textContent = 'MARCAR COMO USADO';
                alert('Error de conexion: ' + err.message);
            });
        }
    </script>
<?php endif; ?>

    <div class="footer">
        <p>BOXTIOVE - Sistema de Gestion de Eventos</p>
    </div>
</div>

</body>
</html>

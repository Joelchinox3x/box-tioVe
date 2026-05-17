<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Pago - BOXTIOVE</title>
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

        /* ===== HEADER ===== */
        .header {
            background: linear-gradient(135deg, #f05d4b 0%, #d94435 100%);
            padding: 30px 24px;
            text-align: center;
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

        /* ===== STATUS ICON ===== */
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
        .icon-invalid { background: #e74c3c; }
        .icon-lock { background: #f39c12; }

        /* ===== BODY ===== */
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
        .text-invalid { color: #e74c3c; }

        /* ===== LOGIN FORM ===== */
        .login-form { padding: 30px 24px; }
        .login-form .form-title { text-align: center; margin-bottom: 24px; }
        .login-form .form-title h2 { font-size: 18px; font-weight: 700; color: #f39c12; margin-bottom: 6px; }
        .login-form .form-title p { font-size: 13px; color: #888; }
        .input-group { margin-bottom: 16px; }
        .input-group label { display: block; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .input-group input { width: 100%; padding: 14px 16px; background: #12121a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; font-size: 15px; outline: none; transition: border-color 0.3s; }
        .input-group input:focus { border-color: #f05d4b; }
        .input-group input::placeholder { color: #555; }
        .btn-login { width: 100%; padding: 16px; background: linear-gradient(135deg, #f05d4b 0%, #d94435 100%); border: none; border-radius: 12px; color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; transition: opacity 0.3s, transform 0.1s; }
        .btn-login:hover { opacity: 0.9; }
        .btn-login:active { transform: scale(0.98); }
        .btn-login:disabled { opacity: 0.5; cursor: not-allowed; }
        .login-error { background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.3); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #e74c3c; text-align: center; display: none; }
        .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-right: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== STAFF BAR ===== */
        .staff-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
            background: rgba(240,93,75,0.08);
            border-bottom: 1px solid rgba(240,93,75,0.15);
        }
        .staff-bar .staff-info {
            font-size: 12px;
            color: #888;
        }
        .staff-bar .staff-name {
            color: #f05d4b;
            font-weight: 600;
        }
        .btn-logout {
            background: none;
            border: 1px solid rgba(255,255,255,0.15);
            color: #888;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        /* ===== INFO ROWS ===== */
        .info-group {
            background: #12121a;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .info-group-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #f05d4b;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(240,93,75,0.15);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        .info-row:not(:last-child) {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .info-label {
            font-size: 13px;
            color: #777;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #eee;
            text-align: right;
            max-width: 60%;
        }

        /* ===== MONTO HIGHLIGHT ===== */
        .monto-box {
            background: linear-gradient(135deg, rgba(240,93,75,0.12) 0%, rgba(39,174,96,0.12) 100%);
            border: 1px solid rgba(240,93,75,0.2);
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            margin-bottom: 14px;
        }
        .monto-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #888;
            margin-bottom: 6px;
        }
        .monto-value {
            font-size: 32px;
            font-weight: 800;
            color: #2ecc71;
        }
        .monto-currency {
            font-size: 16px;
            font-weight: 600;
            color: #888;
        }

        /* ===== BADGE ===== */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pagado { background: rgba(39,174,96,0.15); color: #2ecc71; }
        .badge-pendiente { background: rgba(243,156,18,0.15); color: #f39c12; }
        .badge-inscrito { background: rgba(52,152,219,0.15); color: #3498db; }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            padding: 16px 24px 24px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .footer p {
            font-size: 11px;
            color: #555;
            line-height: 1.6;
        }
        .footer .brand-footer {
            font-weight: 700;
            color: #f05d4b;
        }

        /* ===== ERROR PAGE ===== */
        .error-body {
            padding: 40px 24px;
            text-align: center;
        }
        .error-body h2 {
            font-size: 20px;
            color: #e74c3c;
            margin-bottom: 8px;
        }
        .error-body p {
            font-size: 14px;
            color: #888;
            line-height: 1.6;
        }

        /* ===== SPINNER ===== */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card { animation: fadeInUp 0.5s ease-out; }
        .info-group { animation: fadeInUp 0.5s ease-out backwards; }
        .info-group:nth-child(2) { animation-delay: 0.1s; }
        .info-group:nth-child(3) { animation-delay: 0.2s; }
        .monto-box { animation: fadeInUp 0.5s ease-out 0.15s backwards; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .icon-valid { animation: pulse 2s ease-in-out infinite; }

        .hidden { display: none !important; }

        /* ===== CHECK-IN ===== */
        .checkin-box {
            margin: 14px 0 0;
            padding: 16px;
            border-radius: 14px;
            text-align: center;
        }
        .checkin-box.checkin-pending {
            background: rgba(39,174,96,0.08);
            border: 1px solid rgba(39,174,96,0.2);
        }
        .checkin-box.checkin-done {
            background: rgba(243,156,18,0.08);
            border: 1px solid rgba(243,156,18,0.2);
        }
        .checkin-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .checkin-label.pending { color: #27ae60; }
        .checkin-label.done { color: #f39c12; }
        .checkin-time {
            font-size: 18px;
            font-weight: 700;
            color: #f39c12;
            margin-bottom: 4px;
        }
        .checkin-note {
            font-size: 12px;
            color: #888;
        }
        .btn-checkin {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #27ae60 0%, #219a52 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity 0.3s, transform 0.1s;
            letter-spacing: 1px;
        }
        .btn-checkin:hover { opacity: 0.9; }
        .btn-checkin:active { transform: scale(0.98); }
        .btn-checkin:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="brand">BOXTIOVE</div>
            <div class="header-title">Verificación de Pago</div>
        </div>

        <?php if ($staffLoggedIn): ?>
            <!-- ===== STAFF LOGUEADO ===== -->
            <div class="staff-bar">
                <span class="staff-info">Staff: <span class="staff-name"><?php echo htmlspecialchars($staffName); ?></span></span>
                <button class="btn-logout" onclick="doLogout()">Cerrar sesión</button>
            </div>

            <?php if ($valid): ?>
                <!-- PAGO VÁLIDO -->
                <div class="status-icon">
                    <div class="icon icon-valid">&#10003;</div>
                </div>
                <div class="body">
                    <div class="status-text">
                        <h2 class="text-valid">Pago Verificado</h2>
                        <p>Este comprobante es auténtico y válido</p>
                    </div>
                    <div class="monto-box">
                        <div class="monto-label">Monto pagado</div>
                        <div class="monto-value">
                            <span class="monto-currency">S/ </span><?php echo htmlspecialchars($data['monto']); ?>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-group-title">Peleador</div>
                        <div class="info-row">
                            <span class="info-label">Nombre</span>
                            <span class="info-value"><?php echo htmlspecialchars($data['peleador']); ?></span>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-group-title">Evento</div>
                        <div class="info-row">
                            <span class="info-label">Nombre</span>
                            <span class="info-value"><?php echo htmlspecialchars($data['evento']); ?></span>
                        </div>
                    </div>
                    <div class="info-group">
                        <div class="info-group-title">Detalles del pago</div>
                        <div class="info-row">
                            <span class="info-label">Estado</span>
                            <span class="info-value">
                                <span class="badge <?php echo $data['estado'] === 'PAGADO' ? 'badge-pagado' : ($data['estado'] === 'PENDIENTE' ? 'badge-pendiente' : 'badge-inscrito'); ?>">
                                    <?php echo htmlspecialchars($data['estado']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha</span>
                            <span class="info-value"><?php echo htmlspecialchars($data['fecha_generacion']); ?></span>
                        </div>
                    </div>

                    <!-- CHECK-IN / ASISTENCIA -->
                    <div id="checkinSection">
                        <?php if (!empty($data['checkin'])): ?>
                            <div class="checkin-box checkin-done">
                                <div class="checkin-label done">Ya Ingresó</div>
                                <div class="checkin-time"><?php echo htmlspecialchars($data['fecha_checkin']); ?></div>
                                <div class="checkin-note">Este peleador ya registró su entrada</div>
                            </div>
                        <?php else: ?>
                            <div class="checkin-box checkin-pending" id="checkinPending">
                                <div class="checkin-label pending">Sin registro de entrada</div>
                                <button class="btn-checkin" id="btnCheckin" onclick="doCheckin()">
                                    REGISTRAR ENTRADA
                                </button>
                            </div>
                            <div class="checkin-box checkin-done hidden" id="checkinDone">
                                <div class="checkin-label done" id="checkinDoneLabel">Entrada Registrada</div>
                                <div class="checkin-time" id="checkinDoneTime"></div>
                                <div class="checkin-note" id="checkinDoneNote"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="footer">
                    <p>Verificado el <?php echo date('d/m/Y \a \l\a\s H:i'); ?></p>
                    <p><span class="brand-footer">BOXTIOVE.COM</span> &mdash; Gestión de Eventos de Boxeo</p>
                </div>

            <?php else: ?>
                <!-- PAGO NO ENCONTRADO -->
                <div class="status-icon">
                    <div class="icon icon-invalid">&#10007;</div>
                </div>
                <div class="error-body">
                    <h2>Comprobante No Válido</h2>
                    <p>No se encontró ningún pago asociado a este código QR. El comprobante puede ser falso o haber expirado.</p>
                </div>
                <div class="footer">
                    <p>Si crees que es un error, contacta al administrador.</p>
                    <p><span class="brand-footer">BOXTIOVE.COM</span> &mdash; Gestión de Eventos de Boxeo</p>
                </div>
            <?php endif; ?>

        <?php elseif ($qrPublicView === 'mostrar_staff'): ?>
            <!-- ===== LOGIN STAFF (visible) ===== -->
            <div class="status-icon">
                <div class="icon icon-lock">&#128274;</div>
            </div>

            <div class="login-form" id="loginForm">
                <div class="form-title">
                    <h2>Acceso Staff</h2>
                    <p>Inicia sesión para verificar este comprobante</p>
                </div>

                <div class="login-error" id="loginError"></div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" id="emailInput" placeholder="tu@email.com" autocomplete="email" />
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" id="passwordInput" placeholder="Tu contraseña" autocomplete="current-password" />
                </div>

                <button class="btn-login" id="btnLogin" onclick="doLogin()">
                    Iniciar Sesión
                </button>
            </div>

            <div class="footer">
                <p>Solo personal autorizado (staff/admin).</p>
                <p style="margin-top: 6px; font-size: 10px; color: #444;">También puedes verificar desde la app BoxTiove.</p>
                <p><span class="brand-footer">BOXTIOVE.COM</span> &mdash; Gestión de Eventos de Boxeo</p>
            </div>
        <?php else: ?>
            <!-- ===== VISTA PÚBLICA (sin login staff) ===== -->
            <div class="status-icon">
                <div class="icon icon-valid">&#10003;</div>
            </div>
            <div class="body" style="text-align: center;">
                <h2 style="color: #2ecc71; margin-bottom: 8px;">Comprobante Válido</h2>
                <p style="color: #888; font-size: 14px; line-height: 1.6;">Este QR pertenece a un comprobante de inscripción registrado en el sistema.</p>
                <p style="color: #555; font-size: 12px; margin-top: 16px;">Para ver los detalles, utiliza la app BoxTiove.</p>
            </div>
            <div class="footer">
                <p><span class="brand-footer">BOXTIOVE.COM</span> &mdash; Gestión de Eventos de Boxeo</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        var API_BASE = '<?php echo Config::API_URL; ?>';

        <?php if (!$staffLoggedIn && $qrPublicView === 'mostrar_staff'): ?>
        function doLogin() {
            var email = document.getElementById('emailInput').value.trim();
            var password = document.getElementById('passwordInput').value;
            var btn = document.getElementById('btnLogin');
            var errorDiv = document.getElementById('loginError');

            if (!email || !password) {
                errorDiv.textContent = 'Ingresa email y contraseña';
                errorDiv.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Verificando...';
            errorDiv.style.display = 'none';

            fetch(API_BASE + '/verificar-pago-auth', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ email: email, password: password })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Credenciales inválidas';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Iniciar Sesión';
                }
            })
            .catch(function() {
                errorDiv.textContent = 'Error de conexión. Intenta de nuevo.';
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Iniciar Sesión';
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var btn = document.getElementById('btnLogin');
                if (btn && !btn.disabled) doLogin();
            }
        });
        <?php endif; ?>

        <?php if ($staffLoggedIn && $valid && empty($data['checkin'])): ?>
        function doCheckin() {
            var btn = document.getElementById('btnCheckin');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Registrando...';

            // Extraer token de la URL actual
            var pathParts = window.location.pathname.split('/');
            var token = pathParts[pathParts.length - 1];

            fetch(API_BASE + '/checkin/' + token, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var pending = document.getElementById('checkinPending');
                var done = document.getElementById('checkinDone');
                var doneLabel = document.getElementById('checkinDoneLabel');
                var doneTime = document.getElementById('checkinDoneTime');
                var doneNote = document.getElementById('checkinDoneNote');

                pending.classList.add('hidden');
                done.classList.remove('hidden');

                if (data.success && !data.duplicate) {
                    doneLabel.textContent = 'Entrada Registrada';
                    doneTime.textContent = data.data.fecha_checkin || 'Ahora';
                    doneNote.textContent = 'Check-in exitoso';
                } else if (data.duplicate) {
                    doneLabel.textContent = 'Ya Ingresó';
                    doneLabel.className = 'checkin-label done';
                    doneTime.textContent = data.data.fecha_checkin || '';
                    doneNote.textContent = 'Este peleador ya registró su entrada anteriormente';
                } else {
                    doneLabel.textContent = 'Error';
                    doneTime.textContent = '';
                    doneNote.textContent = data.message || 'No se pudo registrar la entrada';
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'REGISTRAR ENTRADA';
                alert('Error de conexión. Intenta de nuevo.');
            });
        }
        <?php endif; ?>

        <?php if ($staffLoggedIn): ?>
        function doLogout() {
            fetch(API_BASE + '/verificar-pago-logout', {
                method: 'POST',
                credentials: 'include'
            })
            .then(function() { window.location.reload(); })
            .catch(function() { window.location.reload(); });
        }
        <?php endif; ?>
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/AnunciosController.php';
require_once __DIR__ . '/../helpers/TelegramAPI.php';
require_once __DIR__ . '/../helpers/ConversationManager.php';

class TelegramWebhookController {
    private $conn;
    private $anunciosController;
    private $api;
    private $conv;
    private $upload_dir;

    private $bot_token = '';
    private $admin_chat_id = '';
    private $webhook_secret = '';

    private $evento_id = null;
    private $evento_nombre = '';

    public function __construct($db) {
        $this->conn = $db;
        $this->anunciosController = new AnunciosController($db);
        $this->conv = new ConversationManager($db);
        $this->upload_dir = __DIR__ . '/../files/anuncios/';
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        $this->loadSettings();
        $this->api = new TelegramAPI($this->bot_token);
        $this->loadEvento();
    }

    private function loadSettings() {
        try {
            $query = "SHOW TABLES LIKE 'system_settings'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            if ($stmt->rowCount() === 0) return;

            $keys = ['telegram_bot_token', 'telegram_admin_chat_id', 'telegram_webhook_secret'];
            foreach ($keys as $key) {
                $query = "SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':key', $key);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['setting_value'])) {
                    $prop = str_replace('telegram_', '', $key);
                    $this->$prop = $row['setting_value'];
                }
            }
        } catch (PDOException $e) {
            error_log("Error cargando settings telegram: " . $e->getMessage());
        }
    }

    private function loadEvento() {
        try {
            $stmt = $this->conn->query("SELECT id, nombre FROM eventos ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $this->evento_id = (int)$row['id'];
                $this->evento_nombre = $row['nombre'];
            }
        } catch (PDOException $e) {
            error_log("Error cargando evento: " . $e->getMessage());
        }
    }

    // ================================================================
    // PUNTO DE ENTRADA
    // ================================================================

    public function handleWebhook($payload) {
        if (!$payload) {
            http_response_code(400);
            return ["success" => false, "message" => "Payload vacio"];
        }

        if (!empty($this->webhook_secret)) {
            $secretHeader = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
            if ($secretHeader !== $this->webhook_secret) {
                http_response_code(403);
                return ["success" => false, "message" => "No autorizado"];
            }
        }

        // Limpiar expiradas 1 de cada 50 requests
        if (rand(1, 50) === 1) {
            $this->conv->cleanExpired();
        }

        // Botones inline
        if (isset($payload['callback_query'])) {
            return $this->handleCallbackQuery($payload['callback_query']);
        }

        // Mensaje normal
        $message = $payload['message'] ?? null;
        if (!$message) {
            return ["success" => true, "message" => "Sin mensaje"];
        }

        $chatId = $message['chat']['id'] ?? null;
        if (!$this->isAuthorized($chatId)) {
            $this->api->sendMessage($chatId, "No estas autorizado para usar este bot.");
            return ["success" => false, "message" => "No autorizado"];
        }

        $text = $message['text'] ?? '';

        // /start o /menu siempre muestra menu
        if ($text === '/start' || $text === '/menu') {
            $this->conv->reset($chatId);
            $this->showMainMenu($chatId);
            return ["success" => true, "message" => "Menu mostrado"];
        }

        // /cancelar
        if ($text === '/cancelar') {
            $this->conv->reset($chatId);
            $this->api->sendMessage($chatId, "Operacion cancelada.");
            $this->showMainMenu($chatId);
            return ["success" => true, "message" => "Cancelado"];
        }

        // Conversacion activa -> procesar input
        $state = $this->conv->getState($chatId);
        if ($state['state'] !== ConversationManager::IDLE) {
            return $this->handleConversationInput($chatId, $message, $state);
        }

        // Texto suelto sin conversacion -> mostrar menu
        $this->showMainMenu($chatId);
        return ["success" => true, "message" => "Menu mostrado"];
    }

    private function isAuthorized($chatId) {
        if (empty($this->admin_chat_id)) return false;
        return (string)$chatId === (string)$this->admin_chat_id;
    }

    // ================================================================
    // MENU PRINCIPAL
    // ================================================================

    private function showMainMenu($chatId, $messageId = null) {
        $text = "<b>EventoBox - Panel de Control</b>\n\n";
        $text .= "Evento: <b>{$this->evento_nombre}</b>\n\n";
        $text .= "Selecciona una opcion:";

        $keyboard = TelegramAPI::inlineKeyboard([
            [
                TelegramAPI::btn("Crear Anuncio", "menu:anuncio"),
                TelegramAPI::btn("Vender Boleto", "menu:venta"),
            ],
            [
                TelegramAPI::btn("Ver Anuncios", "menu:ver_anuncios"),
                TelegramAPI::btn("Estadisticas", "menu:stats"),
            ],
        ]);

        if ($messageId) {
            $this->api->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->api->sendMessage($chatId, $text, $keyboard);
        }
    }

    // ================================================================
    // CALLBACK QUERIES (BOTONES)
    // ================================================================

    private function handleCallbackQuery($callbackQuery) {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $callbackId = $callbackQuery['id'];
        $data = $callbackQuery['data'] ?? '';

        if (!$this->isAuthorized($chatId)) {
            $this->api->answerCallback($callbackId, "No autorizado", true);
            return ["success" => false];
        }

        $this->api->answerCallback($callbackId);

        $parts = explode(':', $data);
        $action = $parts[0] ?? '';
        $value = $parts[1] ?? '';

        switch ($action) {
            case 'menu':
                return $this->handleMenuAction($chatId, $messageId, $value);

            // Anuncio
            case 'at':
                return $this->handleAnuncioTipo($chatId, $messageId, $value);
            case 'ac':
                return $this->handleAnuncioConfirmar($chatId, $messageId, $value);
            case 'af':
                return $this->handleAnuncioFijar($chatId, $messageId);

            // Venta
            case 'vt':
                return $this->handleVentaTipo($chatId, $messageId, $value);
            case 'vc':
                return $this->handleVentaCantidad($chatId, $messageId, $value);
            case 'vp':
                return $this->handleVentaPago($chatId, $messageId, $value);
            case 'vf':
                return $this->handleVentaConfirmar($chatId, $messageId, $value);

            case 'back':
                $this->conv->reset($chatId);
                $this->showMainMenu($chatId, $messageId);
                return ["success" => true];

            default:
                return ["success" => true];
        }
    }

    private function handleMenuAction($chatId, $messageId, $value) {
        switch ($value) {
            case 'anuncio':
                $this->conv->reset($chatId);
                $this->showAnuncioTipoMenu($chatId, $messageId);
                break;
            case 'venta':
                $this->conv->reset($chatId);
                $this->showVentaTipoMenu($chatId, $messageId);
                break;
            case 'ver_anuncios':
                $this->showAnunciosList($chatId, $messageId);
                break;
            case 'stats':
                $this->showStats($chatId, $messageId);
                break;
        }
        return ["success" => true];
    }

    // ================================================================
    // FLUJO: CREAR ANUNCIO
    // ================================================================

    private function showAnuncioTipoMenu($chatId, $messageId = null) {
        $text = "<b>Crear Anuncio</b>\n\nSelecciona el tipo:";

        $keyboard = TelegramAPI::inlineKeyboard([
            [
                TelegramAPI::btn("Info", "at:info"),
                TelegramAPI::btn("Urgente", "at:urgente"),
            ],
            [
                TelegramAPI::btn("Promo", "at:promo"),
                TelegramAPI::btn("Contacto", "at:contacto"),
            ],
            [
                TelegramAPI::btn("Reglas", "at:reglas"),
            ],
            [TelegramAPI::btn("<< Volver", "back")],
        ]);

        if ($messageId) {
            $this->api->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->api->sendMessage($chatId, $text, $keyboard);
        }
    }

    private function handleAnuncioTipo($chatId, $messageId, $tipo) {
        $this->conv->setState($chatId, ConversationManager::ANUNCIO_MENSAJE, 1, [
            'tipo' => $tipo,
            'fijado' => 0,
        ]);

        $tipoLabel = strtoupper($tipo);
        $text = "<b>Anuncio [{$tipoLabel}]</b>\n\n";
        $text .= "Escribe el mensaje del anuncio:\n\n";
        $text .= "<i>Tambien puedes enviar una foto o video con caption</i>\n";
        $text .= "\n/cancelar para volver al menu";

        $this->api->editMessage($chatId, $messageId, $text);
        return ["success" => true];
    }

    private function handleConversationInput($chatId, $message, $state) {
        switch ($state['state']) {
            case ConversationManager::ANUNCIO_MENSAJE:
                return $this->processAnuncioMensaje($chatId, $message, $state);
            case ConversationManager::VENTA_NOMBRE:
                return $this->processVentaNombre($chatId, $message, $state);
            case ConversationManager::VENTA_DNI:
                return $this->processVentaDni($chatId, $message, $state);
            case ConversationManager::VENTA_TELEFONO:
                return $this->processVentaTelefono($chatId, $message, $state);
            default:
                $this->conv->reset($chatId);
                $this->showMainMenu($chatId);
                return ["success" => true];
        }
    }

    private function processAnuncioMensaje($chatId, $message, $state) {
        $text = $message['text'] ?? $message['caption'] ?? '';
        $data = $state['data'];
        $data['mensaje'] = $text;
        $data['titulo'] = mb_substr($text, 0, 100);

        if (isset($message['photo'])) {
            $photos = $message['photo'];
            $photo = end($photos);
            $fileData = $this->api->downloadFile($photo['file_id']);
            if ($fileData) {
                $filename = $this->anunciosController->saveFileFromData($fileData, 'jpg', 'tg_img_');
                $data['imagen_filename'] = $filename;
                $data['medio'] = 'imagen';
            }
        } elseif (isset($message['video'])) {
            $video = $message['video'];
            $fileSize = $video['file_size'] ?? 0;
            if ($fileSize > 20 * 1024 * 1024) {
                $this->api->sendMessage($chatId, "Video muy grande (max 20MB). Intenta de nuevo:");
                return ["success" => false];
            }
            $fileData = $this->api->downloadFile($video['file_id']);
            if ($fileData) {
                $ext = 'mp4';
                $mimeType = $video['mime_type'] ?? 'video/mp4';
                if (strpos($mimeType, 'webm') !== false) $ext = 'webm';
                $filename = $this->anunciosController->saveFileFromData($fileData, $ext, 'tg_vid_');
                $data['video_filename'] = $filename;
                $data['medio'] = 'video';
            }
        } else {
            $videoLink = $this->anunciosController->detectVideoLink($text);
            if ($videoLink) {
                $data['link_url'] = $videoLink['url'];
                $data['link_tipo'] = $videoLink['tipo'];
                $data['medio'] = 'link';
            } else {
                $data['medio'] = 'texto';
            }
        }

        $this->conv->setState($chatId, ConversationManager::ANUNCIO_CONFIRMAR, 2, $data);
        $this->showAnuncioPreview($chatId, $data);
        return ["success" => true];
    }

    private function showAnuncioPreview($chatId, $data) {
        $tipo = strtoupper($data['tipo']);
        $fijado = !empty($data['fijado']) ? ' [FIJADO]' : '';
        $medio = $data['medio'] ?? 'texto';

        $text = "<b>Vista previa del anuncio</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "<b>Tipo:</b> {$tipo}{$fijado}\n";
        $text .= "<b>Medio:</b> {$medio}\n";
        if (!empty($data['link_url'])) $text .= "<b>Link:</b> {$data['link_url']}\n";
        if (!empty($data['imagen_filename'])) $text .= "<b>Imagen:</b> adjunta\n";
        if (!empty($data['video_filename'])) $text .= "<b>Video:</b> adjunto\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= $data['mensaje'];
        $text .= "\n━━━━━━━━━━━━━━━━━━━━";

        $fijarText = !empty($data['fijado']) ? 'Desfijar' : 'Fijar';

        $keyboard = TelegramAPI::inlineKeyboard([
            [
                TelegramAPI::btn("Publicar", "ac:publicar"),
                TelegramAPI::btn("Cancelar", "ac:cancelar"),
            ],
            [TelegramAPI::btn($fijarText, "af:toggle")],
        ]);

        $this->api->sendMessage($chatId, $text, $keyboard);
    }

    private function handleAnuncioFijar($chatId, $messageId) {
        $state = $this->conv->getState($chatId);
        $data = $state['data'];
        $data['fijado'] = empty($data['fijado']) ? 1 : 0;
        $this->conv->setState($chatId, $state['state'], $state['step'], $data);

        $tipo = strtoupper($data['tipo']);
        $fijado = !empty($data['fijado']) ? ' [FIJADO]' : '';
        $medio = $data['medio'] ?? 'texto';

        $text = "<b>Vista previa del anuncio</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "<b>Tipo:</b> {$tipo}{$fijado}\n";
        $text .= "<b>Medio:</b> {$medio}\n";
        if (!empty($data['link_url'])) $text .= "<b>Link:</b> {$data['link_url']}\n";
        if (!empty($data['imagen_filename'])) $text .= "<b>Imagen:</b> adjunta\n";
        if (!empty($data['video_filename'])) $text .= "<b>Video:</b> adjunto\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= $data['mensaje'];
        $text .= "\n━━━━━━━━━━━━━━━━━━━━";

        $fijarText = !empty($data['fijado']) ? 'Desfijar' : 'Fijar';

        $keyboard = TelegramAPI::inlineKeyboard([
            [
                TelegramAPI::btn("Publicar", "ac:publicar"),
                TelegramAPI::btn("Cancelar", "ac:cancelar"),
            ],
            [TelegramAPI::btn($fijarText, "af:toggle")],
        ]);

        $this->api->editMessage($chatId, $messageId, $text, $keyboard);
        return ["success" => true];
    }

    private function handleAnuncioConfirmar($chatId, $messageId, $action) {
        if ($action === 'cancelar') {
            $this->conv->reset($chatId);
            $this->api->editMessage($chatId, $messageId, "Anuncio cancelado.");
            $this->showMainMenu($chatId);
            return ["success" => true];
        }

        $state = $this->conv->getState($chatId);
        $data = $state['data'];

        $anuncioData = [
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'tipo' => $data['tipo'],
            'medio' => $data['medio'] ?? 'texto',
            'fijado' => $data['fijado'] ?? 0,
            'fuente' => 'telegram',
            'link_url' => $data['link_url'] ?? null,
        ];

        if (!empty($data['imagen_filename']) || !empty($data['video_filename'])) {
            $result = $this->crearConArchivo($anuncioData, $data);
        } else {
            $result = $this->anunciosController->crear($anuncioData);
        }

        $this->conv->reset($chatId);

        if ($result['success']) {
            $id = $result['anuncio_id'] ?? $result['id'] ?? '?';
            $text = "Anuncio publicado!\nID: {$id} | Tipo: " . strtoupper($data['tipo']);
            $this->api->editMessage($chatId, $messageId, $text);
        } else {
            $this->api->editMessage($chatId, $messageId, "Error: " . ($result['message'] ?? 'desconocido'));
        }

        $this->showMainMenu($chatId);
        return ["success" => true];
    }

    private function crearConArchivo($anuncioData, $convData) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO anuncios (titulo, mensaje, tipo, medio, imagen_filename, video_filename, link_url, activo, fijado, fuente)
                 VALUES (:titulo, :mensaje, :tipo, :medio, :imagen, :video, :link, 1, :fijado, 'telegram')"
            );
            $stmt->execute([
                ':titulo' => $anuncioData['titulo'],
                ':mensaje' => $anuncioData['mensaje'],
                ':tipo' => $anuncioData['tipo'],
                ':medio' => $anuncioData['medio'],
                ':imagen' => $convData['imagen_filename'] ?? null,
                ':video' => $convData['video_filename'] ?? null,
                ':link' => $anuncioData['link_url'],
                ':fijado' => $anuncioData['fijado'] ?? 0,
            ]);
            return ["success" => true, "anuncio_id" => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error creando anuncio: " . $e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // ================================================================
    // FLUJO: VENDER BOLETO
    // ================================================================

    private function showVentaTipoMenu($chatId, $messageId = null) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, nombre, precio, cantidad_total, cantidad_vendida
                 FROM tipos_boleto WHERE evento_id = :eid AND activo = 1 ORDER BY orden"
            );
            $stmt->execute([':eid' => $this->evento_id]);
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->api->sendMessage($chatId, "Error cargando tipos de boleto.");
            return;
        }

        if (empty($tipos)) {
            $text = "No hay tipos de boleto activos.";
            $kb = TelegramAPI::inlineKeyboard([[TelegramAPI::btn("<< Volver", "back")]]);
            if ($messageId) $this->api->editMessage($chatId, $messageId, $text, $kb);
            else $this->api->sendMessage($chatId, $text, $kb);
            return;
        }

        $text = "<b>Vender Boleto</b>\n";
        $text .= "Evento: {$this->evento_nombre}\n\n";
        $text .= "Selecciona tipo:";

        $rows = [];
        foreach ($tipos as $t) {
            $disp = $t['cantidad_total'] - $t['cantidad_vendida'];
            $label = "{$t['nombre']} - S/{$t['precio']} ({$disp} disp.)";
            $rows[] = [TelegramAPI::btn($label, "vt:{$t['id']}")];
        }
        $rows[] = [TelegramAPI::btn("<< Volver", "back")];

        $kb = TelegramAPI::inlineKeyboard($rows);
        if ($messageId) $this->api->editMessage($chatId, $messageId, $text, $kb);
        else $this->api->sendMessage($chatId, $text, $kb);
    }

    private function handleVentaTipo($chatId, $messageId, $tipoId) {
        $stmt = $this->conn->prepare("SELECT id, nombre, precio, cantidad_total, cantidad_vendida FROM tipos_boleto WHERE id = :id");
        $stmt->execute([':id' => $tipoId]);
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            $this->api->editMessage($chatId, $messageId, "Tipo no encontrado.");
            return ["success" => false];
        }

        $disp = $tipo['cantidad_total'] - $tipo['cantidad_vendida'];
        if ($disp <= 0) {
            $this->api->editMessage($chatId, $messageId, "Sin boletos disponibles.");
            return ["success" => false];
        }

        $this->conv->setState($chatId, ConversationManager::VENTA_CANTIDAD, 1, [
            'tipo_boleto_id' => (int)$tipo['id'],
            'tipo_nombre' => $tipo['nombre'],
            'precio_unitario' => (float)$tipo['precio'],
            'disponibles' => $disp,
        ]);

        $text = "<b>Venta: {$tipo['nombre']} - S/{$tipo['precio']}</b>\n";
        $text .= "Disponibles: {$disp}\n\nCuantos boletos?";

        $max = min($disp, 10);
        $btnRow1 = [];
        $btnRow2 = [];
        for ($i = 1; $i <= min($max, 5); $i++) {
            $btnRow1[] = TelegramAPI::btn((string)$i, "vc:{$i}");
        }
        if ($max > 5) {
            for ($i = 6; $i <= $max; $i++) {
                $btnRow2[] = TelegramAPI::btn((string)$i, "vc:{$i}");
            }
        }

        $rows = [$btnRow1];
        if (!empty($btnRow2)) $rows[] = $btnRow2;
        $rows[] = [TelegramAPI::btn("<< Volver", "back")];

        $this->api->editMessage($chatId, $messageId, $text, TelegramAPI::inlineKeyboard($rows));
        return ["success" => true];
    }

    private function handleVentaCantidad($chatId, $messageId, $cantidad) {
        $state = $this->conv->getState($chatId);
        $data = $state['data'];
        $data['cantidad'] = (int)$cantidad;
        $data['precio_total'] = $data['precio_unitario'] * (int)$cantidad;

        $this->conv->setState($chatId, ConversationManager::VENTA_NOMBRE, 2, $data);

        $text = "<b>Venta: {$data['tipo_nombre']} x{$cantidad}</b>\n";
        $text .= "Total: S/{$data['precio_total']}\n\n";
        $text .= "Escribe el <b>nombre completo</b> del comprador:";
        $text .= "\n\n/cancelar para volver al menu";

        $this->api->editMessage($chatId, $messageId, $text);
        return ["success" => true];
    }

    private function processVentaNombre($chatId, $message, $state) {
        $nombre = trim($message['text'] ?? '');
        if (strlen($nombre) < 3) {
            $this->api->sendMessage($chatId, "Nombre muy corto. Escribe el nombre completo:");
            return ["success" => false];
        }

        $this->conv->nextStep($chatId, ConversationManager::VENTA_DNI, [
            'comprador_nombre' => $nombre,
        ]);

        $data = $this->conv->getState($chatId)['data'];
        $text = "<b>Venta: {$data['tipo_nombre']} x{$data['cantidad']}</b>\n";
        $text .= "Comprador: {$nombre}\n\n";
        $text .= "Escribe el <b>DNI</b> (8 digitos):";

        $this->api->sendMessage($chatId, $text);
        return ["success" => true];
    }

    private function processVentaDni($chatId, $message, $state) {
        $dni = trim($message['text'] ?? '');
        if (!preg_match('/^\d{8}$/', $dni)) {
            $this->api->sendMessage($chatId, "DNI invalido. Debe tener 8 digitos:");
            return ["success" => false];
        }

        $this->conv->nextStep($chatId, ConversationManager::VENTA_TELEFONO, [
            'comprador_dni' => $dni,
        ]);

        $data = $this->conv->getState($chatId)['data'];
        $text = "<b>Venta: {$data['tipo_nombre']} x{$data['cantidad']}</b>\n";
        $text .= "Comprador: {$data['comprador_nombre']}\n";
        $text .= "DNI: {$dni}\n\n";
        $text .= "Escribe el <b>telefono</b> (9 digitos):";

        $this->api->sendMessage($chatId, $text);
        return ["success" => true];
    }

    private function processVentaTelefono($chatId, $message, $state) {
        $telefono = preg_replace('/\s+/', '', $message['text'] ?? '');
        if (!preg_match('/^9\d{8}$/', $telefono)) {
            $this->api->sendMessage($chatId, "Telefono invalido. Debe empezar con 9 y tener 9 digitos:");
            return ["success" => false];
        }

        $this->conv->nextStep($chatId, ConversationManager::VENTA_PAGO, [
            'comprador_telefono' => $telefono,
        ]);

        $data = $this->conv->getState($chatId)['data'];
        $text = "<b>Venta: {$data['tipo_nombre']} x{$data['cantidad']}</b>\n";
        $text .= "Total: S/{$data['precio_total']}\n";
        $text .= "Comprador: {$data['comprador_nombre']}\n";
        $text .= "DNI: {$data['comprador_dni']} | Tel: {$telefono}\n\n";
        $text .= "Metodo de pago:";

        $keyboard = TelegramAPI::inlineKeyboard([
            [
                TelegramAPI::btn("Yape", "vp:yape"),
                TelegramAPI::btn("Efectivo", "vp:efectivo"),
                TelegramAPI::btn("Transferencia", "vp:transferencia"),
            ],
            [TelegramAPI::btn("<< Cancelar", "back")],
        ]);

        $this->api->sendMessage($chatId, $text, $keyboard);
        return ["success" => true];
    }

    private function handleVentaPago($chatId, $messageId, $metodo) {
        $this->conv->nextStep($chatId, ConversationManager::VENTA_CONFIRMAR, [
            'metodo_pago' => $metodo,
        ]);

        $data = $this->conv->getState($chatId)['data'];

        $text = "<b>CONFIRMAR VENTA</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "<b>Boleto:</b> {$data['tipo_nombre']} x{$data['cantidad']}\n";
        $text .= "<b>Total:</b> S/{$data['precio_total']}\n";
        $text .= "<b>Comprador:</b> {$data['comprador_nombre']}\n";
        $text .= "<b>DNI:</b> {$data['comprador_dni']}\n";
        $text .= "<b>Telefono:</b> {$data['comprador_telefono']}\n";
        $text .= "<b>Pago:</b> " . ucfirst($metodo) . "\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $text .= "Todo correcto?";

        $keyboard = TelegramAPI::inlineKeyboard([
            [TelegramAPI::btn("CONFIRMAR VENTA", "vf:confirmar")],
            [TelegramAPI::btn("Cancelar", "vf:cancelar")],
        ]);

        $this->api->editMessage($chatId, $messageId, $text, $keyboard);
        return ["success" => true];
    }

    private function handleVentaConfirmar($chatId, $messageId, $action) {
        if ($action === 'cancelar') {
            $this->conv->reset($chatId);
            $this->api->editMessage($chatId, $messageId, "Venta cancelada.");
            $this->showMainMenu($chatId);
            return ["success" => true];
        }

        $state = $this->conv->getState($chatId);
        $data = $state['data'];

        try {
            $codigoQR = $this->generarCodigoQR();

            // Verificar disponibilidad
            $stmt = $this->conn->prepare(
                "SELECT cantidad_total, cantidad_vendida FROM tipos_boleto WHERE id = :id AND activo = 1"
            );
            $stmt->execute([':id' => $data['tipo_boleto_id']]);
            $tipoBoleto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tipoBoleto) throw new Exception("Tipo de boleto no encontrado");

            $disp = $tipoBoleto['cantidad_total'] - $tipoBoleto['cantidad_vendida'];
            if ($disp < $data['cantidad']) throw new Exception("Solo quedan {$disp} boletos");

            // Insertar venta (estado_pago = verificado porque el admin esta vendiendo directamente)
            $stmt = $this->conn->prepare(
                "INSERT INTO boletos_vendidos
                 (evento_id, tipo_boleto_id, vendedor_id, comprador_nombres_apellidos, comprador_telefono, comprador_dni, cantidad, precio_total, codigo_qr, metodo_pago, estado_pago, estado_boleto)
                 VALUES (:evento_id, :tipo, NULL, :nombre, :telefono, :dni, :cantidad, :total, :qr, :pago, 'verificado', 'activo')"
            );
            $stmt->execute([
                ':evento_id' => $this->evento_id,
                ':tipo' => $data['tipo_boleto_id'],
                ':nombre' => $data['comprador_nombre'],
                ':telefono' => $data['comprador_telefono'],
                ':dni' => $data['comprador_dni'],
                ':cantidad' => $data['cantidad'],
                ':total' => $data['precio_total'],
                ':qr' => $codigoQR,
                ':pago' => $data['metodo_pago'],
            ]);

            $boletoId = $this->conn->lastInsertId();

            // Actualizar cantidad vendida
            $stmt = $this->conn->prepare(
                "UPDATE tipos_boleto SET cantidad_vendida = cantidad_vendida + :cant WHERE id = :id"
            );
            $stmt->execute([':cant' => $data['cantidad'], ':id' => $data['tipo_boleto_id']]);

            $this->conv->reset($chatId);

            // Recibo
            $text = "VENTA EXITOSA\n";
            $text .= "━━━━━━━━━━━━━━━━━━━━\n";
            $text .= "<b>Boleto #{$boletoId}</b>\n";
            $text .= "<b>QR:</b> <code>{$codigoQR}</code>\n";
            $text .= "<b>Tipo:</b> {$data['tipo_nombre']} x{$data['cantidad']}\n";
            $text .= "<b>Total:</b> S/{$data['precio_total']}\n";
            $text .= "<b>Comprador:</b> {$data['comprador_nombre']}\n";
            $text .= "<b>DNI:</b> {$data['comprador_dni']}\n";
            $text .= "<b>Pago:</b> " . ucfirst($data['metodo_pago']) . " (Verificado)\n";
            $text .= "━━━━━━━━━━━━━━━━━━━━";

            $this->api->editMessage($chatId, $messageId, $text);

            // Enviar QR como imagen
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($codigoQR);
            $caption = "QR Boleto #{$boletoId}\n{$data['comprador_nombre']}\n{$codigoQR}";
            $this->api->sendPhoto($chatId, $qrUrl, $caption);

            $this->showMainMenu($chatId);

        } catch (Exception $e) {
            $this->conv->reset($chatId);
            $this->api->editMessage($chatId, $messageId, "Error: " . $e->getMessage());
            $this->showMainMenu($chatId);
        }

        return ["success" => true];
    }

    private function generarCodigoQR() {
        $stmt = $this->conn->prepare("SELECT nombre FROM eventos WHERE id = :id");
        $stmt->execute([':id' => $this->evento_id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        $palabras = explode(' ', $evento['nombre'] ?? 'EVT');
        $siglas = '';
        foreach ($palabras as $p) {
            if (strlen($p) > 2) $siglas .= strtoupper(substr($p, 0, 1));
        }

        $stmt = $this->conn->prepare("SELECT MAX(id) as ultimo FROM boletos_vendidos WHERE evento_id = :eid");
        $stmt->execute([':eid' => $this->evento_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($r['ultimo'] ?? 0) + 1;

        return sprintf("BOX-%s-%s-%06d", $siglas, date('Y'), $numero);
    }

    // ================================================================
    // VER ANUNCIOS + ESTADISTICAS
    // ================================================================

    private function showAnunciosList($chatId, $messageId) {
        try {
            $stmt = $this->conn->query(
                "SELECT id, titulo, tipo, activo, fijado, created_at FROM anuncios ORDER BY created_at DESC LIMIT 10"
            );
            $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $anuncios = [];
        }

        if (empty($anuncios)) {
            $text = "No hay anuncios.";
        } else {
            $text = "<b>Ultimos 10 Anuncios</b>\n\n";
            foreach ($anuncios as $a) {
                $status = $a['activo'] ? 'ON' : 'OFF';
                $pin = $a['fijado'] ? ' PIN' : '';
                $tipo = strtoupper($a['tipo']);
                $fecha = date('d/m H:i', strtotime($a['created_at']));
                $text .= "#{$a['id']} [{$tipo}] {$status}{$pin}\n";
                $text .= "  {$a['titulo']}\n";
                $text .= "  <i>{$fecha}</i>\n\n";
            }
        }

        $kb = TelegramAPI::inlineKeyboard([[TelegramAPI::btn("<< Volver", "back")]]);
        $this->api->editMessage($chatId, $messageId, $text, $kb);
    }

    private function showStats($chatId, $messageId) {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total, SUM(activo) as activos FROM anuncios");
            $as = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) as ventas, SUM(cantidad) as boletos, SUM(precio_total) as recaudado
                 FROM boletos_vendidos WHERE evento_id = :eid"
            );
            $stmt->execute([':eid' => $this->evento_id]);
            $vs = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                "SELECT nombre, precio, cantidad_total, cantidad_vendida
                 FROM tipos_boleto WHERE evento_id = :eid AND activo = 1 ORDER BY orden"
            );
            $stmt->execute([':eid' => $this->evento_id]);
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->api->editMessage($chatId, $messageId, "Error cargando estadisticas.");
            return;
        }

        $text = "<b>Estadisticas - {$this->evento_nombre}</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        $text .= "<b>Anuncios:</b>\n";
        $text .= "  Total: {$as['total']} | Activos: " . ($as['activos'] ?? 0) . "\n\n";

        $text .= "<b>Ventas:</b>\n";
        $text .= "  Transacciones: " . ($vs['ventas'] ?? 0) . "\n";
        $text .= "  Boletos: " . ($vs['boletos'] ?? 0) . "\n";
        $text .= "  Recaudado: S/" . number_format($vs['recaudado'] ?? 0, 2) . "\n\n";

        $text .= "<b>Por tipo:</b>\n";
        foreach ($tipos as $t) {
            $d = $t['cantidad_total'] - $t['cantidad_vendida'];
            $text .= "  {$t['nombre']} (S/{$t['precio']}): {$t['cantidad_vendida']}/{$t['cantidad_total']} ({$d} disp.)\n";
        }

        $kb = TelegramAPI::inlineKeyboard([[TelegramAPI::btn("<< Volver", "back")]]);
        $this->api->editMessage($chatId, $messageId, $text, $kb);
    }
}

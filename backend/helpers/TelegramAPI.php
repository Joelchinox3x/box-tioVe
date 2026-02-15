<?php
/**
 * TelegramAPI - Wrapper limpio para Telegram Bot API
 */
class TelegramAPI {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    /**
     * Enviar mensaje de texto
     */
    public function sendMessage($chatId, $text, $replyMarkup = null, $parseMode = 'HTML') {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];
        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }
        return $this->request('sendMessage', $params);
    }

    /**
     * Editar mensaje existente (para actualizar menus in-place)
     */
    public function editMessage($chatId, $messageId, $text, $replyMarkup = null, $parseMode = 'HTML') {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];
        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }
        return $this->request('editMessageText', $params);
    }

    /**
     * Responder callback_query (quita el relojito del boton)
     */
    public function answerCallback($callbackId, $text = '', $showAlert = false) {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    /**
     * Enviar foto
     */
    public function sendPhoto($chatId, $photoUrl, $caption = '', $replyMarkup = null) {
        $params = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ];
        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }
        return $this->request('sendPhoto', $params);
    }

    /**
     * Descargar archivo por file_id
     */
    public function downloadFile($fileId) {
        $result = $this->request('getFile', ['file_id' => $fileId]);
        if (!$result || !$result['ok'] || !isset($result['result']['file_path'])) {
            return null;
        }
        $filePath = $result['result']['file_path'];
        $url = "https://api.telegram.org/file/bot{$this->token}/" . $filePath;
        return @file_get_contents($url);
    }

    // ========== KEYBOARD BUILDERS ==========

    /**
     * Construir InlineKeyboardMarkup
     * $rows = [ [ ['text'=>'Btn1','callback_data'=>'cb1'], ['text'=>'Btn2','callback_data'=>'cb2'] ], ... ]
     */
    public static function inlineKeyboard($rows) {
        return ['inline_keyboard' => $rows];
    }

    /**
     * Crear un boton inline
     */
    public static function btn($text, $callbackData) {
        return ['text' => $text, 'callback_data' => $callbackData];
    }

    /**
     * Crear boton con URL
     */
    public static function btnUrl($text, $url) {
        return ['text' => $text, 'url' => $url];
    }

    /**
     * Hacer request a Telegram API
     */
    private function request($method, $params = []) {
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";
        $postData = json_encode($params);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $postData,
                'timeout' => 10,
            ]
        ];

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if (!$response) {
            error_log("Telegram API error: no response for {$method}");
            return null;
        }

        return json_decode($response, true);
    }
}

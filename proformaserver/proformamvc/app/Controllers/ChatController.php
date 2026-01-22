<?php
namespace App\Controllers;

use Core\Controller;
use App\Services\GeminiService;

class ChatController extends Controller {
    
    public function sendMessage() {
        header('Content-Type: application/json');

        // Solo aceptar POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Leer JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? '';
        $history = $input['history'] ?? [];

        if (empty($message)) {
            echo json_encode(['reply' => 'Por favor escribe un mensaje.']);
            return;
        }

        try {
            $service = new GeminiService();
            $reply = $service->chat($history, $message);
            
            echo json_encode(['reply' => $reply]);
        } catch (\Throwable $e) {
            // Log for debugging
            file_put_contents(__DIR__ . '/../../debug_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode(['reply' => 'Error del servidor: ' . $e->getMessage()]);
        }
    }
}

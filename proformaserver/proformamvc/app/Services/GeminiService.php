<?php
namespace App\Services;

use App\Config\Chatbot;

class GeminiService {
    private $apiKey;
    private $model;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private $systemInstruction;

    public function __construct() {
        $config = new Chatbot();
        $this->apiKey = $config->apiKey;
        $this->model = $config->model;
        $this->systemInstruction = $config->systemInstruction;
    }

    /**
     * Envía un mensaje a la API de Gemini
     * @param array $history Historial de chat en formato Gemini
     * @param string $newMessage Mensaje del usuario
     * @param string $context Contexto de uso ('public' o 'manager')
     * @return string Respuesta del bot
     */
    public function chat(array $history, string $newMessage, string $context = 'public') {
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        // Construir Payload
        $contents = [];
        
        foreach ($history as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $newMessage]]
        ];

        // Definir Instrucciones y Herramientas según Contexto
        $currentSystemInstruction = $this->systemInstruction;
        $tools = [];

        if ($context === 'manager') {
            $currentSystemInstruction = "Eres el asistente personal del Gerente de Tradimacova. Tu objetivo es resumir información clave, mostrar leads recientes y ayudar en la gestión. Eres conciso, profesional y directo. Tienes acceso a datos privados de clientes.";
            
            $tools = [
                'function_declarations' => [
                    [
                        'name' => 'consultar_leads_recientes',
                        'description' => 'Obtiene una lista de los últimos clientes interesados (leads) registrados en el sistema.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => (object)[],
                        ]
                    ]
                ]
            ];
        } else {
            // Contexto CLIENTE (Público)
            $tools = [
                'function_declarations' => [
                    [
                        'name' => 'consultar_producto',
                        'description' => 'Busca información de precios y stock de productos en la base de datos.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nombre' => ['type' => 'STRING', 'description' => 'Nombre del producto a buscar']
                            ],
                            'required' => ['nombre']
                        ]
                    ],
                    [
                        'name' => 'registrar_interesado',
                        'description' => 'Registra los datos de un cliente interesado (Lead) para que un vendedor lo contacte. DEBES pedir Nombre, DNI/RUC y Celular.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nombre' => ['type' => 'STRING', 'description' => 'Nombre del cliente o Razón Social'],
                                'dni' => ['type' => 'STRING', 'description' => 'DNI o RUC del cliente'],
                                'telefono' => ['type' => 'STRING', 'description' => 'Número de celular o teléfono'],
                                'interes' => ['type' => 'STRING', 'description' => 'Producto o servicio de interés']
                            ],
                            'required' => ['nombre', 'dni', 'telefono']
                        ]
                    ],
                    [
                        'name' => 'consultar_datos_empresa',
                        'description' => 'Provee información oficial sobre la empresa: Dirección, Horarios de atención, Teléfonos, Redes Sociales/Web y Servicios.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => (object)[], 
                        ]
                    ]
                ]
            ];
        }

        $payload = [
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $currentSystemInstruction]]
            ],
            'tools' => [$tools],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 8192,
            ]
        ];

        $response = $this->callApi($url, $payload);
        return $this->handleToolCalls($response, $url, $contents, $tools);
    }

    private function callApi($url, $payload) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $res = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['error' => 'Curl Error: ' . $error_msg];
        }
        
        curl_close($ch);
        
        $decoded = json_decode($res, true);
        if ($decoded === null) {
            return ['error' => 'JSON Decode Error. Raw: ' . substr($res, 0, 100)];
        }
        
        return $decoded;
    }

    private function toolConsultarDatosEmpresa() {
        return "
        **Nombre:** Tradimacova S.A.C.  (Maquinaria y Andamios)
        **Dirección:** Mz E Lote 6, Av. Cajamarquilla Chosica, Lurigancho , Perú.
        **Horario:** Lunes a Viernes de 8:00 AM a 6:00 PM. Sábados de 8:00 AM a 1:00 PM.
        **Teléfono:** +51 994 280 191
        **Web:** www.tradimacova.com
        **Servicios:** Venta y alquiler de maquinaria pesada para construcción y minería.
        **Envíos:** Hacemos envíos a todo Lima y provincias (con recargo según destino).
        ";
    }

    private function toolConsultarLeadsRecientes() {
        try {
            $db = \Core\Database::getInstance()->getConnection();
            // Corregido: columnas reales (nombre, fecha_creacion)
            $stmt = $db->query("SELECT nombre, telefono, fecha_creacion FROM clientes_pendientes ORDER BY fecha_creacion DESC LIMIT 5");
            $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($leads)) return "No hay leads recientes registrados.";
            
            $text = "Últimos 5 Leads:\n";
            foreach($leads as $l) {
                $text .= "- {$l['nombre']} ({$l['telefono']}) el {$l['fecha_creacion']}\n";
            }
            return $text;
        } catch (\Exception $e) {
            return "Error al consultar leads: " . $e->getMessage();
        }
    }

    private function handleToolCalls($json, $url, $history, $tools) {
        // Manejo de errores de API explícitos
        if (isset($json['error'])) {
            if ($json['error']['code'] == 429) {
                return "⏳ **Límite de velocidad excedido.**\nEl modelo experimental (Gemini 3 Preview) tiene un límite de uso gratuito muy estricto.\n\nPor favor, **espera unos 30 segundos** antes de enviar otro mensaje.";
            }
            return "Error de Inteligencia Artificial: " . ($json['error']['message'] ?? 'Desconocido');
        }

        $parts = $json['candidates'][0]['content']['parts'] ?? [];
        
        // Buscar si hay una llamada a función en alguna de las partes
        $functionCallPart = null;
        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $functionCallPart = $part['functionCall'];
                break;
            }
        }

        // Si encontramos una función, ejecutarla
        if ($functionCallPart) {
            $fc = $functionCallPart;
            $funcName = $fc['name'];
            $args = $fc['args'] ?? [];

            // Ejecutar lógica PHP
            $toolResult = [];
            
            if ($funcName === 'consultar_producto') {
                $toolResult = $this->toolConsultarProducto($args['nombre']);
            } elseif ($funcName === 'consultar_detalle_producto') {
                $toolResult = $this->toolConsultarDetalleProducto($args['id']);
            } elseif ($funcName === 'registrar_interesado') {
                $toolResult = $this->toolRegistrarInteresado($args);
            } elseif ($funcName === 'consultar_datos_empresa') {
                $toolResult = $this->toolConsultarDatosEmpresa();
            } elseif ($funcName === 'consultar_leads_recientes') {
                $toolResult = $this->toolConsultarLeadsRecientes();
            }

            // Enviar resultado de vuelta a Gemini para que genere la respuesta final
            
            // 1. IMPORTANTE: Agregar TODAS las partes originales de la respuesta del modelo al historial
            // Esto preserva el 'thought_signature' requerido por Gemini 2.0/3.0
            $history[] = [
                'role' => 'model',
                'parts' => $parts 
            ];

            // 2. Agregar el resultado de la función
            $history[] = [
                'role' => 'function',
                'parts' => [['functionResponse' => [
                    'name' => $funcName,
                    'response' => ['result' => $toolResult]
                ]]]
            ];

            // 3. Segunda llamada a Gemini con el resultado
            $payload = [
                'contents' => $history,
                'tools' => [$tools],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 8192,
                ]
            ];
            
            $finalJson = $this->callApi($url, $payload);
            
            $finalParts = $finalJson['candidates'][0]['content']['parts'] ?? [];
            if (!empty($finalParts)) {
                // Devolver el texto de la primera parte que tenga texto
                foreach($finalParts as $p) {
                    if (isset($p['text'])) return $p['text'];
                }
            }
            
            return "Error al generar respuesta post-herramienta: " . json_encode($finalJson);
        }

        // Si es texto normal (sin tool call)
        if (isset($parts[0]['text'])) {
            return $parts[0]['text'];
        }

        // DEBUG: Mostrar qué respondió realmente Gemini
        return "Error en respuesta AI: " . json_encode($json);
    }

    // --- HERRAMIENTAS REALES ---

    private function toolConsultarProducto($query) {
        // Conexión directa rápida o usar Modelo
        try {
            $db = \Core\Database::getInstance()->getConnection(); // Obtener laconexión PDO
            // Búsqueda simple LIKE
            // Corregido: columnas reales y agregamos 'moneda'
            $stmt = $db->prepare("SELECT id, nombre, modelo, precio, moneda, descripcion FROM productos WHERE nombre LIKE ? OR descripcion LIKE ? LIMIT 5");
            $like = "%" . $query . "%";
            $stmt->execute([$like, $like]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($items)) return "No se encontraron productos similares a '$query'.";
            
            $resultStrings = ["Encontré estos productos (responde con el número de opción para más detalles):"];
            foreach($items as $index => $row) {
                $num = $index + 1;
                $moneda = $row['moneda'] ?? 'S/';
                $resultStrings[] = "$num. {$row['nombre']} | Modelo: {$row['modelo']} | Precio: {$moneda} {$row['precio']} (ID: {$row['id']})";
            }
            
            return implode("\n", $resultStrings);

        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../public/debug_log.txt', "ERROR BD SEARCH: " . $e->getMessage() . "\n", FILE_APPEND);
            return "ERROR CRÍTICO EN BASE DE DATOS: " . $e->getMessage();
        }
    }

    private function toolConsultarDetalleProducto($id) {
        try {
            $db = \Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $prod = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$prod) return "Producto no encontrado.";
            
            $moneda = $prod['moneda'] ?? 'S/';
            
            return "
            **Detalle de {$prod['nombre']}**
            - Modelo: {$prod['modelo']}
            - Precio: {$moneda} {$prod['precio']}
            - Descripción: {$prod['descripcion']}
            ";
        } catch (\Exception $e) {
            return "Error al consultar detalle: " . $e->getMessage();
        }
    }

    private function toolRegistrarInteresado($args) {
        try {
            $nombre = $args['nombre'] ?? 'Anónimo';
            $dni = $args['dni'] ?? '00000000';
            $telefono = $args['telefono'] ?? '';
            $interes = $args['interes'] ?? 'General';
            
            $db = \Core\Database::getInstance()->getConnection();
            
            // Usar el DNI real proporcionado
            $stmt = $db->prepare("INSERT INTO clientes_pendientes (nombre, telefono, dni_ruc, origen, estado, fecha_creacion) VALUES (?, ?, ?, ?, 'pendiente', NOW())");
            
            $origen = "Chatbot - " . $interes;
            
            if ($stmt->execute([$nombre, $telefono, $dni, $origen])) {
                return "Datos registrados correctamente. Un asesor te contactará pronto.";
            } else {
                return "Error al guardar en BD.";
            }
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../public/debug_log.txt', "ERROR LEAD: " . $e->getMessage() . "\n", FILE_APPEND);
            return "Error al registrar datos.";
        }
    }
}

<?php

namespace App\Services\ApiDniRuc;

use App\Helpers\SettingsHelper;

/**
 * Servicio para consultar DNI y RUC usando Decolecta
 */
class DecolectaService
{
    private $token;
    private $logFile = '/tmp/decolecta_debug.log';

    public function __construct()
    {
        $this->token = trim(SettingsHelper::getDecolectaToken());
    }

    /**
     * Consulta un documento (DNI o RUC)
     * @param string $documento Número de DNI (8 dígitos) o RUC (11 dígitos)
     * @return string JSON response
     */
    public function consultar($documento)
    {
        $documento = trim($documento);
        
        if (empty($this->token)) {
            return json_encode([
                'success' => false,
                'message' => 'Token de Decolecta no configurado'
            ]);
        }

        // Determinar si es DNI o RUC
        $esDni = strlen($documento) === 8;

        // DECOLECTA endpoints - Documentación oficial verificada
        // https://decolecta.gitbook.io/docs/
        $endpoint = $esDni
            ? "https://api.decolecta.com/v1/reniec/dni?numero={$documento}"
            : "https://api.decolecta.com/v1/sunat/ruc?numero={$documento}";

        // Llamada a la API - Ambos usan GET con query parameter
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->token}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // LOG DETALLADO
        $this->log([
            'documento' => $documento,
            'esDni' => $esDni ? 'SI' : 'NO',
            'endpoint' => $endpoint,
            'httpCode' => $httpCode,
            'curlError' => $curlError ?: 'ninguno',
            'response' => $response
        ]);

        if ($httpCode === 200) {
            $apiData = json_decode($response, true);

            // LOG de estructura recibida
            $this->log(['responseDecoded' => $apiData]);

            if ($apiData && !isset($apiData['error'])) {
                $nombreCompleto = '';
                $direccion = '';

                if ($esDni) {
                    // DNI: Estructura Decolecta RENIEC
                    // {first_name, first_last_name, second_last_name, full_name, document_number}
                    $nombreCompleto = $apiData['full_name'] ?? trim(
                        ($apiData['first_name'] ?? '') . ' ' .
                        ($apiData['first_last_name'] ?? '') . ' ' .
                        ($apiData['second_last_name'] ?? '')
                    );
                } else {
                    // RUC: Estructura Decolecta SUNAT
                    // {razon_social, numero_documento, estado, condicion, direccion, distrito, provincia, departamento, ...}
                    $nombreCompleto = $apiData['razon_social'] ?? '';

                    // Dirección completa ya viene en el campo 'direccion'
                    $direccion = $apiData['direccion'] ?? '';

                    // Agregar ubicación si no está incluida en dirección
                    $ubicacion = [];
                    if (!empty($apiData['departamento'])) $ubicacion[] = $apiData['departamento'];
                    if (!empty($apiData['provincia'])) $ubicacion[] = $apiData['provincia'];
                    if (!empty($apiData['distrito'])) $ubicacion[] = $apiData['distrito'];

                    if (!empty($ubicacion)) {
                        $ubicacionStr = implode(' - ', $ubicacion);
                        // Solo agregar si no está ya en la dirección
                        if (strpos(strtoupper($direccion), strtoupper($ubicacionStr)) === false) {
                            $direccion .= ($direccion ? ', ' : '') . $ubicacionStr;
                        }
                    }
                }

                return json_encode([
                    'success' => true,
                    'data' => [
                        'nombre_completo' => $nombreCompleto,
                        'direccion' => $direccion
                    ]
                ]);
            } else {
                $this->log(['error' => 'No se encontró data en la respuesta o hay error']);
                return json_encode([
                    'success' => false,
                    'message' => 'No se encontró información en Decolecta'
                ]);
            }
        } else {
            // Error HTTP
            $errorData = json_decode($response, true);
            $errorMsg = 'HTTP ' . $httpCode;

            if ($errorData && isset($errorData['message'])) {
                $errorMsg .= ': ' . $errorData['message'];
            } elseif ($errorData && isset($errorData['error'])) {
                $errorMsg .= ': ' . $errorData['error'];
            }

            $this->log(['httpError' => $errorMsg]);

            // DEBUG: Mostrar info en el mensaje
            $debugInfo = " | Endpoint: " . $endpoint . " | Response: " . substr($response, 0, 200);

            return json_encode([
                'success' => false,
                'message' => 'Decolecta ' . $errorMsg . $debugInfo
            ]);
        }
    }

    /**
     * Escribe en el archivo de log
     * @param array $data Datos a loguear
     */
    private function log($data)
    {
        $logContent = "\n========== DECOLECTA " . date('Y-m-d H:i:s') . " ==========\n";

        if (isset($data['documento'])) {
            $logContent .= "Token usado: " . substr($this->token, 0, 20) . "...\n";
            $logContent .= "Documento: " . $data['documento'] . "\n";
            $logContent .= "Es DNI: " . $data['esDni'] . "\n";
            $logContent .= "Endpoint: " . $data['endpoint'] . "\n";
            $logContent .= "HTTP Code: " . $data['httpCode'] . "\n";
            $logContent .= "CURL Error: " . $data['curlError'] . "\n";
            $logContent .= "Response RAW: " . $data['response'] . "\n";
        } elseif (isset($data['responseDecoded'])) {
            $logContent .= "Response JSON: " . print_r($data['responseDecoded'], true) . "\n";
        } elseif (isset($data['error'])) {
            $logContent .= "ERROR: " . $data['error'] . "\n";
        } elseif (isset($data['httpError'])) {
            $logContent .= "HTTP ERROR: " . $data['httpError'] . "\n";
        }

        $logContent .= "=====================================\n";
        file_put_contents($this->logFile, $logContent, FILE_APPEND);
    }
}

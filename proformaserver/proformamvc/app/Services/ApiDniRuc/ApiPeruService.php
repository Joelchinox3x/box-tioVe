<?php

namespace App\Services\ApiDniRuc;

use App\Helpers\SettingsHelper;

/**
 * Servicio para consultar DNI y RUC usando ApiPeru.dev
 */
class ApiPeruService
{
    private $token;

    public function __construct()
    {
        $this->token = SettingsHelper::getApiPeruToken();
    }

    /**
     * Consulta un documento (DNI o RUC)
     * @param string $documento Número de DNI (8 dígitos) o RUC (11 dígitos)
     * @return string JSON response
     */
    public function consultar($documento)
    {
        if (empty($this->token)) {
            return json_encode([
                'success' => false,
                'message' => 'Token de ApiPeru no configurado'
            ]);
        }

        // Determinar si es DNI o RUC
        $esDni = strlen($documento) === 8;
        $endpoint = $esDni
            ? "https://apiperu.dev/api/dni"
            : "https://apiperu.dev/api/ruc";

        $bodyData = $esDni
            ? json_encode(['dni' => $documento])
            : json_encode(['ruc' => $documento]);

        // Llamada a la API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->token}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $apiData = json_decode($response, true);

            if ($apiData && $apiData['success'] && isset($apiData['data'])) {
                $data = $apiData['data'];
                $nombreCompleto = '';
                $direccion = '';

                if ($esDni) {
                    // DNI: construir nombre completo
                    $nombreCompleto = trim(
                        ($data['nombre_completo'] ?? '') ?:
                        (($data['nombres'] ?? '') . ' ' .
                         ($data['apellido_paterno'] ?? '') . ' ' .
                         ($data['apellido_materno'] ?? ''))
                    );
                } else {
                    // RUC: razón social
                    $nombreCompleto = $data['nombre_o_razon_social'] ?? '';

                    // Dirección
                    $direccion = $data['direccion_completa'] ?? $data['direccion'] ?? '';
                }

                return json_encode([
                    'success' => true,
                    'data' => [
                        'nombre_completo' => $nombreCompleto,
                        'direccion' => $direccion
                    ]
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'No se encontró información en ApiPeru'
                ]);
            }
        } else {
            return json_encode([
                'success' => false,
                'message' => 'Error en la consulta a ApiPeru'
            ]);
        }
    }
}

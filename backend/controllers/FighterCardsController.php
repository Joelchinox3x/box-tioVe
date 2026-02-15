<?php
require_once __DIR__ . '/../config/Config.php';

/**
 * Controlador de Tarjetas de Peleador
 * Gestiona el guardado y recuperación de composiciones JSON e imágenes quemadas
 */
class FighterCardsController {
    private $db;
    private $upload_dir;

    public function __construct($db) {
        $this->db = $db;
        $this->upload_dir = __DIR__ . '/../files/cards/';
        
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    /**
     * Guardar o actualizar una tarjeta
     */
    public function guardar($data) {
        // Validar datos mínimos
        if (!isset($data['peleador_id']) || !isset($data['composition_json'])) {
            http_response_code(400);
            return ["success" => false, "message" => "Faltan datos obligatorios (peleador_id, composition_json)"];
        }

        try {
            $this->db->beginTransaction();

            $peleador_id = (int)$data['peleador_id'];
            $is_primary = isset($data['is_primary']) ? (int)$data['is_primary'] : 0;
            $layout_type = $data['layout_type'] ?? 'standard';
            $raw_composition = $data['composition_json'];
            $composition_data = json_decode($raw_composition, true);
            
            // --- Procesar Imagen Quemada (si viene en $_FILES) ---
            $baked_url = $data['baked_url'] ?? null; // Si ya viene una URL

            if (isset($_FILES['baked_image']) && $_FILES['baked_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['baked_image']['tmp_name'];
                $fileName = basename($_FILES['baked_image']['name']);
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                if (empty($ext)) $ext = 'png';

                // Guardar en /files/peleadores/{peleador_id}/
                $peleadorDir = __DIR__ . "/../files/peleadores/$peleador_id";
                if (!is_dir($peleadorDir)) {
                    mkdir($peleadorDir, 0777, true);
                }

                $newFileName = 'card_' . time() . '.' . $ext;
                $destination = $peleadorDir . '/' . $newFileName;

                if (move_uploaded_file($fileTmp, $destination)) {
                    $baked_url = "files/peleadores/$peleador_id/" . $newFileName;
                } else {
                    error_log("FighterCardsController: Error al mover archivo subido");
                }
            }

            // --- Procesar Archivos de Capas (Universal JSON) ---
            $peleadorDir = __DIR__ . "/../files/peleadores/$peleador_id";
            $layerDir = "$peleadorDir/layers";
            if (!is_dir($layerDir)) mkdir($layerDir, 0777, true);

            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'layer_file_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
                    $layerId = str_replace('layer_file_', '', $key);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png';
                    $newName = 'layer_' . $layerId . '_' . time() . '.' . $ext;
                    
                    if (move_uploaded_file($file['tmp_name'], $layerDir . '/' . $newName)) {
                        $newUrl = Config::getFileUrl("$peleador_id/layers/" . $newName, 'peleadores');
                        
                        // Reemplazar la URI local por la remota en el objeto
                        if ($composition_data && isset($composition_data['layers'])) {
                            foreach ($composition_data['layers'] as &$layer) {
                                if ((string)$layer['id'] === (string)$layerId) {
                                    $layer['uri'] = $newUrl;
                                }
                            }
                        }
                    }
                }
            }

            // Re-serializar el JSON limpio
            $composition_json = $composition_data ? json_encode($composition_data) : $raw_composition;

            // Si es marcada como primaria, desmarcar las anteriores de este peleador
            if ($is_primary === 1) {
                $queryReset = "UPDATE fighter_cards SET is_primary = 0 WHERE peleador_id = :pid";
                $stmtReset = $this->db->prepare($queryReset);
                $stmtReset->bindParam(':pid', $peleador_id);
                $stmtReset->execute();
            }

            // --- Insertar en BD ---
            $query = "INSERT INTO fighter_cards 
                      (peleador_id, is_primary, baked_url, composition_json, layout_type) 
                      VALUES (:pid, :primary, :url, :json, :layout)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pid', $peleador_id);
            $stmt->bindParam(':primary', $is_primary);
            $stmt->bindParam(':url', $baked_url);
            $stmt->bindParam(':json', $composition_json);
            $stmt->bindParam(':layout', $layout_type);
            $stmt->execute();
            
            $card_id = $this->db->lastInsertId();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Tarjeta guardada correctamente",
                "card_id" => $card_id,
                "baked_url" => $baked_url
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("FighterCardsController Error: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al guardar la tarjeta", "error" => $e->getMessage()];
        }
    }

    /**
     * Obtener las tarjetas de un peleador
     */
    public function listarPorPeleador($peleador_id) {
        $query = "SELECT * FROM fighter_cards WHERE peleador_id = :pid ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pid', $peleador_id);
        $stmt->execute();
        
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir tipos de datos
        foreach ($cards as &$card) {
            $card['id'] = (int)$card['id'];
            $card['peleador_id'] = (int)$card['peleador_id'];
            $card['is_primary'] = (int)$card['is_primary'];
        }

        return [
            "success" => true,
            "cards" => $cards
        ];
    }
}
?>

<?php
namespace App\Helpers;

class CleanupHelper {
    /**
     * Elimina PDFs de vista previa que ya expiraron
     */
    public static function cleanExpiredPreviewPdfs() {
        $pdfDir = __DIR__ . '/../../public/uploads/pdfs';

        if (!is_dir($pdfDir)) {
            return;
        }

        $files = glob($pdfDir . '/proforma_PREVIEW_*.pdf');
        $cleaned = 0;

        foreach ($files as $file) {
            $markerFile = $file . '.delete_after';

            if (file_exists($markerFile)) {
                $expiryTime = (int)file_get_contents($markerFile);

                // Si ya expiró, eliminar
                if (time() > $expiryTime) {
                    @unlink($file);
                    @unlink($markerFile);
                    $cleaned++;
                }
            } else {
                // Si no tiene marcador y es antiguo (más de 10 minutos), eliminar
                if (filemtime($file) < (time() - 600)) {
                    @unlink($file);
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    /**
     * Llamar este método al final de cada request importante
     * Ejecuta limpieza aleatoriamente (1% de probabilidad)
     */
    public static function randomCleanup() {
        // Solo ejecutar en 1% de los casos para no sobrecargar
        if (rand(1, 100) === 1) {
            self::cleanExpiredPreviewPdfs();
        }
    }
}

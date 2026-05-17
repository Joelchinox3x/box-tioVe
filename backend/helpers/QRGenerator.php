<?php
/**
 * QRGenerator - Genera códigos QR
 * Usa una API externa para generar QR codes
 */

class QRGenerator {
    /**
     * Generar QR code y guardarlo como archivo
     * @param string $data Datos para el QR
     * @param string $filename Nombre del archivo (opcional)
     * @return string Path del QR generado
     */
    public static function generate($data, $filename = null) {
        $qrDir = __DIR__ . '/../files/qr_codes/';
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0777, true);
        }

        $filename = $filename ?? 'qr_' . uniqid() . '.png';
        $filepath = $qrDir . $filename;

        // Usar API de QR Server
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($data);

        $qrImage = @file_get_contents($qrUrl);
        if ($qrImage !== false && strlen($qrImage) > 0) {
            file_put_contents($filepath, $qrImage);
            return $filepath;
        }

        throw new Exception('No se pudo generar el código QR');
    }

    /**
     * Generar QR en base64 (para incrustar directamente en PDF)
     * @param string $data Datos para el QR
     * @param int $size Tamaño del QR en pixeles (default 200)
     * @return string Data URI en base64
     */
    public static function generateBase64($data, $size = 200) {
        // Usar API de QR Server (gratuita y funcional)
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);

        $qrImage = @file_get_contents($qrUrl);
        if ($qrImage !== false && strlen($qrImage) > 0) {
            $base64 = base64_encode($qrImage);
            return 'data:image/png;base64,' . $base64;
        }

        // Fallback: generar un QR simple con texto
        error_log('No se pudo generar QR via API, usando fallback');
        return self::generateSimpleQRFallback($data, $size);
    }

    /**
     * Fallback simple si la API falla
     */
    private static function generateSimpleQRFallback($data, $size) {
        // Crear una imagen simple con el texto del QR
        $im = imagecreate($size, $size);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $textcolor = imagecolorallocate($im, 0, 0, 0);

        // Texto centrado
        $text = 'QR: ' . substr($data, -20);
        imagestring($im, 5, 10, $size/2, $text, $textcolor);

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Generar URL de QR (sin guardar archivo)
     * @param string $data Datos para el QR
     * @param int $size Tamaño del QR en pixeles
     * @return string URL del QR
     */
    public static function generateUrl($data, $size = 200) {
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
    }
}

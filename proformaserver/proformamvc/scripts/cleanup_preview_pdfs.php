#!/usr/bin/env php
<?php
/**
 * Script de limpieza manual de PDFs de vista previa
 *
 * Uso:
 *   php cleanup_preview_pdfs.php
 *
 * O desde cron cada 5 minutos:
 *   */5 * * * * php /ruta/a/cleanup_preview_pdfs.php >> /var/log/cleanup.log 2>&1
 */

require_once __DIR__ . '/../app/Helpers/CleanupHelper.php';

use App\Helpers\CleanupHelper;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando limpieza de PDFs de vista previa...\n";

$cleaned = CleanupHelper::cleanExpiredPreviewPdfs();

echo "[" . date('Y-m-d H:i:s') . "] Limpieza completada. Archivos eliminados: $cleaned\n";

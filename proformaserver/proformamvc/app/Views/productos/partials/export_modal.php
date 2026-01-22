<?php
// Usar datos pre-calculados desde el Controlador para evitar errores en la vista
$filename = $exportData['filename'] ?? 'producto.zip';
$countImagenes = $exportData['countImagenes'] ?? 0;
$countFolletos = $exportData['countFolletos'] ?? 0;
$totalSizeMB = $exportData['totalSizeMB'] ?? '0.00';
?>

<!-- Modal de Exportación -->
<div id="exportModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm select-none animate-fade-in" onclick="closeExportModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in overflow-hidden m-4" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 p-6 relative overflow-hidden">
            <div class="relative z-10 flex items-center gap-4">
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="ph-bold ph-package text-2xl text-white"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Resumen de Exportación</h3>
                    <p class="text-indigo-100 text-xs">Generando paquete digital...</p>
                </div>
            </div>
            <!-- Decoración -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-5">
            
            <!-- Nombre del Archivo -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase mb-0.5">Archivo Destino</p>
                    <div class="flex items-center gap-2 text-slate-700">
                        <i class="ph-duotone ph-file-zip text-xl text-violet-500"></i>
                        <span class="font-mono text-sm font-bold"><?= $filename ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Est.</p>
                    <span class="text-xs font-bold text-slate-600 bg-slate-200 px-2 py-1 rounded-lg">~<?= $totalSizeMB ?> MB</span>
                </div>
            </div>

            <!-- Detalles del Contenido -->
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase mb-3 ml-1">Contenido del Paquete</p>
                <div class="space-y-2">
                    <!-- Item: Datos -->
                    <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="ph-bold ph-code"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">Metadatos</p>
                                <p class="text-[10px] text-slate-400">Especificaciones y textos</p>
                            </div>
                        </div>
                        <i class="ph-bold ph-check-circle text-green-500"></i>
                    </div>

                    <!-- Item: Imágenes -->
                    <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                                <i class="ph-bold ph-image"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">Galería de Fotos</p>
                                <p class="text-[10px] text-slate-400"><?= $countImagenes ?> archivos JPG/PNG</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-600"><?= $countImagenes ?></span>
                    </div>

                    <!-- Item: PDFs -->
                    <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                                <i class="ph-bold ph-file-pdf"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">Documentación</p>
                                <p class="text-[10px] text-slate-400"><?= $countFolletos ?> Fichas Técnicas</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-600"><?= $countFolletos ?></span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeExportModal()" 
                        class="flex-1 px-4 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-medium transition-colors text-sm">
                    Cancelar
                </button>
                <a href="<?= url("/inventario/export/" . $producto['id']) ?>" onclick="closeExportModal(); mostrarToast('Descargando archivo...', 'info')"
                   class="flex-[2] px-4 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-200 transform active:scale-95 text-sm">
                    <i class="ph-bold ph-download-simple"></i> Confirmar Descarga
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Hacer las funciones globales para evitar ReferenceError
window.openExportModal = function() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Animacion simple si ya existe
        const content = modal.querySelector('div');
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }
}

window.closeExportModal = function() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}
</script>

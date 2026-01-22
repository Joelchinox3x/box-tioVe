<!-- Modal de Exportación -->
<div id="exportClientModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-5">
            <h3 class="text-white font-bold text-lg"><i class="ph-bold ph-download-simple mr-2"></i> Exportar Clientes</h3>
        </div>
        <div class="p-6">
            <p class="text-slate-600 text-sm mb-6">Vas a descargar una copia de seguridad de todos tus clientes en formato JSON.</p>
            <div class="flex gap-3">
                <button onclick="closeExportModal()" class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-medium hover:bg-slate-200 transition-colors">Cancelar</button>
                <button onclick="confirmExport()" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">
                    Descargar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Importación -->
<div id="importClientModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full animate-scale-in flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-green-600 p-5 flex-shrink-0">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="ph-bold ph-upload-simple"></i> Importar Clientes
            </h3>
        </div>

        <!-- Body Scrollable -->
        <div class="p-6 overflow-y-auto flex-1">
            
            <!-- Paso 1: Selección de Archivo -->
            <div id="importStep1" class="text-center py-4">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100">
                    <i class="ph-duotone ph-file-json text-green-500 text-3xl"></i>
                </div>
                <p class="text-slate-600 mb-6 text-sm">Selecciona el archivo <b>.json</b> que descargaste previamente.</p>
                
                <input type="file" id="importFileInput" accept=".json" class="hidden">
                <button onclick="document.getElementById('importFileInput').click()" 
                        class="px-6 py-3 bg-white border-2 border-dashed border-slate-300 text-slate-600 rounded-xl font-medium hover:border-green-400 hover:text-green-600 hover:bg-green-50 transition-all w-full">
                    <i class="ph-bold ph-folder-open mr-2"></i> Elegir Archivo JSON
                </button>
            </div>

            <!-- Paso 2: Previsualización -->
            <div id="importStep2" class="hidden">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Contactos Encontrados</h4>
                        <p class="text-xs text-slate-500" id="importCountInfo">0 clientes detectados</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">
                        <input type="checkbox" id="selectAllImport" checked class="w-4 h-4 rounded text-green-600 border-slate-300 focus:ring-green-500">
                        <span class="text-xs font-medium text-slate-700">Seleccionar Todos</span>
                    </label>
                </div>

                <div id="importPreviewList" class="space-y-2 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar">
                    <!-- Lista generada dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-4 border-t border-slate-100 flex gap-3 bg-slate-50 rounded-b-2xl flex-shrink-0">
            <button onclick="closeImportModal()" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-medium hover:bg-slate-50 transition-colors">
                Cancelar
            </button>
            <button id="btnConfirmImport" disabled onclick="confirmImport()" class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-colors shadow-lg shadow-green-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Importar
            </button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

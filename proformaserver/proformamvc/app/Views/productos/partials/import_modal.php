<!-- Modal Importar Paquete (Ultra Premium) -->
<div id="importModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black/60 backdrop-blur-sm select-none animate-fade-in" onclick="closeImportModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in overflow-hidden m-4" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-violet-600 to-indigo-600 p-6 relative overflow-hidden">
            <div class="relative z-10 flex items-center gap-4">
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="ph-bold ph-upload-simple text-2xl text-white"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Importar Producto</h3>
                    <p class="text-indigo-100 text-xs">Restaura desde un archivo ZIP</p>
                </div>
            </div>
            <!-- Decoración -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form action="<?= url('/inventario/import') ?>" method="POST" enctype="multipart/form-data" id="importForm">
                
                <p class="text-sm text-slate-600 mb-4 text-center">
                    Arrastra tu archivo aquí o haz clic para buscar. Se restaurarán fotos y datos.
                </p>

                <!-- Zona de Drop -->
                <div id="dropZone" class="relative group cursor-pointer">
                    <input type="file" name="package" id="fileInput" class="hidden" accept=".zip" required onchange="handleFileSelect(this)">
                    
                    <div class="w-full h-40 border-2 border-indigo-200 border-dashed rounded-xl bg-indigo-50/50 hover:bg-indigo-50 transition-all flex flex-col items-center justify-center group-hover:border-indigo-400" id="dropZoneContent">
                        
                        <!-- Estado Inicial -->
                        <div id="emptyState" class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform text-indigo-500">
                                <i class="ph-bold ph-cloud-arrow-up text-2xl"></i>
                            </div>
                            <p class="text-indigo-900 font-bold text-sm">Sube tu archivo ZIP</p>
                            <p class="text-indigo-400 text-xs mt-1">Máximo 50MB</p>
                        </div>

                        <!-- Estado con Archivo -->
                        <div id="fileState" class="hidden flex-col items-center">
                            <i class="ph-duotone ph-file-zip text-4xl text-violet-500 mb-2 animate-bounce"></i>
                            <p class="text-slate-800 font-bold text-sm truncate max-w-[200px]" id="fileName">archivo.zip</p>
                            <span class="px-2 py-1 bg-violet-100 text-violet-700 text-xs font-bold rounded-lg mt-2" id="fileSize">0 MB</span>
                        </div>

                    </div>
                    
                    <!-- Overlay Drop -->
                    <div id="dragOverlay" class="absolute inset-0 bg-indigo-100/90 rounded-xl border-2 border-indigo-500 flex items-center justify-center opacity-0 pointer-events-none transition-opacity">
                        <p class="text-indigo-700 font-bold animate-pulse">¡Suéltalo aquí!</p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeImportModal()" 
                            class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors text-sm">
                        Cancelar
                    </button>
                    <button type="submit" id="submitBtn" disabled
                            class="flex-[2] px-4 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-200 text-sm opacity-50 cursor-not-allowed">
                        <i class="ph-bold ph-check-circle"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones globales de Importar
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const dropZoneContent = document.getElementById('dropZoneContent');
const dragOverlay = document.getElementById('dragOverlay');
const emptyState = document.getElementById('emptyState');
const fileState = document.getElementById('fileState');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
const submitBtn = document.getElementById('submitBtn');

// Click trigger
dropZoneContent.addEventListener('click', () => fileInput.click());

// Drag & Drop events
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dragOverlay.classList.remove('opacity-0');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dragOverlay.classList.add('opacity-0');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dragOverlay.classList.add('opacity-0');
    
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(fileInput);
    }
});

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validar ZIP
        if (!file.name.toLowerCase().endsWith('.zip')) {
            if (typeof mostrarToast === 'function') mostrarToast('Solo archivos .zip', 'error');
            else alert('Solo archivos .zip');
            input.value = '';
            return;
        }

        // Mostrar Info
        emptyState.classList.add('hidden');
        fileState.classList.remove('hidden');
        fileState.classList.add('flex');
        
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

        // Activar botón
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        submitBtn.classList.add('hover:from-violet-700', 'hover:to-indigo-700', 'transform', 'active:scale-95');
    }
}

window.openImportModal = function() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.remove('hidden');
        const content = modal.querySelector('div.bg-white'); // El panel interno
        if(content) {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }
    }
}

window.closeImportModal = function() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form opcional
        // document.getElementById('importForm').reset();
        // resetUI();
    }
}
</script>

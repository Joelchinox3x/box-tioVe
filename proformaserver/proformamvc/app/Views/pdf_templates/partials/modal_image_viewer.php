<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="hidden fixed inset-0 z-[100] bg-black/95 backdrop-blur-sm transition-opacity opacity-0" aria-modal="true" role="dialog">
    <!-- Top Bar -->
    <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-center z-10 bg-gradient-to-b from-black/60 to-transparent">
        <span id="modalImgName" class="text-white text-sm font-mono truncate max-w-[200px] sm:max-w-md opacity-80">filename.jpg</span>
        
        <div class="flex items-center gap-3">
            <a id="modalDownloadBtn" href="#" download class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-full backdrop-blur-md transition-colors text-xs font-bold border border-white/20">
                <i class="ph-bold ph-download-simple"></i> <span class="hidden sm:inline">Descargar</span>
            </a>
            <button onclick="closeImageModal()" type="button" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-colors">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
    </div>

    <!-- Image Container -->
    <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-8" onclick="if(event.target === this) closeImageModal()">
        <img id="modalImg" src="" class="max-w-full max-h-full object-contain shadow-2xl rounded-sm transition-transform duration-300 scale-95 opacity-0">
    </div>
</div>

<script>
    // Image Viewer Logic
    function openImageModal(src, name) {
        const modal = document.getElementById('imageViewerModal');
        const img = document.getElementById('modalImg');
        const nameLabel = document.getElementById('modalImgName');
        const downloadBtn = document.getElementById('modalDownloadBtn');
        
        img.src = src;
        nameLabel.textContent = name;
        downloadBtn.href = src; 
        downloadBtn.setAttribute('download', name); 

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            img.classList.remove('scale-95', 'opacity-0');
            img.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeImageModal() {
        const modal = document.getElementById('imageViewerModal');
        const img = document.getElementById('modalImg');
        
        modal.classList.add('opacity-0');
        img.classList.remove('scale-100', 'opacity-100');
        img.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            img.src = '';
        }, 300);
    }

    // Esc key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('imageViewerModal') && !document.getElementById('imageViewerModal').classList.contains('hidden')) {
            closeImageModal();
        }
    });
</script>

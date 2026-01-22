<!-- Modal Partial: Opciones y Galería de Folletos -->

<!-- Modal 1: Opciones (Ver PDF / Ver Fotos) -->
<div id="modal-opciones-folleto" class="fixed inset-0 z-[70] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modal-opciones-backdrop" onclick="cerrarOpcionesFolleto()"></div>
  <div class="fixed inset-0 z-10 w-screen overflow-y-auto" onclick="cerrarOpcionesFolleto()">
    <div class="flex min-h-full items-center justify-center p-4 text-center">
      <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-sm opacity-0 scale-95" id="modal-opciones-panel" onclick="event.stopPropagation()">
        
        <button type="button" onclick="cerrarOpcionesFolleto()" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-xl"></i></button>

        <div class="p-8 text-center">
           <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
              <i class="ph-fill ph-files"></i>
           </div>
           <h3 class="text-lg font-bold text-slate-800 mb-2">¿Qué deseas ver?</h3>
           <p class="text-sm text-slate-500 mb-6">Este folleto fue generado automáticamente y tiene fotos fuente disponibles.</p>
           
           <div class="flex flex-col gap-3">
              <button type="button" onclick="abrirPdfDesdeOpcion()" class="w-full py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl flex items-center justify-center gap-2 transition-colors">
                  <i class="ph-fill ph-file-pdf text-red-500 text-xl"></i> Ver Documento PDF
              </button>
              <button type="button" onclick="abrirGaleriaDesdeOpcion()" class="w-full py-3.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-blue-200">
                  <i class="ph-bold ph-images text-xl"></i> Ver Galería de Fotos
              </button>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: Galería TIPO CINE (Theater Mode) -->
<div id="modal-cine-galeria" class="fixed inset-0 z-[80] bg-black hidden flex-col select-none opacity-0 transition-opacity duration-300" tabindex="-1">
    
    <!-- TOP TOOLBAR -->
    <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-start z-[90] bg-gradient-to-b from-black/80 to-transparent pointer-events-none">
        
        <!-- Counter -->
        <div class="text-white/80 font-mono text-sm bg-black/40 px-3 py-1 rounded-full backdrop-blur-md pointer-events-auto">
            <span id="cine-counter-current">1</span> / <span id="cine-counter-total">1</span>
        </div>

        <div class="flex items-center gap-2 pointer-events-auto">
            <!-- Download Button -->
            <a id="cine-download-btn" href="#" download target="_blank" class="text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all" title="Descargar Imagen">
                <i class="ph-bold ph-download-simple text-2xl"></i>
            </a>

            <!-- Close Button -->
            <button type="button" onclick="cerrarGaleriaCine()" class="text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all cursor-pointer">
                <i class="ph-bold ph-x text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- MAIN STAGE (Center) -->
    <div class="flex-1 relative flex items-center justify-center overflow-hidden w-full h-full" onclick="cerrarGaleriaCine()">
        
        <!-- Main Image -->
        <!-- Adding click stops propagation to avoid closing -->
        <img id="cine-main-img" src="" 
             class="max-h-[75vh] max-w-full w-auto object-contain shadow-2xl transition-opacity duration-200 select-none pointer-events-auto"
             onclick="event.stopPropagation()"
             draggable="false"
             ondragstart="return false;">

        <!-- Floating Nav Arrows (Visible on Mobile) -->
        <button type="button" onclick="event.stopPropagation(); prevImageCine()" class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-3 md:p-4 rounded-full hover:bg-black/20 transition-all z-[90] focus:outline-none pointer-events-auto">
            <i class="ph-bold ph-caret-left text-4xl md:text-5xl"></i>
        </button>
        <button type="button" onclick="event.stopPropagation(); nextImageCine()" class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-3 md:p-4 rounded-full hover:bg-black/20 transition-all z-[90] focus:outline-none pointer-events-auto">
            <i class="ph-bold ph-caret-right text-4xl md:text-5xl"></i>
        </button>
    </div>

    <!-- FILMSTRIP (Bottom) -->
    <div class="h-24 min-h-[96px] bg-black/80 backdrop-blur-xl border-t border-white/10 z-[90] w-full flex items-center justify-center relative">
        <div id="cine-filmstrip" class="w-full h-full overflow-x-auto flex items-center gap-3 px-4 no-scrollbar snap-x">
            <!-- JS will inject thumbnails here -->
        </div>
    </div>

</div>

<script>
let galeriaFotosActual = [];
let galeriaIndexActual = 0;
let pdfUrlActual = '';

// --- Gestor de Opciones (PDF vs Fotos) ---
function abrirOpcionesFolleto(pdfUrl, fotosJson) {
  pdfUrlActual = pdfUrl;
  try {
     galeriaFotosActual = JSON.parse(fotosJson || '[]');
  } catch(e) {
     galeriaFotosActual = [];
  }

  const modal = document.getElementById('modal-opciones-folleto');
  const backdrop = document.getElementById('modal-opciones-backdrop');
  const panel = document.getElementById('modal-opciones-panel');
  
  if(modal) {
      modal.classList.remove('hidden');
      setTimeout(() => {
         if(backdrop) backdrop.classList.remove('opacity-0');
         if(panel) panel.classList.remove('opacity-0', 'scale-95');
      }, 10);
  }
}

function cerrarOpcionesFolleto() {
  const modal = document.getElementById('modal-opciones-folleto');
  const backdrop = document.getElementById('modal-opciones-backdrop');
  const panel = document.getElementById('modal-opciones-panel');
  
  if(backdrop) backdrop.classList.add('opacity-0');
  if(panel) panel.classList.add('opacity-0', 'scale-95');
  
  setTimeout(() => {
    if(modal) modal.classList.add('hidden');
  }, 300);
}

function abrirPdfDesdeOpcion() {
    window.open(pdfUrlActual, '_blank');
    cerrarOpcionesFolleto();
}

function abrirGaleriaDesdeOpcion() {
    cerrarOpcionesFolleto();
    setTimeout(() => {
        abrirGaleriaCine(0);
    }, 300);
}


// --- GALERÍA CINE LOGIC ---
function abrirGaleriaCine(index) {
    galeriaIndexActual = index || 0;
    
    // Renderizar Tira de Imágenes
    renderizarFilmstrip();
    
    // Abrir Modal
    const modal = document.getElementById('modal-cine-galeria');
    if(modal) {
        modal.classList.remove('hidden');
        // Fade in
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
        });
        
        // Inicializar eventos (Touch/Keyboard)
        initCineEvents(modal);
        
        // Mostrar primera imagen
        actualizarCineUI();
    }
}

function cerrarGaleriaCine() {
    const modal = document.getElementById('modal-cine-galeria');
    if(modal) {
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function renderizarFilmstrip() {
    const filmstrip = document.getElementById('cine-filmstrip');
    if(!filmstrip) return;
    
    const baseUrl = (typeof FOLLETOS_BASE_URL !== 'undefined') ? FOLLETOS_BASE_URL : '/';
    
    filmstrip.innerHTML = '';
    
    galeriaFotosActual.forEach((fotoPath, i) => {
        const fullUrl = fotoPath.startsWith('http') ? fotoPath : baseUrl + fotoPath;
        
        const div = document.createElement('div');
        // Clases base para miniatura
        div.className = "flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 cursor-pointer transition-all opacity-60 hover:opacity-100 relative snap-center";
        div.onclick = (e) => { e.stopPropagation(); irAFotoCine(i); };
        div.id = `thumb-${i}`;
        
        div.innerHTML = `<img src="${fullUrl}" class="w-full h-full object-cover select-none pointer-events-none">`;
        
        filmstrip.appendChild(div);
    });
    
    // Totales
    const totalEl = document.getElementById('cine-counter-total');
    if(totalEl) totalEl.innerText = galeriaFotosActual.length;
}

function irAFotoCine(index) {
    galeriaIndexActual = index;
    actualizarCineUI();
}

function nextImageCine() {
    if (galeriaIndexActual < galeriaFotosActual.length - 1) {
        galeriaIndexActual++;
    } else {
        galeriaIndexActual = 0; // Loop
    }
    actualizarCineUI();
}

function prevImageCine() {
    if (galeriaIndexActual > 0) {
        galeriaIndexActual--;
    } else {
        galeriaIndexActual = galeriaFotosActual.length - 1; // Loop
    }
    actualizarCineUI();
}

function actualizarCineUI() {
    // 1. Imagen Principal
    const imgElement = document.getElementById('cine-main-img');
    const baseUrl = (typeof FOLLETOS_BASE_URL !== 'undefined') ? FOLLETOS_BASE_URL : '/';
    const currentEl = document.getElementById('cine-counter-current');
    const downloadBtn = document.getElementById('cine-download-btn');
    
    if (galeriaIndexActual < 0) galeriaIndexActual = 0;
    
    const path = galeriaFotosActual[galeriaIndexActual];
    if(!path) return;
    
    const fullUrl = path.startsWith('http') ? path : baseUrl + path;
    
    if(imgElement) {
        // Fade transition
        imgElement.style.opacity = '0.5';
        setTimeout(() => {
            imgElement.src = fullUrl;
            imgElement.style.opacity = '1';
        }, 100);
    }

    if(downloadBtn) {
        downloadBtn.href = fullUrl;
    }
    
    if(currentEl) currentEl.innerText = galeriaIndexActual + 1;
    
    // 2. Filmstrip Highlight & Scroll
    const thumbs = document.querySelectorAll('#cine-filmstrip > div');
    thumbs.forEach((t, i) => {
        if(i === galeriaIndexActual) {
            t.classList.remove('border-transparent', 'opacity-60');
            t.classList.add('border-blue-500', 'opacity-100', 'scale-110', 'z-10');
            t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
             t.classList.add('border-transparent', 'opacity-60');
             t.classList.remove('border-blue-500', 'opacity-100', 'scale-110', 'z-10');
        }
    });
}

// EVENTS (Touch & Keyboard)
function initCineEvents(modal) {
    if (modal.dataset.eventsAdded) return;

    let touchStartX = 0;
    let touchEndX = 0;

    const handleSwipe = () => {
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 50) { // Threshold
            if (diff < 0) nextImageCine(); // Swipe Left -> Next
            else prevImageCine(); // Swipe Right -> Prev
        }
    };

    // Usar window o modal para escuchar
    modal.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    modal.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    // Keyboard support
    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('hidden')) {
            if (e.key === 'ArrowRight') nextImageCine();
            if (e.key === 'ArrowLeft') prevImageCine();
            if (e.key === 'Escape') cerrarGaleriaCine();
        }
    });

    modal.dataset.eventsAdded = 'true';
}


function cerrarGaleriaFotos() {
  const modal = document.getElementById('modal-galeria-fotos');
  const backdrop = document.getElementById('modal-galeria-backdrop');
  const panel = document.getElementById('modal-galeria-panel');
  
  if(backdrop) backdrop.classList.add('opacity-0');
  if(panel) panel.classList.add('opacity-0', 'scale-95');
  
  setTimeout(() => {
    if(modal) modal.classList.add('hidden');
    cerrarLightbox();
  }, 300);
}
</script>

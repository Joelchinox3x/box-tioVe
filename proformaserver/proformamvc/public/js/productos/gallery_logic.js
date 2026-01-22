// ============================================
// LÓGICA DE GALERÍA DE IMÁGENES
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    updateControls(); // Inicializar controles al cargar
});

function updateControls() {
    const container = document.getElementById('current-images-grid');
    if (!container) return; // Si no hay imágenes, no hacemos nada

    const cards = Array.from(container.children);
    let firstUnlockedIndex = -1;

    // 1. Encontrar la primera posición desbloqueada (Candidato)
    for (let i = 0; i < cards.length; i++) {
        if (!cards[i].classList.contains('locked-pos')) {
            firstUnlockedIndex = i;
            break;
        }
    }

    // 2. Actualizar botones según la lógica
    cards.forEach((card, index) => {
        const btnStar = card.querySelector('.btn-star');
        const btnLock = card.querySelector('.btn-lock');
        // Badge eliminado según requerimiento

        if (!btnStar || !btnLock) return;

        // --- RESET INICIAL ---
        btnStar.classList.add('hidden');
        btnLock.classList.add('hidden');
        btnLock.classList.remove('group/lock');

        // Clases base para el botón de candado (Posición fija top-2 left-2)
        btnLock.className = "btn-lock group/lock absolute top-2 left-2 z-20 w-8 h-8 flex items-center justify-center rounded-lg shadow-sm ring-1 ring-black/5 transition-all text-xs font-bold";

        const isLocked = card.classList.contains('locked-pos');

        // ==== LÓGICA DE VISUALIZACIÓN DETALLADA ====

        /* 
           CASO 1: POSICIÓN BLOQUEADA
           - Botón: Muestra ÍCONO DE CANDADO ROJO SIEMPRE (No número).
        */
        if (isLocked) {
            btnLock.classList.remove('hidden', 'opacity-0');
            btnLock.classList.add('bg-white', 'text-red-500', 'hover:bg-red-50');

            // Muestra CANDADO CERRADO siempre (sin número)
            btnLock.innerHTML = `<i class="ph-fill ph-lock-key text-sm"></i>`;

            btnStar.classList.add('hidden');
        }
        /* 
           CASO 2: CANDIDATO (Primera posición LIBRE)
           - Botón: Muestra el NÚMERO (ej. "2"). Hover -> Candado abierto.
        */
        else if (index === firstUnlockedIndex) {
            btnLock.classList.remove('hidden', 'opacity-0');
            btnLock.classList.add('bg-white/90', 'text-slate-500', 'hover:bg-white', 'hover:text-slate-700');
            btnLock.title = "Bloquear esta posición";

            btnLock.innerHTML = `
                <span class="block group-hover/lock:hidden">${index + 1}</span>
                <i class="ph-bold ph-lock-open hidden group-hover/lock:block text-sm"></i>
             `;

            btnStar.classList.add('hidden');
        }
        /* 
           CASO 3: EL RESTO (Imágenes desordenadas)
           - Muestran la ESTRELLA para "priorizarlas".
           - La estrella es amarilla sólida.
        */
        else {
            btnStar.classList.remove('hidden');
            btnStar.className = "btn-star absolute top-2 left-2 z-20 bg-amber-400 text-white p-1.5 rounded-lg text-xs hover:bg-amber-500 hover:scale-110 shadow-md ring-1 ring-black/5 transition-all w-7 h-7 flex items-center justify-center";
            btnStar.title = "Mover a la posición " + (firstUnlockedIndex + 1);

            btnLock.classList.add('hidden');
        }
    });
}

function toggleLock(btn) {
    if (typeof protegido !== 'undefined' && protegido) return;

    // Usar .group para encontrar el wrapper principal de la tarjeta
    const card = btn.closest('.group');

    // Toggle estado locked
    if (card.classList.contains('locked-pos')) {
        // Desbloquear
        card.classList.remove('locked-pos', 'ring-2', 'ring-red-400');
        mostrarToast('Posición desbloqueada', 'info');
    } else {
        // Bloquear
        card.classList.add('locked-pos', 'ring-2', 'ring-red-400');
        mostrarToast('Posición asegurada', 'success');
    }

    updateControls(); // Recalcular números e iconos
}

function priorizarImagen(btn) {
    if (typeof protegido !== 'undefined' && protegido) return;

    const card = btn.closest('.group');
    const container = card.parentElement;

    // Buscar a dónde va (Primera posición no bloqueada)
    const cards = Array.from(container.children);
    let targetIndex = -1;

    for (let i = 0; i < cards.length; i++) {
        if (!cards[i].classList.contains('locked-pos')) {
            targetIndex = i;
            break;
        }
    }

    if (targetIndex === -1) {
        mostrarToast('Todas las posiciones anteriores están bloqueadas', 'warning');
        return;
    }

    const referenceNode = container.children[targetIndex];

    if (referenceNode === card) return;

    container.insertBefore(card, referenceNode);

    // Animar para dar feedback visual
    card.classList.add('animate-pulse');
    setTimeout(() => card.classList.remove('animate-pulse'), 500);

    updateControls();
}

// --- MANEJO DE ELIMINACIÓN DE IMÁGENES (Modal) ---
let imageToDelete = null;

function eliminarImagenExistente(btn) {
    if (typeof protegido !== 'undefined' && protegido) {
        mostrarToast('Debes desbloquear para eliminar', 'warning');
        return;
    }

    imageToDelete = btn.closest('.group');
    openDeleteImageModal();
}

function openDeleteImageModal() {
    const modal = document.getElementById('deleteImageModal');
    modal.classList.remove('hidden');
    // Forzar reflow para animación
    void modal.offsetWidth;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.querySelector('div').classList.remove('scale-95');
    modal.querySelector('div').classList.add('scale-100');
}

function closeDeleteImageModal() {
    const modal = document.getElementById('deleteImageModal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.querySelector('div').classList.remove('scale-100');
    modal.querySelector('div').classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        imageToDelete = null;
    }, 300);
}

// Inicializar listener del botón confirmar (una sola vez)
document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('confirmDeleteImageBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (imageToDelete) {
                imageToDelete.remove();
                updateControls();
                mostrarToast('Imagen eliminada. Guarda los cambios.', 'info');
                closeDeleteImageModal();
            }
        });
    }
});


// --- PREVIEW IMAGES LOGIC (Para Upload) ---
// Global DataTransfer para acumular imágenes
const imageAccumulator = new DataTransfer();

async function previewImages(input) {
    const container = document.getElementById('preview-container');

    // Si hay nuevos archivos, agregarlos al acumulador
    if (input.files && input.files.length > 0) {
        // Validar límite total (ej. 10) o por lote? 
        // Vamos a procesar los nuevos y añadirlos

        const processPromises = Array.from(input.files).map(async (file) => {
            // Validar tipo
            if (!file.type.startsWith('image/')) {
                mostrarToast(`El archivo ${file.name} no es una imagen`, 'error');
                return null;
            }

            // Optimizar imagen
            let processedFile = file;
            try {
                if (typeof ImageOptimizer !== 'undefined') {
                    processedFile = await ImageOptimizer.optimize(file, {
                        maxWidth: 1200, quality: 0.8, maxSizeMB: 1
                    });

                    // Feedback detallado por archivo
                    if (processedFile.size < file.size) {
                        const originalKB = (file.size / 1024).toFixed(0);
                        const newKB = (processedFile.size / 1024).toFixed(0);
                        mostrarToast(`Optimizada: ${originalKB}KB ➜ ${newKB}KB`, 'success');
                    } else {
                        // Mostrar como 'info' si no hubo reducción (ej. era pequeña)
                        mostrarToast(`Procesada: ${(file.size / 1024).toFixed(0)}KB`, 'info');
                    }
                }
            } catch (err) {
                console.warn('Error optimizando:', err);
                mostrarToast(`Error optimizando ${file.name}`, 'error');
            }
            return processedFile;
        });

        const newFiles = await Promise.all(processPromises);

        // Agregar válidos al acumulador
        newFiles.forEach(f => {
            if (f) imageAccumulator.items.add(f);
        });
    }

    // Actualizar el input con TODOS los archivos acumulados
    input.files = imageAccumulator.files;

    // Renderizar Todo desde cero (para mantener índices sincronizados con el acumulador)
    renderPreviewGrid();
}

function renderPreviewGrid() {
    const container = document.getElementById('preview-container');
    const input = document.getElementById('input-fotos');
    container.innerHTML = '';

    Array.from(imageAccumulator.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const src = e.target.result;
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-xl overflow-hidden group shadow-sm bg-slate-100 animate-scale-in';
            div.style.animationDelay = `${index * 50}ms`;

            div.innerHTML = `
                <img src="${src}" class="w-full h-full object-cover cursor-pointer hover:scale-110 transition-transform duration-500" 
                     onclick="showImagePreview('${src}')">
                
                <button type="button" 
                        onclick="removeNewImage(${index})"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 transition-transform hover:scale-110 z-20">
                  <i class="ph-bold ph-x text-xs"></i>
                </button>
                
                <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-1 pointer-events-none">
                    <p class="text-[10px] text-white text-center truncate px-1 font-medium">${(file.size / 1024).toFixed(1)} KB</p>
                </div>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeNewImage(index) {
    const dt = new DataTransfer();
    const files = imageAccumulator.files;

    // Reconstruir DataTransfer excluyendo el índice eliminado
    for (let i = 0; i < files.length; i++) {
        if (index !== i) dt.items.add(files[i]);
    }

    // Actualizar acumulador global
    imageAccumulator.items.clear();
    for (let i = 0; i < dt.files.length; i++) {
        imageAccumulator.items.add(dt.files[i]);
    }

    // Actualizar input real
    const input = document.getElementById('input-fotos');
    input.files = imageAccumulator.files;

    // Re-renderizar
    renderPreviewGrid();
}

// ============================================
// LIGHTBOX CON SWIPE (Variable Global)
// ============================================
let lightboxImages = [];
let currentLightboxIndex = 0;

function initLightbox() {
    // Recolectar todas las imágenes visibles (Actuales + Nuevas) based on specific selectors
    const currentImgs = Array.from(document.querySelectorAll('#current-images-grid img'));
    const previewImgs = Array.from(document.querySelectorAll('#preview-container img'));

    // Combinar listas y extraer sources
    lightboxImages = [...currentImgs, ...previewImgs].map(img => img.src);

    // Setup modal events once
    const modal = document.getElementById('imagePreviewModal');
    if (!modal.dataset.eventsAdded) {
        let touchStartX = 0;
        let touchEndX = 0;

        const handleSwipe = () => {
            if (touchEndX < touchStartX - 50) nextImage();
            if (touchEndX > touchStartX + 50) prevImage();
        };

        modal.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });

        modal.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'Escape') closeImagePreview();
        });

        modal.dataset.eventsAdded = 'true';
    }
}

// Modificar para aceptar índice o src (búsqueda inversa si es src)
function openLightbox(index_or_src) {
    initLightbox(); // Refrescar lista por si hubo cambios

    if (typeof index_or_src === 'number') {
        currentLightboxIndex = index_or_src;
    } else {
        // Normalizar URLs para comparación
        const target = index_or_src.split('?')[0]; // Ignorar query params

        // Intento 1: Búsqueda exacta
        currentLightboxIndex = lightboxImages.findIndex(src => src === index_or_src);

        // Intento 2: Búsqueda flexible (si el src contiene la ruta relativa o viceversa)
        if (currentLightboxIndex === -1) {
            currentLightboxIndex = lightboxImages.findIndex(src => {
                const srcClean = src.split('?')[0];
                return srcClean.includes(target) || target.includes(srcClean);
            });
        }

        // Fallback
        if (currentLightboxIndex === -1) currentLightboxIndex = 0;
    }

    updateLightboxUI();
    document.getElementById('imagePreviewModal').classList.remove('hidden');
}

function updateLightboxUI() {
    const img = document.getElementById('previewModalImage');
    const modal = document.getElementById('imagePreviewModal');

    // Animación de fade
    img.style.opacity = '0.5';
    setTimeout(() => {
        img.src = lightboxImages[currentLightboxIndex];
        img.style.opacity = '1';
    }, 150);

    // Actualizar contadores o UI si existieran
}

function nextImage() {
    if (currentLightboxIndex < lightboxImages.length - 1) {
        currentLightboxIndex++;
        updateLightboxUI();
    } else {
        // Loop? Or stop? Let's loop for better UX
        currentLightboxIndex = 0;
        updateLightboxUI();
    }
}

function prevImage() {
    if (currentLightboxIndex > 0) {
        currentLightboxIndex--;
        updateLightboxUI();
    } else {
        currentLightboxIndex = lightboxImages.length - 1;
        updateLightboxUI();
    }
}

// Alias para compatibilidad con código viejo que llama showImagePreview(src)
function showImagePreview(src) {
    if (!lightboxImages.length) initLightbox(); // Try to init if empty
    openLightbox(src);
}

function closeImagePreview() {
    document.getElementById('imagePreviewModal').classList.add('hidden');
}


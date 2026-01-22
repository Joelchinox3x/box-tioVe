/**
 * Lógica de Administración para show.php (Admin)
 * Incluye: Subida Rápida de Fotos, Compartir, Eliminar
 */

// Cambiar imagen principal
function cambiarImagen(fullUrl) {
    const img = document.querySelector('#mainImage img');
    if (img) {
        img.src = fullUrl;
    }
}

// Confirmar eliminación
// Confirmar eliminación (Usa el mismo modal de productos/index.php)
window.confirmarEliminar = function () {
    const modal = document.getElementById('confirmDeleteModal');
    const nameEl = document.getElementById('deleteProductName');

    // Asignar nombre del producto al modal
    if (nameEl && window.SHARE_TITLE) {
        nameEl.textContent = window.SHARE_TITLE;
    }

    // Mostrar modal con animación
    if (modal) {
        modal.classList.remove('hidden');
        const content = modal.querySelector('div');
        if (content) {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }
    } else {
        console.error("Error: Modal de eliminación no encontrado en el DOM");
    }
}

// Funciones llamadas por el modal compartido (delete_modal.php)
window.cerrarModalEliminar = function () {
    const modal = document.getElementById('confirmDeleteModal');
    if (modal) modal.classList.add('hidden');
}

window.ejecutarEliminacion = function () {
    const deleteUrl = window.DELETE_URL;
    if (deleteUrl) window.location.href = deleteUrl;
}

// Subida Rápida de Fotos
async function handleQuickUpload(input) {
    const files = input.files;
    if (files.length === 0) return;

    // Feedback Visual
    const originalLabel = input.parentElement.innerHTML;
    input.parentElement.innerHTML = '<div class="animate-spin text-indigo-600"><i class="ph-bold ph-spinner"></i></div>';

    const formData = new FormData();
    let count = 0;

    try {
        for (let i = 0; i < files.length; i++) {
            // Optimizar
            try {
                const compressed = await ImageOptimizer.compress(files[i], 0.8, 1200);

                // Mensaje de éxito si se optimizó
                if (files[i].size > compressed.size) {
                    mostrarToast(`Imagen optimizada: ${((files[i].size / 1024).toFixed(0))}KB ➜ ${(compressed.size / 1024).toFixed(0)}KB`, 'success');
                } else {
                    mostrarToast(`Imagen procesada: ${((files[i].size / 1024).toFixed(0))}KB`, 'info');
                }

                formData.append('imagenes[]', compressed);
                count++;
            } catch (e) {
                console.error(e);
                mostrarToast(`Error optimizando ${files[i].name}`, 'error');
                formData.append('imagenes[]', files[i]); // Fallback
            }
        }

        if (count > 0) {
            // Enviar AJAX
            // UPLOAD_URL debe ser global
            const uploadUrl = window.UPLOAD_URL;

            if (!uploadUrl) {
                mostrarToast("Error de configuración: no upload url", "error");
                return;
            }

            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                mostrarToast("Fotos subidas correctamente. Recargando...", "success");
                setTimeout(() => window.location.reload(), 1500);
            } else {
                mostrarToast("Error al subir: " + (result.error || 'Desconocido'), "error");
                setTimeout(() => window.location.reload(), 2000);
            }
        }
    } catch (error) {
        console.error(error);
        mostrarToast("Error de conexión", "error");
        setTimeout(() => window.location.reload(), 2000);
    }
}

// Compartir Producto
function compartirProducto() {
    // SHARE_URL y SHARE_TITLE deben ser globales
    const shareUrl = window.SHARE_URL;
    const shareTitle = window.SHARE_TITLE;
    const managerName = window.MANAGER_NAME || "";

    // Mensaje profesional
    const shareText = `Hola, soy ${managerName}.\nTe comparto la ficha técnica de: ${shareTitle}.\n\nVer aquí:`;

    if (navigator.share) {
        navigator.share({
            title: shareTitle,
            text: shareText,
            url: shareUrl
        }).catch(err => {
            // Fallback a clipboard si cancela o falla
            if (err.name !== 'AbortError') copyToClipboard(shareUrl);
        });
    } else {
        copyToClipboard(shareUrl);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        mostrarToast('Enlace copiado al portapapeles', 'success');
    }).catch(err => {
        prompt("Copia este enlace:", text);
    });
}

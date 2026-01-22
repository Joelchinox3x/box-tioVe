<?php
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

// Configuración del header para CREAR
$title = 'Nuevo Producto';
$subtitle = 'Ingresa los detalles del nuevo equipo';
$back_url = url('/inventario');
$badge = 'Creando';
$badge_color = 'blue';
$search = false;
$section = 'inventario';
include __DIR__ . '/../partials/load_header.php';

// Inicializar array vacío para evitar errores en partials que esperan $producto
$producto = [
    'nombre' => '',
    'precio' => '',
    'moneda' => 'PEN',
    'modelo' => '',
    'sku' => '',
    'descripcion' => '',
    'imagenes' => [], // Array vacío para galería
    'specs' => [],    // Array vacío para specs
    'protegido' => false
];
?>

<!-- Utilidad de compresión y Scripts Modulares -->
<script src="<?= asset('js/utils/image-optimizer.js') ?>"></script>

<script src="<?= asset('js/productos/gallery_logic.js') ?>" defer></script>
<script src="<?= asset('js/utils/form_validator.js') ?>"></script>
<script src="<?= asset('js/productos/specs_logic.js') ?>" defer></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="pt-8 px-5 pb-10 max-w-md mx-auto">

  <form action="<?= url('/inventario/store') ?>" method="POST" id="productForm" enctype="multipart/form-data" class="space-y-3" novalidate>

    <!-- 1. Información General (Reutilizado) -->
    <?php include __DIR__ . '/partials/info_general.php'; ?>

    <!-- 2. Galería de Imágenes (Reutilizado - mostrará solo upload) -->
    <?php include __DIR__ . '/partials/gallery_component.php'; ?>

    <!-- 3. Especificaciones Técnicas (Reutilizado) -->
    <?php include __DIR__ . '/partials/tech_specs.php'; ?>

    <!-- (Panel de Protección NO se incluye en create) -->

    <!-- Botones de acción -->
    <div class="fixed bottom-20 left-0 right-0 z-20 animate-fade-in-up px-4" style="animation-delay: 0.4s">
        <div class="flex gap-3 bg-white/95 backdrop-blur-xl p-3 rounded-2xl shadow-2xl border-2 border-slate-200/50 ring-4 ring-slate-100/50 max-w-lg mx-auto">
            <a href="<?= url('/inventario') ?>" class="flex-1 px-5 py-3 bg-slate-200 border-2 border-slate-400 text-slate-700 rounded-xl font-semibold text-center hover:bg-slate-400 hover:border-slate-600 transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2 text-sm">
            <i class="ph-bold ph-x"></i>
            <span>Cancelar</span>
            </a>
            <button type="submit" form="productForm" id="submitBtn" class="flex-1 px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                <i class="ph-bold ph-plus-circle relative z-10"></i>
                <span class="relative z-10">Crear Producto</span>
            </button>
        </div>
    </div>

  </form>

</main>

<script>
// ============================================
// VARIABLES Y HANDLERS ESPECÍFICOS DE CREATE
// ============================================
// En create no hay protección inicial
const protegido = false; 
// contador se maneja en specs_logic.js 

// Función global para specs ahora en specs_logic.js
const style = document.createElement('style');
style.textContent = `
  @keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
  @keyframes scale-in { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
  .animate-fade-in-up { animation: fade-in-up 0.5s ease-out forwards; opacity: 0; }
  .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
  .animate-scale-in { animation: scale-in 0.3s ease-out forwards; }
`;
document.head.appendChild(style);

// NOTIFICACIONES
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_GET['error'])): ?>
    mostrarToast('<?= htmlspecialchars($_GET['error']) ?>', 'error');
  <?php endif; ?>
});

// Toast Helper (Global - Ahora usa toast.js centralizado)
// Eliminada implementación inline duplicada

// Configuración de validación con FormValidator
document.addEventListener('DOMContentLoaded', function() {
    if (typeof FormValidator !== 'undefined') {
        // Validación Nombre
        FormValidator.setupNameInput('nombre', {
            checkId: 'nombre-check',
            errorId: 'nombre-error',
            nextInputId: 'precio',
            isProtected: typeof protegido !== 'undefined' ? protegido : false
        });

        // Validación Precio
        FormValidator.setupPriceInput('precio', {
            checkId: 'precio-check',
            errorId: 'precio-error',
            hintId: 'precio-hint',
            isProtected: typeof protegido !== 'undefined' ? protegido : false
        });

        // Validación al enviar
        const form = document.getElementById('productForm');
        form?.addEventListener('submit', function(e) {
            let hasErrors = false;

            // Validar Nombre
            if (!FormValidator.validateOnSubmit(e, 'nombre', 'El nombre debe contener al menos 4 letras')) {
                hasErrors = true;
            }

            // Validar Precio
            // Si ya hubo un error (nombre), el preventDefault ya se llamó, pero igual queremos marcar visualmente el precio si falla
            // Pasamos 'price' como validationType
            if (!FormValidator.validateOnSubmit(e, 'precio', 'El precio debe ser mayor a 0', 'price')) {
                 hasErrors = true;
            }

            if (hasErrors) {
                // Si hubo errores, el validateOnSubmit se encarga del scroll y toast.
                // Si ambos fallan, el scroll irá al último validado (precio), o podemos forzar scroll al primero 'nombre' si requerimos prioridad.
                return false;
            }

            // Animación de botón guardar
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                 submitBtn.innerHTML = `
                  <i class="ph-bold ph-circle-notch animate-spin relative z-10"></i>
                  <span class="relative z-10">Guardando...</span>
                `;
                submitBtn.disabled = true;
            }
        });
    }
});
</script>

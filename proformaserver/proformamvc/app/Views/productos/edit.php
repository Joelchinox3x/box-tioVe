<?php
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

// Configuración del header para editar
$title = 'Editar Producto';
$subtitle = $producto['protegido'] ?? false ? 'Solo lectura' : 'Actualiza la información';
$back_url = url('/inventario');
$badge = $producto['protegido'] ?? false ? 'Bloqueado' : 'Editando';
$badge_color = $producto['protegido'] ?? false ? 'red' : 'orange';
$search = false;
$section = 'inventario';
include __DIR__ . '/../partials/load_header.php';
?>

<!-- Utilidad de compresión y Scripts Modulares -->
<script src="<?= asset('js/utils/image-optimizer.js') ?>"></script>

<script src="<?= asset('js/productos/gallery_logic.js') ?>" defer></script>
<script src="<?= asset('js/productos/form_validation.js') ?>" defer></script>
<script src="<?= asset('js/productos/specs_logic.js') ?>" defer></script>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="pt-8 px-5 pb-10 max-w-md mx-auto">

  <form action="<?= url("/inventario/update/{$producto['id']}") ?>" method="POST" id="productForm" enctype="multipart/form-data" class="space-y-3" novalidate>

    <!-- 1. Información General -->
    <?php include __DIR__ . '/partials/info_general.php'; ?>

    <!-- 2. Galería de Imágenes -->
    <?php include __DIR__ . '/partials/gallery_component.php'; ?>

    <!-- 3. Folletos PDF -->
    <?php include __DIR__ . '/partials/folletos_manager.php'; ?>

    <!-- 4. Especificaciones Técnicas -->
    <?php include __DIR__ . '/partials/tech_specs.php'; ?>

    <!-- 5. Panel de Protección (Solo en Edit) -->
    <?php include __DIR__ . '/partials/protection_panel.php'; ?>

    <!-- Botones de acción -->
    <div class="fixed bottom-20 left-0 right-0 z-50 animate-fade-in-up px-4" style="animation-delay: 0.4s">
        <?php
            // Definir botón class dinámicamente si no está definido en partials
            $buttonClass = ($producto['protegido'] ?? false) 
                ? 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 cursor-not-allowed'
                : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700';
        ?>
        <div class="flex gap-3 bg-white/95 backdrop-blur-xl p-3 rounded-2xl shadow-2xl border-2 border-slate-200/50 ring-4 ring-slate-100/50 max-w-lg mx-auto">
            <a href="<?= url('/inventario') ?>" class="flex-1 px-5 py-3 bg-slate-200 border-2 border-slate-400 text-slate-700 rounded-xl font-semibold text-center hover:bg-slate-400 hover:border-slate-600 transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2 text-sm">
            <i class="ph-bold ph-x"></i>
            <span>Cancelar</span>
            </a>
            <button type="submit" form="productForm" id="submitBtn" class="flex-1 px-5 py-3 <?= $buttonClass ?> text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm"
            <?= ($producto['protegido'] ?? false) ? ' onclick="mostrarToast(\'Producto bloqueado. Desbloquea primero para guardar cambios.\', \'error\'); return false;"' : '' ?>>
            <?php if (!($producto['protegido'] ?? false)): ?>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
            <?php endif; ?>
            <i class="ph-bold <?= ($producto['protegido'] ?? false) ? 'ph-lock' : 'ph-check' ?> relative z-10"></i>
            <span class="relative z-10"><?= ($producto['protegido'] ?? false) ? 'Bloqueado' : 'Actualizar' ?></span>
            </button>
        </div>
    </div>

  </form>

</main>

<script>
// ============================================
// VARIABLES Y HANDLERS ESPECÍFICOS DE EDIT
// ============================================
const protegido = <?= ($producto['protegido'] ?? false) ? 'true' : 'false' ?>;
// contador se maneja en specs_logic.js

// Función global specs_logic.js
// ESTILOS INYECTADOS
const style = document.createElement('style');
style.textContent = `
  @keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
  @keyframes scale-in { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
  @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
  .animate-fade-in-up { animation: fade-in-up 0.5s ease-out forwards; opacity: 0; }
  .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
  .animate-scale-in { animation: scale-in 0.3s ease-out forwards; }
  .animate-shake { animation: shake 0.3s ease-out; }
`;
document.head.appendChild(style);

// NOTIFICACIONES INICIALES
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_GET['error'])): ?>
    mostrarToast('<?= htmlspecialchars($_GET['error']) ?>', 'error');
  <?php endif; ?>
  <?php if (isset($_GET['success'])): ?>
    mostrarToast('Los cambios se guardaron correctamente', 'success');
  <?php endif; ?>
});

// Toast Helper (Global - Ahora usa toast.js centralizado)
// La función mostrarToast ya existe globalmente gracias a toast.js
// Se mantiene la llamada para compatibilidad si hay lógica custom aquí, 
// pero eliminamos la definición duplicada.


// Lógica de Protección (Dejarla inline es aceptable o mover a protection_logic.js si se desea mayor pureza, 
// pero está muy ligada al HTML del modal en el partial).
let intentandoDesbloquear = false;
let estadoProteccionInicial = <?= ($producto['protegido'] ?? false) ? 'true' : 'false' ?>;

function manejarCambioProteccion(checkbox) {
  if (estadoProteccionInicial && !checkbox.checked) {
    checkbox.checked = true;
    intentandoDesbloquear = true;
    mostrarModalPin();
  } else if (!estadoProteccionInicial && checkbox.checked) {
    intentandoDesbloquear = false;
    mostrarToast('Guardando cambios...', 'info');
    setTimeout(() => { document.getElementById('productForm').submit(); }, 500);
  }
}
function mostrarModalPin() {
  document.getElementById('pinModal').classList.remove('hidden');
  document.getElementById('pinInput').focus();
}
function cerrarModalPin() {
    const checkbox = document.getElementById('protegidoCheck');
    document.getElementById('pinModal').classList.add('hidden');
    document.getElementById('pinInput').value = '';
    
    if(intentandoDesbloquear) checkbox.checked = true;
    intentandoDesbloquear = false;
}
function togglePinVisibility() {
    const input = document.getElementById('pinInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}
function verificarPin() {
    const pin = document.getElementById('pinInput').value.trim();
    if(!pin) return;
    
    fetch('<?= url('/inventario/verificar-pin') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ pin: pin })
    })
    .then(r => r.json())
    .then(data => {
        if(data.valid) {
            intentandoDesbloquear = false; // Evitar que cerrarModalPin vuelva a bloquear
            document.getElementById('protegidoCheck').checked = false;
            document.getElementById('adminPin').value = pin;
            cerrarModalPin();
            mostrarToast('Desbloqueado', 'success');
            setTimeout(() => document.getElementById('productForm').submit(), 500);
        } else {
            mostrarToast('PIN Incorrecto', 'error');
        }
    });
}
</script>

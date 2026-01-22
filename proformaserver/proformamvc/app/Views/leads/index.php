<?php
// Título y Configuración
$title = 'Solicitudes de Proforma';
$subtitle = 'Gestiona los clientes potenciales que llegaron via web';
$section = 'clientes'; // Para activar el color del header
?>

<!-- Header -->
<?php require_once __DIR__ . '/../partials/load_header.php'; ?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido -->
<div class="pt-1 px-4 pb-6 max-w-md mx-auto animate-fade-in-up bg-slate-50 min-h-screen">

    <!-- Header de Sección -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Solicitudes</h1>
            <p class="text-slate-500 text-sm">Pendientes de revisión que llegan desde solicitar proforma.</p>
        </div>
        <a href="<?= url('/clientes') ?>" class="w-10 h-10 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition flex items-center justify-center">
            <i class="ph-bold ph-users text-xl"></i>
        </a>
    </div>

    <!-- Lista de Leads -->
    <?php if (empty($leads)): ?>
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-br from-slate-50 to-slate-100 text-slate-300 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="ph-duotone ph-check-fat text-5xl"></i>
            </div>
            <p class="text-slate-500 text-base font-medium">No hay solicitudes pendientes</p>
            <p class="text-slate-400 text-sm mt-1">¡Todo listo por ahora!</p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($leads as $lead): ?>
                <?php include __DIR__ . '/partials/lead_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Extrafaccionado -->
<?php include __DIR__ . '/partials/search_modal.php'; ?>

<!-- Lógica JS Externa -->
<script src="<?= asset('js/leads/index_logic.js') ?>"></script>

<style>
@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>

<!-- Proteger Producto -->
<?php

// Determinar estilos según el estado de protección
if ($producto['protegido'] ?? false) {
    // ESTADO: BLOQUEADO (Tonos Rojo/Naranja)
    $cardBg   = 'from-red-50 to-red-50 border-red-200';
    $iconBg   = 'from-red-500 to-red-600';
    $iconName = 'ph-shield-check';
    $decoBg   = 'from-red-400/10 to-rose-400/10';
    $textInfo = 'text-red-800';
    $titulo      = 'Producto Bloqueado';
    $descripcion = 'Este producto está protegido y no se puede eliminar.';
    $switchOnColor = 'peer-checked:bg-red-500';
    $buttonClass = 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 cursor-not-allowed';
} else {
    // ESTADO: NORMAL (Tonos Azul/Slate)
    $cardBg   = 'from-cyan-50 to-blue-50 border-cyan-200';
    $iconBg   = 'from-blue-500 to-indigo-600';
    $iconName = 'ph-shield'; 
    $decoBg   = 'from-blue-400/10 to-indigo-400/10';
    $textInfo = 'text-blue-800';
    $titulo      = 'Proteger Producto';
    $descripcion = 'Activa esto para evitar eliminación accidental.';
    $switchOnColor = 'peer-checked:bg-blue-500';
    $buttonClass = 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700';
}
?>

<div class="relative bg-gradient-to-br <?= $cardBg ?> rounded-2xl p-3.5 shadow-sm border overflow-hidden animate-fade-in-up" style="animation-delay: 0.15s">
  <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br <?= $decoBg ?> rounded-full -mr-12 -mt-12"></div>
  
  <label class="flex items-center justify-between cursor-pointer group">
    <div class="flex items-center gap-2.5">

      <div class="w-12 h-12 bg-gradient-to-br <?= $iconBg ?> rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
       <i class="ph-bold <?= $iconName ?> text-white text-3xl leading-none"></i>
      </div>

      <div>
        <p class="font-semibold text-slate-800 text-sm"><?= $titulo ?></p>
        <p class="text-xs <?= $textInfo ?>"><?= $descripcion ?></p>
      </div>

    </div>

    <div class="relative">
      <input
        type="checkbox"
        name="protegido"
        value="1"
        id="protegidoCheck"
        <?= ($producto['protegido'] ?? false) ? 'checked' : '' ?>
        class="sr-only peer"
        onchange="manejarCambioProteccion(this)"
      >
        <div class="w-12 h-6 bg-slate-300 rounded-full transition-all duration-300 shadow-inner <?= $switchOnColor ?>"></div>
        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-all duration-300 peer-checked:translate-x-6"></div>
    </div>
  </label>
</div>

<!-- Input oculto para el PIN -->
<input type="hidden" name="admin_pin" id="adminPin" value="">

<!-- Modal de PIN -->
<div id="pinModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in">
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-4 rounded-t-2xl">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
          <i class="ph-bold ph-shield-check text-white text-xl"></i>
        </div>
        <div>
          <h3 class="text-white font-bold text-base">PIN de Seguridad</h3>
          <p class="text-white/90 text-xs">Ingresa tu código de administrador</p>
        </div>
      </div>
    </div>

    <div class="p-5">
      <p class="text-slate-600 text-sm mb-4">Para desbloquear este cliente protegido, ingresa el PIN de administrador:</p>
      <label class="block text-xs font-bold text-slate-600 mb-2">Código PIN</label>
      <div class="relative">
        <input
          type="password"
          id="pinInput"
          placeholder="••••••"
          maxlength="6"
          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-12 text-center text-2xl font-bold tracking-widest focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all"
          autocomplete="off"
        >
        <button
          type="button"
          onclick="togglePinVisibility()"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
        >
          <i class="ph-bold ph-eye text-xl" id="togglePinIcon"></i>
        </button>
      </div>

      <div class="flex gap-2 mt-4">
        <button
          type="button"
          onclick="cerrarModalPin()"
          class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all"
        >
          Cancelar
        </button>
        <button
          type="button"
          onclick="verificarPin()"
          class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-bold transition-all shadow-md hover:shadow-lg"
        >
          Verificar
        </button>
      </div>
    </div>
  </div>
</div>

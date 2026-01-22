<!-- Modal de Confirmación de Eliminación ULTRA PREMIUM -->
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in overflow-hidden">
    <!-- Header con gradiente rojo peligro -->
    <div class="bg-gradient-to-r from-red-500 to-rose-600 p-5 relative overflow-hidden">
      <!-- Patrón decorativo de fondo -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full -ml-12 -mb-12"></div>
      </div>

      <div class="flex items-center gap-3 relative z-10">
        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
          <i class="ph-bold ph-warning text-white text-2xl animate-pulse"></i>
        </div>
        <div>
          <h3 class="text-white font-bold text-lg">¡Advertencia!</h3>
          <p class="text-white/90 text-xs">Esta acción no se puede deshacer</p>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="p-6">
      <p class="text-slate-700 text-sm mb-1">¿Estás seguro de eliminar el producto:</p>
      <p class="text-slate-900 font-bold text-lg mb-3" id="deleteProductName">Producto</p>

      <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg mb-4">
        <div class="flex items-start gap-2">
          <i class="ph-bold ph-warning-circle text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
          <div>
            <p class="text-amber-800 text-xs font-semibold mb-1">Se eliminarán también:</p>
            <ul class="text-amber-700 text-xs space-y-0.5">
              <li>• Todas sus imágenes</li>
              <li>• Todas sus especificaciones</li>
              <li>• Todo su historial</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="flex gap-3">
        <button
          onclick="cerrarModalEliminar()"
          class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all active:scale-95"
        >
          Cancelar
        </button>
        <button
          onclick="ejecutarEliminacion()"
          class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold transition-all shadow-md hover:shadow-lg active:scale-95"
        >
          Sí, eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes fade-in-up {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes scale-in {
    from {
      opacity: 0;
      transform: scale(0.9);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }

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

  @keyframes slide-out {
    from {
      opacity: 1;
      transform: translateX(0);
    }
    to {
      opacity: 0;
      transform: translateX(100%);
    }
  }

  .animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out forwards;
  }

  .animate-scale-in {
    animation: scale-in 0.3s ease-out forwards;
  }

  .animate-shake {
    animation: shake 0.3s ease-out;
  }

  .animate-slide-in {
    animation: slide-in 0.3s ease-out forwards;
  }

  .animate-slide-out {
    animation: slide-out 0.3s ease-out forwards;
  }
</style>

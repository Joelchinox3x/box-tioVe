  <!-- Botón flotante inferior con Total -->
  <div id="floatingTotalContainer" class="fixed bottom-32 left-0 right-0 px-4 max-w-md mx-auto z-30 translate-y-5">

    <div class="bg-slate-900 text-white p-3 rounded-2xl shadow-2xl shadow-slate-900/30 flex justify-between items-center border-2 border-slate-700/50 backdrop-blur-md">
      <div>
        <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider mb-1">Total Estimado</p>
        <div class="text-3xl font-bold flex items-baseline gap-2">
          <span class="text-xl text-slate-400">S/.</span>
          <span id="displayTotal">0.00</span>
        </div>
      </div>

      <button type="submit" form="proformaForm" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-xl shadow-blue-500/40 transition-all duration-300 transform active:scale-95 hover:scale-105 flex items-center gap-2">
        <i class="ph-bold ph-check-circle text-xl"></i> Generar
      </button>
    </div>
  </div>

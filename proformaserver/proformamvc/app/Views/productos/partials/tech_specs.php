<div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
      <i class="ph-fill ph-list-dashes text-blue-500"></i> Ficha Técnica
    </h2>
    <div class="flex gap-2">
      <?php if (!($producto['protegido'] ?? false)): ?>
        <!-- Selector Diseño Specs -->
        <select name="layout_specs" class="bg-gray-50 border border-gray-200 text-gray-600 text-[10px] font-bold rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-200 outline-none mr-1">
            <option value="2col" <?= ($producto['layout_specs'] ?? '2col') === '2col' ? 'selected' : '' ?>>2 Cols</option>
            <option value="1col" <?= ($producto['layout_specs'] ?? '') === '1col' ? 'selected' : '' ?>>1 Col</option>
        </select>

        <button type="button" onclick="document.getElementById('modalParser').classList.remove('hidden')" class="text-gray-600 text-xs font-bold hover:bg-gray-100 px-3 py-1.5 rounded-lg transition border border-gray-200 flex items-center gap-1">
          <?php echo '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>'; ?>
          Pegar Tabla
        </button>
        <button 
          type="button" 
          onclick="agregarFila()" 
          class="text-[10px] bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 transition inline-flex items-center gap-1"
        >
          <i class="ph-bold ph-plus"></i> Añadir
        </button>
      <?php else: ?>
        <button 
            type="button" 
            onclick="mostrarToast('Producto bloqueado. Desbloquea para editar especificaciones.', 'error')" 
            class="text-[10px] bg-slate-100 text-slate-400 px-3 py-1.5 rounded-lg font-bold cursor-pointer inline-flex items-center gap-1"
        >
            <i class="ph-bold ph-lock"></i> Añadir
        </button>
      <?php endif; ?>
    </div>
  </div>

  <div id="contenedor-specs" class="space-y-2">
    <?php if (!empty($producto['specs'])): ?>
      <?php foreach ($producto['specs'] as $index => $spec): ?>
        <div class="flex gap-2 items-center group">
          <div class="relative w-1/2">
            <input
              type="text"
              name="spec_atributo[]"
              value="<?= htmlspecialchars($spec['atributo']) ?>"
              placeholder="Atributo"
              <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-green-200 outline-none <?= ($producto['protegido'] ?? false) ? 'focus:ring-2 focus:ring-slate-400 cursor-not-allowed' : '' ?>"
            >
          </div>
          <div class="relative w-1/2">
            <input
              type="text"
              name="spec_valor[]"
              value="<?= htmlspecialchars($spec['valor']) ?>"
              placeholder="Valor"
              <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-green-200 outline-none <?= ($producto['protegido'] ?? false) ? 'focus:ring-2 focus:ring-slate-400 cursor-not-allowed' : '' ?>"
            >
          </div>
          <?php if (!($producto['protegido'] ?? false)): ?>
            <button type="button" onclick="this.parentElement.remove()" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition">
              <i class="ph-bold ph-x text-xs"></i>
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Parser (Pegar Tabla) -->
<div id="modalParser" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-2">Pegar Datos Técnicos</h3>
    <p class="text-xs text-gray-500 mb-3">Copia tu tabla (Excel, PDF o Web) y pégala aquí.</p>

    <textarea id="textoPegado" class="w-full h-40 border border-gray-300 rounded-xl p-3 text-xs focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Modelo    HQ-YL1000
Motor     Honda GX160"></textarea>

    <div class="flex justify-end gap-3">
      <button type="button" onclick="document.getElementById('modalParser').classList.add('hidden')" class="text-gray-500 font-semibold text-sm hover:bg-gray-100 px-4 py-2 rounded-lg">Cancelar</button>
      <button type="button" onclick="procesarPegado()" class="bg-blue-600 text-white font-bold text-sm px-6 py-2 rounded-lg hover:bg-blue-700">Procesar</button>
    </div>
  </div>
</div>

    <!-- Sección 1: Cliente -->
    <div id="clienteSection" class="bg-white p-2 rounded-2xl shadow-md border-2 border-slate-200 hover:shadow-lg transition-all duration-300">
      <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-1.5">
        <div class="bg-blue-100 p-1.5 rounded-xl">
          <i class="ph-fill ph-user text-blue-600 text-base"></i>
        </div>
        <span>Cliente</span>
      </h2>

      <!-- Select de Cliente con Buscador Integrado -->
      <div class="relative">
        <!-- Input Hidden para el valor real -->
        <input type="hidden" name="cliente_id" id="clienteIdHidden" required>

        <!-- Campo de búsqueda visible -->
        <div class="relative">
          <input
            type="text"
            id="clienteSearchInput"
            placeholder="Seleccionar cliente..."
            autocomplete="off"
            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-2 pl-10 pr-10 text-sm text-slate-700 focus:ring-1 focus:ring-slate-300 focus:border-slate-400 outline-none font-medium transition-all duration-300 hover:border-slate-300"
            readonly
          >
          <i class="ph-bold ph-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-base pointer-events-none"></i>
          <i class="ph-bold ph-caret-down absolute right-3 top-2.5 text-slate-400 pointer-events-none text-base"></i>
        </div>

        <!-- Dropdown de resultados -->
        <div id="clienteDropdown" class="hidden absolute z-50 w-full mt-2 bg-white border-2 border-slate-300 rounded-xl shadow-xl max-h-80 overflow-hidden">
          <!-- Campo de búsqueda interno -->
          <div class="sticky top-0 bg-white border-b-2 border-slate-200 p-2">
            <div class="relative">
              <input
                type="text"
                id="clienteSearchField"
                placeholder="Buscar por nombre o DNI/RUC..."
                class="w-full bg-slate-50 border-2 border-slate-200 rounded-lg p-2 pl-9 text-xs text-slate-700 focus:ring-1 focus:ring-slate-300 focus:border-slate-400 outline-none"
              >
              <i class="ph-bold ph-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-sm pointer-events-none"></i>
            </div>
          </div>

          <!-- Lista de opciones -->
          <div id="clienteOptions" class="overflow-y-auto max-h-80">
            <?php foreach ($clientes as $cliente): ?>
              <div
                class="cliente-option px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-b-0 transition-all duration-200"
                data-id="<?= $cliente['id'] ?>"
                data-nombre="<?= htmlspecialchars($cliente['nombre']) ?>"
                data-dni="<?= htmlspecialchars($cliente['dni_ruc']) ?>"
                data-telefono="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
              >
                <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($cliente['nombre']) ?></div>
                <div class="text-[11px] text-slate-500 font-medium"><?= htmlspecialchars($cliente['dni_ruc']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

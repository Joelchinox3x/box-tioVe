    <!-- Sección 2: Items -->
    <div id="maquinariaSection" class="bg-white p-2 rounded-2xl shadow-md border-2 border-slate-200 hover:shadow-lg transition-all duration-300">
      
      <!-- Cabecera con Toggle -->
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
          <div id="iconContainer" class="bg-indigo-100 p-1.5 rounded-xl transition-colors duration-300">
            <i class="ph-fill ph-list-dashes text-indigo-600 text-base" id="sectionIcon"></i>
          </div>
          <span id="sectionTitle">Maquinaria</span>
        </h2>

        <!-- Toggle Cotizar / Folleto -->
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <button type="button" 
                    id="btnModeQuote"
                    onclick="toggleBrochureMode(false)"
                    class="px-3 py-1.5 text-[10px] font-bold rounded-lg bg-indigo-100 text-indigo-600 shadow-sm transition-all duration-300 flex items-center gap-1">
                <i class="ph-bold ph-calculator"></i> Cotizar
            </button>
            <button type="button" 
                    id="btnModeBrochure"
                    onclick="toggleBrochureMode(true)"
                    class="px-3 py-1.5 text-[10px] font-bold rounded-lg text-slate-500 hover:text-purple-600 transition-all duration-300 flex items-center gap-1">
                <i class="ph-bold ph-file-pdf"></i> Folleto
            </button>
        </div>
      </div>

      <!-- MODO COTIZAR: Selector de productos del inventario -->
      <div id="modeQuoteContainer" class="relative mb-1.5">
        <!-- Campo de búsqueda visible -->
        <div class="relative">
          <input
            type="text"
            id="productoSearchInput"
            placeholder="+ Agregar producto del inventario..."
            autocomplete="off"
            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-2 pl-10 pr-10 text-sm text-slate-700 focus:ring-1 focus:ring-slate-300 focus:border-slate-400 outline-none font-medium transition-all duration-300 hover:border-slate-300"
            readonly
          >
          <i class="ph-bold ph-package absolute left-3 top-2.5 text-slate-400 text-base pointer-events-none"></i>
          <i class="ph-bold ph-caret-down absolute right-3 top-2.5 text-slate-400 pointer-events-none text-base"></i>
        </div>

        <!-- Dropdown de resultados -->
        <div id="productoDropdown" class="hidden absolute z-50 w-full mt-2 bg-white border-2 border-slate-300 rounded-xl shadow-xl max-h-[500px] overflow-hidden">
          <!-- Campo de búsqueda interno -->
          <div class="sticky top-0 bg-white border-b-2 border-slate-200 p-2">
            <div class="relative">
              <input
                type="text"
                id="productoSearchField"
                placeholder="Buscar producto..."
                class="w-full bg-slate-50 border-2 border-slate-200 rounded-lg p-2 pl-9 text-sm text-slate-700 focus:ring-1 focus:ring-slate-300 focus:border-slate-400 outline-none"
              >
              <i class="ph-bold ph-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-sm pointer-events-none"></i>
            </div>
          </div>

          <!-- Lista de opciones -->
          <div id="productoOptions" class="overflow-y-auto max-h-80">
            <?php foreach ($productos as $producto): ?>
              <div
                class="producto-option px-4 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-b-0 transition-all duration-200"
                data-producto='<?= htmlspecialchars(json_encode([
                  'id' => $producto['id'],
                  'nombre' => $producto['nombre'],
                  'precio' => $producto['precio'],
                  'moneda' => $producto['moneda'] ?? 'PEN',
                  'sku' => $producto['sku'] ?? ''
                ]), ENT_QUOTES, 'UTF-8') ?>'
                data-moneda="<?= htmlspecialchars($producto['moneda'] ?? 'PEN') ?>"
                data-nombre="<?= htmlspecialchars($producto['nombre']) ?>"
                data-sku="<?= htmlspecialchars($producto['sku'] ?? '') ?>"
              >
                <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($producto['nombre']) ?></div>
                <div class="flex justify-between items-center text-[11px] text-slate-500 font-medium">
                  <span><?= ($producto['moneda'] ?? 'PEN') === 'USD' ? '$' : 'S/.' ?> <?= number_format($producto['precio'], 2) ?></span>
                  <?php if(!empty($producto['sku'])): ?>
                    <span class="text-[10px] text-slate-400 font-mono tracking-wider bg-slate-100 px-1.5 py-0.5 rounded">SKU: <?= htmlspecialchars($producto['sku']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- MODO COTIZAR: Container de items -->
      <div id="itemsContainer" class="space-y-2 mb-2"></div>
      
      <!-- MODO COTIZAR: Botón Manual -->
      <button type="button" id="btnManualItem" onclick="agregarItemManual()" class="w-full py-2.5 text-xs font-bold text-orange-500 bg-white border-2 border-dashed border-orange-300 rounded-xl hover:shadow-md transition-all duration-300 flex items-center justify-center gap-2 mt-2 shadow-sm active:scale-[0.99]">
        <i class="ph-bold ph-pencil-simple text-base"></i> Agregar ítem manual (Texto libre)
      </button>

      <!-- MODO FOLLETO: Grid de Resultados -->
      <div id="brochurePreviewPanel" class="hidden mt-3 bg-purple-50 rounded-xl p-3 border border-purple-200">
          <div class="flex justify-between items-center mb-2">
              <h3 class="text-xs font-bold text-slate-700" id="brochureProductName">Selecciona un producto</h3>
              <span class="text-[10px] text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full font-medium" id="brochureCountBadge">0 folletos</span>
          </div>

          <div id="brochureGridContainer" class="grid grid-cols-3 gap-3">
              <!-- Cards inyectadas por JS -->
          </div>
      </div>



    </div>

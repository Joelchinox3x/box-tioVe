<div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
  <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
    <i class="ph-fill ph-info text-blue-500"></i> Información General
  </h2>

  <div class="space-y-3">
    <!-- Nombre del Producto -->
    <div>
      <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block flex items-center gap-1">
        Nombre del Producto
        <span class="text-red-500">*</span>
      </label>
      <div class="relative">
        <input
          type="text"
          id="nombre"
          name="nombre"
          required
          value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>"
          <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-14 text-sm text-slate-700 focus:outline-none font-medium transition-all duration-300 <?= ($producto['protegido'] ?? false) ? 'focus:border-slate-500 focus:ring-4 focus:ring-slate-500/20 cursor-not-allowed' : '' ?>"
          placeholder="Ej: RetroExcavadora"
        >
        <?php if (!($producto['protegido'] ?? false)): ?>
          <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
            <dotlottie-player id="nombre-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
            <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="nombre-error"></i>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Moneda y Precio -->
    <div class="grid grid-cols-3 gap-3">
      <div class="col-span-1">
        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Moneda</label>
        <div class="relative">
          <select
            name="moneda"
            <?= ($producto['protegido'] ?? false) ? 'disabled' : '' ?>
            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 appearance-none focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all duration-300 font-bold <?= ($producto['protegido'] ?? false) ? 'cursor-not-allowed' : '' ?>"
          >
            <option value="PEN" <?= ($producto['moneda'] ?? 'PEN') === 'PEN' ? 'selected' : '' ?>>S/.</option>
            <option value="USD" <?= ($producto['moneda'] ?? 'PEN') === 'USD' ? 'selected' : '' ?>>$</option>
          </select>
          <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
        </div>
      </div>
      <div class="col-span-2">
        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block flex items-center gap-1">
          Precio
          <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input
            type="number"
            id="precio"
            step="0.01"
            name="precio"
            required
            min="0.01"
            value="<?= htmlspecialchars($producto['precio'] ?? '') ?>"
            <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-14 text-sm text-slate-700 focus:outline-none font-bold text-right transition-all duration-300 <?= ($producto['protegido'] ?? false) ? 'focus:border-slate-500 focus:ring-4 focus:ring-slate-500/20 cursor-not-allowed' : '' ?>"
            placeholder="0.00"
          >
          <?php if (!($producto['protegido'] ?? false)): ?>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
              <dotlottie-player id="precio-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
              <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="precio-error"></i>
            </div>
          <?php endif; ?>
        </div>
        <p class="text-xs text-slate-500 mt-1 ml-1" id="precio-hint">Debe ser mayor a 0</p>
      </div>
    </div>

    <!-- Modelo y SKU -->
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Modelo</label>
        <input
          type="text"
          name="modelo"
          value="<?= htmlspecialchars($producto['modelo'] ?? '') ?>"
          <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium <?= ($producto['protegido'] ?? false) ? 'focus:border-slate-500 focus:ring-4 focus:ring-slate-500/20 cursor-not-allowed' : '' ?>"
          placeholder="Ej: 320D"
        >
      </div>
      <div>
        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">SKU / Código</label>
        <input
          type="text"
          name="sku"
          value="<?= htmlspecialchars($producto['sku'] ?? '') ?>"
          <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium <?= ($producto['protegido'] ?? false) ? 'focus:border-slate-500 focus:ring-4 focus:ring-slate-500/20 cursor-not-allowed' : '' ?>"
          placeholder="Ej: PROD-001"
        >
      </div>
    </div>

    <!-- Descripción -->
    <div>
      <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Descripción</label>
      <textarea
        name="descripcion"
        rows="3"
        <?= ($producto['protegido'] ?? false) ? 'readonly' : '' ?>
        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 font-medium resize-none <?= ($producto['protegido'] ?? false) ? 'focus:border-slate-500 focus:ring-4 focus:ring-slate-500/20 cursor-not-allowed' : '' ?>"
      ><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
    </div>
  </div>
</div>

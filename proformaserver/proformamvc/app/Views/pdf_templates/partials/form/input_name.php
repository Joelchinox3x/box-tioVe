<?php
// Props: $value, $label (optional), $placeholder (optional)
$val = $value ?? '';
$lbl = $label ?? 'Nombre del Tema';
$ph = $placeholder ?? '';
?>
<div class="mb-4">
    <label class="block text-sm font-bold text-slate-700 mb-2"><?= $lbl ?></label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($val) ?>" placeholder="<?= $ph ?>" required
           class="w-full rounded-xl border-slate-300 p-3 text-slate-700 font-medium focus:border-purple-500 focus:ring-purple-500 transition-all shadow-sm">
    <p class="text-[10px] text-slate-400 mt-1">Se generará un ID único automáticamente (slug).</p>
</div>

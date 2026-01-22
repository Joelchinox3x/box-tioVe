<?php
// Props: $value (hex)
$val = $value ?? '#333333';
?>
<div class="mb-4">
    <label class="block text-sm font-bold text-slate-700 mb-2">Color de Marca</label>
    <div class="flex items-center gap-3">
        <input type="color" name="color_brand" value="<?= $val ?>" class="h-12 w-20 rounded cursor-pointer border-0 p-0 shadow-sm">
        <!-- Input de texto read-only para ver el HEX -->
        <input type="text" value="<?= $val ?>" readonly class="w-24 rounded-lg border-slate-300 text-sm p-2 text-center bg-slate-50 text-slate-600">
        <span class="text-xs text-slate-400 hidden sm:inline">Color principal para bordes y títulos.</span>
    </div>
</div>

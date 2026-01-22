<?php 
// Props: $value (selected header filename)
$selectedHeader = $value ?? 'header_simple.php';
// Mapeo legacy si es necesario, pero intentaremos usar nombres de archivo directos
?>
<div class="mb-4">
    <label class="block text-sm font-bold text-slate-700 mb-2">Diseño de Cabecera</label>
    <select name="header_php" class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none transition-all appearance-none cursor-pointer">
        <?php 
            $headersDir = __DIR__ . '/../../../pdf/master/partials/';
            // Ajustar ruta relativa: partials/form/ -> ../../../pdf/master/partials/
            // Realpath para seguridad si es necesario, pero glob funciona bien
            // Nota: __DIR__ es .../app/Views/pdf_templates/partials/form
            // header path: .../app/Views/pdf/master/partials
            $searchPath = __DIR__ . '/../../../pdf/master/partials/header_*.php';
            $files = glob($searchPath);
            
            if(empty($files)) {
                // Fallback si la ruta relativa falla (por estructura de carpetas diferente)
                 echo "<option>No headers found</option>";
            }

            foreach($files as $file):
                $filename = basename($file);
                // Format Name: header_blue.php -> Blue
                $displayName = ucfirst(str_replace(['header_', '.php'], '', $filename));
                $selected = ($selectedHeader === $filename) ? 'selected' : '';
        ?>
            <option value="<?= $filename ?>" <?= $selected ?>>
                <?= $displayName ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="text-[10px] text-slate-400 mt-1 pl-1">Selecciona la estructura PHP del encabezado</p>
</div>

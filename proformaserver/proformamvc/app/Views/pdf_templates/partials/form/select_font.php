<?php
// app/Views/pdf_templates/partials/form/select_font.php
$fonts = $availableFonts ?? [];
$currentFont = $template['title_font'] ?? 'sans-serif';
$currentSize = $template['title_size'] ?? 31;
$currentBold = isset($template['title_bold']) ? $template['title_bold'] : 1;

// Prepare font name for display
$currentFontName = $fonts[$currentFont] ?? 'Sans-Serif (Default)';
?>
<style>
    /* Hide native spin buttons */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    /* Dynamic Font Faces for Preview */
    <?php foreach($fonts as $key => $name): ?>
    @font-face {
        font-family: 'Preview-<?= $key ?>';
        src: url('/assets/fonts/<?= $key ?>-Regular.ttf') format('truetype'),
             url('/assets/fonts/<?= ucfirst($key) ?>-Regular.ttf') format('truetype'); /* Fallback case */
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }
    <?php endforeach; ?>
</style>

<div class="mb-4" id="section_font_selector">
    <label class="block text-sm font-bold text-slate-700 mb-2">Tipografía Título Principal</label>
    
    <div class="flex flex-col gap-4">
        <!-- 1. Custom Font Selector with Preview -->
        <div class="w-full relative" id="fontSelectorContainer">
            <input type="hidden" name="title_font" id="input_title_font" value="<?= $currentFont ?>">
            
            <!-- Trigger Button -->
            <button type="button" onclick="toggleFontDropdown()" 
                    class="w-full flex items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-left focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-500/10 transition-all group">
                <span id="display_font_name" class="text-sm font-medium text-slate-700 truncate">
                    <?= $currentFontName ?>
                </span>
                <i class="ph-bold ph-caret-down text-slate-400 group-hover:text-purple-500 transition-colors"></i>
            </button>

            <!-- Custom Dropdown List -->
            <div id="fontDropdownList" class="hidden absolute z-30 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 max-h-64 overflow-y-auto transform origin-top transition-all">
                <!-- Option: Default -->
                <div onclick="selectFont('sans-serif', 'Sans-Serif (Default)')" 
                     class="px-4 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between group transition-colors">
                    <span class="text-sm text-slate-600 font-sans">Sans-Serif (Default)</span>
                    <?php if($currentFont === 'sans-serif'): ?>
                        <i class="ph-bold ph-check text-purple-600"></i>
                    <?php endif; ?>
                </div>

                <!-- Dynamic Options -->
                <?php foreach($fonts as $key => $name): ?>
                <div onclick="selectFont('<?= $key ?>', '<?= $name ?>')" 
                     class="px-4 py-2 hover:bg-purple-50 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between group transition-colors">
                    <span class="text-lg text-slate-700 group-hover:text-purple-700 transition-colors" 
                          style="font-family: 'Preview-<?= $key ?>', sans-serif;">
                        <?= $name ?>
                    </span>
                    <?php if($currentFont === $key): ?>
                        <i class="ph-bold ph-check text-purple-600"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <p class="mt-1 text-xs text-slate-400 hidden sm:block">Vista previa con fuentes instaladas</p>
        </div>

        <!-- 2. Stepper Tamaño de Fuente (Fila Exclusiva) -->
        <div class="w-full">
            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tamaño (px)</label>
            <div class="flex items-center gap-2">
                <!-- Boton Menos -->
                <button type="button" onclick="stepSize(-1)" class="w-14 h-12 flex items-center justify-center rounded-xl border border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100 active:bg-slate-200 transition-colors">
                    <i class="ph-bold ph-minus text-lg"></i>
                </button>

                <!-- Input Valor -->
                <div class="relative flex-1">
                    <input type="number" id="input_title_size" name="title_size" value="<?= $currentSize ?>" 
                           class="w-full h-12 rounded-xl border border-slate-300 px-2 text-lg font-bold text-center text-slate-700 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20" 
                           placeholder="30">
                </div>
            
                <!-- Boton Mas -->
                <button type="button" onclick="stepSize(1)" class="w-14 h-12 flex items-center justify-center rounded-xl bg-purple-100 text-purple-700 border border-purple-200 hover:bg-purple-200 active:bg-purple-300 transition-colors">
                    <i class="ph-bold ph-plus text-lg"></i>
                </button>
            </div>
        </div>

        <!-- 3. Checkbox Bold (Fila Exclusiva) -->
        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 h-12 justify-start cursor-pointer hover:bg-slate-100 transition-colors" onclick="document.getElementById('title_bold').click()">
            <input type="checkbox" name="title_bold" id="title_bold" value="1" <?= $currentBold ? 'checked' : '' ?> class="w-5 h-5 rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer pointer-events-auto">
            <label for="title_bold" class="text-sm font-bold text-slate-700 select-none cursor-pointer flex-1 whitespace-nowrap">Título en Negrita (Bold)</label>
        </div>

        <!-- 4. Color del Título (Fila Exclusiva) -->
        <div class="w-full">
            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Color del Texto</label>
            <div class="flex items-center gap-3 p-2 rounded-xl border border-slate-300 bg-white hover:border-purple-300 transition-colors cursor-pointer" onclick="document.getElementById('title_color').click()">
                <div class="relative w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shadow-sm">
                     <input type="color" name="title_color" id="title_color" value="<?= $template['title_color'] ?? '#000000' ?>" 
                       class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer p-0 border-0">
                </div>
               
                <div class="flex-1">
                    <span class="block text-sm font-bold text-slate-700">Seleccionar Color</span>
                    <span class="block text-xs font-normal text-slate-400">Clic para cambiar</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logic for Custom Font Selector
function toggleFontDropdown() {
    const list = document.getElementById('fontDropdownList');
    list.classList.toggle('hidden');
    
    // Auto-scroll to title/label if opened
    if(!list.classList.contains('hidden')) {
        setTimeout(() => {
            const container = document.getElementById('section_font_selector');
            if(container) {
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }
}

function selectFont(key, name) {
    // Update Hidden Input
    document.getElementById('input_title_font').value = key;
    // Update Display Text
    const display = document.getElementById('display_font_name');
    display.textContent = name;
    
    // Auto-update font of the display if it's not sans-serif
    if(key !== 'sans-serif') {
        display.style.fontFamily = `'Preview-${key}', sans-serif`;
    } else {
        display.style.fontFamily = 'inherit';
    }

    // Close Dropdown
    document.getElementById('fontDropdownList').classList.add('hidden');
    
    // Update ticks visually (simple re-render or remove all checks)
    const checks = document.getElementById('fontDropdownList').querySelectorAll('.ph-check');
    checks.forEach(el => el.remove());
    
    // We could add the check back dynamically but simple is fine for now
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const container = document.getElementById('fontSelectorContainer');
    const list = document.getElementById('fontDropdownList');
    if (!container.contains(event.target) && !list.classList.contains('hidden')) {
        list.classList.add('hidden');
    }
});

// Existing logic
function stepSize(amount) {
    const input = document.getElementById('input_title_size');
    let val = parseInt(input.value) || 0;
    val += amount;
    if(val < 1) val = 1;
    input.value = val;
}
</script>

<?php 
// Props: $value (selected footer filename)
$currentFooter = $value ?? '';
$usedFooters = $usedFooters ?? []; 

// Layout logic
$footersDir = __DIR__ . '/../../../pdf/footer/';
$files = array_merge(
    glob($footersDir . '*.png'), 
    glob($footersDir . '*.jpg'),
    glob($footersDir . '*.webp'),
    glob($footersDir . '*.jpeg')
);

// Sort
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Prepare Options
$optionsHtml = '<option value="">-- Sin Footer --</option>';
$dropdownItemsHtml = '';
$currentDisplay = 'Seleccionar Footer';
$foundCurrent = false;

// Generate Options & Dropdown Items
foreach($files as $file) {
    $filename = basename($file);
    
    // Parse Display Name 
    $display = $filename;
    if(preg_match('/^footer_(.+)_(\d+)\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
         // Old format: footer_slug_123.png -> Slug 123
         $display = ucwords(str_replace('-', ' ', $matches[1])) . ' ' . $matches[2];
    } elseif(preg_match('/^footer_([a-z0-9]{3})\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
         // New format: footer_xyz.png -> Footer XYZ
         $display = 'Footer ' . strtoupper($matches[1]);
    } else {
         // Fallback
         $clean = str_replace(['footer_', 'footer'], '', $filename);
         $display = ucwords(str_replace(['.png','.jpg','.jpeg','.webp','_','-'], ['','','','',' ',' '], $clean));
    }
    
    $isSelected = ($currentFooter === $filename);
    if($isSelected) {
        $currentDisplay = $display;
        $foundCurrent = true;
    }
    $src = url('/pdf-templates/footer-image/' . $filename);
    
    // 1. Build Native Option
    $selectedAttr = $isSelected ? 'selected' : '';
    $optionsHtml .= "<option value=\"$filename\" data-src=\"$src\" $selectedAttr>$display</option>";

    // 2. Build Custom Dropdown Item
    $activeClass = $isSelected ? 'bg-purple-50' : 'hover:bg-slate-50';
    $textClass = $isSelected ? 'text-purple-700' : 'text-slate-700';
    $checkIcon = $isSelected ? '<i class="ph-bold ph-check text-purple-600"></i>' : '';

    $dropdownItemsHtml .= "
    <div onclick=\"selectCustomFooter('$filename', '" . addslashes($display) . "')\" 
         class='cursor-pointer px-4 py-3 flex items-center justify-between transition-colors border-b border-slate-50 last:border-0 group $activeClass'>
        <span class='text-sm font-bold transition-colors group-hover:text-purple-700 $textClass truncate'>$display</span>
        $checkIcon
    </div>";
}
?>

<div class="mb-6">
    <label class="block text-sm font-bold text-slate-700 mb-2 border-b border-slate-100">Imagen del Pie de Página (Banner)</label>
    
    <div class="flex items-start gap-2 mb-4 relative z-20" id="footerSelectorContainer">
        <!-- Hidden Native Select (Kept for compatibility) -->
        <select name="footer_select" id="footer_select" onchange="updateFooterPreviewFromSelect(this)" class="hidden">
            <?= $optionsHtml ?>
        </select>

        <!-- Custom Dropdown Trigger -->
        <div class="relative flex-1 min-w-0">
            <button type="button" onclick="toggleFooterDropdown()" id="toggleFooterDropdownBtn"
                class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 bg-white text-left shadow-sm hover:border-purple-300 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all flex items-center gap-3">
                <span id="display_footer_name" class="block truncate font-bold text-slate-700 flex-1">
                    <?= $foundCurrent ? $currentDisplay : '-- Sin Footer --' ?>
                </span>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                     <i class="ph-bold ph-caret-down"></i>
                </div>
            </button>

            <!-- Dropdown List -->
            <div id="footerDropdownList" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-slate-100 z-30 max-h-60 overflow-y-auto custom-scrollbar">
                <div onclick="selectCustomFooter('', '-- Sin Footer --')" class="cursor-pointer px-4 py-3 flex items-center gap-3 hover:bg-slate-50 border-b border-slate-50 text-slate-500 group">
                    <i class="ph-bold ph-prohibit text-lg"></i>
                    <span class="text-sm font-bold transition-colors group-hover:text-purple-700">Sin Footer</span>
                </div>
                <?= $dropdownItemsHtml ?>
            </div>
        </div>
        
        <!-- Rename Button (For current selection) -->
        <button type="button" onclick="openRenameFooterModal()" class="shrink-0 w-[50px] h-[50px] flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm group" title="Renombrar Footer Actual">
            <i class="ph-bold ph-pencil-simple text-xl group-hover:scale-110 transition-transform"></i>
        </button>
        
        <!-- Gallery Button -->
        <button type="button" onclick="openFooterGallery()" class="shrink-0 w-[50px] h-[50px] flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-purple-50 hover:text-purple-600 hover:border-purple-200 transition-all shadow-sm" title="Ver Galería">
            <i class="ph-bold ph-squares-four text-xl"></i>
        </button>
    </div>

    <!-- Preview Container (Full Width) -->
    <div id="footer_preview_container" class="mt-4 relative w-full rounded-xl border border-slate-200 shadow-sm bg-slate-100 overflow-hidden group" style="aspect-ratio: 2000/210;">
        <div id="footer_preview_content" class="<?= ($currentFooter) ? '' : 'hidden' ?> w-full h-full relative">
            <div onclick="openFooterZoom()" class="cursor-zoom-in w-full h-full relative group">
                <img id="footer_preview_img" src="<?= $currentFooter ? url('/pdf-templates/footer-image/'.$currentFooter) : '' ?>" class="w-full h-full object-contain">
                 <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/10">
                    <i class="ph-bold ph-eye text-white text-3xl drop-shadow-md"></i>
                </div>
            </div>
             <!-- Clear Button inside preview -->
            <button type="button" onclick="clearFooterSelection()" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors z-20" title="Quitar footer">
                <i class="ph-bold ph-trash text-xs"></i>
            </button>
        </div>
        
        <!-- Empty State -->
        <div id="footer_preview_empty" class="<?= ($currentFooter) ? 'hidden' : '' ?> flex items-center justify-center w-full h-full text-slate-300 gap-2">
            <i class="ph-duotone ph-image text-2xl"></i>
            <span class="text-xs font-medium">Sin pie de página</span>
        </div>
    </div>
</div>

<script>
    // ...
    function updateFooterPreviewFromSelect(select) {
        const selectedOption = select.options[select.selectedIndex];
        const src = selectedOption.getAttribute('data-src');
        const value = select.value;
        const img = document.getElementById('footer_preview_img');
        const content = document.getElementById('footer_preview_content');
        const empty = document.getElementById('footer_preview_empty');

        if(value && src) {
            img.src = src;
            content.classList.remove('hidden');
            empty.classList.add('hidden');
        } else {
            content.classList.add('hidden');
            empty.classList.remove('hidden');
        }
        
        // Ensure delete flag is 0 if we selected something, 1 if we cleared it
        const deleteInput = document.getElementById('delete_footer');
        if(deleteInput) deleteInput.value = (value) ? '0' : '1';
    }
    
    function clearFooterSelection() {
        selectCustomFooter('', '-- Sin Footer --');
        // Explicitly set delete flag
        const deleteInput = document.getElementById('delete_footer');
        if(deleteInput) deleteInput.value = '1';
    }
    // ...
</script>

<!-- Footer Gallery Modal -->
<div id="footer_gallery_modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeFooterGallery()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0" onclick="closeFooterGallery()">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200" onclick="event.stopPropagation()">
                
                <!-- Header -->
                <div class="bg-white px-4 py-4 border-b border-slate-100 sticky top-0 z-10">
                    <!-- Row 1: Title & Close -->
                    <div class="flex items-center justify-between mb-3">
                         <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph-bold ph-image text-purple-600"></i>
                            Galería de Footers
                        </h3>
                        
                        <button type="button" onclick="closeFooterGallery()" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors" title="Cerrar Galería">
                            <i class="ph-bold ph-x text-xl"></i>
                        </button>
                    </div>

                    <!-- Row 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <!-- Filter Pill -->
                        <div class="flex bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                            <button type="button" onclick="filterFooterGallery('all')" id="footer_filter_btn_all" class="px-3 py-1.5 text-xs font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all">Todos</button>
                            <button type="button" onclick="filterFooterGallery('used')" id="footer_filter_btn_used" class="px-3 py-1.5 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all">En uso</button>
                            <button type="button" onclick="filterFooterGallery('unused')" id="footer_filter_btn_unused" class="px-3 py-1.5 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all">No usados</button>
                        </div>

                         <!-- Botón Upload -->
                         <div class="relative">
                            <input type="file" id="modal_footer_upload" accept="image/*" class="hidden" onchange="uploadFooterAsync(this)">
                            <label for="modal_footer_upload" class="cursor-pointer px-4 py-2 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 border border-purple-100 shadow-sm">
                                <i class="ph-bold ph-upload-simple"></i> Subir
                            </label>
                         </div>
                    </div>
                </div>

                <!-- Grid Content -->
                <div class="p-6 bg-slate-50 max-h-[60vh] overflow-y-auto">
                    <?php if(empty($files)): ?>
                        <div class="text-center py-10 text-slate-400">
                            <i class="ph-duotone ph-image-broken text-4xl mb-2"></i>
                            <p>No hay imágenes disponibles.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-4"> 
                            <!-- Single Column for wide footers looks better -->
                            <?php foreach($files as $file): 
                                $filename = basename($file);
                                $display = $filename;
                                if(preg_match('/^footer_(.+)_(\d+)\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
                                     $display = ucwords(str_replace('-', ' ', $matches[1])) . ' ' . $matches[2];
                                } elseif(preg_match('/^footer_([a-z0-9]{3})\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
                                     $display = 'Footer ' . strtoupper($matches[1]);
                                } else {
                                     $clean = str_replace(['footer_', 'footer'], '', $filename);
                                     $display = ucwords(str_replace(['.png','.jpg','.jpeg','.webp','_','-'], ['','','','',' ',' '], $clean));
                                }
                                
                                $src = url('/pdf-templates/footer-image/' . $filename);
                                $activeClass = ($currentFooter === $filename) ? 'ring-2 ring-purple-500 ring-offset-2' : 'hover:ring-2 hover:ring-slate-300 hover:ring-offset-2';
                                $isUsed = in_array($filename, $usedFooters);
                            ?>
                            <div class="relative group footer-gallery-item" data-used="<?= $isUsed ? 'true' : 'false' ?>" data-filename="<?= $filename ?>">
                                <button type="button" onclick="selectFooterFromGallery('<?= $filename ?>')" class="w-full bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all text-left <?= $activeClass ?>">
                                    <div class="w-full bg-slate-50 relative" style="aspect-ratio: 2000/210;">
                                        <img src="<?= $src ?>" loading="lazy" class="w-full h-full object-contain">
                                        
                                        <!-- Label Overlay -->
                                        <div class="absolute bottom-2 left-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-[10px] font-bold text-slate-700 shadow-sm border border-slate-100">
                                            <?= $display ?>
                                        </div>

                                        <?php if($isUsed): ?>
                                            <div class="absolute top-2 left-2 bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded text-[9px] font-bold border border-purple-200 shadow-sm z-10">
                                                En uso
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
                                    </div>
                                    
                                    <?php if($currentFooter === $filename): ?>
                                        <div class="absolute top-2 right-2 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center shadow-sm z-20">
                                            <i class="ph-bold ph-check text-xs"></i>
                                        </div>
                                    <?php endif; ?>
                                </button>
                                
                                <!-- Delete Button -->
                                <button type="button" onclick="openDeleteFooterModal(this.closest('.footer-gallery-item'), '<?= $filename ?>', '<?= addslashes($display) ?>', <?= $isUsed ? 'true' : 'false' ?>)" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 hover:scale-110 z-20" title="Eliminar">
                                    <i class="ph-bold ph-trash text-[10px]"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div id="renameFooterModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 transform scale-95 transition-transform duration-300">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4 text-blue-600">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="ph-bold ph-pencil-simple text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Renombrar Footer</h3>
                    <p class="text-xs text-slate-500">Cambiar nombre del archivo</p>
                </div>
            </div>
            
            <p class="text-sm text-slate-600 mb-4">
                Ingresa el nuevo nombre para este footer. Se actualizará en todos los temas que lo usen.
            </p>
            
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 mb-1">Nombre Actual</label>
                <div id="renameFooterOldNameDisplay" class="text-sm font-mono text-slate-700 bg-slate-100 px-3 py-2 rounded-lg mb-3 break-all">...</div>
                
                <label class="block text-xs font-bold text-slate-500 mb-1">Nuevo Nombre</label>
                <input type="text" id="renameFooterNewNameInput" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" placeholder="Ej: footer-verano">
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeRenameFooterModal()" class="flex-1 px-4 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="renameFooterConfirm()" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors shadow-lg shadow-blue-200">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Footer) -->
<div id="deleteFooterModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden transform scale-95 transition-all duration-300">
        <!-- Header -->
        <div id="delFooterModalHeader" class="bg-gradient-to-r from-red-500 to-rose-600 p-5 relative overflow-hidden transition-colors duration-300">
             <!-- Decoracion -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full -ml-12 -mb-12"></div>
            </div>
            <div class="flex items-center gap-3 relative z-10">
                <div id="delFooterModalIconWrapper" class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm transition-all">
                    <i class="ph-bold ph-trash text-white text-2xl" id="delFooterModalIcon"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg" id="delFooterModalTitle">Eliminar Footer</h3>
                    <p class="text-white/90 text-xs" id="delFooterModalSub">Esta acción es irreversible</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
             <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <p class="text-slate-600 text-sm mb-2" id="delFooterModalBodyMsg">¿Eliminar este archivo permanentemente?</p>
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-image text-purple-600"></i>
                    <p class="font-bold text-slate-800 text-sm truncate uppercase" id="deleteFooterName">Name</p>
                </div>
            </div>

            <!-- Actions Standard -->
            <div id="delFooterModalActions" class="flex gap-3">
                <button type="button" onclick="closeDeleteFooterModal()" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="deleteFooterConfirm()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Sí, eliminar
                </button>
            </div>
            
             <!-- Actions Blocked -->
            <div id="delFooterModalActionsBlocked" class="hidden flex gap-3">
                <button type="button" onclick="closeDeleteFooterModal()" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    // --- DROPDOWN LOGIC ---
    function toggleFooterDropdown() {
        const list = document.getElementById('footerDropdownList');
        const btn = document.getElementById('toggleFooterDropdownBtn');
        
        // Offset Logic for Header
        if(list.classList.contains('hidden')) {
             const headerOffset = 180; 
             const rect = btn.getBoundingClientRect();
             const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
             const targetY = rect.top + scrollTop - headerOffset;
             
             // Smooth Scroll
             window.scrollTo({ top: targetY, behavior: 'smooth' });
        }
        
        list.classList.toggle('hidden');
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('footerSelectorContainer');
        const list = document.getElementById('footerDropdownList');
        if (container && !container.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    function selectCustomFooter(value, displayName) {
        // 1. Close immediately
        const list = document.getElementById('footerDropdownList');
        if(list) list.classList.add('hidden');

        // 2. Update Hidden Select
        const select = document.getElementById('footer_select');
        select.value = value;
        
        // 3. Update Trigger Text
        document.getElementById('display_footer_name').textContent = displayName;
        
        // 4. Update UI List Styles
        const items = list.querySelectorAll('div[onclick]');
        items.forEach(item => {
             // Reset
             item.classList.remove('bg-purple-50');
             item.classList.add('hover:bg-slate-50');
             const span = item.querySelector('span');
             if(span) span.classList.remove('text-purple-700');
             const check = item.querySelector('.ph-check');
             if(check) check.remove();
             
             // Check Match
             if(item.getAttribute('onclick').includes(`('${value}'`)) {
                  item.classList.add('bg-purple-50');
                  item.classList.remove('hover:bg-slate-50');
                  if(span) span.classList.add('text-purple-700');
                  item.insertAdjacentHTML('beforeend', '<i class="ph-bold ph-check text-purple-600"></i>');
             }
        });
        
        // 5. Update Preview
        updateFooterPreviewFromSelect(select);
    }
    
    function updateFooterPreviewFromSelect(select) {
        const selectedOption = select.options[select.selectedIndex];
        const src = selectedOption.getAttribute('data-src');
        const value = select.value;
        const img = document.getElementById('footer_preview_img');
        const content = document.getElementById('footer_preview_content');
        const empty = document.getElementById('footer_preview_empty');

        if(value && src) {
            img.src = src;
            content.classList.remove('hidden');
            empty.classList.add('hidden');
        } else {
            content.classList.add('hidden');
            empty.classList.remove('hidden');
        }
        
        // Ensure delete flag is 0 if we selected something, 1 if we cleared it
        const deleteInput = document.getElementById('delete_footer');
        if(deleteInput) deleteInput.value = (value) ? '0' : '1';
    }
    
    function clearFooterSelection() {
        selectCustomFooter('', '-- Sin Footer --');
        // Explicitly set delete flag
        const deleteInput = document.getElementById('delete_footer');
        if(deleteInput) deleteInput.value = '1';
    }
    
    function openFooterZoom() {
        const img = document.getElementById('footer_preview_img');
        if(img.src) {
             // Use global modal if available
             if(typeof openImageModal === 'function') {
                 const select = document.getElementById('footer_select');
                 const name = select.options[select.selectedIndex].text;
                 openImageModal(img.src, name);
             }
        }
    }

    // --- GALLERY MODAL LOGIC ---
    function openFooterGallery() {
        document.getElementById('footer_gallery_modal').classList.remove('hidden');
    }
    
    function closeFooterGallery() {
        document.getElementById('footer_gallery_modal').classList.add('hidden');
    }
    
    function selectFooterFromGallery(filename) {
        // Find Display Name from hidden select options
        const select = document.getElementById('footer_select');
        let display = filename;
        for(let i=0; i<select.options.length; i++) {
            if(select.options[i].value === filename) {
                display = select.options[i].text;
                break;
            }
        }
        selectCustomFooter(filename, display);
        closeFooterGallery();
    }
    
    function filterFooterGallery(mode) {
        const items = document.querySelectorAll('.footer-gallery-item');
        const btnAll = document.getElementById('footer_filter_btn_all');
        const btnUsed = document.getElementById('footer_filter_btn_used');
        const btnUnused = document.getElementById('footer_filter_btn_unused');
        
        [btnAll, btnUsed, btnUnused].forEach(b => b.className = "px-3 py-1.5 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all");
        
        if (mode === 'all') btnAll.className = "px-3 py-1.5 text-xs font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all";
        else if (mode === 'used') btnUsed.className = "px-3 py-1.5 text-xs font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all";
        else if (mode === 'unused') btnUnused.className = "px-3 py-1.5 text-xs font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all";

        items.forEach(item => {
            const isUsed = item.getAttribute('data-used') === 'true';
            if (mode === 'all') item.classList.remove('hidden');
            else if (mode === 'used') isUsed ? item.classList.remove('hidden') : item.classList.add('hidden');
            else if (mode === 'unused') !isUsed ? item.classList.remove('hidden') : item.classList.add('hidden');
        });
    }

    // --- UPLOAD LOGIC ---
    async function uploadFooterAsync(input) {
        if (!input.files || !input.files[0]) return;

        const formData = new FormData();
        formData.append('footer_file', input.files[0]);
        
        try {
            sessionStorage.setItem('footer_scroll_pos', window.scrollY);
            const response = await fetch('/pdf-templates/upload-footer', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if(data.success) {
                sessionStorage.setItem('footer_auto_select', data.filename);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch(e) {
            console.error(e);
            alert('Error al subir');
        }
    }

    // --- RENAME LOGIC ---
    function openRenameFooterModal() {
        const select = document.getElementById('footer_select');
        const filename = select.value;
        
        if(!filename) {
            alert('Selecciona un footer para renombrar.');
            return;
        }
        
        document.getElementById('renameFooterOldNameDisplay').textContent = filename;
        document.getElementById('renameFooterNewNameInput').value = '';
        
        const modal = document.getElementById('renameFooterModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
             modal.classList.remove('opacity-0');
             modal.querySelector('div').classList.remove('scale-95');
             modal.querySelector('div').classList.add('scale-100');
             document.getElementById('renameFooterNewNameInput').focus();
        }, 10);
    }

    function closeRenameFooterModal() {
        const modal = document.getElementById('renameFooterModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    async function renameFooterConfirm() {
        const select = document.getElementById('footer_select');
        const oldName = select.value;
        const newName = document.getElementById('renameFooterNewNameInput').value;
        
        if(!newName.trim()) return;
        
        try {
            sessionStorage.setItem('footer_scroll_pos', window.scrollY);
            const response = await fetch('/pdf-templates/rename-footer', {
                method: 'POST',
                body: JSON.stringify({ old_name: oldName, new_name: newName }),
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            
            if(data.success) {
                 sessionStorage.setItem('footer_auto_select', data.new_name);
                 window.location.reload();
            } else {
                 alert('Error: ' + data.message);
            }
        } catch(e) {
            console.error(e);
            alert('Error al renombrar');
        }
    }

    // --- DELETE LOGIC ---
    let footerToDel = null;
    let footerToDelElement = null;
    function openDeleteFooterModal(element, filename, display, isUsed) {
        footerToDel = filename;
        footerToDelElement = element;
        document.getElementById('deleteFooterName').textContent = display;
        
        // UI Elements
        const header = document.getElementById('delFooterModalHeader');
        const icon = document.getElementById('delFooterModalIcon');
        const title = document.getElementById('delFooterModalTitle');
        const sub = document.getElementById('delFooterModalSub');
        const bodyMsg = document.getElementById('delFooterModalBodyMsg');
        const actions = document.getElementById('delFooterModalActions');
        const actionsBlocked = document.getElementById('delFooterModalActionsBlocked');

        if(isUsed) {
             header.classList.remove('from-red-500', 'to-rose-600');
             header.classList.add('from-orange-400', 'to-amber-500');
             icon.classList.remove('ph-trash'); icon.classList.add('ph-warning-circle');
             title.textContent = 'Archivo en Uso';
             sub.textContent = 'No se puede eliminar';
             bodyMsg.textContent = 'Este footer está asignado a uno o más temas.';
             actions.classList.add('hidden');
             actionsBlocked.classList.remove('hidden');
        } else {
             header.classList.remove('from-orange-400', 'to-amber-500');
             header.classList.add('from-red-500', 'to-rose-600');
             icon.classList.remove('ph-warning-circle'); icon.classList.add('ph-trash');
             title.textContent = 'Eliminar Footer';
             sub.textContent = 'Esta acción es irreversible';
             bodyMsg.textContent = '¿Eliminar este archivo permanentemente?';
             actions.classList.remove('hidden');
             actionsBlocked.classList.add('hidden');
        }

        const modal = document.getElementById('deleteFooterModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
             modal.classList.remove('opacity-0');
             modal.querySelector('div').classList.remove('scale-95');
             modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }
    
    function closeDeleteFooterModal() {
         const modal = document.getElementById('deleteFooterModal');
         modal.classList.add('opacity-0');
         modal.querySelector('div').classList.remove('scale-100');
         modal.querySelector('div').classList.add('scale-95');
         setTimeout(() => modal.classList.add('hidden'), 300);
         footerToDel = null;
    }

    async function deleteFooterConfirm() {
        if(!footerToDel) return;
        try {
            const response = await fetch('/pdf-templates/delete-footer', {
                method: 'POST',
                body: JSON.stringify({ filename: footerToDel }),
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            
            if(data.success) {
                if(footerToDelElement) footerToDelElement.remove();
                
                // Update Select and Custom UI
                const select = document.getElementById('footer_select');
                if(select) {
                    const option = select.querySelector(`option[value="${footerToDel}"]`);
                    if(option) option.remove();

                    // Update Custom Dropdown List
                    const list = document.getElementById('footerDropdownList');
                    if(list) {
                        const items = list.querySelectorAll('div[onclick]');
                        items.forEach(item => {
                            if(item.getAttribute('onclick').includes(`'${footerToDel}'`)) {
                                item.remove();
                            }
                        });
                    }

                    if(select.value === footerToDel) {
                        select.value = '';
                        // Update trigger text
                        const displayLabel = document.getElementById('display_footer_name');
                        if(displayLabel) displayLabel.textContent = '-- Sin Footer --';
                        updateFooterPreviewFromSelect(select);
                    }
                }

                if(typeof mostrarToast === 'function') mostrarToast('Archivo eliminado', 'success');
                closeDeleteFooterModal();
            } else {
                if(typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error');
                else alert('Error: ' + data.message);
            }
        } catch(e) {
             console.error(e);
             if(typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error');
             else alert('Error al eliminar');
        }
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        // Auto Select Check FIRST (Prioritize showing this)
        const autoSelect = sessionStorage.getItem('footer_auto_select');
        
        if(autoSelect) {
            const select = document.getElementById('footer_select');
            // Try to find
            if(select.querySelector(`option[value="${autoSelect}"]`)) {
                 // Trigger Selection logic
                 // Need display name
                 let display = autoSelect;
                 for(let i=0; i<select.options.length; i++) {
                     if(select.options[i].value === autoSelect) {
                         display = select.options[i].text;
                         break;
                     }
                 }
                 selectCustomFooter(autoSelect, display);
            }
            sessionStorage.removeItem('footer_auto_select');
            
            // FORCE SCROLL TO FOOTER (Override generic scroll)
             setTimeout(() => {
                const container = document.getElementById('footerSelectorContainer');
                if(container) {
                     const headerOffset = 180; 
                     const elementPosition = container.getBoundingClientRect().top;
                     const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                     window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            }, 300); // Slight delay to ensure render
            
        } else {
            // Restore Generic Scroll if NO auto-select action
            const scrollPos = sessionStorage.getItem('footer_scroll_pos');
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('footer_scroll_pos');
            }
        }
    });
        

</script>
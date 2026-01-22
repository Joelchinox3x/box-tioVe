<?php 
// Props: $value (selected background filename - not strictly used as edit.php uses $template['fondo_img'])
$currentBackground = $value ?? '';
$usedBackgrounds = $usedBackgrounds ?? []; 

// Logic to find images
$fondoDir = __DIR__ . '/../../../pdf/fondo/';
$files = array_merge(
    glob($fondoDir . '*.png'), 
    glob($fondoDir . '*.jpg'),
    glob($fondoDir . '*.webp'),
    glob($fondoDir . '*.jpeg')
);

// Sort by date (most recent first)
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Find current image URL safely
$currentSrcHtml = '';
?>

<div class="mb-6">
    <label class="block text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Imagen de Fondo (A4)</label>
    
    <!-- 1. Select (Middle) with Gallery Button -->
    <!-- 1. Custom Select + Buttons Row -->
    <?php 
    // Prepare Options & Current State
    $optionsHtml = '';
    $dropdownItemsHtml = '';
    $currentDisplayName = '-- Sin Fondo --';

    // Option: Empty/None
    $optionsHtml .= '<option value="">-- Sin Fondo --</option>';

    foreach($files as $file):
        $filename = basename($file);
        
        // Parse Display Name 
        $display = $filename;
        if(preg_match('/^fondo_(.+)_(\d+)\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
             $display = ucwords(str_replace('-', ' ', $matches[1])) . ' ' . $matches[2];
        } elseif(preg_match('/^fondo_([a-z0-9]{3})\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
             $display = 'Fondo ' . strtoupper($matches[1]);
        } else {
             $clean = str_replace(['fondo_', 'fondo'], '', $filename);
             $display = ucwords(str_replace(['.png','.jpg','.jpeg','.webp','_','-'], ['','','','',' ',' '], $clean));
        }
        
        $selected = ($currentBackground === $filename) ? 'selected' : '';
        $src = url('/pdf-templates/image/' . $filename);
        
        if($selected) {
            $currentSrcHtml = $src;
            $currentDisplayName = $display;
        }

        // 1. Hidden Option
        $optionsHtml .= '<option value="'.$filename.'" data-src="'.$src.'" '.$selected.'>'.$display.'</option>';

        // 2. Custom Dropdown Item
        $checkIcon = ($currentBackground === $filename) ? '<i class="ph-bold ph-check text-purple-600"></i>' : '';
        $activeClass = ($currentBackground === $filename) ? 'bg-purple-50' : 'hover:bg-slate-50';
        
        $dropdownItemsHtml .= '
        <div onclick="selectCustomBackground(\''.$filename.'\', \''.addslashes($display).'\')" 
             class="px-4 py-3 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between group transition-colors '.$activeClass.'">
            <span class="text-sm text-slate-700 font-bold group-hover:text-purple-700 transition-colors truncate block flex-1 pr-2">
                '.$display.'
            </span>
            '.$checkIcon.'
        </div>';
    endforeach; 
    ?>

    <div class="flex items-start gap-2 mb-4 relative z-20" id="bgSelectorContainer">
        <!-- Hidden Native Select (Kept for compatibility) -->
        <select name="fondo_select" id="fondo_select" onchange="updateBackgroundPreviewFromSelect(this)" class="hidden">
            <?= $optionsHtml ?>
        </select>

        <!-- Custom Selector (Replaces visible Select) -->
        <div class="relative flex-1 min-w-0">
             <!-- Trigger Button -->
             <button type="button" onclick="toggleBgDropdown()" 
                    class="w-full flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm hover:border-purple-300 focus:border-purple-500 focus:outline-none focus:ring-4 focus:ring-purple-500/10 transition-all group">
                <span id="display_bg_name" class="text-sm font-bold text-slate-700 truncate block flex-1 pr-2">
                    <?= !empty($currentBackground) ? $currentDisplayName : '-- Sin Fondo --' ?>
                </span>
                <i class="ph-bold ph-caret-down text-slate-400 group-hover:text-purple-500 transition-colors"></i>
            </button>

            <!-- Dropdown List -->
            <div id="bgDropdownList" class="hidden absolute left-0 right-0 z-30 mt-2 bg-white rounded-xl shadow-xl border border-slate-100 max-h-64 overflow-y-auto transform origin-top transition-all">
                <div onclick="selectCustomBackground('', '-- Sin Fondo --')" class="cursor-pointer px-4 py-3 flex items-center gap-3 hover:bg-slate-50 border-b border-slate-50 text-slate-500 group">
                    <i class="ph-bold ph-prohibit text-lg"></i>
                    <span class="text-sm font-bold transition-colors group-hover:text-purple-700">Sin Fondo</span>
                </div>
                <?= $dropdownItemsHtml ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <!-- Rename -->
        <button type="button" onclick="openRenameBackgroundModal()" class="shrink-0 w-[50px] h-[50px] flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm group" title="Renombrar Fondo Actual">
            <i class="ph-bold ph-pencil-simple text-xl group-hover:scale-110 transition-transform"></i>
        </button>

        <!-- Gallery -->
        <button type="button" onclick="openBackgroundGallery()" class="shrink-0 w-[50px] h-[50px] flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-purple-50 hover:text-purple-600 hover:border-purple-200 transition-all shadow-sm group" title="Ver Galería Completa">
             <i class="ph-bold ph-squares-four text-xl group-hover:scale-110 transition-transform"></i>
        </button>
    </div>

    <!-- Central Preview -->
    <div class="flex justify-center mt-4">
        <div class="w-full max-w-[220px]">
            <div class="relative w-full">
                <div id="current_fondo_container" class="relative w-full h-auto rounded-xl border border-slate-200 shadow-sm bg-slate-100 overflow-hidden group" style="aspect-ratio: 210/297;">
                    
                    <!-- Content State (Show if we have a current image URL or Select Value) -->
                    <div id="fondo_preview_content" class="<?= (!empty($currentSrcHtml)) ? '' : 'hidden' ?> w-full h-full relative">
                        <div onclick="openBackgroundZoom()" class="cursor-zoom-in w-full h-full relative group">
                            <img id="fondo_current_img" src="<?= $currentSrcHtml ?>" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="ph-bold ph-eye text-white text-3xl drop-shadow-md"></i>
                            </div>
                        </div>
                        
                        <button type="button" onclick="deleteCurrentBackground()" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors z-20" title="Quitar Fondo">
                            <i class="ph-bold ph-trash text-xs"></i>
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div id="fondo_preview_empty" class="<?= (!empty($currentSrcHtml)) ? 'hidden' : '' ?> flex items-center justify-center h-full text-slate-300">
                        <i class="ph-duotone ph-image text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Modal (Backgrounds) -->
<div id="background_gallery_modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeBackgroundGallery()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0" onclick="closeBackgroundGallery()">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200" onclick="event.stopPropagation()">
                
                <!-- Header -->
                <!-- Header -->
                <div class="bg-white px-4 py-4 border-b border-slate-100 sticky top-0 z-10">
                    <!-- Row 1: Title & Close -->
                    <div class="flex items-center justify-between mb-3">
                         <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="ph-bold ph-image text-purple-600"></i>
                            Galería de Fondos
                        </h3>
                        
                        <button type="button" onclick="closeBackgroundGallery()" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors" title="Cerrar Galería">
                            <i class="ph-bold ph-x text-xl"></i>
                        </button>
                    </div>

                    <!-- Row 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <!-- Filter Pill -->
                        <div class="flex bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                            <button type="button" onclick="filterBackgroundGallery('all')" id="bg_filter_btn_all" class="px-3 py-1.5 text-xs font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all">Todos</button>
                            <button type="button" onclick="filterBackgroundGallery('used')" id="bg_filter_btn_used" class="px-3 py-1.5 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all">En uso</button>
                            <button type="button" onclick="filterBackgroundGallery('unused')" id="bg_filter_btn_unused" class="px-3 py-1.5 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all">No usados</button>
                        </div>

                         <!-- Botón Upload -->
                         <div class="relative">
                            <input type="file" id="modal_bg_upload" accept="image/*" class="hidden" onchange="uploadBackgroundAsync(this)">
                            <label for="modal_bg_upload" class="cursor-pointer px-4 py-2 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 border border-purple-100 shadow-sm">
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
                            <p>No hay fondos disponibles.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach($files as $file): 
                                $filename = basename($file);
                                $display = $filename; // simplified logic reused
                                if(preg_match('/^fondo_(.+)_(\d+)\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
                                     $display = ucwords(str_replace('-', ' ', $matches[1])) . ' ' . $matches[2];
                                } elseif(preg_match('/^fondo_([a-z0-9]{3})\.(png|jpg|jpeg|webp)$/i', $filename, $matches)) {
                                     $display = 'Fondo ' . strtoupper($matches[1]);
                                } else {
                                     $clean = str_replace(['fondo_', 'fondo'], '', $filename);
                                     $display = ucwords(str_replace(['.png','.jpg','.jpeg','.webp','_','-'], ['','','','',' ',' '], $clean));
                                }
                                
                                $src = url('/pdf-templates/image/' . $filename);
                                $activeClass = ($currentBackground === $filename) ? 'ring-2 ring-purple-500 ring-offset-2' : 'hover:ring-2 hover:ring-slate-300 hover:ring-offset-2';
                                $isUsed = in_array($filename, $usedBackgrounds);
                            ?>
                            <div class="relative group bg-gallery-item" data-used="<?= $isUsed ? 'true' : 'false' ?>">
                                <button type="button" onclick="selectBackgroundFromGallery('<?= $filename ?>')" class="w-full bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all text-left <?= $activeClass ?>">
                                    <div class="w-full bg-slate-50 relative" style="aspect-ratio: 210/297;">
                                        <img src="<?= $src ?>" loading="lazy" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
                                        
                                        <!-- Label Overlay -->
                                        <div class="absolute bottom-2 left-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-[9px] font-bold text-slate-700 shadow-sm border border-slate-100 truncate text-center">
                                            <?= $display ?>
                                        </div>

                                        <?php if($isUsed): ?>
                                            <div class="absolute top-2 left-2 bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded text-[9px] font-bold border border-purple-200 shadow-sm z-10">
                                                En uso
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if($currentBackground === $filename): ?>
                                        <div class="absolute top-2 right-2 w-6 h-6 bg-purple-500 text-white rounded-full flex items-center justify-center shadow-sm z-20">
                                            <i class="ph-bold ph-check text-xs"></i>
                                        </div>
                                    <?php endif; ?>
                                </button>

                                <!-- Delete Button (Always visible for mobile) -->
                                <button type="button" onclick="openDeleteBackgroundModal(this.closest('.relative'), '<?= $filename ?>', '<?= addslashes($display) ?>', <?= $isUsed ? 'true' : 'false' ?>)" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 hover:scale-110 z-20" title="Eliminar">
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

<!-- RENAME MODAL -->
<div id="renameBackgroundModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 transform scale-95 transition-transform duration-300">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4 text-blue-600">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="ph-bold ph-pencil-simple text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Renombrar Fondo</h3>
                    <p class="text-xs text-slate-500">Cambiar nombre del archivo</p>
                </div>
            </div>
            
            <p class="text-sm text-slate-600 mb-4">
                Ingresa el nuevo nombre para este fondo. Se actualizará en todos los temas que lo usen.
            </p>
            
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 mb-1">Nombre Actual</label>
                <div id="renameBgOldNameDisplay" class="text-sm font-mono text-slate-700 bg-slate-100 px-3 py-2 rounded-lg mb-3 break-all">...</div>
                
                <label class="block text-xs font-bold text-slate-500 mb-1">Nuevo Nombre</label>
                <input type="text" id="renameBgNewNameInput" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" placeholder="Ej: fondo-corporativo">
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeRenameBackgroundModal()" class="flex-1 px-4 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="renameBackgroundConfirm()" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-colors shadow-lg shadow-blue-200">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Background) -->
<div id="deleteBackgroundModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden transform scale-95 transition-all duration-300">
        <!-- Header -->
        <div id="delBgModalHeader" class="bg-gradient-to-r from-red-500 to-rose-600 p-5 relative overflow-hidden transition-colors duration-300">
             <!-- Decoracion -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full -ml-12 -mb-12"></div>
            </div>
            <div class="flex items-center gap-3 relative z-10">
                <div id="delBgModalIcon" class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm transition-all">
                    <i class="ph-bold ph-trash text-white text-2xl" id="delBgModalIconI"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg" id="delBgModalTitle">Eliminar Fondo</h3>
                    <p class="text-white/90 text-xs" id="delBgModalSub">Esta acción es irreversible</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
             <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <p class="text-slate-600 text-sm mb-2" id="delBgModalBodyMsg">¿Eliminar este archivo permanentemente?</p>
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-image text-purple-600"></i>
                    <p class="font-bold text-slate-800 text-sm truncate uppercase" id="deleteBackgroundName">Name</p>
                </div>
            </div>

            <!-- Actions Standard -->
            <div id="delBgModalActions" class="flex gap-3">
                <button type="button" onclick="closeDeleteBackgroundModal()" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="deleteBackgroundConfirm()" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Sí, eliminar
                </button>
            </div>
            
             <!-- Actions Blocked -->
            <div id="delBgModalActionsBlocked" class="hidden flex gap-3">
                <button type="button" onclick="closeDeleteBackgroundModal()" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- PART 1: Direct Upload Logic (Removed) ---

    // --- PART 4: Delete Modal Logic (Background) ---
    // (Previous code remains)

    // --- RENAME MODAL LOGIC ---
    function openRenameBackgroundModal() {
        const select = document.getElementById('fondo_select');
        const filename = select.value;
        if(!filename) {
            alert('Selecciona un fondo primero.');
            return;
        }
        
        const nameWithoutExt = filename.replace(/\.[^/.]+$/, "");
        
        document.getElementById('renameBgOldNameDisplay').textContent = filename;
        document.getElementById('renameBgNewNameInput').value = ''; // Clean start 
        
        const modal = document.getElementById('renameBackgroundModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
            document.getElementById('renameBgNewNameInput').focus();
        }, 10);
    }
    
    function closeRenameBackgroundModal() {
        const modal = document.getElementById('renameBackgroundModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
    
    async function renameBackgroundConfirm() {
        const select = document.getElementById('fondo_select');
        const oldName = select.value;
        const newName = document.getElementById('renameBgNewNameInput').value;
        
        if(!newName.trim()) return;
        
        try {
            const response = await fetch('/pdf-templates/rename-background', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ old_name: oldName, new_name: newName })
            });
            const data = await response.json();
            
            if(data.success) {
                if(typeof mostrarToast === 'function') mostrarToast('Fondo renombrado. Recargando...', 'success');
                
                // Save scroll position
                sessionStorage.setItem('bg_scroll_pos', window.scrollY);
                
                // Save new name for auto-selection
                sessionStorage.setItem('bg_auto_select', data.new_name);

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        } catch(e) {
            console.error(e);
            alert('Error de conexión');
        }
    }


    // --- PART 2: Selection Logic (Right Col / Select) ---
    function updateBackgroundPreviewFromSelect(select) {
        if (select.selectedIndex < 0) {
             document.getElementById('fondo_preview_content').classList.add('hidden');
             document.getElementById('fondo_preview_empty').classList.remove('hidden');
             document.getElementById('fondo_current_img').src = '';
             return;
        }
        const selectedOption = select.options[select.selectedIndex];
        const src = selectedOption.getAttribute('data-src');
        const value = select.value;
        
        const content = document.getElementById('fondo_preview_content');
        const empty   = document.getElementById('fondo_preview_empty');
        const img     = document.getElementById('fondo_current_img');

        if (value && src) {
            img.src = src;
            content.classList.remove('hidden');
            empty.classList.add('hidden');
            
            // Clear Direct Upload (Removed)
            // clearBackgroundUpload();
        } else {
            // Check if there is NO direct upload either, then show empty
            // But here we rely on Select state. 
            img.src = '';
            content.classList.add('hidden');
            empty.classList.remove('hidden');
        }
        
        // Ensure delete flag is 0 if we selected something
        const deleteInput = document.getElementById('delete_fondo');
        if(deleteInput) deleteInput.value = (value) ? '0' : '1'; 
        // Note: If value is empty, it means "No selection".
        // If there is also no upload, it effectively deletes.
    }

    function clearBackgroundSelection(updateUI = true) {
        const select = document.getElementById('fondo_select');
        select.value = '';
        if(updateUI) updateBackgroundPreviewFromSelect(select);
    }
    
    // Action from the Trash Button on Right Col
    function deleteCurrentBackground() {
        clearBackgroundSelection(true);
        // Also ensure delete flag is set (handled in update fn)
        const deleteInput = document.getElementById('delete_fondo');
        if(deleteInput) deleteInput.value = '1';
    }
    
    // Zoom
    function openBackgroundZoom() {
        const img = document.getElementById('fondo_current_img');
        if(img.src) {
             // Try to get name
             const select = document.getElementById('fondo_select');
             let name = 'Fondo';
             if(select.selectedIndex >= 0) name = select.options[select.selectedIndex].text.trim();
             
             if(typeof openImageModal === 'function') openImageModal(img.src, name);
        }
    }

    // --- PART 3: Gallery Modal Logic ---
    function openBackgroundGallery() {
        document.getElementById('background_gallery_modal').classList.remove('hidden');
    }

    function closeBackgroundGallery() {
        document.getElementById('background_gallery_modal').classList.add('hidden');
    }
    
    function selectBackgroundFromGallery(filename) {
        const select = document.getElementById('fondo_select');
        select.value = filename;
        updateBackgroundPreviewFromSelect(select);
        closeBackgroundGallery();
    }
    
    function filterBackgroundGallery(mode) {
        const items = document.querySelectorAll('.bg-gallery-item');
        const btnAll = document.getElementById('bg_filter_btn_all');
        const btnUsed = document.getElementById('bg_filter_btn_used');
        const btnUnused = document.getElementById('bg_filter_btn_unused');
        
        const activeClass = "px-3 py-1 text-[10px] font-bold rounded-md bg-white shadow-sm text-purple-700 transition-all";
        const inactiveClass = "px-3 py-1 text-[10px] font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all";
        
        btnAll.className = inactiveClass;
        btnUsed.className = inactiveClass;
        btnUnused.className = inactiveClass;

        if (mode === 'all') btnAll.className = activeClass;
        else if (mode === 'used') btnUsed.className = activeClass;
        else if (mode === 'unused') btnUnused.className = activeClass;

        items.forEach(item => {
            const isUsed = item.getAttribute('data-used') === 'true';
            if (mode === 'all') item.classList.remove('hidden');
            else if (mode === 'used') {
                if(isUsed) item.classList.remove('hidden'); else item.classList.add('hidden');
            } else if (mode === 'unused') {
                if(!isUsed) item.classList.remove('hidden'); else item.classList.add('hidden');
            }
        });
    }

    async function uploadBackgroundAsync(input) {
        if (!input.files || !input.files[0]) return;
        
        const formData = new FormData();
        formData.append('background_file', input.files[0]);

        try {
            const response = await fetch('/pdf-templates/upload-background', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                if(typeof mostrarToast === 'function') mostrarToast('Fondo subido correctamente. Recargando...', 'success');
                
                // Save scroll position for "invisible refresh"
                sessionStorage.setItem('bg_scroll_pos', window.scrollY);
                
                // Save filename to auto-select after reload
                sessionStorage.setItem('bg_auto_select', data.filename);

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión.');
        }
        
        input.value = ''; // Reset
    }

    // --- PART 4: Delete Modal Logic (Background) ---
    let bgToDelFilename = null;
    let bgToDelElement = null;

    function openDeleteBackgroundModal(element, filename, display, isUsed = false) {
        bgToDelFilename = filename;
        bgToDelElement = element;
        document.getElementById('deleteBackgroundName').textContent = display;

        // UI Elements
        const header = document.getElementById('delBgModalHeader');
        const icon = document.getElementById('delBgModalIconI');
        const title = document.getElementById('delBgModalTitle');
        const sub = document.getElementById('delBgModalSub');
        const bodyMsg = document.getElementById('delBgModalBodyMsg');
        const actions = document.getElementById('delBgModalActions');
        const actionsBlocked = document.getElementById('delBgModalActionsBlocked');

        if(isUsed) {
             header.classList.remove('from-red-500', 'to-rose-600');
             header.classList.add('from-orange-400', 'to-amber-500');
             icon.classList.remove('ph-trash'); icon.classList.add('ph-warning-circle');
             title.textContent = 'Archivo en Uso';
             sub.textContent = 'No se puede eliminar';
             bodyMsg.textContent = 'Este fondo está asignado a uno o más temas.';
             actions.classList.add('hidden');
             actionsBlocked.classList.remove('hidden');
        } else {
             header.classList.remove('from-orange-400', 'to-amber-500');
             header.classList.add('from-red-500', 'to-rose-600');
             icon.classList.remove('ph-warning-circle'); icon.classList.add('ph-trash');
             title.textContent = 'Eliminar Fondo';
             sub.textContent = 'Esta acción es irreversible';
             bodyMsg.textContent = '¿Eliminar este archivo permanentemente?';
             actions.classList.remove('hidden');
             actionsBlocked.classList.add('hidden');
        }

        const modal = document.getElementById('deleteBackgroundModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }

    function closeDeleteBackgroundModal() {
        const modal = document.getElementById('deleteBackgroundModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function deleteBackgroundConfirm() {
        if(!bgToDelFilename) return;
        try {
            const response = await fetch('/pdf-templates/delete-background', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ filename: bgToDelFilename })
            });

            const data = await response.json();
            if (data.success) {
                if(bgToDelElement) bgToDelElement.remove();
                
                // Update Select and Custom UI
                const select = document.getElementById('fondo_select');
                if(select) {
                    const option = select.querySelector(`option[value="${bgToDelFilename}"]`);
                    if(option) option.remove();
                    
                    // Update Custom Dropdown List
                    const list = document.getElementById('bgDropdownList');
                    if(list) {
                        const items = list.querySelectorAll('div[onclick]');
                        items.forEach(item => {
                            if(item.getAttribute('onclick').includes(`'${bgToDelFilename}'`)) {
                                item.remove();
                            }
                        });
                    }

                    if(select.value === bgToDelFilename) {
                        select.value = '';
                        // Update trigger text
                        const displayLabel = document.getElementById('display_bg_name');
                        if(displayLabel) displayLabel.textContent = '-- Sin Fondo --';
                        updateBackgroundPreviewFromSelect(select);
                    }
                }
                
                if(typeof mostrarToast === 'function') mostrarToast('Archivo eliminado', 'success');
                closeDeleteBackgroundModal();
            } else {
                 if(typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error');
            }
        } catch (error) {
             if(typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error');
        }
    }

    // --- Custom Background Selector Logic ---
    function toggleBgDropdown() {
        const list = document.getElementById('bgDropdownList');
        if(!list) return;
        list.classList.toggle('hidden');
        
        // Auto-scroll
        if(!list.classList.contains('hidden')) {
             setTimeout(() => {
                const container = document.getElementById('bgSelectorContainer');
                if(container) {
                     // Header offset same as fonts (~140px)
                     const yOffset = -140; 
                     const y = container.getBoundingClientRect().top + window.pageYOffset + yOffset;
                     window.scrollTo({top: y, behavior: 'smooth'});
                }
            }, 100);
        }
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('bgSelectorContainer');
        const list = document.getElementById('bgDropdownList');
        if (container && !container.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    function selectCustomBackground(value, displayName) {
        // 1. Close immediately for responsiveness
        const list = document.getElementById('bgDropdownList');
        if(list) list.classList.add('hidden');

        // 2. Update Hidden Select
        const select = document.getElementById('fondo_select');
        if(!select) return;
        select.value = value;
        
        // 3. Update Trigger Text
        const displayLabel = document.getElementById('display_bg_name');
        if(displayLabel) displayLabel.textContent = displayName;
        
        // 4. Trigger Change Logic (Preview)
        updateBackgroundPreviewFromSelect(select);
        
        // 5. Update List UI (Visual Feedback) - Done after close
        if(list) {
            const items = list.querySelectorAll('div[onclick]');
            items.forEach(item => {
                 // Reset
                 item.classList.remove('bg-purple-50');
                 item.classList.add('hover:bg-slate-50');
                 const span = item.querySelector('span');
                 if(span) span.classList.remove('text-purple-700');
                 const check = item.querySelector('.ph-check');
                 if(check) check.remove();
                 
                 // Check if matches value
                 if(item.getAttribute('onclick').includes(`('${value}'`)) {
                      item.classList.add('bg-purple-50');
                      item.classList.remove('hover:bg-slate-50');
                      if(span) span.classList.add('text-purple-700');
                      item.insertAdjacentHTML('beforeend', '<i class="ph-bold ph-check text-purple-600"></i>');
                 }
            });
        }
    }

    // Restore scroll and selection if needed (after rename/upload reload)
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Restore Selection (Auto-select uploaded/renamed file)
        const autoSelect = sessionStorage.getItem('bg_auto_select');
        
        if(autoSelect) {
            const select = document.getElementById('fondo_select');
            if(select) {
                const option = select.querySelector(`option[value="${autoSelect}"]`);
                if(option) {
                    // Trigger Selection logic
                    selectCustomBackground(autoSelect, option.text);
                }
            }
            sessionStorage.removeItem('bg_auto_select');
            
            // FORCE SCROLL TO BACKGROUND (Smart Scroll)
            setTimeout(() => {
                const container = document.getElementById('bgSelectorContainer');
                if(container) {
                    const headerOffset = 180; 
                    const elementPosition = container.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            }, 300);

        } else {
            // Restore Generic Scroll if NO auto-select action
            const scrollPos = sessionStorage.getItem('bg_scroll_pos');
            if(scrollPos) {
                window.scrollTo(0, parseInt(scrollPos)); 
                sessionStorage.removeItem('bg_scroll_pos'); 
            }
        }
    });
</script>
 
 
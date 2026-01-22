<?php
// Props: $value (icon class)
$currentIcon = $value ?? 'ph-paint-brush';

// Expanded Icon Set
$icons = [
    // General / UI
    ['class' => 'ph-paint-brush', 'name' => 'Brocha'],
    ['class' => 'ph-palette', 'name' => 'Paleta'],
    ['class' => 'ph-pencil', 'name' => 'Lápiz'], 
    ['class' => 'ph-pen-nib', 'name' => 'Pluma'], 
    ['class' => 'ph-rulers', 'name' => 'Regla'], 
    ['class' => 'ph-scissors', 'name' => 'Tijeras'],
    ['class' => 'ph-trash', 'name' => 'Basura'],
    ['class' => 'ph-copy', 'name' => 'Copiar'],
    ['class' => 'ph-magnifying-glass', 'name' => 'Buscar'],
    ['class' => 'ph-gear', 'name' => 'Ajustes'],
    
    // Business & Office
    ['class' => 'ph-briefcase', 'name' => 'Maletín'], 
    ['class' => 'ph-building', 'name' => 'Edificio'], 
    ['class' => 'ph-storefront', 'name' => 'Tienda'],
    ['class' => 'ph-files', 'name' => 'Archivos'], 
    ['class' => 'ph-file-pdf', 'name' => 'PDF'],
    ['class' => 'ph-calendar-check', 'name' => 'Calendario'],
    ['class' => 'ph-chart-bar', 'name' => 'Gráfico'],
    ['class' => 'ph-presentation-chart', 'name' => 'Presentación'],
    
    // Communication & Contact
    ['class' => 'ph-envelope', 'name' => 'Correo'],
    ['class' => 'ph-envelope-open', 'name' => 'Correo Abierto'],
    ['class' => 'ph-phone', 'name' => 'Teléfono'],
    ['class' => 'ph-phone-call', 'name' => 'Llamada'],
    ['class' => 'ph-chat-circle', 'name' => 'Chat'],
    ['class' => 'ph-paper-plane-tilt', 'name' => 'Enviar'],
    ['class' => 'ph-globe', 'name' => 'Web'],
    ['class' => 'ph-map-pin', 'name' => 'Ubicación'],
    
    // Commerce
    ['class' => 'ph-shopping-cart', 'name' => 'Carrito'],
    ['class' => 'ph-credit-card', 'name' => 'Tarjeta'],
    ['class' => 'ph-tag', 'name' => 'Etiqueta'],
    ['class' => 'ph-currency-dollar', 'name' => 'Dinero'],
    ['class' => 'ph-receipt', 'name' => 'Recibo'],
    
    // Status & Feedback
    ['class' => 'ph-star', 'name' => 'Estrella'], 
    ['class' => 'ph-crown', 'name' => 'Corona'], 
    ['class' => 'ph-diamond', 'name' => 'Diamante'], 
    ['class' => 'ph-trophy', 'name' => 'Trofeo'],
    ['class' => 'ph-heart', 'name' => 'Corazón'], 
    ['class' => 'ph-thumbs-up', 'name' => 'Me Gusta'], 
    ['class' => 'ph-check-circle', 'name' => 'Verificado'], 
    ['class' => 'ph-seal-check', 'name' => 'Sello'],
    ['class' => 'ph-warning', 'name' => 'Alerta'],
    ['class' => 'ph-info', 'name' => 'Info'],
    
    // Nature & Elements
    ['class' => 'ph-fire', 'name' => 'Fuego'],
    ['class' => 'ph-lightning', 'name' => 'Rayo'], 
    ['class' => 'ph-lightbulb', 'name' => 'Bombilla'], 
    ['class' => 'ph-rocket', 'name' => 'Cohete'], 
    ['class' => 'ph-leaf', 'name' => 'Hoja'], 
    ['class' => 'ph-flower', 'name' => 'Flor'], 
    ['class' => 'ph-tree', 'name' => 'Árbol'], 
    ['class' => 'ph-drop', 'name' => 'Gota'],
    ['class' => 'ph-sun', 'name' => 'Sol'], 
    ['class' => 'ph-moon', 'name' => 'Luna'], 
    ['class' => 'ph-cloud', 'name' => 'Nube'], 
    ['class' => 'ph-rainbow', 'name' => 'Arcoíris'],
    
    // Transport
    ['class' => 'ph-airplane', 'name' => 'Avión'], 
    ['class' => 'ph-car', 'name' => 'Auto'], 
    ['class' => 'ph-truck', 'name' => 'Camión'], 
    ['class' => 'ph-bicycle', 'name' => 'Bicicleta'],
    
    // People
    ['class' => 'ph-user', 'name' => 'Usuario'],
    ['class' => 'ph-users', 'name' => 'Grupo'],
    ['class' => 'ph-user-gear', 'name' => 'Admin'],
];

// Find Current Name
$currentName = 'Desconocido';
foreach($icons as $i) {
    if($i['class'] === $currentIcon) {
        $currentName = $i['name'];
        break;
    }
}
?>

<div class="mb-0">
    <label class="block text-sm font-bold text-slate-700 mb-2">Icono Principal</label>
    <input type="hidden" name="icon" id="input_icon" value="<?= $currentIcon ?>">
    
    <!-- Trigger Button (Premium Look) -->
    <button type="button" onclick="openIconModal()" 
            class="w-full group bg-white hover:bg-slate-50 border border-slate-200 hover:border-pink-300 rounded-xl p-3 flex items-center justify-between transition-all shadow-sm">
        
        <div class="flex items-center gap-4">
            <!-- Icon Preview Box -->
            <div id="trigger_icon_preview" class="w-12 h-12 rounded-lg bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-600 transition-transform group-hover:scale-110">
                <i class="ph-bold <?= $currentIcon ?> text-2xl"></i>
            </div>
            
            <div class="text-left">
                <span id="trigger_icon_name" class="block text-base font-bold text-slate-700 group-hover:text-pink-700 transition-colors">
                    <?= $currentName ?>
                </span>
                <span class="text-xs text-slate-400">Clic para cambiar icono</span>
            </div>
        </div>

        <div class="text-slate-300 group-hover:text-pink-400 transition-colors">
            <i class="ph-bold ph-caret-down text-xl"></i>
        </div>
    </button>
</div>

<!-- Icon Picker Modal -->
<div id="iconPickerModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeIconModal()"></div>
    
    <!-- Panel -->
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 modal-panel flex flex-col max-h-[80vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10 rounded-t-2xl">
            <div>
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-bold ph-star text-pink-500"></i>
                    Seleccionar Icono
                </h3>
                <p class="text-xs text-slate-500">Elige un icono que represente este tema</p>
            </div>
            <button type="button" onclick="closeIconModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        <!-- Grid Content -->
        <div class="p-6 overflow-y-auto custom-scrollbar bg-white">
            <div id="iconsGrid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                <?php foreach($icons as $ic): 
                    $isActive = ($currentIcon == $ic['class']);
                ?>
                    <button type="button" onclick="selectIcon('<?= $ic['class'] ?>', '<?= $ic['name'] ?>')" 
                            class="icon-btn group relative flex flex-col items-center justify-center gap-2 p-3 rounded-xl border transition-all duration-200 outline-none focus:ring-2 focus:ring-pink-500/40 <?= $isActive ? 'bg-pink-50 border-pink-500/50 ring-1 ring-pink-500' : 'bg-slate-50 border-slate-100 hover:bg-white hover:border-pink-200 hover:shadow-md' ?>">
                        
                        <i class="ph-bold <?= $ic['class'] ?> text-2xl <?= $isActive ? 'text-pink-600' : 'text-slate-600 group-hover:text-pink-500 group-hover:scale-110 transition-transform' ?>"></i>
                        
                        <?php if($isActive): ?>
                            <div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-pink-500"></div>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
    </div>
</div>

<script>
    const iconModal = document.getElementById('iconPickerModal');
    const iconBackdrop = iconModal.querySelector('.modal-backdrop');
    const iconPanel = iconModal.querySelector('.modal-panel');

    function openIconModal() {
        iconModal.classList.remove('hidden');
        // Animate In
        setTimeout(() => {
            iconBackdrop.classList.remove('opacity-0');
            iconPanel.classList.remove('opacity-0', 'scale-95');
            iconPanel.classList.add('scale-100');
        }, 10);
    }

    function closeIconModal() {
        // Animate Out
        iconBackdrop.classList.add('opacity-0');
        iconPanel.classList.remove('scale-100');
        iconPanel.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            iconModal.classList.add('hidden');
        }, 300);
    }

    function selectIcon(iconClass, iconName) {
        // 1. Update Input
        document.getElementById('input_icon').value = iconClass;
        
        // 2. Update Trigger UI
        document.getElementById('trigger_icon_name').textContent = iconName;
        const triggerIcon = document.getElementById('trigger_icon_preview').querySelector('i');
        triggerIcon.className = `ph-bold ${iconClass} text-2xl`;
        
        // 3. Update Modal Selection State (Visual feedback)
        document.querySelectorAll('.icon-btn').forEach(btn => {
            // Reset Styles
            btn.className = "icon-btn group relative flex flex-col items-center justify-center gap-2 p-3 rounded-xl border transition-all duration-200 outline-none focus:ring-2 focus:ring-pink-500/40 bg-slate-50 border-slate-100 hover:bg-white hover:border-pink-200 hover:shadow-md";
            
            // Reset Icon Color
            const icon = btn.querySelector('i');
            icon.className = `ph-bold ${icon.className.replace('ph-bold ', '').split(' ')[0]} text-2xl text-slate-600 group-hover:text-pink-500 group-hover:scale-110 transition-transform`;

            // Remove marker
            const marker = btn.querySelector('.absolute.top-1.right-1');
            if(marker) marker.remove();

            // Check if matches new selection
            if(btn.onclick.toString().includes(`'${iconClass}'`)) {
                 btn.className = "icon-btn group relative flex flex-col items-center justify-center gap-2 p-3 rounded-xl border transition-all duration-200 outline-none focus:ring-2 focus:ring-pink-500/40 bg-pink-50 border-pink-500/50 ring-1 ring-pink-500";
                 icon.className = `ph-bold ${iconClass} text-2xl text-pink-600`;
                 btn.insertAdjacentHTML('beforeend', '<div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-pink-500"></div>');
            }
        });

        // 4. Close Modal
        closeIconModal();
    }
</script>

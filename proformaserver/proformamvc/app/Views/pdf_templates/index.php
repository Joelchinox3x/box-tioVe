<?php
// app/Views/pdf_templates/index.php

// Configuración del header
// Configuración del header
$title = 'Plantillas PDF';
$subtitle = count($templates) . ' temas disponibles';

// Multiples botones de accion
$action_buttons = [
    [
        'url' => '#',
        'icon' => 'ph-squares-four', 
        'label' => 'Cambiar Vista',
        'onclick' => 'toggleViewMode(event)',
        'target' => '_self'
    ],
    [
        'url' => url('/pdf-templates/create'),
        'icon' => 'ph-plus',
        'label' => 'Nuevo Tema'
    ]
];

$section = 'pdf_templates'; 
$back_url = url('/proformas/create'); // Botón atrás en header

include __DIR__ . '/../partials/load_header.php';
?>

<main class="pt-18 px-6 pb-6 max-w-5xl mx-auto"> <!-- Ancho mas grande para grids de 4 -->
    
    <div id="templatesGrid" class="grid gap-4 transition-all duration-300 view-mode-0 grid-cols-2 md:grid-cols-2">
        <?php foreach($templates as $tpl): ?>
            <?php include __DIR__ . '/partials/card.php'; ?>
        <?php endforeach; ?>
    </div>

    <?php if(empty($templates)): ?>
        <div class="text-center py-10">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <i class="ph-fill ph-paint-brush-broad text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm font-medium">No hay temas personalizados aún.</p>
        </div>
    <?php endif; ?>

</main>

</main>

<!-- Modal de Clonación -->
<div id="cloneModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeCloneModal()"></div>
    
    <!-- Modal Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 modal-panel">
                
                <form id="cloneForm" method="POST" action="">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="ph-bold ph-copy text-blue-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Clonar Tema</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">
                                        Crea una copia exacta de este tema para modificarla sin afectar el original.
                                    </p>
                                    
                                    <label for="new_name" class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Copia</label>
                                    <input type="text" name="new_name" id="clone_new_name" 
                                           class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-slate-700" 
                                           placeholder="Ej: Copia de Orange" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors">
                            Crear Copia
                        </button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors" onclick="closeCloneModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>

<script>
    // --- View Logic (Existente) ---
    let currentViewMode = localStorage.getItem('pdf_template_view_mode') || 0;
    currentViewMode = parseInt(currentViewMode);
    applyViewMode(currentViewMode);

    function toggleViewMode(e) {
        if(e) e.preventDefault();
        const grid = document.getElementById('templatesGrid');
        grid.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            currentViewMode = (currentViewMode + 1) % 3;
            applyViewMode(currentViewMode);
            localStorage.setItem('pdf_template_view_mode', currentViewMode);
            grid.classList.remove('opacity-0', 'scale-95');
        }, 200);
    }
    
    function applyViewMode(mode) {
        const grid = document.getElementById('templatesGrid');
        // Clean all possible grid classes
        grid.classList.remove('view-mode-0', 'view-mode-1', 'view-mode-2');
        grid.classList.remove('grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 
                            'md:grid-cols-2', 'md:grid-cols-3', 'md:grid-cols-4', 'gap-2', 'gap-4');
        
        grid.classList.add('view-mode-' + mode);
        
        // Default gap
        grid.classList.add('gap-4');

        if(mode === 0) {
            // Vista 1: 2 Col Mobile / 2 Desktop
            grid.classList.add('grid-cols-2', 'md:grid-cols-2');
        }
        else if (mode === 1) {
            // Vista 2: 3 Col Mobile / 3 Desktop
            grid.classList.add('grid-cols-3', 'md:grid-cols-3');
        }
        else {
            // Vista 3: 4 Col Mobile / 4 Desktop !!!
            // Reduce gap for space
            grid.classList.remove('gap-4');
            grid.classList.add('gap-2');
            grid.classList.add('grid-cols-4', 'md:grid-cols-4');
        }
    }

    // --- Clone Modal Logic ---
    const modal = document.getElementById('cloneModal');
    const backdrop = modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('.modal-panel');
    const form = document.getElementById('cloneForm');
    const inputName = document.getElementById('clone_new_name');

    function openCloneModal(id, currentName) {
        // Configurar accion form
        form.action = "<?= url('/pdf-templates/duplicate/') ?>" + id;
        
        // Prellenar nombre
        inputName.value = currentName + ' (Copia)';
        
        // Mostrar
        modal.classList.remove('hidden');
        // Animacion entrada (pequeño delay para permitir render)
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4');
        }, 10);
        
        inputName.focus();
    }

    function closeCloneModal() {
        // Animacion salida
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Esperar duracion transition
    }
    // Delete Modal Logic
    function openDeleteModal(id, name) {
        document.getElementById('delete_target_name').textContent = name;
        document.getElementById('btn_confirm_delete').href = "<?= url('/pdf-templates/delete/') ?>" + id;
        
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('div[class*="transform"]').classList.remove('opacity-0', 'scale-95');
            modal.querySelector('div[class*="transform"]').classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.querySelector('div[class*="transform"]').classList.remove('opacity-100', 'scale-100');
        modal.querySelector('div[class*="transform"]').classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // --- Restore Factory Modal Logic ---
    function openRestoreModal(id, name) {
        document.getElementById('restore_target_name').textContent = name;
        document.getElementById('restoreForm').action = "<?= url('/pdf-templates/restore/') ?>" + id;

        const modal = document.getElementById('restoreModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel = modal.querySelector('.modal-panel');

        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4');
        }, 10);
    }

    function closeRestoreModal() {
        const modal = document.getElementById('restoreModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel = modal.querySelector('.modal-panel');

        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>

<!-- Restore Factory Modal -->
<div id="restoreModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeRestoreModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 modal-panel">

                <form id="restoreForm" method="POST" action="">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="ph-bold ph-arrow-counter-clockwise text-orange-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Restaurar a Fábrica</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">
                                        Esto restaurará el tema <strong id="restore_target_name"></strong> a su configuración original de fábrica.
                                    </p>
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-xs text-orange-800">
                                        <strong>Advertencia:</strong> Se perderán todos los cambios personalizados (colores, fuentes, fondos, footers).
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-orange-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-orange-500 sm:ml-3 sm:w-auto transition-colors">
                            Restaurar
                        </button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors" onclick="closeRestoreModal()">
                            Cancelar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full opacity-0 scale-95">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ph-bold ph-trash text-red-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Eliminar Tema</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                ¿Estás seguro de que deseas eliminar el tema <strong id="delete_target_name"></strong>?
                                <br>Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a href="#" id="btn_confirm_delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Eliminar
                </a>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

 

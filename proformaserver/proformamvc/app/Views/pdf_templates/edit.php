<?php
// app/Views/pdf_templates/edit.php
$title = 'Editar Tema: ' . ($template['display_name'] ?? $template['nombre']);
$section = 'pdf_templates';
$back_url = url('/pdf-templates'); // Botón atrás en header
include __DIR__ . '/../partials/load_header.php';

$color = $template['color_brand'];

// Verificar si existe snapshot Y si es diferente al estado actual
$snapshotPath = __DIR__ . '/../pdf/snapshots/tema_' . $template['id'] . '_snapshot.json';
$hasSnapshot = false;

if (file_exists($snapshotPath)) {
    $snapshotData = json_decode(file_get_contents($snapshotPath), true);

    // Comparar valores clave para ver si hay diferencias
    $isDifferent = (
        $snapshotData['nombre'] !== $template['nombre'] ||
        $snapshotData['color_brand'] !== $template['color_brand'] ||
        $snapshotData['icon'] !== $template['icon'] ||
        $snapshotData['header_php'] !== $template['header_php'] ||
        $snapshotData['footer_img'] !== $template['footer_img'] ||
        $snapshotData['fondo_img'] !== $template['fondo_img'] ||
        $snapshotData['title_font'] !== $template['title_font'] ||
        $snapshotData['title_size'] != $template['title_size'] ||
        $snapshotData['title_bold'] != $template['title_bold'] ||
        $snapshotData['title_color'] !== $template['title_color']
    );

    // Solo mostrar botón si hay diferencias
    $hasSnapshot = $isDifferent;
}
?>

<main class="pt-0 px-6 pb-6 max-w-lg mx-auto">
     
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                    <i class="ph-bold ph-pencil-simple"></i>
                </div>
                Editar Tema
            </h2>

            <div class="flex items-center gap-2">
                <!-- Botón Vista Previa -->
                <a href="<?= url('/pdf-templates/preview/' . $template['id']) ?>" target="_blank"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 transition-colors" title="Vista Previa">
                    <i class="ph-bold ph-eye text-lg"></i>
                </a>
                
                <!-- Botón Clonar -->
                <button onclick="openCloneModal(<?= $template['id'] ?>, '<?= htmlspecialchars($template['display_name'] ?? $template['nombre'], ENT_QUOTES) ?>')"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition-colors" title="Clonar Tema">
                    <i class="ph-bold ph-copy text-lg"></i>
                </button>

                <!-- Botón Guardar Snapshot -->
                <button onclick="openSnapshotModal(<?= $template['id'] ?>, '<?= htmlspecialchars($template['display_name'] ?? $template['nombre'], ENT_QUOTES) ?>')"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 transition-colors" title="Guardar Punto de Restauración">
                    <i class="ph-bold ph-floppy-disk-back text-lg"></i>
                </button>

                <?php if($hasSnapshot): ?>
                <!-- Botón Restaurar Snapshot (solo si existe snapshot guardado) -->
                <button onclick="openRestoreModal(<?= $template['id'] ?>, '<?= htmlspecialchars($template['display_name'] ?? $template['nombre'], ENT_QUOTES) ?>')"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 border border-orange-200 transition-colors" title="Restaurar Snapshot">
                    <i class="ph-bold ph-arrow-counter-clockwise text-lg"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <form action="<?= url('/pdf-templates/update/' . $template['id']) ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="delete_fondo" id="delete_fondo" value="0">
            <input type="hidden" name="delete_footer" id="delete_footer" value="0">
            
            <!-- Nombre (Editable) -->
           <div class="mb-6 bg-blue-50 p-4 rounded-xl border border-blue-200">
            
           <?php 
                $value = $template['nombre'];
                $label = 'ID del Tema (Nombre Interno)';
                include __DIR__ . '/partials/form/input_name.php'; 
            ?>
            </div>

            <!-- Header Selector -->
            <div class="mb-6 bg-purple-50 p-4 rounded-xl border border-purple-200">
                <?php 
                    $value = $template['header_php'] ?? 'header_simple.php';
                    include __DIR__ . '/partials/form/select_header.php'; 
                ?>
            </div>  

            <!-- Font Config -->
            <div class="mb-6 bg-red-50 p-4 rounded-xl border border-red-200">
                <?php include __DIR__ . '/partials/form/select_font.php'; ?>
            </div>

            <!-- Color Config -->
            <div class="mb-6 bg-orange-50 p-4 rounded-xl border border-orange-200">
                <?php 
                    $value = $color;
                    include __DIR__ . '/partials/form/input_color.php'; 
                ?>
            </div>

            <!-- Footer Selector (Full Width) -->
             <div class="mb-6 bg-emerald-50 p-4 rounded-xl border border-emerald-200">
             <?php 
                $value = $template['footer_img'] ?? '';
                include __DIR__ . '/partials/form/select_footer.php'; 
             ?>
             </div>

            <!-- Icono Picker (Collapsible) -->
            <div class="mb-6 bg-pink-50 p-4 rounded-xl border border-pink-200">
            <?php 
                $value = $template['icon'] ?? 'ph-paint-brush';
                include __DIR__ . '/partials/form/picker_icon.php'; 
            ?>
            </div>

            <!-- Fondo Imagen (Diseño Compacto + Galería) -->
            <div class="mb-6 bg-blue-50 p-4 rounded-xl border border-blue-200">
            <?php 
                $value = $template['fondo_img'] ?? '';
                include __DIR__ . '/partials/form/select_background.php'; 
            ?>
            </div>  

            <!-- Botones Flotantes (DENTRO del form) -->
            <?php
                $floating_buttons = [
                    'cancel_url' => url('/pdf-templates'),
                    'submit_text' => 'Guardar Cambios',
                    'submit_icon' => 'ph-floppy-disk',
                    'submit_color' => 'purple'
                ];
                include __DIR__ . '/../partials/floating_form_buttons.php';
            ?>
        </form>
    </div>
     
</main>

<!-- Restore Snapshot Modal -->
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
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Restaurar Punto Guardado</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">
                                        Esto restaurará el tema <strong id="restore_target_name"></strong> al último punto de restauración guardado.
                                    </p>
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-xs text-orange-800">
                                        <strong>Advertencia:</strong> Se perderán todos los cambios realizados después de guardar el punto de restauración.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-orange-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-orange-500 sm:ml-3 sm:w-auto">Restaurar</button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto" onclick="closeRestoreModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Clonación (Copiado de index) -->
<div id="cloneModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeCloneModal()"></div>
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
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Clonar Tema</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">Crea una copia exacta de este tema.</p>
                                    <label for="new_name" class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Copia</label>
                                    <input type="text" name="new_name" id="clone_new_name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Crear Copia</button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto" onclick="closeCloneModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Guardar Snapshot -->
<div id="snapshotModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeSnapshotModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 modal-panel">
                <form id="snapshotForm" method="POST" action="">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="ph-bold ph-floppy-disk-back text-blue-600 text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Guardar Punto de Restauración</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">
                                        ¿Guardar la configuración actual del tema <strong id="snapshot_target_name"></strong> como punto de restauración?
                                    </p>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                                        <strong>Info:</strong> Podrás volver a este estado en cualquier momento usando el botón "Restaurar". Si ya existe un punto guardado, será sobrescrito.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Guardar Punto</button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto" onclick="closeSnapshotModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal Partial -->
<?php include __DIR__ . '/partials/modal_image_viewer.php'; ?>

<script>    




    // Clone Modal Logic
    const modal = document.getElementById('cloneModal');
    const backdrop = modal.querySelector('.modal-backdrop');
    const panel = modal.querySelector('.modal-panel');
    const form = document.getElementById('cloneForm');
    const inputName = document.getElementById('clone_new_name');

    function openCloneModal(id, currentName) {
        form.action = "<?= url('/pdf-templates/duplicate/') ?>" + id;
        inputName.value = currentName + ' (Copia)';
        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4');
        }, 10);
        inputName.focus();
    }

    function closeCloneModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    // Restore Modal Logic
    function openRestoreModal(id, name) {
        document.getElementById('restore_target_name').textContent = name;
        document.getElementById('restoreForm').action = "<?= url('/pdf-templates/restore/') ?>" + id;

        const restoreModal = document.getElementById('restoreModal');
        const restoreBackdrop = restoreModal.querySelector('.modal-backdrop');
        const restorePanel = restoreModal.querySelector('.modal-panel');

        restoreModal.classList.remove('hidden');
        setTimeout(() => {
            restoreBackdrop.classList.remove('opacity-0');
            restorePanel.classList.remove('opacity-0', 'translate-y-4');
        }, 10);
    }

    function closeRestoreModal() {
        const restoreModal = document.getElementById('restoreModal');
        const restoreBackdrop = restoreModal.querySelector('.modal-backdrop');
        const restorePanel = restoreModal.querySelector('.modal-panel');

        restoreBackdrop.classList.add('opacity-0');
        restorePanel.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => { restoreModal.classList.add('hidden'); }, 300);
    }

    // Snapshot Modal Logic
    function openSnapshotModal(id, name) {
        document.getElementById('snapshot_target_name').textContent = name;
        document.getElementById('snapshotForm').action = "<?= url('/pdf-templates/save-snapshot/') ?>" + id;

        const snapshotModal = document.getElementById('snapshotModal');
        const snapshotBackdrop = snapshotModal.querySelector('.modal-backdrop');
        const snapshotPanel = snapshotModal.querySelector('.modal-panel');

        snapshotModal.classList.remove('hidden');
        setTimeout(() => {
            snapshotBackdrop.classList.remove('opacity-0');
            snapshotPanel.classList.remove('opacity-0', 'translate-y-4');
        }, 10);
    }

    function closeSnapshotModal() {
        const snapshotModal = document.getElementById('snapshotModal');
        const snapshotBackdrop = snapshotModal.querySelector('.modal-backdrop');
        const snapshotPanel = snapshotModal.querySelector('.modal-panel');

        snapshotBackdrop.classList.add('opacity-0');
        snapshotPanel.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => { snapshotModal.classList.add('hidden'); }, 300);
    }

    // ========================================
    // SISTEMA DE NOTIFICACIONES
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (isset($_GET['msg'])): ?>
        <?php
        $msg = $_GET['msg'];
        $notificaciones = [
          'updated' => ['titulo' => '¡Guardado!', 'mensaje' => 'El tema se actualizó correctamente', 'tipo' => 'success'],
          'snapshot_saved' => ['titulo' => '¡Punto Guardado!', 'mensaje' => 'Se guardó el punto de restauración correctamente', 'tipo' => 'success'],
          'error' => ['titulo' => 'Error', 'mensaje' => 'Ocurrió un error al guardar', 'tipo' => 'error']
        ];
        $notif = $notificaciones[$msg] ?? null;
        ?>
        <?php if ($notif): ?>
          if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(
              '<?= $notif['titulo'] ?>',
              '<?= $notif['mensaje'] ?>',
              '<?= $notif['tipo'] ?>'
            );
          }
        <?php endif; ?>
      <?php endif; ?>
    });
</script>
 

<?php
// app/Views/pdf_templates/create.php
$title = 'Nuevo Tema PDF';
$section = 'pdf_templates';
include __DIR__ . '/../partials/load_header.php';
?>

<main class="pt-20 px-6 pb-6 max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
            <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                <i class="ph-bold ph-paint-brush-broad"></i>
            </div>
            Crear Nuevo Tema
        </h2>

        <form action="<?= url('/pdf-templates/store') ?>" method="POST">
            
            <!-- Nombre -->
            <?php 
                $value = '';
                $label = 'Nombre del Tema';
                $placeholder = 'Ej: Azul Corporativo';
                include __DIR__ . '/partials/form/input_name.php';
            ?>

            <!-- Color -->
             <?php 
                $value = '#f37021';
                include __DIR__ . '/partials/form/input_color.php';
            ?>

            <!-- Icono -->
            <?php 
                $value = 'ph-paint-brush';
                include __DIR__ . '/partials/form/picker_icon.php';
            ?>

            <!-- Header View -->
            <?php 
                $value = 'header_simple.php';
                include __DIR__ . '/partials/form/select_header.php';
            ?>

            <!-- Footer View (Image) -->
            <?php 
                $value = '';
                include __DIR__ . '/partials/form/select_footer.php';
            ?>

            <!-- Acciones (Botones Flotantes) -->
            <?php
                $floating_buttons = [
                    'cancel_url' => url('/pdf-templates'),
                    'submit_text' => 'Crear Tema',
                    'submit_icon' => 'ph-check',
                    'submit_color' => 'purple'
                ];
                include __DIR__ . '/../partials/floating_form_buttons.php';
            ?>
        </form>
    </div>
</main>

<!-- Image Viewer Modal Partial -->
<?php include __DIR__ . '/partials/modal_image_viewer.php'; ?>

<?php include __DIR__ . '/../partials/load_footer.php'; ?>

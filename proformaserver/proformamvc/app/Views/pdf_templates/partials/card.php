<?php 
// app/Views/pdf_templates/partials/card.php

// Variables esperadas: $tpl (array asociativo)
$color = $tpl['color_brand'] ?? '#333';
$name = $tpl['nombre'];
$isDefault = ($tpl['es_default'] == 1);

// Icono desde DB
$icon = $tpl['icon'] ?? 'ph-paint-brush';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow duration-300 group relative
            [.view-mode-2_&]:flex [.view-mode-2_&]:flex-col [.view-mode-2_&]:items-center [.view-mode-2_&]:p-2 [.view-mode-2_&]:gap-2 [.view-mode-2_&]:text-center
            [.view-mode-1_&]:flex [.view-mode-1_&]:flex-col [.view-mode-1_&]:items-center [.view-mode-1_&]:p-2 [.view-mode-1_&]:gap-1 [.view-mode-1_&]:text-center
            <?= $isDefault ? '[.view-mode-1_&]:border-2 [.view-mode-1_&]:border-purple-500' : '' ?>"
     style="<?= $isDefault ? '' : '' ?>"> 
    
    <?php if($isDefault): ?>
        <div class="absolute top-2 right-2 bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-purple-200 z-10
                    [.view-mode-1_&]:hidden
                    [.view-mode-2_&]:hidden"> 
            DEFAULT
        </div>
    <?php else: ?>
        <button onclick="openDeleteModal(<?= $tpl['id'] ?>, '<?= htmlspecialchars($tpl['display_name'] ?? $name, ENT_QUOTES) ?>')" 
           class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-white hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-lg shadow-sm border border-slate-100 hover:border-red-100 z-20 cursor-pointer
                  [.view-mode-1_&]:-top-2 [.view-mode-1_&]:-right-2 [.view-mode-1_&]:w-5 [.view-mode-1_&]:h-5 [.view-mode-1_&]:rounded-full [.view-mode-1_&]:bg-red-500 [.view-mode-1_&]:text-white [.view-mode-1_&]:border-red-600 [.view-mode-1_&]:shadow-md
                  [.view-mode-2_&]:hidden"
           title="Eliminar Tema">
            <!-- Icono Trash (Mode 0) -->
            <i class="ph-bold ph-trash [.view-mode-1_&]:hidden"></i>
            <!-- Icono X (Mode 1) -->
            <i class="ph-bold ph-x hidden [.view-mode-1_&]:block text-xs"></i>
        </button>
    <?php endif; ?> 
    
    <!-- Color Header -->
    <div class="h-16 w-full rounded-t-2xl relative flex items-center justify-center transition-colors duration-300
                [.view-mode-1_&]:w-auto [.view-mode-1_&]:h-auto [.view-mode-1_&]:!bg-transparent [.view-mode-1_&]:mb-1
                [.view-mode-2_&]:w-10 [.view-mode-2_&]:h-10 [.view-mode-2_&]:rounded-full [.view-mode-2_&]:shrink-0" 
         style="background-color: <?= $color ?>15;"> 
        
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white shadow-sm transition-transform duration-300
                    [.view-mode-1_&]:w-8 [.view-mode-1_&]:h-8 [.view-mode-1_&]:text-sm
                    [.view-mode-2_&]:w-full [.view-mode-2_&]:h-full [.view-mode-2_&]:text-base" 
             style="background-color: <?= $color ?>;">
            <i class="ph-bold <?= $icon ?> text-xl [.view-mode-1_&]:text-base [.view-mode-2_&]:text-base"></i>
        </div>

        <?php if($isDefault): ?>
            <div class="absolute top-2 right-2 bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-purple-200
                        [.view-mode-1_&]:hidden
                        [.view-mode-2_&]:hidden"> 
                DEFAULT
            </div>
             <!-- Badge alternativo para mode 2 y 1 -->
             <div class="hidden [.view-mode-2_&]:block absolute -top-1 -right-1 w-3 h-3 bg-purple-500 rounded-full border border-white"></div>
        <?php endif; ?>
    </div>
    
    <div class="p-4 [.view-mode-1_&]:contents
                    [.view-mode-2_&]:p-0 [.view-mode-2_&]:w-full">
                                 
        <h3 class="font-bold text-slate-800 text-lg capitalize mb-1 truncate 
                   [.view-mode-1_&]:text-xs [.view-mode-1_&]:mb-2 [.view-mode-1_&]:w-full
                   [.view-mode-2_&]:text-[10px] [.view-mode-2_&]:mb-0"><?= $tpl['display_name'] ?? $name ?></h3>
        
        <p class="text-xs text-slate-400 mb-4 font-mono [.view-mode-1_&]:hidden [.view-mode-2_&]:hidden">ID: <?= $name ?></p>
        
        <div class="flex items-center gap-2 justify-between
                    [.view-mode-1_&]:w-full [.view-mode-1_&]:mt-auto
                    [.view-mode-2_&]:hidden">
            <!-- Botón Editar -->
            <a href="<?= url('/pdf-templates/edit/' . $tpl['id']) ?>" class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs text-center border border-slate-200 transition-colors flex items-center justify-center gap-1
                      [.view-mode-1_&]:py-2 [.view-mode-1_&]:px-0 [.view-mode-1_&]:w-9 [.view-mode-1_&]:flex-none">
                <i class="ph-bold ph-pencil-simple text-sm [.view-mode-1_&]:text-lg"></i> <span class="[.view-mode-1_&]:hidden">Editar</span>
            </a>

            <!-- Botón Clonar -->
            <button onclick="openCloneModal(<?= $tpl['id'] ?>, '<?= htmlspecialchars($tpl['display_name'] ?? $name, ENT_QUOTES) ?>')"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition-colors cursor-pointer
                      [.view-mode-1_&]:w-7 [.view-mode-1_&]:h-7" title="Clonar Tema">
                <i class="ph-bold ph-copy"></i>
            </button>
        </div>
        
        <!-- Link invisible/overlay para mode 2 -->
        <a href="<?= url('/pdf-templates/edit/' . $tpl['id']) ?>" class="hidden [.view-mode-2_&]:block absolute inset-0 z-10" title="Editar Tema"></a>
    </div>
</div>

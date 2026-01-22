    <!-- Sección 3: Diseño del PDF -->
    <div id="designSection" class="bg-white p-2 rounded-2xl shadow-md border-2 border-slate-200 hover:shadow-lg transition-all duration-300">
      <div class="flex justify-between items-center mb-1.5">
          <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <div class="bg-purple-100 p-1.5 rounded-xl">
              <i class="ph-fill ph-paint-brush-broad text-purple-600 text-base"></i>
            </div>
            <span>Diseño del PDF</span>
          </h2>
          
          <!-- Botón Configuración (Header) -->
          <button type="button" onclick="openThemeConfigModal()" class="text-slate-400 hover:text-purple-600 hover:bg-purple-50 p-1.5 rounded-lg transition-all" title="Gestionar Temas">
            <i class="ph-fill ph-gear text-lg"></i>
          </button>
      </div>

      <div class="grid grid-cols-4 gap-3"> <!-- Cambiado a grid-cols-4 para más espacio -->
        <?php 
        // fallback si no hay templates cargados
        $templates = $templates ?? [
            ['nombre' => 'orange', 'color_brand' => '#f37021', 'es_default' => 1],
            ['nombre' => 'blue',   'color_brand' => '#004481', 'es_default' => 0],
            ['nombre' => 'simple', 'color_brand' => '#666666', 'es_default' => 0]
        ];

        // Mostrar solo los primeros 3 (o los que vengan, pero la idea es limitar si hay muchos)
        // El usuario quiere 4 elementos visibles (3 temas + 1 botón más)
        
        foreach($templates as $tpl): 
            $name = $tpl['nombre'];
            $color = $tpl['color_brand'];
            
            // Lógica de Selección
            $savedTemplate = $proforma['template'] ?? null;
            if ($savedTemplate) {
                $isChecked = ($name === $savedTemplate);
            } else {
                $isChecked = ($tpl['es_default'] == 1);
            }
            
            // Icono lógica simple
            $icon = 'ph-paint-brush';
            if(strpos($name, 'blue') !== false) $icon = 'ph-buildings';
            if(strpos($name, 'orange') !== false) $icon = 'ph-star';
            if(strpos($name, 'simple') !== false) $icon = 'ph-printer';
        ?>
        <label class="cursor-pointer relative group">
          <input type="radio" name="template" value="<?= $name ?>" <?= $isChecked ? 'checked' : '' ?> class="peer sr-only">
          
          <div class="p-2 rounded-xl border-2 border-slate-200 peer-checked:border-[<?= $color ?>] peer-checked:bg-opacity-10 transition-all duration-300 hover:border-[<?= $color ?>] text-center"
               style="--tw-peer-checked-border-color: <?= $color ?>; --tw-peer-checked-bg: <?= $color ?>18;">
            
            <div class="h-9 w-9 rounded-full mx-auto mb-2 flex items-center justify-center text-white shadow-md relative"
                 style="background-color: <?= $color ?>;">
              <i class="ph-bold <?= $icon ?> text-base"></i>
            </div>
            
            <span class="text-xs font-bold text-slate-700 block capitalize truncate"><?= $name ?></span>
          </div>
        </label>
        <?php endforeach; ?>
        
        <!-- Botón "+ Mas" (Cargar más temas) -->
        <button type="button" onclick="loadMoreThemes()" class="p-2 rounded-xl border-2 border-dashed border-slate-300 hover:border-purple-500 hover:bg-purple-50 transition-all text-center flex flex-col items-center justify-center gap-1 group h-full">
            <div class="h-7 w-7 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 group-hover:text-purple-600 transition-colors">
                <i class="ph-bold ph-plus"></i>
            </div>
            <span class="text-[10px] font-bold text-slate-500 group-hover:text-purple-600">Más</span>
        </button>

      </div>
    </div>

<script>
function openThemeConfigModal() {
    window.location.href = '/pdf-templates';
}

function loadMoreThemes() {
    const btn = document.querySelector('button[onclick="loadMoreThemes()"]');
    const originalContent = btn.innerHTML;
    
    // Feedback visual
    btn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-purple-600"></div>';
    
    // Contar cuantos hay actualmente para calcular offset
    const currentCount = document.querySelectorAll('input[name="template"]').length;
    
    fetch('/pdf-templates/api/list?offset=' + currentCount)
        .then(response => response.json())
        .then(data => {
            if(data && data.length > 0) {
                const grid = btn.parentElement; 
                
                data.forEach(tpl => {
                   // Crear nodo
                   const label = document.createElement('label');
                   label.className = "cursor-pointer relative group fade-in";
                   
                   const icon = tpl.nombre.includes('blue') ? 'ph-buildings' : 
                               (tpl.nombre.includes('orange') ? 'ph-star' : 'ph-paint-brush');
                   
                   label.innerHTML = `
                      <input type="radio" name="template" value="${tpl.nombre}" class="peer sr-only">
                      <div class="p-2 rounded-xl border-2 border-slate-200 peer-checked:border-[${tpl.color_brand}] peer-checked:bg-opacity-10 transition-all duration-300 hover:border-[${tpl.color_brand}] text-center"
                           style="--tw-peer-checked-border-color: ${tpl.color_brand}; --tw-peer-checked-bg: ${tpl.color_brand}18;">
                        <div class="h-9 w-9 rounded-full mx-auto mb-2 flex items-center justify-center text-white shadow-md relative"
                             style="background-color: ${tpl.color_brand};">
                          <i class="ph-bold ${icon} text-base"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-700 block capitalize truncate">${tpl.display_name || tpl.nombre}</span>
                      </div>
                   `;
                   // Insertar antes del botón
                   grid.insertBefore(label, btn);
                });
                
                // Si trajo menos de 4, es que ya no hay más
                if(data.length < 4) {
                    btn.remove();
                } else {
                     btn.innerHTML = originalContent;
                }
            } else {
                btn.remove(); // No hay más
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalContent;
            // Silent error or small toast
        });
}
</script>

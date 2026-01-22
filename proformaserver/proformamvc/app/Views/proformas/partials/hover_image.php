<?php if ($primerImagen): ?>
    <!-- Imagen Flotante al Hover de la CARD -->
    <!-- Ajusta aquí 'top', 'right', 'w-32', 'h-32' para cambiar posición y tamaño -->
    <div class="absolute top-7 left-2 hidden group-hover:block z-[100] animate-scale-in pointer-events-none transition-all duration-300">
        <img src="<?= asset($primerImagen) ?>" 
             class="w-auto h-16 rounded-xl border-4 border-white shadow-2xl object-contain bg-white -rotate-3">
    </div>
<?php endif; ?>

<!-- HEADER DE MARCA (Desktop / Full Width / Fuera del Contenedor) -->
<header id="desktopAppHeader" class="hidden md:block fixed top-0 left-0 right-0 z-50 transition-all duration-300 glass-panel border-b-0 h-24">
    <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between relative">
        
        <!-- Izquierda: Logo + Back -->
        <div class="flex items-center gap-4 z-20 cursor-pointer" onclick="window.history.back()">
            <div class="w-10 h-10 rounded-full bg-slate-800/50 flex items-center justify-center hover:bg-indigo-600 transition-colors group">
                <i class="ph-bold ph-arrow-left text-slate-300 group-hover:text-white"></i>
            </div>
            <img src="<?= asset($appLogo) ?>" class="h-12 w-auto object-contain" alt="Logo">
        </div>

        <!-- Centro: Título de Marca -->
        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none">
             <h1 class="font-black text-4xl tracking-tighter uppercase leading-none">
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-indigo-300 to-purple-400 animate-gradient-x">
                    <?= htmlspecialchars($appName) ?>
                 </span>
            </h1>
        </div>
        
     
    </div>
</header>

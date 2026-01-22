<!-- ====================================================================================
     VISTA PC / ESCRITORIO (SOLO VISIBLE EN DESKTOP)
     ==================================================================================== -->
<header class="hidden md:block fixed top-0 left-0 right-0 z-50 glass-panel border-b-0 h-24 transition-all">
    <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between relative">
        
        <!-- Izquierda: Logo + Subtítulo "Catálogo General" -->
        <div class="flex items-center gap-4 z-20">
            <img src="<?= asset($appLogo) ?>" class="h-12 w-auto object-contain" alt="Logo">
            <div class="w-px h-8 bg-white/10"></div>
            <div class="flex flex-col leading-none">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Catálogo</span>
                <span class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">General</span>
            </div>
        </div>

        <!-- Centro: Título Flotante (Marca) -->
        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10 pointer-events-none">
             <h1 class="font-black text-4xl tracking-tighter uppercase leading-none">
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-indigo-300 to-purple-400 animate-gradient-x">
                    <?= htmlspecialchars($appName) ?>
                 </span>
            </h1>
        </div>
        
        <!-- Derecha: Buscador Desktop -->
        <div class="w-96 relative group z-20">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph-bold ph-magnifying-glass text-slate-500 group-focus-within:text-blue-400 transition-colors"></i>
            </div>
            <input type="text" id="desktopSearch" 
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-800 text-slate-200 placeholder-slate-500 transition-all font-sans" 
                   placeholder="Buscar modelo, nombre...">
        </div>
    </div>
</header>

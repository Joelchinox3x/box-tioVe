<!-- ====================================================================================
     VISTA CÉLULAR (SOLO VISIBLE EN MÓVIL)
     ==================================================================================== -->
<header class="md:hidden fixed top-0 left-0 right-0 z-50 glass-panel border-b-0 h-20 transition-all">
    <div class="px-4 h-full flex items-center justify-between">
        
        <!-- Logo + Título Grande (Mobile) -->
        <div class="flex items-center gap-3 overflow-hidden">
            <img src="<?= asset($appLogo) ?>" class="h-10 w-auto object-contain flex-shrink-0" alt="Logo">
            <div class="w-px h-6 bg-white/10 flex-shrink-0"></div>
            <h1 class="font-black text-3xl tracking-tighter uppercase leading-none truncate">
                 <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-indigo-300 to-purple-400 animate-gradient-x">
                    <?= htmlspecialchars($appName) ?>
                 </span>
            </h1>
        </div>

        <!-- Botón Buscar Mobile -->
        <button onclick="document.getElementById('mobileSearchPanel').classList.toggle('hidden')" class="p-2 text-slate-300 bg-slate-800/50 rounded-lg flex-shrink-0">
            <i class="ph-bold ph-magnifying-glass text-xl"></i>
        </button>
    </div>
    
    <!-- Panel Buscador Desplegable Mobile -->
    <div id="mobileSearchPanel" class="hidden p-4 border-t border-white/5 bg-slate-900/95 backdrop-blur-xl absolute w-full left-0 top-20 shadow-2xl">
        <div class="relative">
            <i class="ph-bold ph-magnifying-glass absolute left-3 top-3.5 text-slate-500"></i>
            <input type="text" id="mobileSearch" placeholder="Buscar por nombre..." 
                class="w-full bg-slate-800 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 text-white placeholder-slate-500">
        </div>
    </div>
</header>

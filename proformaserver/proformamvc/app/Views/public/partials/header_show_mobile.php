<!-- HEADER DE MARCA (Móvil / Inmersivo / Dentro del Contenedor) -->
<header id="mobileAppHeader" class="md:hidden fixed top-0 w-full max-w-lg z-50 transition-all duration-500 ease-in-out bg-transparent border-b border-transparent">
    <div class="relative flex items-center justify-between px-4 h-20 max-w-full">
        
        <!-- 1. Logo (Izquierda) -->
        <div class="flex-shrink-0 z-20">
            <img src="<?= asset($appLogo) ?>" alt="Logo" class="h-14 w-auto object-contain hover:scale-105 transition-transform duration-300 drop-shadow-sm">
        </div>

        <!-- 2. Título Central (Móvil) -->
        <div class="flex-1 ml-3 z-10 text-left pointer-events-none">
            <h1 class="font-black text-3xl tracking-tighter uppercase leading-none truncate" style="filter: drop-shadow(0 2px 4px rgba(79, 70, 229, 0.2));">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-700 dark:from-blue-400 dark:via-indigo-300 dark:to-purple-400 animate-gradient-x">
                    <?= htmlspecialchars($appName) ?>
                </span>
            </h1>
        </div>

        <!-- 3. Accesos (Derecha) -->
        <div class="flex-shrink-0 z-20 flex items-center gap-2">
             <!-- Espacio vacio para mantener layout o futuros controles -->
        </div>
    </div>
</header>

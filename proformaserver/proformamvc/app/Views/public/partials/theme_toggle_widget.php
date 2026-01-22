
<!-- PESTAÑA LATERAL (Corner - Slide Left) -->
<!-- Visible en Móvil y Desktop (Adaptable) -->
<div id="themeTab" 
     class="fixed top-20 right-0 z-[100] h-12 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md rounded-l-2xl shadow-lg border-y border-l border-slate-200 dark:border-white/10 transition-all duration-300 ease-out cursor-pointer w-3 hover:w-16 opacity-60 hover:opacity-100 flex items-center justify-start overflow-hidden group"
     onclick="toggleThemeTab(event)"
     onmouseenter="openThemeTab()"
     onmouseleave="closeThemeTab()">
    
    <!-- Indicador Visual (Pequeña barra vertical) -->
    <div class="absolute right-1 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-slate-400/50 rounded-full transition-opacity group-[.open]:opacity-0"></div>

    <!-- Contenido Oculto (Botón) -->
    <div id="themeTabContent" class="absolute right-2 opacity-0 group-[.open]:opacity-100 transition-all duration-300 transform translate-x-10 group-[.open]:translate-x-0 pointer-events-none group-[.open]:pointer-events-auto flex items-center justify-center w-full h-full">
        <button onclick="toggleTheme(); event.stopPropagation();" class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-slate-700 text-orange-400 dark:text-yellow-400 flex items-center justify-center shadow-sm border border-indigo-100 dark:border-white/5 hover:scale-110 transition-transform">
            <i class="ph-fill ph-sun text-lg dark:hidden"></i>
            <i class="ph-fill ph-moon-stars text-lg hidden dark:block"></i>
        </button>
    </div>
</div>

<script>
    // Lógica Compartida del Theme Toggle
    function toggleThemeTab(e) {
        const tab = document.getElementById('themeTab');
        e.stopPropagation(); 
        if (tab.classList.contains('open')) {
            closeThemeTab();
        } else {
            openThemeTab();
        }
    }

    function openThemeTab() {
        const tab = document.getElementById('themeTab');
        if(tab && !tab.classList.contains('open')) {
            tab.classList.add('open');
            tab.classList.remove('w-3');
            tab.classList.add('w-16'); // Expandir
            tab.classList.remove('opacity-60');
            tab.classList.add('opacity-100');
        }
    }

    function closeThemeTab() {
        const tab = document.getElementById('themeTab');
        if(tab) {
            tab.classList.remove('open');
            tab.classList.remove('w-16');
            tab.classList.add('w-3'); // Colapsar
            tab.classList.remove('opacity-100');
            tab.classList.add('opacity-60');
        }
    }

    // Lógica de Tema (Dark Default)
    function toggleTheme() {
        const html = document.documentElement;
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            html.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }

    // Cargar preferencia al inicio (Dark por defecto)
    (function initTheme() {
        if (localStorage.theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    })();

    // Cerrar al click fuera
    document.addEventListener('click', (e) => {
        const tab = document.getElementById('themeTab');
        if (tab && tab.classList.contains('open') && !tab.contains(e.target)) closeThemeTab();
    });
</script>

    <!-- WHATSAPP FLOTANTE (Fixed Bubble + Panel) -->
    <!-- Positioned higher for both Mobile (bottom-36) and Desktop (md:bottom-12) as requested -->
    <div id="whatsappWidget" class="fixed bottom-40 md:bottom-20 right-6 md:right-8 z-[90] group" onmouseleave="closeContact()">
        
        <!-- Panel Expandido (Absolute Positioned to avoid layout shift) -->
        <!-- right-full pushes it to the left of the button. mr-4 adds spacing. top-1/2 centers it. -->
        <div id="contactPanel" class="hidden absolute right-full mr-4 top-1/2 -translate-y-1/2 bg-slate-900 rounded-2xl shadow-2xl border border-white/10 p-4 w-64 animate-fade-in origin-right bg-opacity-95 backdrop-blur-xl">
            <!-- Flechita -->
            <div class="absolute top-1/2 -right-2 w-4 h-4 bg-slate-900 transform -translate-y-1/2 rotate-45 border-t border-r border-white/10"></div>
            
            <div class="flex items-center gap-3 mb-3 border-b border-white/5 pb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                    <i class="ph-bold ph-user text-lg"></i>
                </div>
                <div class="leading-tight min-w-0">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate">Asesor Comercial</p>
                    <?php 
                        // Asegurar acceso a Helpers si no están cargados
                        if (!class_exists('\App\Helpers\SettingsHelper')) {
                            require_once __DIR__ . '/../../../../Helpers/SettingsHelper.php';
                        }
                        $managerName = \App\Helpers\SettingsHelper::getManagerName();
                        $managerPhone = \App\Helpers\SettingsHelper::getManagerWhatsapp();
                    ?>
                    <p class="text-xs font-extra-bold text-white truncate"><?= htmlspecialchars($managerName) ?></p>
                </div>
            </div>
            
            <div class="bg-slate-800/50 p-2.5 rounded-xl border border-white/5 flex items-center gap-2 group/copy hover:border-green-500/30 transition-colors cursor-pointer" onclick="copyNumber(this.querySelector('button'))">
                <i class="ph-fill ph-whatsapp-logo text-green-500 text-lg"></i>
                <span class="text-xs font-bold text-slate-200 flex-1 font-mono tracking-tight leading-none"><?= $managerPhone ?></span>
                <button class="w-6 h-6 flex items-center justify-center bg-slate-700 rounded-md text-slate-400 hover:text-green-400 transition-all">
                    <i class="ph-bold ph-copy text-[10px]"></i>
                </button>
            </div>

            <?php 
                $urlActual = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $msjFlotante = "Hola, vengo del Catálogo Web. Quisiera consultar por uno de sus equipos. " . $urlActual;
            ?>
            <a href="https://wa.me/51<?= $managerPhone ?>?text=<?= urlencode($msjFlotante) ?>" target="_blank" class="mt-3 flex items-center justify-center w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-xs shadow-lg shadow-green-900/40 transition-all">
                Iniciar Chat
            </a>
        </div>

        <!-- Trigger Button (Lottie) -->
        <button onclick="toggleContact()" class="w-14 h-14 md:w-16 md:h-16 bg-slate-900 rounded-full flex items-center justify-center shadow-xl shadow-green-900/20 border border-white/10 hover:scale-110 active:scale-95 transition-all duration-300 relative group z-[91]">
            <div class="w-12 h-12 flex items-center justify-center">
                 <lottie-player src="<?= asset('assets/lottie/Whatsapp.json') ?>" background="transparent" speed="1" style="width: 48px; height: 48px;" loop autoplay></lottie-player>
            </div>
            <span class="absolute top-0 right-0 flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-slate-900"></span>
            </span>
        </button>
    </div>

    <script>
        // --- WhatsApp Widget Logic ---
        function toggleContact() {
            const panel = document.getElementById('contactPanel');
            if (panel) panel.classList.toggle('hidden');
        }

        function closeContact() {
            const panel = document.getElementById('contactPanel');
            if (panel) panel.classList.add('hidden');
        }

        function copyNumber(btn) {
            const number = "<?= $managerPhone ?>";
            navigator.clipboard.writeText(number).then(() => {
                showToast('Número copiado', 'success');
            });
        }
        
        // --- Shared Toast/Share Logic (if not defined elsewhere) ---
        // Ensure showToast wrapper exists
        if (typeof showToast !== 'function') {
            window.showToast = function(msg, type = 'success') {
                if (typeof mostrarToast === 'function') {
                    mostrarToast(msg, type);
                } else {
                    alert(msg);
                }
            };
        }

        // Close panel on scroll
        window.addEventListener('scroll', closeContact);
    </script>

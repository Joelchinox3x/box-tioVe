<?php
// app/Views/productos/public_show.php
// VISTA DE VENTA DE ALTO IMPACTO
// Optimizada para conversión y móviles
// Cargar helper de configuración
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

$moneda = $producto['moneda'] ?? 'PEN';
$simbolo = $moneda === 'USD' ? '$' : 'S/.';
$whatsapp = SettingsHelper::getManagerWhatsapp(); // Número de ventas dinámico
$nombreProducto = htmlspecialchars($producto['nombre']);
$urlActual = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Mensajes diferenciados
$msjConsulta = "Hola, me interesa el equipo *{$nombreProducto}*. ¿Me podrían dar más información? {$urlActual}";
$msjProforma = "Hola, necesito una *Proforma Formal* del equipo *{$nombreProducto}*. Mis datos son...";

// --- Lógica SEO (Open Graph / WhatsApp) ---
$seoImgs = is_string($producto['imagenes']) ? json_decode($producto['imagenes'], true) : $producto['imagenes'];
$seoImgs = $seoImgs ?? [];
$seoImgUrl = !empty($seoImgs[0]) ? asset($seoImgs[0]) : asset('assets/img/logo.png'); // Fallback a logo
// Asegurar URL absoluta para WhatsApp
if (strpos($seoImgUrl, 'http') !== 0) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $seoImgUrl = $protocol . $_SERVER['HTTP_HOST'] . $seoImgUrl;
}

$seoDesc = strip_tags($producto['descripcion'] ?? '');
$seoDesc = mb_substr($seoDesc, 0, 150) . '...';
if (empty($seoDesc)) $seoDesc = "Cotiza este equipo profesional con nosotros. Disponibilidad inmediata.";

$seoTitle = "{$nombreProducto} | " . ($moneda === 'USD' ? '$' : 'S/.') . number_format($producto['precio'], 0);
$appName = SettingsHelper::getAppName();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/partials/metadata.php'; ?>
    
    <style>
        /* Estilos Específicos de Show (Swiper) */
        .swiper-main { width: 100%; height: 50vh; background: transparent; }
        .swiper-main img { width: 100%; height: 100%; object-fit: contain; padding: 20px; }
        
        .swiper-thumbs { height: 100px; padding: 10px 0; background: #f8fafc; }
        .swiper-thumbs .swiper-slide { width: 80px !important; height: 80px !important; opacity: 0.6; transition: 0.3s; border-radius: 12px; overflow: hidden; border: 2px solid transparent; cursor: pointer; }
        .swiper-thumbs .swiper-slide-thumb-active { opacity: 1; border-color: #4f46e5; transform: scale(0.95); }
        .swiper-thumbs img { width: 100%; height: 100%; object-fit: cover; }

        /* Sombras suaves */
        .soft-shadow { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08); }
        .floating-bar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); box-shadow: 0 -5px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-gray-100 min-h-screen flex justify-center transition-colors duration-300 overflow-x-hidden">
    <!-- CONTENEDOR CENTRAL "APP VIEW" (Desktop Optimized) -->
    <!-- Expanded width for Desktop, Removed max-w-lg -->
    <div class="w-full max-w-[1600px] mx-auto bg-white dark:bg-slate-900 min-h-screen shadow-2xl relative transition-colors duration-300 overflow-visible p-6">

        <!-- PESTAÑA LATERAL (Corner - Slide Left) -->
        <div id="themeTab" 
             class="fixed top-0 right-0 z-[100] h-12 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md rounded-bl-2xl shadow-lg border-y border-l border-slate-200 dark:border-white/10 transition-all duration-300 ease-out cursor-pointer w-3 hover:w-6 opacity-60 hover:opacity-100 flex items-center justify-start overflow-hidden group"
             onclick="toggleThemeTab(event)"
             onmouseenter="openThemeTab()"
             onmouseleave="closeThemeTab()">
            
            <!-- Indicador Visual (Pequeña barra vertical) -->
            <div class="absolute right-1 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-slate-400/50 rounded-full transition-opacity group-[.open]:opacity-0"></div>

            <!-- Contenido Oculto (Botón) -->
            <div id="themeTabContent" class="absolute right-3 opacity-0 group-[.open]:opacity-100 transition-all duration-300 transform translate-x-10 group-[.open]:translate-x-0 pointer-events-none group-[.open]:pointer-events-auto">
                <button onclick="toggleTheme(); event.stopPropagation();" class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-slate-700 text-orange-400 dark:text-yellow-400 flex items-center justify-center shadow-sm border border-indigo-100 dark:border-white/5 hover:scale-110 transition-transform">
                    <i class="ph-fill ph-sun text-lg dark:hidden"></i>
                    <i class="ph-fill ph-moon-stars text-lg hidden dark:block"></i>
                </button>
            </div>
        </div>


        <!-- HEADER DE MARCA (Desktop) -->
        <?php include __DIR__ . '/partials/header_show_desktop.php'; ?>

        <!-- LAYOUT GRID DESKTOP (2 COLUMNS) -->
        <div class="grid grid-cols-12 gap-8 mt-24 items-start">
            
            <!-- LEFT COLUMN (Gallery + Details) -->
            <div class="col-span-12 lg:col-span-8 space-y-8">
                
                <!-- GALERÍA DE IMÁGENES -->
                <div class="relative bg-slate-50 dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-white/5" data-aos="fade-down" data-aos-duration="1000">
                    <?php if (!empty($producto['imagenes'])): ?>
                        <!-- Principal -->
                        <div class="swiper swiper-main mySwiper2 h-[500px] lg:h-[600px] bg-white dark:bg-slate-800/50"> 
                            <div class="swiper-wrapper">
                                <?php foreach ($producto['imagenes'] as $key => $img): ?>
                                <div class="swiper-slide cursor-zoom-in" onclick="openLightbox(<?= $key ?>)">
                                    <img src="<?= asset(htmlspecialchars($img)) ?>" loading="lazy" class="w-full h-full object-contain p-4 transition-transform duration-500 hover:scale-105">
                                    <div class="swiper-lazy-preloader"></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Miniaturas -->
                        <div thumbsSlider="" class="swiper swiper-thumbs mySwiper px-4 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-white/5">
                            <div class="swiper-wrapper">
                                <?php foreach ($producto['imagenes'] as $img): ?>
                                <div class="swiper-slide rounded-xl overflow-hidden border-2 border-transparent hover:border-indigo-500 transition-all cursor-pointer">
                                    <img src="<?= asset(htmlspecialchars($img)) ?>" class="w-full h-full object-cover">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="h-[500px] flex items-center justify-center bg-indigo-50">
                            <i class="ph-duotone ph-image text-5xl text-indigo-200"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TABS DE INFORMACIÓN (Specs, Desc, Etc) -->
                <div class="bg-white dark:bg-slate-800/20 rounded-2xl border border-slate-200 dark:border-white/5 p-8">
                    
                    <!-- Descripción -->
                    <?php if ($producto['descripcion']): ?>
                    <div class="mb-10">
                        <h3 class="font-bold text-slate-900 dark:text-white text-2xl mb-4 border-b border-slate-200 dark:border-white/10 pb-2">Detalles del Equipo</h3>
                        <div class="text-slate-600 dark:text-slate-300 text-lg leading-relaxed space-y-4 text-justify">
                            <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Especificaciones Completas -->
                    <?php 
                        $specs = json_decode($producto['especificaciones'] ?? '[]', true); 
                        if(!empty($specs)):
                    ?>
                    <div class="mb-10">
                        <h3 class="font-bold text-slate-900 dark:text-white text-2xl mb-6 border-b border-slate-200 dark:border-white/10 pb-2">Especificaciones</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($specs as $index => $spec): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5 hover:border-indigo-200 transition-colors">
                                <span class="font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                    <i class="ph-fill ph-caret-right text-xs text-indigo-400"></i>
                                    <?= htmlspecialchars($spec['atributo']) ?>
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($spec['valor']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Características Técnicas Extra -->
                    <?php if (!empty($producto['specs'])): ?>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-2xl mb-6 border-b border-slate-200 dark:border-white/10 pb-2">Características Técnicas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($producto['specs'] as $index => $spec): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5">
                                <span class="text-slate-500 dark:text-slate-400 font-medium"><?= htmlspecialchars($spec['atributo']) ?></span>
                                <span class="text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($spec['valor']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                
                <!-- RELATED PRODUCTS CAROUSEL -->
                 <?php if (!empty($otrosProductos)): ?>
                <div class="mt-12">
                    <h3 class="font-bold text-slate-900 dark:text-white text-2xl mb-6">También te puede interesar</h3>
                    <div class="swiper mySwiper3 !overflow-visible px-1">
                        <div class="swiper-wrapper">
                            <?php foreach ($otrosProductos as $prod): ?>
                                <?php
                                    $imgs = json_decode($prod['imagenes'] ?? '[]', true);
                                    $imgUrl = !empty($imgs[0]) ? asset($imgs[0]) : ''; 
                                    $publicUrl = url('/p/producto/' . ($prod['token'] ?? ''));
                                ?>
                                <div class="swiper-slide h-full">
                                    <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-white/5 rounded-2xl p-4 relative group h-full flex flex-col hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-xl transition-all duration-300">
                                        <a href="<?= $publicUrl ?>" class="block flex-1 group/link">
                                            <div class="aspect-square rounded-xl bg-slate-50 dark:bg-slate-700/50 mb-4 overflow-hidden relative">
                                                <?php if(!empty($imgUrl)): ?>
                                                    <img src="<?= $imgUrl ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="ph-duotone ph-image text-4xl"></i></div>
                                                <?php endif; ?>
                                                <div class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-2 py-1 rounded-lg">
                                                    <?= $prod['moneda'] ?> <?= number_format($prod['precio'], 0) ?>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <h4 class="font-bold text-slate-800 dark:text-white leading-tight mb-1 group-hover/link:text-indigo-400 transition-colors line-clamp-3 min-h-[2.5rem]"><?= htmlspecialchars($prod['nombre']) ?></h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium truncate"><?= htmlspecialchars($prod['modelo'] ?? 'Sin modelo') ?></p>
                                            </div>
                                        </a>
                                        <button onclick="compartirProducto('<?= addslashes($prod['nombre']) ?>', '<?= $publicUrl ?>')" class="w-full py-2 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 rounded-xl font-bold text-xs transition-colors flex items-center justify-center gap-2">
                                            <i class="ph-bold ph-share-network"></i> Compartir
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- RIGHT COLUMN (Sticky Buy Box + Info) -->
            <div class="col-span-12 lg:col-span-4 relative">
                <div class="sticky top-28 space-y-6">
                    
                    <!-- BUY BOX CARD -->
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-white/5">
                        <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold uppercase tracking-wider rounded-md mb-4">Nuevo Ingreso</span>
                        
                        <h1 class="text-3xl font-black text-slate-900 dark:text-white leading-tight mb-2"><?= $nombreProducto ?></h1>
                        <p class="text-sm font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 border-b border-slate-100 dark:border-white/5 pb-4"><?= htmlspecialchars($producto['modelo'] ?? 'Sin Modelo') ?></p>

                        <!-- Price Large -->
                        <div class="mb-8">
                            <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Precio de Lista</span>
                            <div class="flex items-end gap-2">
                                <span class="text-2xl font-bold text-slate-500 dark:text-slate-400 mb-1"><?= $simbolo ?></span>
                                <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tight"><?= number_format($producto['precio'], 2) ?></span>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-1 rounded-full">Disponible</span>
                                <span class="text-xs font-medium text-slate-400">Stock Limitado</span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col gap-3 mb-8">
                            <a href="https://wa.me/51<?= $whatsapp ?>?text=<?= urlencode($msjConsulta) ?>" target="_blank" 
                               class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50 transition transform hover:-translate-y-1 flex items-center justify-center gap-3 font-bold text-lg">
                                <i class="ph-bold ph-whatsapp-logo text-2xl"></i>
                                Consultar Ahora
                            </a>
                            <button onclick="openProformaModal()" class="w-full py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-white/10 hover:border-indigo-500 text-slate-700 dark:text-slate-200 rounded-xl transition flex items-center justify-center gap-2 font-bold hover:shadow-md">
                                <i class="ph-bold ph-file-text text-lg"></i>
                                Solicitar Proforma Formal
                            </button>
                        </div>
                        
                        <!-- Garantías Short -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-seal-check text-xl"></i>
                                </div>
                                <div class="leading-none">
                                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Estado</span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Nuevo</span>
                                </div>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-shield-check text-xl"></i>
                                </div>
                                <div class="leading-none">
                                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Garantía</span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">12 Meses</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Lottie Trust Badges -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col items-center justify-center p-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-white/5 text-center">
                            <div class="w-20 h-20 mb-2">
                                <lottie-player src="<?= asset('assets/lottie/calidad.json') ?>" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Calidad Grantizada</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-white/5 text-center">
                            <div class="w-20 h-20 mb-2">
                                <lottie-player src="<?= asset('assets/lottie/delivery.json') ?>" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Envíos Nacionales</span>
                        </div>
                    </div>

                    <!-- WHATSAPP WIDGET DESKTOP POSITION (In column or fixed bottom) -->
                    <!-- Usually fixed is better, but since this is desktop-only file, we can keep the fixed widget we already have or integrate it. 
                         Let's keep the user's preference of not deleting functions. The fixed widget is good. -->

                </div>
            </div>

        </div> 
        <!-- End Grid -->
        
        <!-- WHATSAPP FLOTANTE removed (Handled by footer_cta.php) -->


        <!-- Partial Footer CTA (WhatsApp + Scripts) -->
        <?php include __DIR__ . '/partials/footer_cta.php'; ?>

    </div>

    <!-- MODAL SOLICITAR PROFORMA (Partial) -->
    <?php include __DIR__ . '/partials/proforma_modal.php'; ?>
    
    <!-- Contenedor de notificaciones -->
    <!-- top-0 para pegarlo arriba, z-index alto -->
    <div id="notificationContainer" class="fixed top-2 right-2 sm:right-4 z-[100] mt-2 space-y-2 max-w-xs transition-all pointer-events-none px-2 sm:px-0"></div>

    <style>
        /* Estilos para el Sistema de Notificaciones */
        @keyframes slide-in {
          from { transform: translateY(-100%); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slide-out {
          from { transform: translateY(0); opacity: 1; }
          to { transform: translateY(-100%); opacity: 0; }
        }
        
        /* Animación Shake (Temblor) para error */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .animate-shake { animation: shake 0.3s ease-in-out; }

        .animate-slide-in { animation: slide-in 0.3s ease-out forwards; }
        .animate-slide-out { animation: slide-out 0.3s ease-in forwards; }
        #notificationContainer { pointer-events: none; }
        #notificationContainer > * { pointer-events: auto; }
    </style>

    <!-- Lightbox Modal (Swiper) -->
    <div id="lightboxModal" class="fixed inset-0 z-[110] bg-black bg-opacity-95 hidden flex-col items-center justify-center transition-all duration-300">
        
        <!-- Close Button -->
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-indigo-400 transition-colors z-[120] p-2 bg-black/50 rounded-full">
            <i class="ph-bold ph-x text-2xl"></i>
        </button>

        <!-- Swiper Container -->
        <div class="swiper swiper-lightbox w-full h-full max-h-screen">
            <div class="swiper-wrapper items-center">
                <?php foreach ($producto['imagenes'] as $img): ?>
                <div class="swiper-slide flex items-center justify-center p-2 sm:p-6 bg-transparent">
                    <div class="swiper-zoom-container w-full h-full flex items-center justify-center">
                        <img src="<?= asset(htmlspecialchars($img)) ?>" class="max-h-full max-w-full object-contain shadow-2xl rounded-lg select-none" alt="Full view">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Nav Beans -->
            <div class="swiper-button-next text-white/70 hover:text-white transition-colors after:text-2xl sm:after:text-4xl"></div>
            <div class="swiper-button-prev text-white/70 hover:text-white transition-colors after:text-2xl sm:after:text-4xl"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Variables Globales para JS -->
    <script>
        const MANAGER_NAME = "<?= addslashes(SettingsHelper::getManagerName()) ?>";
        const MANAGER_PHONE = "<?= $whatsapp ?>";
        const PRODUCT_NAME = "<?= addslashes($producto['nombre']) ?>";
        const PRODUCT_ORIGIN = "<?= addslashes($producto['nombre']) ?>";

        // Toggle functions removed (Handled by footer_cta.php)

        // Lógica de Pestaña Lateral (Slide Left)
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
                tab.style.width = '60px'; // Expandir hacia la izquierda
                tab.classList.remove('opacity-60');
                tab.classList.add('opacity-100');
            }
        }

        function closeThemeTab() {
            const tab = document.getElementById('themeTab');
            if(tab) {
                tab.classList.remove('open');
                tab.style.width = ''; // Volver a w-3
                tab.classList.remove('opacity-100');
                tab.classList.add('opacity-60');
            }
        }

        // Cerrar al hacer click fuera, scroll o mouseleave
        document.addEventListener('click', (e) => {
            // Cerrar Theme Tab
            const tab = document.getElementById('themeTab');
            if (tab && tab.classList.contains('open') && !tab.contains(e.target)) closeThemeTab();

            // Cerrar Contact Panel
            const waWidget = document.getElementById('whatsappWidget');
            if (waWidget && !waWidget.contains(e.target)) closeContact();
        });

        window.addEventListener('scroll', () => {
             closeThemeTab();
             closeContact();
             
             // Efecto Header: Desaparecer si bajamos de la foto (Hero)
             const header = document.getElementById('mainAppHeader');
             const threshold = 300; // Punto medio de la foto grande

             if (window.scrollY > threshold) {
                 // Si bajamos mucho, escondemos el header por completo
                 header.classList.add('-translate-y-full');
                 // Limpiamos estilos de fondo por si acaso
                 header.classList.remove('bg-white/95', 'dark:bg-slate-900/90', 'backdrop-blur-md', 'shadow-sm', 'border-slate-100', 'dark:border-white/5');
             } else {
                 // Si estamos arriba, lo mostramos (Transparente)
                 header.classList.remove('-translate-y-full');
                 header.classList.add('bg-transparent', 'border-transparent');
                 // Asegurar que no tenga fondo sólido
                 header.classList.remove('bg-white/95', 'dark:bg-slate-900/90', 'backdrop-blur-md', 'shadow-sm', 'border-slate-100', 'dark:border-white/5');
             }
        }, {passive: true});

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

        // Cargar preferencia (Dark por defecto)
        if (localStorage.theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark'); // Default
        }

        // Inicializar Swiper de Más Equipos
        document.addEventListener('DOMContentLoaded', () => {
             const swiper3 = new Swiper(".mySwiper3", {
                slidesPerView: 2.2,
                spaceBetween: 12,
                grabCursor: true,
                freeMode: true,
                breakpoints: {
                    640: {
                        slidesPerView: 3.2,
                        spaceBetween: 16
                    },
                    1024: {
                        slidesPerView: 4.2,
                        spaceBetween: 20
                    }
                }
            });
        });

        // Lógica de Compartir (Público)
        window.MANAGER_NAME = "<?= addslashes(SettingsHelper::getManagerName()) ?>";

        function compartirProducto(nombre, url) {
            // Mensaje neutral para compartir entre amigos/público
            const shareText = `Te comparto la ficha técnica de: ${nombre}.\n\nVer aquí:`;

            if (navigator.share) {
                navigator.share({
                    title: nombre,
                    text: shareText,
                    url: url
                }).catch(err => {
                    if (err.name !== 'AbortError') copyToClipboard(url);
                });
            } else {
                // Desktop: Abrir WhatsApp Web con el texto prellenado
                const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + " " + url)}`;
                window.open(whatsappUrl, '_blank');
            }
        }

        function copyToClipboard(text) {
             // Fallback simple si no hay notificaciones
             navigator.clipboard.writeText(text).then(() => {
                 alert('Enlace copiado al portapapeles');
             }).catch(err => {
                 prompt("Copia este enlace:", text);
             });
        }
    </script>
    
    <!-- Lógica de Interacción Principal -->
    <script src="<?= asset('js/productos/landing_logic.js') ?>"></script>
</body>
</html>

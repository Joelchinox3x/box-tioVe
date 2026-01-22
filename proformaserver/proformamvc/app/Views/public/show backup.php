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
$msjConsulta = "Hola, me interesa el equipo *{$nombreProducto}*. ¿Me podrían dar más información?";
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
        
        .swiper-thumbs { height: 80px; padding: 10px 0; background: #f8fafc; }
        .swiper-thumbs .swiper-slide { width: 80px; height: 100%; opacity: 0.6; transition: 0.3s; border-radius: 12px; overflow: hidden; border: 2px solid transparent; cursor: pointer; }
        .swiper-thumbs .swiper-slide-thumb-active { opacity: 1; border-color: #4f46e5; transform: scale(0.95); }
        .swiper-thumbs img { width: 100%; height: 100%; object-fit: cover; }

        /* Sombras suaves */
        .soft-shadow { box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08); }
        .floating-bar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); box-shadow: 0 -5px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-gray-100 min-h-screen flex justify-center transition-colors duration-300 overflow-x-hidden">
    <!-- CONTENEDOR CENTRAL "APP VIEW" -->
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 min-h-screen shadow-2xl relative transition-colors duration-300 overflow-hidden">

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


        <!-- HEADER DE MARCA (Inmersivo) -->
        <header id="mainAppHeader" class="fixed top-0 w-full max-w-lg z-50 transition-all duration-500 ease-in-out bg-transparent border-b border-transparent">
            <div class="relative flex items-center justify-between px-4 h-20 max-w-full">
                
                <!-- 1. Logo (Izquierda) -->
                <div class="flex-shrink-0 z-20">
                    <img src="<?= asset($appLogo) ?>" alt="Logo" class="h-14 md:h-16 w-auto object-contain hover:scale-105 transition-transform duration-300 drop-shadow-sm">
                </div>

                <!-- 2. Título Central (Híbrido: Flex en Móvil / Absoluto en Desktop) -->
                <!-- Móvil: Se pone al lado del logo (pleno ancho disponible) -->
                <!-- Desktop: Se centra absolutamente para estética premium -->
                <div class="flex-1 ml-3 z-10 md:absolute md:left-1/2 md:top-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:ml-0 text-left md:text-center pointer-events-none">
                    <h1 class="font-black text-3xl md:text-5xl tracking-tighter uppercase leading-none truncate md:overflow-visible" style="filter: drop-shadow(0 2px 4px rgba(79, 70, 229, 0.2));">
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



        <!-- GALERÍA DE IMÁGENES (DOBLE TIRA) -->
        <div class="relative bg-slate-50 dark:bg-slate-900 pb-2 border-b border-slate-300 dark:border-white/5" data-aos="fade-down" data-aos-duration="1000">
            <?php if (!empty($producto['imagenes'])): ?>
                <!-- Principal -->
                <div class="swiper swiper-main mySwiper2 px-4 pt-2 pb-6"> <!-- Added padding for borders/shadows -->
                    <div class="swiper-wrapper">
                        <?php foreach ($producto['imagenes'] as $key => $img): ?>
                        <div class="swiper-slide bg-indigo-100 dark:bg-slate-800 rounded-2xl border-2 border-indigo-200 dark:border-white/5 overflow-hidden shadow-sm relative cursor-zoom-in" onclick="openLightbox(<?= $key ?>)">
                            <img src="<?= asset(htmlspecialchars($img)) ?>" loading="lazy" class="object-contain w-full h-full p-1">
                            <div class="swiper-lazy-preloader"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Miniaturas -->
                <div thumbsSlider="" class="swiper swiper-thumbs mySwiper px-4 pb-2 dark:bg-slate-900 transition-colors" data-aos="fade-up" data-aos-delay="200">
                    <div class="swiper-wrapper">
                        <?php foreach ($producto['imagenes'] as $img): ?>
                        <div class="swiper-slide bg-emerald-100 dark:bg-slate-800 rounded-xl overflow-hidden border-2 border-emerald-400 dark:border-white/10 transition-all">
                            <img src="<?= asset(htmlspecialchars($img)) ?>" class="object-cover w-full h-full mix-blend-multiply dark:mix-blend-normal">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="h-[40vh] flex items-center justify-center bg-indigo-50">
                    <i class="ph-duotone ph-image text-4xl text-indigo-100"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- CONTENIDO DE VENTA -->
        <main class="px-5 mt-4">
            
            <!-- Título y Precio -->
            <div class="flex items-start justify-between mb-4">
                <div>
                    <span class="inline-block px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-[10px] font-bold uppercase tracking-wider rounded-md mb-2">Nuevo Ingreso</span>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight mb-1"><?= $nombreProducto ?></h1>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"><?= htmlspecialchars($producto['modelo'] ?? 'Sin Modelo') ?></p>
                </div>
            </div>

            <!-- Precio Card -->
            <div class="bg-slate-50 dark:bg-slate-800/50 dark:backdrop-blur-md rounded-2xl p-4 mb-6 border border-slate-100 dark:border-white/5 flex items-center justify-between">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">Precio de Lista</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm font-bold text-slate-500 dark:text-slate-400"><?= $simbolo ?></span>
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight"><?= number_format($producto['precio'], 2) ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-full mb-1">Disponible</span>
                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">Stock Limitado</span>
                </div>
            </div>

            <!-- Características -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <!-- Estado -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="ph-bold ph-seal-check text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Estado</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Nuevo / Sellado</span>
                    </div>
                </div>
                <!-- Garantía -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <i class="ph-bold ph-shield-check text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Garantía</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">12 Meses</span>
                    </div>
                </div>
            </div>

            <!-- Detalles Técnicos -->
            <?php 
                $specs = json_decode($producto['especificaciones'] ?? '[]', true); 
                if(!empty($specs)):
            ?>
            <div class="mb-6 animate-fade-in" data-aos="fade-up">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3">Especificaciones</h3>
                <div class="bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-slate-100 dark:border-white/5 overflow-hidden">
                    <?php foreach($specs as $index => $spec): ?>
                    <div class="flex items-center justify-between p-3 border-b border-slate-200 dark:border-white/5 last:border-0 hover:bg-white dark:hover:bg-slate-800/50 transition-colors">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <i class="ph-fill ph-caret-right text-[10px] text-indigo-400"></i>
                            <?= htmlspecialchars($spec['atributo']) ?>
                        </span>
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-200 text-right"><?= htmlspecialchars($spec['valor']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Descripción -->
            <?php if ($producto['descripcion']): ?>
            <div class="mb-8">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-3">Detalles del Equipo</h3>
                <div class="text-slate-600 dark:text-slate-300 text-[15px] leading-relaxed space-y-2 text-justify">
                    <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Specs -->
            <?php if (!empty($producto['specs'])): ?>
            <div class="mb-10">
                <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-4">Características Técnicas</h3>
                <div class="grid grid-cols-1 gap-3">
                    <?php foreach ($producto['specs'] as $index => $spec): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-white/5">
                        <span class="text-sm text-slate-500 dark:text-slate-400 font-medium"><?= htmlspecialchars($spec['atributo']) ?></span>
                        <span class="text-sm text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($spec['valor']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Garantías -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800/50 border border-slate-100 dark:border-white/5 rounded-xl shadow-sm text-center">
                    <div class="w-20 h-20 mb-2">
                        <lottie-player src="<?= asset('assets/lottie/calidad.json') ?>" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                    </div>
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Garantía Asegurada</span>
                </div>
                <div class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-800/50 border border-slate-100 dark:border-white/5 rounded-xl shadow-sm text-center">
                    <div class="w-20 h-20 mb-2">
                        <lottie-player src="<?= asset('assets/lottie/delivery.json') ?>" background="transparent" speed="1" style="width: 100%; height: 100%;" loop autoplay></lottie-player>
                    </div>
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Envíos a Todo Perú</span>
                </div>
            </div>

            <!-- Carrusel de Más Equipos -->
            <?php if (!empty($otrosProductos)): ?>
            <div class="animate-fade-in mb-4" data-aos="fade-up">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-lg">Ver más equipos</h3>
                </div>

                <!-- Swiper -->
                <div class="swiper mySwiper3 !overflow-visible">
                    <div class="swiper-wrapper">
                        <?php foreach ($otrosProductos as $prod): ?>
                            <?php
                                $imgs = json_decode($prod['imagenes'] ?? '[]', true);
                                $imgUrl = !empty($imgs[0]) ? asset($imgs[0]) : ''; 
                                $hasImg = !empty($imgUrl);
                                $publicUrl = url('/p/producto/' . ($prod['token'] ?? ''));
                            ?>
                            <div class="swiper-slide h-full">
                                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-white/5 rounded-2xl p-3 relative group h-full flex flex-col hover:border-indigo-300 dark:hover:border-indigo-500/50 hover:shadow-xl transition-all duration-300">
                                    
                                    <!-- Link Wrapper (Image + Content) -->
                                    <a href="<?= $publicUrl ?>" class="block flex-1 group/link cursor-pointer">
                                        <!-- Image -->
                                        <div class="aspect-square rounded-xl bg-slate-50 dark:bg-slate-700/50 mb-3 overflow-hidden relative group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20 transition-colors">
                            
                                            <?php if($hasImg): ?>
                                                <img src="<?= $imgUrl ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                                    <i class="ph-duotone ph-image text-4xl"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Floating Price -->
                                            <div class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded-lg">
                                                <?= $prod['moneda'] ?> <?= number_format($prod['precio'], 0) ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="mb-3">
                                            <h4 class="text-xs font-bold text-slate-800 dark:text-white leading-tight mb-1 line-clamp-2 min-h-[2.5em] group-hover/link:text-indigo-400 transition-colors"><?= htmlspecialchars($prod['nombre']) ?></h4>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate"><?= htmlspecialchars($prod['modelo'] ?? 'Sin modelo') ?></p>
                                        </div>
                                    </a>

                                    <!-- Action Button (Share) -->
                                    <button onclick="compartirProducto('<?= addslashes($prod['nombre']) ?>', '<?= $publicUrl ?>')" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-[10px] text-center transition-colors flex items-center justify-center gap-2 active:scale-95 shadow-lg shadow-indigo-500/20 cursor-pointer">
                                        <i class="ph-bold ph-share-network"></i>
                                        <span>Compartir</span> 
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Espaciador para no tapar contenido con la barra fija -->
            <div class="h-32"></div>

        </main>

        <!-- BARRA FLOTANTE DE ACCIÓN (CTA) -->
        <div class="fixed bottom-0 w-full max-w-lg floating-bar p-4 pb-6 z-50 left-1/2 transform -translate-x-1/2 bg-white/95 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-100 dark:border-white/5 transition-colors duration-300">
            <div class="grid grid-cols-2 gap-3">
                
                <!-- Botón Proforma (MODAL) -->
                <button onclick="openProformaModal()" 
                   class="flex flex-col items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 py-3 rounded-xl transition active:scale-95 group border border-slate-200 dark:border-white/5">
                    <span class="text-[10px] font-bold uppercase tracking-wide opacity-70 group-hover:opacity-100">Solicitar</span>
                    <span class="text-sm font-bold flex items-center gap-1">
                        <i class="ph-bold ph-file-text"></i> Proforma
                    </span>
                </button>

                <!-- Botón Comprar (WhatsApp Principal) -->
                <a href="https://wa.me/51<?= $whatsapp ?>?text=<?= urlencode($msjConsulta) ?>" target="_blank" 
                   class="flex flex-col items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-indigo-900/50 transition active:scale-95">
                    <span class="text-[10px] font-bold uppercase tracking-wide opacity-90">Me interesa</span>
                    <span class="text-sm font-bold flex items-center gap-1">
                        <i class="ph-bold ph-whatsapp-logo"></i> Consultar
                    </span>
                </a>

            </div>
        </div>

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

        // Toggle Contact Panel
        function toggleContact() {
            const panel = document.getElementById('contactPanel');
            panel.classList.toggle('hidden');
        }

        function closeContact() {
            const panel = document.getElementById('contactPanel');
            if (panel && !panel.classList.contains('hidden')) {
                panel.classList.add('hidden');
            }
        }

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

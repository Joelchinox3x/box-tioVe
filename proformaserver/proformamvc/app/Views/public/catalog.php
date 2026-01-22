<?php
// app/Views/public/catalog.php
// VISTA DE CATÁLOGO GENERAL (Refactorizada con Partials)

require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
// Variables para metadata.php
$seoTitle = "Catálogo General | " . ($appName ?? 'Catálogo');
$seoDesc = "Explora nuestro catálogo completo de equipos y servicios. Calidad y garantía.";
$seoImgUrl = asset('assets/img/logo.png'); 
$urlActual = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/partials/metadata.php'; ?>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen selection:bg-indigo-500/30">

    <!-- HEADER MÓVIL (Archivo Separado) -->
    <?php include __DIR__ . '/partials/header_mobile.php'; ?>

    <!-- HEADER ESCRITORIO (Archivo Separado) -->
    <?php include __DIR__ . '/partials/header_desktop.php'; ?>

    <!-- Main Content -->
    <main class="pt-28 pb-32 px-4 max-w-7xl mx-auto">
        
        <!-- PRODUCTS GRID -->
        <div id="productsGrid" class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($productos as $prod): ?>
            <?php 
                $img = asset('assets/img/logo.png'); // Fallback
                $imagenes = json_decode($prod['imagenes'], true);
                if (!empty($imagenes) && is_array($imagenes)) {
                    $img = asset($imagenes[0]);
                }
                $publicUrl = url("p/producto/" . $prod['token']);
            ?>
                <article class="product-card group relative flex flex-col bg-slate-900/40 backdrop-blur-sm border border-white/5 rounded-2xl overflow-hidden hover:border-blue-500/30 hover:bg-slate-900/60 hover:shadow-2xl hover:shadow-blue-900/20 transition-all duration-300 animate-fade-up cursor-pointer" 
                         onclick="window.location.href='<?= $publicUrl ?>'"
                         data-name="<?= strtolower($prod['nombre']) ?>" 
                         data-model="<?= strtolower($prod['modelo'] ?? '') ?>">
                    
                    <!-- Imagen -->
                    <div class="aspect-square relative overflow-hidden bg-slate-900/50 p-4">
                        <img src="<?= $img ?>" class="w-full h-full object-contain transform group-hover:scale-110 transition-transform duration-500 ease-out" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                        
                        <!-- Badge Estado -->
                        <div class="absolute top-3 left-3">
                             <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md backdrop-blur-md">
                                Disponible
                             </span>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="flex-1 p-4 flex flex-col">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-slate-100 leading-tight mb-1 line-clamp-2 min-h-[2.5em] group-hover:text-blue-400 transition-colors">
                                <?= htmlspecialchars($prod['nombre']) ?>
                            </h3>
                        </div>
                        
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                            <span class="text-[10px] sm:text-xs font-medium text-slate-500 bg-slate-800/50 px-2 py-1 rounded truncate max-w-[50%]">
                                <?= htmlspecialchars($prod['modelo'] ?? 'Stock') ?>
                            </span>
                            
                            <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                <!-- Botón Compartir (Round) -->
                                <button onclick="compartirProducto('<?= addslashes($prod['nombre']) ?>', '<?= $publicUrl ?>')" 
                                    class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-green-600 hover:text-white transition-all shadow-sm cursor-pointer z-10" title="Compartir">
                                    <i class="ph-bold ph-whatsapp-logo"></i>
                                </button>

                                <!-- Botón Ver Detalle (Round / Arrow) -->
                                <a href="<?= $publicUrl ?>" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-indigo-600 hover:text-white transition-all group-hover:rotate-[-45deg]" title="Ver detalle">
                                    <i class="ph-bold ph-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Empty State -->
        <div id="noResults" class="hidden flex flex-col items-center justify-center py-20 text-center animate-fade-in">
            <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mb-4">
                <i class="ph-duotone ph-magnifying-glass text-4xl text-slate-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-300">No encontramos resultados</h3>
            <p class="text-slate-500 mt-2">Intenta buscar con otro nombre o modelo.</p>
        </div>

    </main>

    <!-- Partial Footer CTA (WhatsApp + Scripts) -->
    <?php include __DIR__ . '/partials/footer_cta.php'; ?>

    <!-- Scripts Específicos de Catálogo -->
    <script>
        // --- Search Logic ---
        const desktopSearch = document.getElementById('desktopSearch');
        const mobileSearch = document.getElementById('mobileSearch');
        const grid = document.getElementById('productsGrid');
        const cards = document.querySelectorAll('.product-card');
        const noResults = document.getElementById('noResults');

        function filterProducts(query) {
            query = query.toLowerCase().trim();
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.dataset.name;
                const model = card.dataset.model || '';
                
                if(name.includes(query) || model.includes(query)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if(visibleCount === 0) {
                grid.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                grid.classList.remove('hidden');
                noResults.classList.add('hidden');
            }
        }

        if(desktopSearch) desktopSearch.addEventListener('input', (e) => filterProducts(e.target.value));
        if(mobileSearch) mobileSearch.addEventListener('input', (e) => filterProducts(e.target.value));

        // --- Share Logic (Specific Overlay) ---
        function compartirProducto(nombre, url) {
            // Mensaje neutral para catálogo público
            const shareText = `Te comparto la ficha técnica de: ${nombre}.\n\nVer aquí:`;

            if (navigator.share) {
                navigator.share({
                    title: nombre,
                    text: shareText,
                    url: url
                }).catch(console.error);
            } else {
                // Fallback a clipboard
                navigator.clipboard.writeText(`${shareText} ${url}`).then(() => {
                    if(typeof showToast === 'function') showToast('Enlace copiado al portapapeles', 'success');
                    else alert('Enlace copiado al portapapeles');
                });
            }
        }
    </script>
</body>
</html>

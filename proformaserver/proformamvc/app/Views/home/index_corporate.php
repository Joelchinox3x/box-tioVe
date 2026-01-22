<?php
// Cargar helper de configuración
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

// Obtener configuraciones desde la base de datos
$app_name = SettingsHelper::getAppName();
$app_logo = SettingsHelper::getAppLogo() ?: 'assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | <?= htmlspecialchars($app_name) ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  
  <script>window.TOAST_THEME = 'modern';</script>
  <script src="<?= asset('js/utils/toast.js') ?>"></script>

  <!-- Swiper CSS & JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <!-- Estilos Globales -->
  <link rel="stylesheet" href="<?= asset('css/global.css') ?>">

  <style>
    /* Estilos específicos del dashboard corporativo */

    /* Glass card personalizado para el header oscuro */
    .glass-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.08) 100%);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    /* Header con gradiente corporativo y efecto shimmer */
    .header-gradient {
      background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #1e293b 100%);
      position: relative;
      overflow: hidden;
    }

    .header-gradient::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 50%, transparent 70%);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0%, 100% { transform: translateX(-100%); }
      50% { transform: translateX(100%); }
    }
  </style>
</head>
<body class="bg-slate-50">

  <div class="max-w-md mx-auto min-h-screen bg-white shadow-xl">

    <!-- Header corporativo discreto -->
    <header class="header-gradient relative text-white pt-8 pb-16 px-6 rounded-b-[3rem] overflow-hidden">

      <?php include __DIR__ . '/../partials/home_settings_button.php'; ?>

      <!-- Efecto sutil de fondo -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-slate-600 rounded-full opacity-10 blur-3xl -mr-20 -mt-20"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-slate-700 rounded-full opacity-10 blur-3xl -ml-16 -mb-16"></div>

      <!-- Contenido del header -->
      <div class="relative z-10">

        <!-- Top bar con logo más grande -->
        <div class="flex justify-between items-start mb-6 animate-fade-in">
          <div class="flex-1">
            <!-- Saludo -->
            <p class="text-slate-300 text-xs font-medium uppercase tracking-wider mb-1">
              <?php
              $hora = date('G');
              if ($hora >= 5 && $hora < 12) echo "Buenos días ☀️";
              else if ($hora >= 12 && $hora < 19) echo "Buenas tardes 🌤️";
              else echo "Buenas noches 🌙";
              ?>
            </p>

            <h1 class="text-2xl font-bold text-white mb-1"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h1>
            <p class="text-slate-400 text-sm">
              Sistema de Proformas Empresarial
            </p>
          </div>

          <!-- Logo más grande y prominente -->
          <div class="glass-card p-2 rounded-2xl animate-fade-in delay-100" >
            <img src="<?= asset($app_logo) ?>" alt="Logo <?= htmlspecialchars($app_name) ?>" class="w-20 h-20 object-contain">
          </div>
        </div>

        <!-- Tarjetas de estadísticas corporativas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fade-in delay-200">

          <!-- Solicitudes Web (Destacado) -->
          <a href="<?= url('/leads') ?>" class="glass-card p-3 rounded-xl transition-all duration-300 group active:scale-95 relative overflow-hidden">
            <div class="flex flex-col items-center text-center relative z-10">
              <?php $hayPendientes = ($stats['total_pendientes'] ?? 0) > 0; ?>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-2 transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3 <?= $hayPendientes ? 'bg-red-500/20 group-hover:bg-red-600/80 animate-pulse-slow' : 'bg-slate-700/50 group-hover:bg-slate-600/70' ?>">
                <i class="ph-fill ph-bell-ringing text-2xl transition-colors <?= $hayPendientes ? 'text-red-400 group-hover:text-white' : 'text-slate-200 group-hover:text-white' ?>"></i>
              </div>
              <span class="text-[10px] font-semibold uppercase tracking-wider mb-1 <?= $hayPendientes ? 'text-red-300' : 'text-slate-300' ?>">Solicitudes</span>
              <span class="text-2xl font-bold stat-pulse group-hover:scale-110 transition-transform <?= $hayPendientes ? 'text-red-400' : 'text-white' ?>"><?= $stats['total_pendientes'] ?? 0 ?></span>
              <p class="text-[11px] font-medium <?= $hayPendientes ? 'text-red-300/80' : 'text-slate-400' ?>">pendientes</p>
            </div>
            <!-- Glow Effect for Pending -->
            <?php if($hayPendientes): ?>
                <div class="absolute inset-0 bg-red-600/10 blur-xl animate-pulse"></div>
            <?php endif; ?>
          </a>

          <!-- Proformas -->
          <a href="<?= url('/proformas') ?>" class="glass-card p-3 rounded-xl transition-all duration-300 group active:scale-95">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700/50 flex items-center justify-center mb-2 group-hover:bg-blue-600/70 group-hover:scale-110 transition-all duration-300 group-hover:rotate-3">
                <i class="ph-fill ph-file-text text-2xl text-slate-200 group-hover:text-white transition-colors"></i>
              </div>
              <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-300 mb-1">Proformas</span>
              <span class="text-2xl font-bold text-white stat-pulse group-hover:scale-110 transition-transform"><?= $stats['total_proformas'] ?? 0 ?></span>
              <p class="text-[11px] text-slate-400 font-medium">generadas</p>
            </div>
          </a>

          <!-- Clientes -->
          <a href="<?= url('/clientes') ?>" class="glass-card p-3 rounded-xl transition-all duration-300 group active:scale-95">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700/50 flex items-center justify-center mb-2 group-hover:bg-green-600/70 group-hover:scale-110 transition-all duration-300 group-hover:-rotate-3">
                <i class="ph-fill ph-users text-2xl text-slate-200 group-hover:text-white transition-colors"></i>
              </div>
              <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-300 mb-1">Clientes</span>
              <span class="text-2xl font-bold text-white stat-pulse group-hover:scale-110 transition-transform"><?= $stats['total_clientes'] ?? 0 ?></span>
              <p class="text-[11px] text-slate-400 font-medium">activos</p>
            </div>
          </a>

          <!-- Inventario -->
          <a href="<?= url('/inventario') ?>" class="glass-card p-3 rounded-xl transition-all duration-300 group active:scale-95">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700/50 flex items-center justify-center mb-2 group-hover:bg-orange-600/70 group-hover:scale-110 transition-all duration-300 group-hover:rotate-3">
                <i class="ph-fill ph-package text-2xl text-slate-200 group-hover:text-white transition-colors"></i>
              </div>
              <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-300 mb-1">Inventario</span>
              <span class="text-2xl font-bold text-white stat-pulse group-hover:scale-110 transition-transform"><?= $stats['total_productos'] ?? 0 ?></span>
              <p class="text-[11px] text-slate-400 font-medium">equipos</p>
            </div>
          </a>

        </div>
      </div>
    </header>

    <!-- Accesos rápidos profesionales -->
    <div class="px-6 -mt-8 relative z-20 animate-fade-in delay-300">
      <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-4">
        <div class="grid grid-cols-3 gap-3">

          <!-- Nueva Proforma -->
          <a href="<?= url('/proformas/create') ?>" class="group rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 hover:from-blue-50 hover:to-blue-100 p-4 transition-all duration-300 border border-slate-200 hover:border-blue-300 hover:shadow-lg hover:scale-105 active:scale-95">
            <div class="flex flex-col items-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700 group-hover:bg-gradient-to-br group-hover:from-blue-600 group-hover:to-blue-700 flex items-center justify-center mb-2 transition-all duration-300 shadow-sm group-hover:shadow-md group-hover:rotate-6">
                <i class="ph-bold ph-plus text-xl text-white group-hover:scale-110 transition-transform"></i>
              </div>
              <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700 text-center leading-tight transition-colors">Nueva<br>Proforma</span>
            </div>
          </a>

          <!-- Agregar Cliente -->
          <a href="<?= url('/clientes/create') ?>" class="group rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 hover:from-green-50 hover:to-green-100 p-4 transition-all duration-300 border border-slate-200 hover:border-green-300 hover:shadow-lg hover:scale-105 active:scale-95">
            <div class="flex flex-col items-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700 group-hover:bg-gradient-to-br group-hover:from-green-600 group-hover:to-green-700 flex items-center justify-center mb-2 transition-all duration-300 shadow-sm group-hover:shadow-md group-hover:-rotate-6">
                <i class="ph-bold ph-user-plus text-xl text-white group-hover:scale-110 transition-transform"></i>
              </div>
              <span class="text-xs font-semibold text-slate-700 group-hover:text-green-700 text-center leading-tight transition-colors">Agregar<br>Cliente</span>
            </div>
          </a>

         <!-- Agregar Producto -->
          <a href="<?= url('/inventario/create') ?>" class="group rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 hover:from-orange-50 hover:to-orange-100 p-4 transition-all duration-300 border border-slate-200 hover:border-orange-300 hover:shadow-lg hover:scale-105 active:scale-95">
            <div class="flex flex-col items-center">
              <div class="w-12 h-12 rounded-xl bg-slate-700 group-hover:bg-gradient-to-br group-hover:from-orange-600 group-hover:to-orange-700 flex items-center justify-center mb-2 transition-all duration-300 shadow-sm group-hover:shadow-md group-hover:rotate-6">
                <i class="ph-bold ph-package text-xl text-white group-hover:scale-110 transition-transform"></i>
              </div>
              <span class="text-xs font-semibold text-slate-700 group-hover:text-orange-700 text-center leading-tight transition-colors">Agregar<br>Maquinaria</span>
            </div>
          </a>

        </div>
      </div>
    </div>

    <!-- Actividad Reciente -->
    <section class="px-6 mt-8 pb-8 animate-fade-in delay-400">

      <!-- Título de sección -->
      <div class="flex justify-between items-center mb-4">
        <div>
          <h3 class="text-lg font-bold text-slate-800">Actividad Reciente</h3>
          <p class="text-xs text-slate-500 mt-0.5">Últimas proformas generadas</p>
        </div>
        <a href="<?= url('/proformas') ?>" class="text-xs font-semibold text-slate-700 hover:text-blue-700 bg-slate-100 hover:bg-blue-100 px-3 py-2 rounded-lg transition-all flex items-center gap-1 hover:gap-2 hover:scale-105 active:scale-95 border border-transparent hover:border-blue-200">
          Ver todo <i class="ph-bold ph-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
        </a>
      </div>

      <?php if (!empty($stats['proformas_recientes']) && count($stats['proformas_recientes']) > 0): ?>
        <div class="space-y-3">
          <?php foreach ($stats['proformas_recientes'] as $index => $proforma): ?>
            <a href="<?= url("/proformas/viewPdf/{$proforma['id']}") ?>"
               target="_blank"
               class="block bg-white rounded-xl border border-slate-200 hover:border-blue-200 shadow-sm hover:shadow-lg transition-all duration-300 animate-slide-in group hover:scale-[1.02] active:scale-[0.98] relative overflow-hidden"
               style="animation-delay: <?= 0.5 + ($index * 0.1) ?>s">

              <!-- Barra lateral de color con gradiente -->
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-cyan-500 to-blue-500 group-hover:w-1.5 transition-all"></div>

              <div class="p-4 flex items-center justify-between pl-5">

                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 group-hover:from-cyan-100 group-hover:to-cyan-200 flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <i class="ph-bold ph-receipt text-xl text-slate-600 group-hover:text-cyan-600 transition-colors"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-sm text-slate-800 group-hover:text-blue-700 truncate mb-0.5 transition-colors">
                      <?= htmlspecialchars($proforma['cliente_nombre'] ?? 'Cliente') ?>
                    </h4>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                      <span><?= date('d M, Y', strtotime($proforma['fecha_creacion'] ?? 'now')) ?></span>
                      <span>•</span>
                      <span class="font-medium text-slate-600 transition-colors">
                        <?= htmlspecialchars('TRA' . str_pad($proforma['id'], 5, '0', STR_PAD_LEFT)) ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="text-right ml-3 flex-shrink-0 whitespace-nowrap">
                  <span class="block text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                    <?= $proforma['moneda'] ?? 'PEN' ?><br><?= number_format($proforma['total'] ?? 0, 0) ?>
                  </span>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 group-hover:bg-emerald-100 group-hover:border-emerald-300 mt-1 transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-semibold text-emerald-700">Activo</span>
                  </span>
                </div>

              </div>
            </a>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <!-- Estado vacío corporativo -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-8 text-center border-2 border-dashed border-slate-300 animate-fade-in delay-500 relative overflow-hidden group">
          <!-- Efecto de fondo animado -->
          <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-blue-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

          <div class="relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-300 group-hover:from-purple-200 group-hover:to-purple-300 flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:scale-110 group-hover:rotate-6">
              <i class="ph-duotone ph-folder-dashed text-4xl text-slate-400 group-hover:text-purple-600 transition-colors"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-base mb-2">Sin movimientos recientes</h4>
            <p class="text-sm text-slate-600 mb-5 max-w-xs mx-auto">
              Aún no has creado proformas. Comienza a gestionar tu negocio ahora.
            </p>
            <a href="<?= url('/proformas/create') ?>"
               class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 px-6 py-3 rounded-xl transition-all shadow-md hover:shadow-lg hover:scale-105 active:scale-95">
              <i class="ph-bold ph-plus"></i>
              Crear Primera Proforma
            </a>
          </div>
        </div>
      <?php endif; ?>

    </section>

  </div>

  <!-- Catálogo Rápido / Publicidad -->
  <?php if (!empty($productos_carousel ?? [])): ?>
    <section class="max-w-md mx-auto px-6 pb-96 animate-fade-in delay-500">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Catálogo Rápido</h3>
                <p class="text-xs text-slate-500 mt-0.5">Comparte tus equipos por WhatsApp</p>
            </div>
            <a href="<?= url('/p/catalogo') ?>" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-blue-100 hover:text-blue-600 transition-colors" title="Ver catálogo público detallado">
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <!-- Swiper -->
        <div class="swiper mySwiper !overflow-visible">
            <div class="swiper-wrapper">
                <?php foreach ($productos_carousel as $prod): ?>
                    <?php
                        $imgs = json_decode($prod['imagenes'] ?? '[]', true);
                        $imgUrl = !empty($imgs[0]) ? asset($imgs[0]) : ''; 
                        $hasImg = !empty($imgUrl);
                        
                        // Generar link público
                        $publicUrl = url('/p/producto/' . ($prod['token'] ?? ''));
                        
                        // Mensaje para compartir: "Mira este equipo: [Nombre] [Link]"
                        $mensaje = "Mira este equipo: *{$prod['nombre']}* \nVer detalles aquí: " . $publicUrl;
                        $wspLink = "https://api.whatsapp.com/send?text=" . urlencode($mensaje);
                    ?>
                    <div class="swiper-slide h-full">
                         <div class="glass-card bg-white border border-slate-200 rounded-2xl p-3 relative group h-full flex flex-col hover:border-indigo-300 hover:shadow-xl transition-all duration-300">
                            <!-- Link Wrapper (Image + Content) -->
                            <a href="<?= $publicUrl ?>" class="block flex-1 group/link cursor-pointer">
                                <!-- Image -->
                                <div class="aspect-square rounded-xl bg-slate-50 mb-3 overflow-hidden relative group-hover:bg-indigo-50 transition-colors">
                                    <?php if($hasImg): ?>
                                        <img src="<?= $imgUrl ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
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
                                    <h4 class="text-sm font-bold text-slate-800 leading-tight mb-1 line-clamp-2 min-h-[2.5em] group-hover/link:text-indigo-600 transition-colors"><?= htmlspecialchars($prod['nombre']) ?></h4>
                                    <p class="text-[10px] text-slate-500 font-medium truncate"><?= htmlspecialchars($prod['modelo'] ?? 'Sin modelo') ?></p>
                                </div>
                            </a>

                            <!-- Action Button (Estilo show.php) -->
                            <button onclick="compartirProducto('<?= addslashes($prod['nombre']) ?>', '<?= $publicUrl ?>')" class="w-full py-2 bg-indigo-600 text-white rounded-xl font-medium text-center hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2 group/btn active:scale-95 shadow-lg shadow-indigo-100 cursor-pointer">
                                <i class="ph-bold ph-share-network"></i> 
                                <span class="text-xs font-bold">Compartir</span>
                            </button>
                         </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Pagination fuera para diseño limpio -->
            <div class="swiper-pagination !-bottom-6"></div>
        </div>
    </section>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
         // Ajuste para evitar conflictos si existe otro swiper
         const swiper = new Swiper(".mySwiper", {
            slidesPerView: 1.8,
            spaceBetween: 16,
            grabCursor: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 3.2,
                }
            }
        });
    });

    // Lógica de Compartir (Replicada de show_admin.js)
    window.MANAGER_NAME = "<?= addslashes(SettingsHelper::getManagerName()) ?>";

    function compartirProducto(nombre, url) {
        const managerName = window.MANAGER_NAME || "Asesor";
        const shareText = `Hola, soy ${managerName}.\nTe comparto la ficha técnica de: ${nombre}.\n\nVer aquí:`;

        if (navigator.share) {
            navigator.share({
                title: nombre,
                text: shareText,
                url: url
            }).catch(err => {
                // Fallback a clipboard si cancela o falla (y no es AbortError)
                if (err.name !== 'AbortError') copyToClipboard(url);
            });
        } else {
            // Fallback Desktop: Copiar o WhatsApp Web directo?
            // El usuario pidió "compartir x wsp", así que si no hay navigator.share (Desktop),
            // podríamos intentar abrir WhatsApp Web con el texto formateado.
            
            const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + " " + url)}`;
            window.open(whatsappUrl, '_blank');
            
            // Opcional: Copiar también por si acaso
            // copyToClipboard(url); 
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if(typeof mostrarToast === 'function') {
                mostrarToast('Enlace copiado al portapapeles', 'success');
            } else {
                alert('Enlace copiado');
            }
        }).catch(err => {
            prompt("Copia este enlace:", text);
        });
    }
  </script>

</body>
</html>
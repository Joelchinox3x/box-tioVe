<?php
$title = 'Configuración';
$show_home = true;
$section = 'clientes';

include __DIR__ . '/../partials/load_header.php';

// Cargar helper de configuración
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

// Las variables $currentTheme y $availableThemes vienen del controlador
?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<!-- Contenido principal -->
<main class="max-w-md mx-auto pt-0 px-4 pb-24">

  <!-- Formulario único para todos los cambios -->
  <form id="settingsForm" method="POST">

    <!-- ========================================== -->
    <!-- SECCIÓN 1: Personalización Visual (Acordeón) -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-3 overflow-hidden">
      <!-- Header del Acordeón -->
      <button type="button" onclick="toggleAccordion('visualSection')" class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
            <i class="ph-bold ph-palette text-white text-xl"></i>
          </div>
          <div class="text-left">
            <h2 class="text-base font-bold text-slate-800">Personalización Visual</h2>
            <p class="text-xs text-slate-500">Temas, colores y estilos</p>
          </div>
        </div>
        <i id="visualSectionIcon" class="ph-bold ph-caret-down text-slate-400 text-xl transition-transform"></i>
      </button>

      <!-- Contenido del Acordeón -->
      <div id="visualSection" class="accordion-content">
        <div class="p-4 pt-0 space-y-4 border-t border-slate-100">

          <!-- Tema de la Aplicación -->
          <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
              <i class="ph-bold ph-paint-brush text-blue-600"></i>
              Tema de la Aplicación
            </h3>
            <div class="overflow-x-auto no-scrollbar snap-x snap-mandatory">
              <div class="flex gap-2 pb-2">
                <?php foreach ($availableThemes as $themeKey => $theme): ?>
                  <div class="snap-center flex-shrink-0 w-32">
                    <label class="block cursor-pointer">
                      <input type="radio" name="theme" value="<?= $themeKey ?>" class="hidden theme-radio" <?= $currentTheme === $themeKey ? 'checked' : '' ?>>
                      <div class="theme-option p-2 rounded-lg border-2 transition-all duration-300 <?= $currentTheme === $themeKey ? 'border-blue-500 bg-blue-50' : 'border-slate-200' ?>">
                        <div class="relative mb-1">
                          <div class="w-full h-14 rounded-md overflow-hidden border border-white shadow-sm" style="background: <?= $theme['preview_color'] ?>;">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-6 bg-white/90 flex items-center justify-center">
                              <i class="<?= $theme['icon'] ?> text-slate-700 text-base"></i>
                            </div>
                          </div>
                          <?php if ($currentTheme === $themeKey): ?>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                              <i class="ph-bold ph-check text-white text-[10px]"></i>
                            </div>
                          <?php endif; ?>
                        </div>
                        <h4 class="font-semibold text-xs text-slate-800 text-center"><?= $theme['name'] ?></h4>
                      </div>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Estilo de Encabezado -->
          <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
              <i class="ph-bold ph-layout text-purple-600"></i>
              Estilo de Encabezado
            </h3>
            <div class="overflow-x-auto no-scrollbar snap-x snap-mandatory">
              <div class="flex gap-2 pb-2">
                <!-- Header 1 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="header" value="header" class="hidden header-radio" <?= ($currentHeader ?? 'header') === 'header' ? 'checked' : '' ?>>
                    <div class="header-option p-2 rounded-lg border-2 transition-all <?= ($currentHeader ?? 'header') === 'header' ? 'border-purple-500 bg-purple-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-sparkle text-white text-2xl"></i>
                        </div>
                        <?php if (($currentHeader ?? 'header') === 'header'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Premium</h4>
                    </div>
                  </label>
                </div>
                <!-- Header 2 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="header" value="header_v2" class="hidden header-radio" <?= ($currentHeader ?? 'header') === 'header_v2' ? 'checked' : '' ?>>
                    <div class="header-option p-2 rounded-lg border-2 transition-all <?= ($currentHeader ?? 'header') === 'header_v2' ? 'border-purple-500 bg-purple-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-minus-circle text-white text-2xl"></i>
                        </div>
                        <?php if (($currentHeader ?? 'header') === 'header_v2'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Minimalista</h4>
                    </div>
                  </label>
                </div>
                <!-- Header 3 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="header" value="header_v3" class="hidden header-radio" <?= ($currentHeader ?? 'header') === 'header_v3' ? 'checked' : '' ?>>
                    <div class="header-option p-2 rounded-lg border-2 transition-all <?= ($currentHeader ?? 'header') === 'header_v3' ? 'border-purple-500 bg-purple-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-blue-600 to-purple-700 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-gradient text-white text-2xl"></i>
                        </div>
                        <?php if (($currentHeader ?? 'header') === 'header_v3'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Modern Gradient</h4>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Estilo de Navegación -->
          <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
              <i class="ph-bold ph-compass text-emerald-600"></i>
              Estilo de Navegación
            </h3>
            <div class="overflow-x-auto no-scrollbar snap-x snap-mandatory">
              <div class="flex gap-2 pb-2">
                <!-- Navbar 1 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="navbar" value="navbar" class="hidden navbar-radio" <?= ($currentNavbar ?? 'navbar') === 'navbar' ? 'checked' : '' ?>>
                    <div class="navbar-option p-2 rounded-lg border-2 transition-all <?= ($currentNavbar ?? 'navbar') === 'navbar' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-rocket-launch text-white text-2xl"></i>
                        </div>
                        <?php if (($currentNavbar ?? 'navbar') === 'navbar'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Premium</h4>
                    </div>
                  </label>
                </div>
                <!-- Navbar 2 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="navbar" value="navbar_v2" class="hidden navbar-radio" <?= ($currentNavbar ?? 'navbar') === 'navbar_v2' ? 'checked' : '' ?>>
                    <div class="navbar-option p-2 rounded-lg border-2 transition-all <?= ($currentNavbar ?? 'navbar') === 'navbar_v2' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-squares-four text-white text-2xl"></i>
                        </div>
                        <?php if (($currentNavbar ?? 'navbar') === 'navbar_v2'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Minimalista</h4>
                    </div>
                  </label>
                </div>
                <!-- Navbar 3 -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="navbar" value="navbar_v3" class="hidden navbar-radio" <?= ($currentNavbar ?? 'navbar') === 'navbar_v3' ? 'checked' : '' ?>>
                    <div class="navbar-option p-2 rounded-lg border-2 transition-all <?= ($currentNavbar ?? 'navbar') === 'navbar_v3' ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-sm">
                          <i class="ph-fill ph-apple-logo text-white text-2xl"></i>
                        </div>
                        <?php if (($currentNavbar ?? 'navbar') === 'navbar_v3'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Dock macOS</h4>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Estilo de Notificaciones Toast -->
          <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
              <i class="ph-bold ph-bell text-indigo-600"></i>
              Estilo de Notificaciones
            </h3>
            <div class="overflow-x-auto no-scrollbar snap-x snap-mandatory">
              <div class="flex gap-2 pb-2">
                <!-- Toast Classic -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="toast_style" value="classic" class="hidden toast-radio" <?= ($currentToastStyle ?? 'classic') === 'classic' ? 'checked' : '' ?>>
                    <div class="toast-option p-2 rounded-lg border-2 transition-all <?= ($currentToastStyle ?? 'classic') === 'classic' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-white border border-slate-200 flex items-center justify-center shadow-sm overflow-hidden">
                          <div class="w-full h-full flex items-center gap-1 px-2 border-l-4 border-green-500 bg-green-50">
                            <i class="ph-bold ph-check-circle text-green-800 text-sm"></i>
                            <div class="flex-1 min-w-0">
                              <p class="text-[8px] font-bold text-green-800">Éxito</p>
                              <p class="text-[6px] text-green-800">Mensaje...</p>
                            </div>
                          </div>
                        </div>
                        <?php if (($currentToastStyle ?? 'classic') === 'classic'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Classic</h4>
                    </div>
                  </label>
                </div>
                <!-- Toast Modern -->
                <div class="snap-center flex-shrink-0 w-32">
                  <label class="block cursor-pointer">
                    <input type="radio" name="toast_style" value="modern" class="hidden toast-radio" <?= ($currentToastStyle ?? 'classic') === 'modern' ? 'checked' : '' ?>>
                    <div class="toast-option p-2 rounded-lg border-2 transition-all <?= ($currentToastStyle ?? 'classic') === 'modern' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200' ?>">
                      <div class="relative mb-1">
                        <div class="w-full h-14 rounded-md bg-green-500 flex items-center justify-center shadow-sm overflow-hidden">
                          <div class="w-full h-full flex items-center gap-1 px-2">
                            <div class="bg-white/20 rounded p-0.5">
                              <i class="ph-bold ph-check-circle text-white text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                              <p class="text-[7px] font-bold text-white uppercase">ÉXITO</p>
                              <p class="text-[6px] text-white">Mensaje...</p>
                            </div>
                          </div>
                        </div>
                        <?php if (($currentToastStyle ?? 'classic') === 'modern'): ?>
                          <div class="absolute -top-1 -right-1 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center">
                            <i class="ph-bold ph-check text-white text-[10px]"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <h4 class="font-semibold text-xs text-slate-800 text-center">Modern</h4>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Estilo de Botones (Próximamente) -->
          <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
              <i class="ph-bold ph-cursor-click text-orange-600"></i>
              Estilo de Botones
              <span class="text-xs text-slate-400 font-normal">(Próximamente)</span>
            </h3>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
              <p class="text-xs text-slate-500 text-center">
                <i class="ph-bold ph-wrench"></i>
                Esta función estará disponible próximamente
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- SECCIÓN 2: Información de la Aplicación (Compacta) -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 mb-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
          <i class="ph-bold ph-identification-card text-amber-600 text-base"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-800">Información de la Aplicación</h2>
      </div>

      <div class="space-y-3">
        <!-- Nombre de la App -->
        <div>
          <label for="appName" class="block text-xs font-semibold text-slate-600 mb-1">
            <i class="ph-bold ph-text-aa text-blue-600"></i>
            Nombre de la Aplicacion
          </label>
          <input type="text" id="appName" name="app_name" value="<?= htmlspecialchars(SettingsHelper::getAppName()) ?>" placeholder="Tradimacova" maxlength="50" class="w-full px-3 py-2 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-amber-500 focus:bg-white transition-all outline-none">
        </div>

        <!-- Logo de la App -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            <i class="ph-bold ph-image text-pink-600"></i>
            Logo de la Aplicación
          </label>
          <div class="flex items-center gap-2">
            <input type="file" id="appLogoInput" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">
            <input type="hidden" id="eliminarLogoInput" value="0">

            <div id="logoContainer" class="relative group bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg p-2 border-2 border-slate-200 flex items-center justify-center cursor-pointer transition-all hover:border-pink-400 overflow-hidden w-16 h-16 flex-shrink-0" onclick="document.getElementById('appLogoInput').click()">
              <?php
              $currentLogo = SettingsHelper::getAppLogo();
              $hasLogo = !empty($currentLogo);
              ?>
              <div id="logoPreviewContainer">
                <?php if ($hasLogo): ?>
                  <img src="<?= asset($currentLogo) ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                <?php else: ?>
                  <i class="ph-bold ph-image-broken text-slate-400 text-2xl"></i>
                <?php endif; ?>
              </div>
              <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <i class="ph-bold ph-pencil text-white text-base"></i>
              </div>
              <button type="button" id="btnEliminarLogo" onclick="event.stopPropagation(); eliminarLogoPreview()" class="<?= $hasLogo ? '' : 'hidden' ?> absolute -top-1 -right-1 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10">
                <i class="ph-bold ph-x text-xs"></i>
              </button>
            </div>

            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-slate-700" id="logoStatusText">
                <?= $hasLogo ? 'Logo configurado' : 'Sin logo' ?>
              </p>
              <p class="text-xs text-slate-500">PNG, JPG, WEBP • Máx 5MB</p>
            </div>
          </div>
        </div>

        <!-- GPS en Clientes -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <div>
            <p class="text-xs font-semibold text-slate-700 flex items-center gap-1">
              <i class="ph-bold ph-map-pin text-blue-600"></i>
              GPS en Clientes
            </p>
            <p class="text-xs text-slate-500">Habilitar ubicación</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="gps_enabled" id="gpsToggle" value="1" class="sr-only peer" <?= SettingsHelper::isGpsEnabled() ? 'checked' : '' ?>>
            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
        </div>

        <!-- Chatbot de IA -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 mb-3">
          <div>
            <p class="text-xs font-semibold text-slate-700 flex items-center gap-1">
              <i class="ph-bold ph-robot text-indigo-600"></i>
              Chatbot de IA (Gemini)
            </p>
            <p class="text-xs text-slate-500">Activar asistente virtual</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="enable_chatbot" id="chatbotToggle" value="1" class="sr-only peer" <?= SettingsHelper::isChatbotEnabled() ? 'checked' : '' ?>>
            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
          </label>
        </div>

        <!-- Manager Info -->
        <div class="pt-2 border-t border-slate-100">
           <!-- Manager Name --> 
           <div class="mb-3">
              <label for="managerName" class="block text-xs font-semibold text-slate-600 mb-1">
                <i class="ph-bold ph-user-circle text-indigo-600"></i>
                Nombre del Manager
              </label>
              <input type="text" id="managerName" name="manager_name" value="<?= htmlspecialchars(SettingsHelper::getManagerName()) ?>" placeholder="Ej: Juan Pérez" maxlength="100" class="w-full px-3 py-2 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-indigo-500 focus:bg-white transition-all outline-none">
           </div>

           <!-- Manager WhatsApp -->
           <div>
              <label for="managerWhatsapp" class="block text-xs font-semibold text-slate-600 mb-1">
                <i class="ph-bold ph-whatsapp-logo text-green-600"></i>
                WhatsApp del Manager
              </label>
              <input type="tel" id="managerWhatsapp" name="manager_whatsapp" value="<?= htmlspecialchars(SettingsHelper::getManagerWhatsapp()) ?>" placeholder="Ej: 51999999999" maxlength="20" class="w-full px-3 py-2 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-green-500 focus:bg-white transition-all outline-none">
           </div>
        </div>
      </div>
    </div>

    <!-- Configuración de IGV -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 mb-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i class="ph-bold ph-percent text-green-600 text-base"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-800">Configuración de IGV</h2>
      </div>

      <div class="space-y-2">
        <div>
          <label for="igvPercent" class="block text-xs font-semibold text-slate-600 mb-1">Porcentaje de IGV (%)</label>
          <input type="number" id="igvPercent" name="igv_percent" value="<?= htmlspecialchars(SettingsHelper::getIgvPercent()) ?>" min="0" max="100" step="0.01" placeholder="18" class="w-full px-3 py-2 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-green-500 focus:bg-white transition-all outline-none text-center">
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <div>
            <p class="text-xs font-semibold text-slate-700">Precios incluyen IGV</p>
            <p class="text-xs text-slate-500">Ya tienen IGV incluido</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="prices_include_igv" id="pricesIncludeIgvToggle" value="1" class="sr-only peer" <?= SettingsHelper::pricesIncludeIgv() ? 'checked' : '' ?>>
            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
          </label>
        </div>
      </div>
    </div>

    <!-- PIN de Desbloqueo -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 mb-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
          <i class="ph-bold ph-lock text-red-600 text-base"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-800">PIN de Desbloqueo</h2>
      </div>

      <div class="space-y-2">
        <!-- PIN Actual con botón para mostrar/ocultar -->
        <div>
          <label for="currentPin" class="block text-xs font-semibold text-slate-600 mb-1">PIN Actual</label>
          <div class="relative">
            <input type="password" id="currentPin" name="current_pin" placeholder="Ingrese PIN actual" maxlength="6" pattern="[0-9]*" inputmode="numeric" class="w-full px-3 py-2 pr-10 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-red-500 focus:bg-white transition-all outline-none text-center tracking-widest">
            <button type="button" onclick="togglePinVisibility('currentPin', 'toggleCurrentPinIcon')" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
              <i id="toggleCurrentPinIcon" class="ph-bold ph-eye text-base"></i>
            </button>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            <i class="ph-bold ph-info"></i>
         <!--   PIN guardado en BD: <span class="font-mono font-bold text-blue-600"><?= htmlspecialchars($currentPin ?? '1234') ?></span> -->
         PIN guardado en BD: <span class="font-mono font-bold text-blue-600">Son solo numeros
          </p>
        </div>

        <!-- Nuevo PIN con botón para mostrar/ocultar -->
        <div>
          <label for="adminPin" class="block text-xs font-semibold text-slate-600 mb-1">Nuevo PIN</label>
          <div class="relative">
            <input type="password" id="adminPin" name="admin_pin" placeholder="Ingrese nuevo PIN" maxlength="6" pattern="[0-9]*" inputmode="numeric" class="w-full px-3 py-2 pr-10 text-sm bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-semibold focus:border-red-500 focus:bg-white transition-all outline-none text-center tracking-widest">
            <button type="button" onclick="togglePinVisibility('adminPin', 'toggleAdminPinIcon')" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
              <i id="toggleAdminPinIcon" class="ph-bold ph-eye text-base"></i>
            </button>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            <i class="ph-bold ph-info"></i>
            Solo números, mínimo 3 y máximo 6 dígitos
          </p>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- SECCIÓN: API DNI/RUC -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 mb-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
          <i class="ph-bold ph-identification-badge text-blue-600 text-base"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-800">API DNI/RUC</h2>
      </div>

      <div class="space-y-3">
        <!-- Toggle para activar/desactivar búsqueda DNI/RUC -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <div>
            <p class="text-xs font-semibold text-slate-700 flex items-center gap-1">
              <i class="ph-bold ph-magnifying-glass text-blue-600"></i>
              Habilitar Búsqueda DNI/RUC
            </p>
            <p class="text-xs text-slate-500">Botón en crear clientes</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="enable_dni_ruc" id="dniSearchToggle" value="1" class="sr-only peer" <?= SettingsHelper::isDniSearchEnabled() ? 'checked' : '' ?>>
            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
        </div>

        <!-- Selección de Proveedor API -->
        <div id="apiProviderSection">
          <label class="block text-xs font-semibold text-slate-600 mb-2">
            <i class="ph-bold ph-cloud text-purple-600"></i>
            Proveedor de API
          </label>
          <div class="space-y-2">
            <!-- API Peru -->
            <label class="flex items-center gap-3 p-2 rounded-lg border-2 cursor-pointer transition-all hover:bg-slate-50" id="apiPeruOption">
              <input type="radio" name="dni_ruc_provider" value="apiperu" class="w-4 h-4 text-blue-600" <?= (SettingsHelper::getApiProvider() ?? 'apiperu') === 'apiperu' ? 'checked' : '' ?>>
              <div class="flex-1">
                <p class="text-xs font-semibold text-slate-700">ApiPeru.dev</p>
                <p class="text-xs text-slate-500">DNI y RUC oficial</p>
              </div>
              <?php if (!empty(SettingsHelper::getApiPeruToken())): ?>
                <i class="ph-fill ph-check-circle text-green-500 text-lg"></i>
              <?php endif; ?>
            </label>
            <!-- Decolecta -->
            <label class="flex items-center gap-3 p-2 rounded-lg border-2 cursor-pointer transition-all hover:bg-slate-50" id="decolectaOption">
              <input type="radio" name="dni_ruc_provider" value="decolecta" class="w-4 h-4 text-purple-600" <?= (SettingsHelper::getApiProvider() ?? 'apiperu') === 'decolecta' ? 'checked' : '' ?>>
              <div class="flex-1">
                <p class="text-xs font-semibold text-slate-700">Decolecta</p>
                <p class="text-xs text-slate-500">Alternativa DNI/RUC</p>
              </div>
              <?php if (!empty(SettingsHelper::getDecolectaToken())): ?>
                <i class="ph-fill ph-check-circle text-green-500 text-lg"></i>
              <?php endif; ?>
            </label>
          </div>
        </div>

        <!-- Token ApiPeru -->
        <div id="apiPeruTokenSection" class="space-y-1">
          <label for="apiPeruToken" class="block text-xs font-semibold text-slate-600">
            <i class="ph-bold ph-key text-blue-600"></i>
            Token ApiPeru.dev
          </label>
          <div class="relative">
            <input type="password" id="apiPeruToken" name="apiperu_token" value="<?= htmlspecialchars(SettingsHelper::getApiPeruToken()) ?>" placeholder="Ingresa tu token" class="w-full px-3 py-2 pr-10 text-xs bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-mono focus:border-blue-500 focus:bg-white transition-all outline-none">
            <?php if (!empty(SettingsHelper::getApiPeruToken())): ?>
              <div class="absolute right-3 top-1/2 -translate-y-1/2">
                <i class="ph-fill ph-check-circle text-green-500 text-lg"></i>
              </div>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-500">
            <i class="ph ph-info"></i>
            Obtén tu token en <a href="https://apiperu.dev" target="_blank" class="text-blue-600 underline">apiperu.dev</a>
          </p>
        </div>

        <!-- Token Decolecta -->
        <div id="decolectaTokenSection" class="space-y-1 hidden">
          <label for="decolectaToken" class="block text-xs font-semibold text-slate-600">
            <i class="ph-bold ph-key text-purple-600"></i>
            Token Decolecta
          </label>
          <div class="relative">
            <input type="password" id="decolectaToken" name="decolecta_token" value="<?= htmlspecialchars(SettingsHelper::getDecolectaToken()) ?>" placeholder="Ingresa tu token" class="w-full px-3 py-2 pr-10 text-xs bg-slate-50 border-2 border-slate-200 rounded-lg text-slate-800 font-mono focus:border-purple-500 focus:bg-white transition-all outline-none">
            <?php if (!empty(SettingsHelper::getDecolectaToken())): ?>
              <div class="absolute right-3 top-1/2 -translate-y-1/2">
                <i class="ph-fill ph-check-circle text-green-500 text-lg"></i>
              </div>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-500">
            <i class="ph ph-info"></i>
            Obtén tu token en <a href="https://decolecta.com/profile/" target="_blank" class="text-purple-600 underline">decolecta.com/profile</a>
          </p>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- SECCIÓN: Registro de Usuarios -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 mb-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
          <i class="ph-bold ph-user-plus text-green-600 text-base"></i>
        </div>
        <h2 class="text-sm font-bold text-slate-800">Registro de Usuarios</h2>
      </div>

      <div class="space-y-3">
        <!-- Toggle para activar/desactivar registro -->
        <div class="flex items-center justify-between pb-2">
          <div>
            <p class="text-xs font-semibold text-slate-700 flex items-center gap-1">
              <i class="ph-bold ph-user-circle-plus text-green-600"></i>
              Habilitar Registro Público
            </p>
            <p class="text-xs text-slate-500">Permite que nuevos usuarios se registren</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="enable_registration" value="1" class="sr-only peer" <?= $registrationEnabled === '1' ? 'checked' : '' ?>>
            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
          </label>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-2">
          <p class="text-xs text-amber-800 flex items-start gap-2">
            <i class="ph-bold ph-warning text-amber-600 text-sm mt-0.5"></i>
            <span>Por seguridad, desactiva esta opción después de crear las cuentas necesarias.</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Botón Guardar -->
    <button type="submit" id="saveBtn" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
      <i class="ph-bold ph-floppy-disk text-lg"></i>
      <span>Guardar Cambios</span>
    </button>

    <!-- Botón de Cerrar Sesión -->
    <a href="<?= url('/logout') ?>" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')" class="mt-3 w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 block">
      <i class="ph-bold ph-sign-out text-lg"></i>
      <span>Cerrar Sesión</span>
    </a>

  </form>

</main>

<style>
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

  .theme-radio:checked + .theme-option { border-color: #3b82f6; background-color: #eff6ff; }
  .navbar-radio:checked + .navbar-option { border-color: #10b981; background-color: #ecfdf5; }
  .header-radio:checked + .header-option { border-color: #a855f7; background-color: #faf5ff; }
  .toast-radio:checked + .toast-option { border-color: #6366f1; background-color: #eef2ff; }

  .theme-option:hover, .navbar-option:hover, .header-option:hover, .toast-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  /* Acordeón */
  .accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
  }

  .accordion-content.active {
    max-height: 2000px;
    transition: max-height 0.5s ease-in;
  }

  .animate-fade-in {
    animation: fadeIn 0.3s ease-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<script>
// ========================================
// SISTEMA DE NOTIFICACIONES
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_GET['msg'])): ?>
    <?php
    $msg = $_GET['msg'];
    $notificaciones = [
      'updated' => ['titulo' => '¡Guardado!', 'mensaje' => 'Configuración guardada correctamente', 'tipo' => 'success'],
      'pin_updated' => ['titulo' => 'PIN Actualizado', 'mensaje' => 'El PIN de seguridad se cambió exitosamente', 'tipo' => 'success'],
      'pin_incorrect' => ['titulo' => 'PIN Incorrecto', 'mensaje' => 'El PIN actual no coincide', 'tipo' => 'error'],
      'pin_too_short' => ['titulo' => 'PIN muy corto', 'mensaje' => 'El PIN debe tener al menos 3 dígitos', 'tipo' => 'warning'],
      'pin_incomplete' => ['titulo' => 'Datos incompletos', 'mensaje' => 'Debe ingresar el PIN actual y el nuevo PIN', 'tipo' => 'warning'],
      'registration_updated' => ['titulo' => 'Registro Actualizado', 'mensaje' => 'La configuración de registro se actualizó correctamente', 'tipo' => 'success'],
      'error' => ['titulo' => 'Error', 'mensaje' => 'Ocurrió un error al guardar', 'tipo' => 'error']
    ];
    $notif = $notificaciones[$msg] ?? null;
    ?>
    <?php if ($notif): ?>
      mostrarNotificacion(
        '<?= $notif['titulo'] ?>',
        '<?= $notif['mensaje'] ?>',
        '<?= $notif['tipo'] ?>'
      );
    <?php endif; ?>
  <?php endif; ?>
});

// ========================================
// FUNCIÓN ACORDEÓN
// ========================================
function toggleAccordion(sectionId) {
  const content = document.getElementById(sectionId);
  const icon = document.getElementById(sectionId + 'Icon');

  content.classList.toggle('active');
  icon.classList.toggle('ph-caret-down');
  icon.classList.toggle('ph-caret-up');
}

// ========================================
// FUNCIÓN PARA MOSTRAR/OCULTAR PIN
// ========================================
function togglePinVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('ph-eye');
    icon.classList.add('ph-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('ph-eye-slash');
    icon.classList.add('ph-eye');
  }
}

// ========================================
// API DNI/RUC - MOSTRAR/OCULTAR TOKENS
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  const dniSearchToggle = document.getElementById('dniSearchToggle');
  const apiProviderSection = document.getElementById('apiProviderSection');
  const apiPeruTokenSection = document.getElementById('apiPeruTokenSection');
  const decolectaTokenSection = document.getElementById('decolectaTokenSection');
  const apiProviderRadios = document.querySelectorAll('input[name="dni_ruc_provider"]');

  // Función para mostrar/ocultar secciones según toggle
  function toggleApiSections() {
    if (dniSearchToggle.checked) {
      apiProviderSection.classList.remove('hidden');
      updateTokenSection();
    } else {
      apiProviderSection.classList.add('hidden');
      apiPeruTokenSection.classList.add('hidden');
      decolectaTokenSection.classList.add('hidden');
    }
  }

  // Función para mostrar el token correcto según proveedor
  function updateTokenSection() {
    const selectedProvider = document.querySelector('input[name="dni_ruc_provider"]:checked')?.value;

    if (selectedProvider === 'apiperu') {
      apiPeruTokenSection.classList.remove('hidden');
      decolectaTokenSection.classList.add('hidden');
    } else if (selectedProvider === 'decolecta') {
      apiPeruTokenSection.classList.add('hidden');
      decolectaTokenSection.classList.remove('hidden');
    }
  }

  // Eventos
  dniSearchToggle?.addEventListener('change', toggleApiSections);
  apiProviderRadios.forEach(radio => {
    radio.addEventListener('change', updateTokenSection);
  });

  // Inicializar al cargar
  toggleApiSections();
});

// ========================================
// VALIDACIÓN DE PIN EN TIEMPO REAL
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  const currentPinInput = document.getElementById('currentPin');
  const adminPinInput = document.getElementById('adminPin');
  const storedPin = '<?= htmlspecialchars($currentPin ?? '1234') ?>';

  // Validar PIN actual en tiempo real
  currentPinInput?.addEventListener('input', function() {
    // Solo permitir números
    this.value = this.value.replace(/[^0-9]/g, '');

    // Verificar si coincide con el PIN guardado
    if (this.value.length >= 3 && this.value !== storedPin) {
      this.classList.add('border-red-500', 'bg-red-50');
      this.classList.remove('border-slate-200');
    } else if (this.value === storedPin) {
      this.classList.add('border-green-500', 'bg-green-50');
      this.classList.remove('border-slate-200', 'border-red-500', 'bg-red-50');
    } else {
      this.classList.remove('border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
      this.classList.add('border-slate-200');
    }
  });

  // Validar nuevo PIN en tiempo real
  adminPinInput?.addEventListener('input', function() {
    // Solo permitir números
    this.value = this.value.replace(/[^0-9]/g, '');

    // Verificar longitud mínima
    if (this.value.length > 0 && this.value.length < 3) {
      this.classList.add('border-amber-500', 'bg-amber-50');
      this.classList.remove('border-slate-200', 'border-green-500', 'bg-green-50');
    } else if (this.value.length >= 3) {
      this.classList.add('border-green-500', 'bg-green-50');
      this.classList.remove('border-slate-200', 'border-amber-500', 'bg-amber-50');
    } else {
      this.classList.remove('border-amber-500', 'bg-amber-50', 'border-green-500', 'bg-green-50');
      this.classList.add('border-slate-200');
    }
  });
});

// ========================================
// MANEJO DEL FORMULARIO
// ========================================
document.getElementById('settingsForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const theme = formData.get('theme');
  const navbar = formData.get('navbar');
  const header = formData.get('header');
  const appName = formData.get('app_name');
  const gpsEnabled = document.getElementById('gpsToggle').checked ? '1' : '0';
  const igvPercent = formData.get('igv_percent');
  const pricesIncludeIgv = document.getElementById('pricesIncludeIgvToggle').checked ? '1' : '0';
  const currentPin = formData.get('current_pin');
  const adminPin = formData.get('admin_pin');

  // API DNI/RUC
  const enableDniRuc = document.getElementById('dniSearchToggle').checked ? '1' : '0';
  const dniRucProvider = formData.get('dni_ruc_provider');
  const apiPeruToken = formData.get('apiperu_token');
  const decolectaToken = formData.get('decolecta_token');

  // Manager Info
  const managerName = formData.get('manager_name');
  const managerWhatsapp = formData.get('manager_whatsapp');

  // Toast Style
  const toastStyle = formData.get('toast_style');

  // Chatbot
  const chatbotEnabled = document.getElementById('chatbotToggle').checked ? '1' : '0';

  // Mostrar indicador de carga
  const saveBtn = document.getElementById('saveBtn');
  const originalContent = saveBtn.innerHTML;

  // Validar PIN
  if ((currentPin && !adminPin) || (!currentPin && adminPin)) {
    mostrarNotificacion('Datos incompletos', 'Debe ingresar tanto el PIN actual como el nuevo PIN', 'warning');
    saveBtn.innerHTML = originalContent;
    saveBtn.disabled = false;
    return;
  }

  if (adminPin && adminPin.length < 3) {
    mostrarNotificacion('PIN muy corto', 'El nuevo PIN debe tener al menos 3 dígitos', 'warning');
    saveBtn.innerHTML = originalContent;
    saveBtn.disabled = false;
    return;
  }

  // Validar que el PIN actual sea correcto antes de enviar
  const storedPin = '<?= htmlspecialchars($currentPin ?? '1234') ?>';
  if (currentPin && currentPin !== storedPin) {
    mostrarNotificacion('PIN Incorrecto', 'El PIN actual no coincide con el guardado en la base de datos', 'error');
    saveBtn.innerHTML = originalContent;
    saveBtn.disabled = false;
    return;
  }
  saveBtn.innerHTML = '<i class="ph-bold ph-spinner text-lg animate-spin"></i><span>Guardando...</span>';
  saveBtn.disabled = true;

  // Crear FormData para cada sección
  const themeData = new FormData();
  themeData.append('theme', theme);

  const navbarData = new FormData();
  navbarData.append('navbar', navbar);

  const headerData = new FormData();
  headerData.append('header', header);

  const managerData = new FormData();
  managerData.append('manager_name', managerName);
  managerData.append('manager_whatsapp', managerWhatsapp);

  const appNameData = new FormData();
  appNameData.append('app_name', appName);

  const gpsData = new FormData();
  gpsData.append('gps_enabled', gpsEnabled);

  const igvData = new FormData();
  igvData.append('igv_percent', igvPercent);
  igvData.append('prices_include_igv', pricesIncludeIgv);

  const pinData = new FormData();
  pinData.append('current_pin', currentPin);
  pinData.append('admin_pin', adminPin);

  const apiDniData = new FormData();
  apiDniData.append('enable_dni_ruc', enableDniRuc);
  apiDniData.append('dni_ruc_provider', dniRucProvider);
  apiDniData.append('apiperu_token', apiPeruToken);
  apiDniData.append('decolecta_token', decolectaToken);

  // Registro
  const enableRegistration = document.querySelector('input[name="enable_registration"]').checked ? '1' : '0';
  const registrationData = new FormData();
  registrationData.append('enable_registration', enableRegistration);

  // Toast Style
  const toastStyleData = new FormData();
  toastStyleData.append('toast_style', toastStyle);

  // Chatbot
  const chatbotData = new FormData();
  chatbotData.append('enable_chatbot', chatbotEnabled);

  const logoFile = document.getElementById('appLogoInput').files[0];
  const eliminarLogo = document.getElementById('eliminarLogoInput').value === '1';

  // Guardar todo secuencialmente
  fetch('<?= url('/settings/change-theme') ?>', { method: 'POST', body: themeData })
    .then(() => fetch('<?= url('/settings/change-header') ?>', { method: 'POST', body: headerData }))
    .then(() => fetch('<?= url('/settings/change-navbar') ?>', { method: 'POST', body: navbarData }))
    .then(() => fetch('<?= url('/settings/change-app-name') ?>', { method: 'POST', body: appNameData }))
    .then(() => fetch('<?= url('/settings/change-manager-info') ?>', { method: 'POST', body: managerData }))
    .then(() => fetch('<?= url('/settings/change-gps') ?>', { method: 'POST', body: gpsData }))
    .then(() => fetch('<?= url('/settings/change-igv') ?>', { method: 'POST', body: igvData }))
    .then(() => fetch('<?= url('/settings/change-pin') ?>', { method: 'POST', body: pinData }))
    .then(() => fetch('<?= url('/settings/change-api-dni') ?>', { method: 'POST', body: apiDniData }))
    .then(() => fetch('<?= url('/settings/change-registration') ?>', { method: 'POST', body: registrationData }))
    .then(() => fetch('<?= url('/settings/change-toast-style') ?>', { method: 'POST', body: toastStyleData }))
    .then(() => fetch('<?= url('/settings/change-chatbot') ?>', { method: 'POST', body: chatbotData }))
    .then(() => {
      if (eliminarLogo) {
        return fetch('<?= url('/settings/delete-logo') ?>', { method: 'POST' });
      } else if (logoFile) {
        const logoData = new FormData();
        logoData.append('logo', logoFile);
        return fetch('<?= url('/settings/change-logo') ?>', { method: 'POST', body: logoData });
      }
      return Promise.resolve();
    })
    .then(() => {
      window.location.href = '<?= url('/') ?>';
    })
    .catch(error => {
      console.error('Error:', error);
      saveBtn.innerHTML = originalContent;
      saveBtn.disabled = false;
    });
});

// ========================================
// MANEJO DEL LOGO
// ========================================
document.getElementById('appLogoInput').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (!file) return;

  const sizeMB = (file.size / 1024 / 1024).toFixed(2);
  if (file.size > 5 * 1024 * 1024) {
    mostrarNotificacion('Archivo muy grande', `Máximo 5MB. Tu archivo: ${sizeMB}MB`, 'warning');
    this.value = '';
    return;
  }

  const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
  if (!validTypes.includes(file.type)) {
    mostrarNotificacion('Formato no válido', 'Solo se permiten: PNG, JPG, WEBP', 'warning');
    this.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function(event) {
    const previewContainer = document.getElementById('logoPreviewContainer');
    previewContainer.innerHTML = `<img src="${event.target.result}" class="max-w-full max-h-full object-contain animate-fade-in">`;
    document.getElementById('btnEliminarLogo').classList.remove('hidden');
    document.getElementById('logoStatusText').textContent = 'Nuevo logo (sin guardar)';
    document.getElementById('eliminarLogoInput').value = '0';
  };
  reader.readAsDataURL(file);
});

function eliminarLogoPreview() {
  const previewContainer = document.getElementById('logoPreviewContainer');
  previewContainer.innerHTML = '<i class="ph-bold ph-image-broken text-slate-400 text-2xl"></i>';
  document.getElementById('btnEliminarLogo').classList.add('hidden');
  document.getElementById('logoStatusText').textContent = 'Sin logo';
  document.getElementById('appLogoInput').value = '';
  document.getElementById('eliminarLogoInput').value = '1';
}

// Animación de spinner
const style = document.createElement('style');
style.textContent = `
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  .animate-spin {
    animation: spin 1s linear infinite;
  }
`;
document.head.appendChild(style);
</script>

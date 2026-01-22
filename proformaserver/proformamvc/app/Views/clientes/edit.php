

<?php
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;
// Configuración del header ultra premium para editar
$title = 'Editar Cliente';
$subtitle = $cliente['protegido'] ? 'Solo lectura' : 'Actualiza la información';
$back_url = url('/clientes');
$badge = $cliente['protegido'] ? 'Bloqueado' : 'Editando';
$badge_color = $cliente['protegido'] ? 'red' : 'amber';
$search = false;
$section = 'clientes';
include __DIR__ . '/../partials/load_header.php';
?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>

<script src="<?= asset('js/utils/form_validator.js') ?>"></script>
<script src="<?= asset('js/utils/contact_picker.js') ?>"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

<!-- Contenido principal -->
<main class="pt-8 px-5 pb-10 max-w-md mx-auto">

  <form action="<?= url("/clientes/update/{$cliente['id']}") ?>" method="POST" id="clienteForm" class="space-y-3" novalidate>

    <!-- Foto de perfil MEJORADA con efectos premium -->
    <div class="relative bg-gradient-to-br from-white to-slate-50/50 rounded-2xl p-4 shadow-sm border border-blue-100 overflow-hidden animate-fade-in-up">
      <!-- Decoración de fondo -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 rounded-full -mr-16 -mt-16"></div>
      <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-500/5 to-cyan-500/5 rounded-full -ml-12 -mb-12"></div>

      <div class="flex items-center gap-4 relative">
        <!-- Preview de foto con efecto hover mejorado -->
        <div class="relative group flex-shrink-0">
          <div id="preview" onclick="<?= $cliente['protegido'] ? 'mostrarToast(\'Cliente bloqueado. Desbloquea primero para cambiar la foto.\', \'error\')' : 'document.getElementById(\'galleryInput\').click()' ?>" 
          class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center overflow-hidden shadow-lg ring-2 ring-blue-100 transition-all duration-300 
          <?= !$cliente['protegido'] ? 'group-hover:scale-105 group-hover:shadow-2xl group-hover:ring-4 group-hover:ring-blue-200 cursor-pointer active:scale-95' : 'cursor-not-allowed' ?>">
            <?php if ($cliente['foto_url']): ?>
              <img src="<?= asset("/{$cliente['foto_url']}") ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <i class="ph-bold ph-user text-white text-3xl"></i>
            <?php endif; ?>
          </div>
          <!-- Overlay con efecto de edición (solo si no está bloqueado) -->
          <?php if (!$cliente['protegido']): ?>
            <div class="absolute inset-0 rounded-2xl bg-blue-500/0 group-hover:bg-blue-500/20 transition-all duration-300 flex items-center justify-center pointer-events-none">
              <div class="w-8 h-8 bg-white/95 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-110 shadow-lg">
                <i class="ph-bold ph-camera text-blue-600 text-sm"></i>
              </div>
            </div>
          <?php endif; ?>

          <!-- Botón eliminar foto (siempre en el DOM, solo visible cuando hay foto y no está bloqueado) -->
          <?php if (!$cliente['protegido']): ?>
            <button
              type="button"
              id="btnEliminarFoto"
              onclick="event.stopPropagation(); eliminarFoto();"
              class="<?= $cliente['foto_url'] ? '' : 'hidden' ?> absolute -top-2 -right-2 w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 active:scale-95 z-10"
              title="Eliminar foto"
            >
              <i class="ph-bold ph-x text-white text-sm"></i>
            </button>
          <?php endif; ?>

          <!-- Badge de bloqueado -->
          <?php if ($cliente['protegido']): ?>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
              <i class="ph-bold ph-lock text-white text-[10px]"></i>
            </div>
          <?php endif; ?>
        </div>

        <!-- Botones de acción mejorados -->
        <div class="flex-1 flex gap-2">
          <button
            type="button"
            onclick="<?= $cliente['protegido'] ? 'mostrarToast(\'Cliente bloqueado. Desbloquea primero para cambiar la foto.\', \'error\')' : 'document.getElementById(\'cameraInput\').click()' ?>"
            class="flex-1 px-3 py-2.5 <?= $cliente['protegido'] ? 'bg-gradient-to-r from-slate-300 to-slate-400 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700' ?> text-white rounded-xl font-medium transition-all duration-300 shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-1.5 text-sm"
            
          >
            <i class="ph-bold ph-camera"></i>
            <span>Cámara</span>
          </button>

          <button
            type="button"
            onclick="<?= $cliente['protegido'] ? 'mostrarToast(\'Cliente bloqueado. Desbloquea primero para cambiar la foto.\', \'error\')' : 'document.getElementById(\'galleryInput\').click()' ?>"
            class="flex-1 px-3 py-2.5 <?= $cliente['protegido'] ? 'bg-slate-200 cursor-not-allowed' : 'bg-white border-2 border-slate-200 hover:border-blue-300' ?> <?= $cliente['protegido'] ? 'text-slate-400' : 'text-slate-700' ?> rounded-xl font-medium transition-all duration-300 hover:shadow-md hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-1.5 text-sm"
            
          >
            <i class="ph-bold ph-image text-blue-600"></i>
            <span>Galería</span>
          </button>
        </div>

        <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden" <?= $cliente['protegido'] ? 'disabled' : '' ?>>
        <input type="file" id="galleryInput" accept="image/*" class="hidden" <?= $cliente['protegido'] ? 'disabled' : '' ?>>
        <input type="hidden" name="foto_base64" id="fotoBase64">
        <input type="hidden" name="eliminar_foto" id="eliminarFotoInput" value="0">
      </div>
    </div>

    <!-- Información básica MEJORADA -->
    <div class="relative bg-gradient-to-br from-white to-slate-50/50 rounded-2xl p-4 shadow-sm border border-slate-200 overflow-hidden animate-fade-in-up" style="animation-delay: 0.05s">
      <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-indigo-500/5 to-blue-500/5 rounded-full -mr-12 -mt-12"></div>

      <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2 text-sm">
        <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center">
          <i class="ph-bold ph-user-circle text-white text-xs"></i>
        </div>
        <span>Información Personal</span>
        <?php if ($cliente['protegido']): ?>
          <span class="ml-auto text-xs text-red-600 font-medium flex items-center gap-1">
            <i class="ph-bold ph-lock text-xs"></i>
            Bloqueado
          </span>
        <?php endif; ?>
      </h3>

      <div class="space-y-3 relative">
        <!-- Nombre -->
        <div class="group">
          <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-1">
            <i class="ph-bold ph-user text-blue-600 text-sm"></i>
            Nombre Completo
            <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              type="text"
              name="nombre"
              id="nombre"
              required
              value="<?= htmlspecialchars($cliente['nombre']) ?>"
              class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 placeholder-slate-400 <?= $cliente['protegido'] ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'bg-white' ?>"
              placeholder="Ej: Juan Pérez García"
              autocomplete="name"
              <?= $cliente['protegido'] ? 'readonly' : '' ?>
            >
            <?php if (!$cliente['protegido']): ?>
              <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
                <dotlottie-player id="nombre-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
                <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="nombre-error"></i>
              </div>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-500 mt-1 ml-1" id="nombre-hint">Mínimo 4 caracteres</p>
        </div>

        <!-- DNI/RUC y Teléfono -->
        <div class="flex flex-col gap-3">
          <!-- DNI/RUC -->
          <div class="group">
            <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-1">
              <i class="ph-bold ph-identification-card text-blue-600 text-sm"></i>
              DNI / RUC
              <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-2">
              <div class="relative flex-1">
                <input
                  type="text"
                  name="dni_ruc"
                  id="dni_ruc"
                  required
                  value="<?= htmlspecialchars($cliente['dni_ruc']) ?>"
                  class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 placeholder-slate-400 <?= $cliente['protegido'] ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'bg-white' ?>"
                  placeholder="8 u 11 dígitos"
                  <?= $cliente['protegido'] ? 'readonly' : '' ?>
                >
                <?php if (!$cliente['protegido']): ?>
                  <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
                    <dotlottie-player id="dni-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
                    <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="dni-error"></i>
                  </div>
                <?php endif; ?>
              </div>
              <?php if (!$cliente['protegido'] && SettingsHelper::isDniSearchEnabled()): ?>
              <button
                type="button"
                id="btn-consultar-dni"
                class="px-4 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                title="Consultar RENIEC/SUNAT"
              >
                <i class="ph-bold ph-magnifying-glass text-sm"></i>
              </button>
              <?php endif; ?>
            </div>
            <p class="text-xs text-slate-500 mt-1 ml-1" id="dni-hint">8 u 11 dígitos</p>
          </div>

          <!-- Teléfono -->
          <div class="group">
            <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-1">
              <i class="ph-bold ph-phone text-blue-600 text-sm"></i>
              Teléfono / WhatsApp
            </label>
            <div class="flex gap-2">
            <div id="telefonoContainer" class="grid grid-cols-[72px_1fr_44px] items-center relative border-2 border-slate-200 rounded-xl <?= $cliente['protegido'] ? 'bg-slate-50' : 'bg-white' ?> transition flex-1">
              <!-- Prefijo -->
              <div class="flex items-center justify-center gap-2 text-sm text-slate-600 font-medium pointer-events-none">
                <img src="https://flagcdn.com/w20/pe.png" alt="PE" class="w-5 h-4 rounded">
                <span>+51</span>
              </div>

              <!-- Input -->
              <input
                type="tel"
                name="telefono_full"
                id="telefonoInput"
                value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
                class="w-full py-3.5 text-sm outline-none bg-transparent placeholder-slate-400 <?= $cliente['protegido'] ? 'cursor-not-allowed text-slate-600' : '' ?>"
                placeholder="987 654 321"
                <?= $cliente['protegido'] ? 'readonly' : '' ?>
              >

              <!-- WhatsApp Lottie -->
              <div class="flex items-center justify-center pr-4">
                <dotlottie-player id="whatsapp-icon" src="<?= asset('assets/lottie/Whatsapp.json') ?>" background="transparent" speed="1" style="width: 45px; height: 45px;" loop="false" class="opacity-0 transition-opacity"></dotlottie-player>
              </div>
            </div>

            <!-- Botón Importar Contacto (Ahora disponible en Edit) -->
            <?php if (!$cliente['protegido']): ?>
            <button type="button" id="btnImportarContacto"
                    class="hidden px-4 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all shadow-sm hover:shadow-md"
                    title="Importar desde Agenda">
              <i class="ph-bold ph-address-book text-sm"></i>
            </button>
            <?php endif; ?>
            </div>
            <p class="text-xs mt-1 ml-1" id="telefono-hint">
              <span id="telefono-hint-text">Ingresa 9 dígitos sin el +51</span>
            </p>
          </div>
        </div>

        <!-- Email -->
        <div class="group">
          <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-1">
            <i class="ph-bold ph-envelope text-blue-600 text-sm"></i>
            Correo Electrónico
          </label>
          <div class="relative">
            <input
              type="email"
              name="email"
              id="email"
              value="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
              class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 placeholder-slate-400 <?= $cliente['protegido'] ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'bg-white' ?>"
              placeholder="cliente@ejemplo.com"
              <?= $cliente['protegido'] ? 'readonly' : '' ?>
            >
            <?php if (!$cliente['protegido']): ?>
              <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
                <dotlottie-player id="email-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
                <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="email-error"></i>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Dirección -->
        <div class="group">
          <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-1">
            <i class="ph-bold ph-map-pin text-blue-600 text-sm"></i>
            Dirección
          </label>
          <div class="relative">
            <textarea
              name="direccion"
              id="direccion"
              rows="3"
              class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 placeholder-slate-400 resize-none <?= $cliente['protegido'] ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'bg-white' ?>"
              placeholder="Av. Principal 123, Distrito, Ciudad"
              <?= $cliente['protegido'] ? 'readonly' : '' ?>
            ><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
            <?php if (!$cliente['protegido']): ?>
              <div class="absolute right-2 top-3 transition-all">
                <dotlottie-player id="direccion-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px; margin-top: -30px;" loop="false" class="hidden"></dotlottie-player>
                <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="direccion-error"></i>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Ubicación GPS MEJORADA con diseño premium -->
    <?php
    // Verificar si GPS está habilitado desde la base de datos
    $gps_enabled = SettingsHelper::isGpsEnabled();

    // Si GPS está deshabilitado pero el cliente tiene coordenadas, preservarlas con campos hidden
    if (!$gps_enabled && ($cliente['latitud'] || $cliente['longitud'])):
    ?>
      <input type="hidden" name="latitud" value="<?= htmlspecialchars($cliente['latitud'] ?? '') ?>">
      <input type="hidden" name="longitud" value="<?= htmlspecialchars($cliente['longitud'] ?? '') ?>">
    <?php
    endif;

    if ($gps_enabled):
    ?>
    <div class="relative bg-gradient-to-br from-emerald-50 to-green-50/50 rounded-2xl p-4 shadow-sm border border-green-200 overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s">
      <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-green-400/10 to-emerald-400/10 rounded-full -mr-12 -mt-12"></div>

      <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2 text-sm">
        <div class="w-7 h-7 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
          <i class="ph-bold ph-map-pin text-white text-xs"></i>
        </div>
        <span>Ubicación GPS</span>
        <span class="text-xs text-green-600 font-normal">(Opcional)</span>
        <?php if ($cliente['protegido']): ?>
          <span class="ml-auto text-xs text-red-600 font-medium flex items-center gap-1">
            <i class="ph-bold ph-lock text-xs"></i>
            Bloqueado
          </span>
        <?php endif; ?>
      </h3>

      <button
        type="button"
        onclick="<?= $cliente['protegido'] ? 'mostrarToast(\'Cliente bloqueado. Desbloquea primero.\', \'error\')' : 'obtenerUbicacion()' ?>"
        id="btnUbicacion"
        class="w-full px-4 py-3.5 <?= $cliente['protegido'] ? 'bg-gradient-to-r from-slate-400 to-slate-500 cursor-not-allowed' : 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' ?> text-white rounded-xl font-medium transition-all duration-300 shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 text-sm mb-3 relative overflow-hidden group"
        
      >
        <?php if (!$cliente['protegido']): ?>
          <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
        <?php endif; ?>
        <i class="ph-bold <?= $cliente['protegido'] ? 'ph-lock' : 'ph-crosshair' ?> text-xl relative z-10" id="iconUbicacion"></i>
        <span class="relative z-10" id="textoUbicacion">
          <?php if ($cliente['protegido']): ?>
            No disponible (bloqueado)
          <?php else: ?>
            <?= ($cliente['latitud'] && $cliente['longitud']) ? 'Actualizar ubicación' : 'Obtener mi ubicación' ?>
          <?php endif; ?>
        </span>
      </button>

      <div class="grid grid-cols-2 gap-3" id="coordsContainer">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5 flex items-center gap-1">
            <i class="ph-bold ph-globe text-green-600 text-xs"></i>
            Latitud
          </label>
          <input
            type="text"
            name="latitud"
            id="latitud"
            value="<?= htmlspecialchars($cliente['latitud'] ?? '') ?>"
            readonly
            class="w-full px-3 py-2.5 <?= $cliente['protegido'] ? 'bg-slate-50' : 'bg-white' ?> border-2 border-green-200 rounded-lg text-sm font-mono text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20"
            placeholder="-12.046374"
          >
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5 flex items-center gap-1">
            <i class="ph-bold ph-globe text-green-600 text-xs"></i>
            Longitud
          </label>
          <input
            type="text"
            name="longitud"
            id="longitud"
            value="<?= htmlspecialchars($cliente['longitud'] ?? '') ?>"
            readonly
            class="w-full px-3 py-2.5 <?= $cliente['protegido'] ? 'bg-slate-50' : 'bg-white' ?> border-2 border-green-200 rounded-lg text-sm font-mono text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20"
            placeholder="-77.042793"
          >
        </div>
      </div>

      <!-- Indicador visual si ya tiene coordenadas -->
      <?php if ($cliente['latitud'] && $cliente['longitud']): ?>
        <div class="mt-3 px-3 py-2 bg-green-100 border border-green-200 rounded-lg flex items-center gap-2">
          <i class="ph-bold ph-check-circle text-green-600"></i>
          <span class="text-xs text-green-700 font-medium">Ubicación registrada</span>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; // Fin del condicional GPS ?>

    <!-- Protección del cliente MEJORADA -->
    
    <?php
    // --- DEFINICIÓN DE ESTILOS (Lógica PHP) ---
    // Aquí defines los colores una sola vez.
  
    if ($cliente['protegido']) {
        // ESTADO: BLOQUEADO (Tonos Naranja/Ámbar)
        $cardBg   = 'from-red-50 to-red-50/50 border-red-200';
        $iconBg   = 'from-red-500 to-red-500';
        $iconName = 'ph-shield-check';
        $decoBg   = 'from-red-400/10 to-rose-400/10';
        $textInfo = 'text-red-800'; // Opcional: para el texto descriptivo
        $titulo      = 'Cliente Protegido';
        $descripcion = 'Desbloquea para poder eliminar o editar.';
        $switchOnColor = 'peer-checked:bg-red-500';
        $buttonClass = 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 cursor-not-allowed';
    } else {
        // ESTADO: NORMAL (Tonos Azul/Slate)
        $cardBg   = 'from-cyan-50 to-blue-50 border-cyan-200';
        $iconBg   = 'from-blue-500 to-indigo-600';
        $iconName = 'ph-shield'; 
        $decoBg   = 'from-blue-400/10 to-indigo-400/10';
        $textInfo = 'text-blue-800';
        $titulo      = 'Proteger Cliente';
        $descripcion = 'Activa esto para evitar eliminación accidental.';
        $switchOnColor = 'peer-checked:bg-blue-500';
        $buttonClass = 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700';
    }
    ?>

    <div class="relative bg-gradient-to-br <?= $cardBg ?> rounded-2xl p-3.5 shadow-sm border overflow-hidden animate-fade-in-up" style="animation-delay: 0.15s">
      <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br <?= $decoBg ?> rounded-full -mr-12 -mt-12"></div>

        <label class="flex items-center justify-between cursor-pointer group">
          <div class="flex items-center gap-2.5">
           
            <div class="w-12 h-12 bg-gradient-to-br <?= $iconBg ?> rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
              <i class="ph-bold <?= $iconName ?> text-white text-3xl leading-none"></i>

            </div>
            
            <div>
              <p class="font-semibold text-slate-800 text-sm"><?= $titulo ?></p>
              <p class="text-xs <?= $textInfo ?>"><?= $descripcion ?></p>
            </div>
            
          </div>
          
          <div class="relative">
            <input
              type="checkbox"
              name="protegido"
              value="1"
              id="protegidoCheck"
              <?= $cliente['protegido'] ? 'checked' : '' ?>
              class="peer sr-only"
              onchange="manejarCambioProteccion(this)"
            >
            <div class="w-12 h-6 bg-slate-300 rounded-full transition-all duration-300 shadow-inner <?= $switchOnColor ?>"></div>
            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-all duration-300 peer-checked:translate-x-6"></div>
          </div>
        </label>
      </div>

    <!-- Input oculto para el PIN -->
    <input type="hidden" name="admin_pin" id="adminPin" value="">

    <!-- Botones de acción -->
    <div class="fixed bottom-20 left-0 right-0 z-20 animate-fade-in-up px-4" style="animation-delay: 0.4s">
      <div class="flex gap-3 bg-white/95 backdrop-blur-xl p-3 rounded-2xl shadow-2xl border-2 border-slate-200/50 ring-4 ring-slate-100/50 max-w-lg mx-auto">
        <a
          href="<?= url('/clientes') ?>"
          class="flex-1 px-5 py-3 bg-slate-200 border-2 border-slate-400 text-slate-700 rounded-xl font-semibold text-center hover:bg-slate-400 hover:border-slate-600 transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 flex items-center justify-center gap-2 text-sm"
        >
          <i class="ph-bold ph-x"></i>
          <span>Cancelar</span>
        </a>
        <button
          type="submit"
          id="submitBtn"
          class="flex-1 px-5 py-3 <?= $buttonClass ?> text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm"
          <?= $cliente['protegido'] ? ' onclick="mostrarToast(\'Cliente bloqueado. Desbloquea primero para guardar cambios.\', \'error\'); return false;"' : '' ?>
        >
          <?php if (!$cliente['protegido']): ?>
            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
          <?php endif; ?>
          <i class="ph-bold <?= $cliente['protegido'] ? 'ph-lock' : 'ph-floppy-disk' ?> relative z-10"></i>
          <span class="relative z-10"><?= $cliente['protegido'] ? 'Bloqueado' : 'Actualizar' ?></span>
        </button>
      </div>
    </div>

  </form>

</main>

<!-- Modal de PIN -->
<div id="pinModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full animate-scale-in">
    <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-4 rounded-t-2xl">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
          <i class="ph-bold ph-shield-check text-white text-xl"></i>
        </div>
        <div>
          <h3 class="text-white font-bold text-base">PIN de Seguridad</h3>
          <p class="text-white/90 text-xs">Ingresa tu código de administrador</p>
        </div>
      </div>
    </div>

    <div class="p-5">
      <p class="text-slate-600 text-sm mb-4">Para desbloquear este cliente protegido, ingresa el PIN de administrador:</p>
      <label class="block text-xs font-bold text-slate-600 mb-2">Código PIN</label>

      <div class="relative">
        <input
          type="password"
          id="pinInput"
          placeholder="•••••"
          maxlength="6"
          class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 pr-12 text-center text-2xl font-bold tracking-widest focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all"
          autocomplete="off"
        >
        <button
          type="button"
          onclick="togglePinVisibility()"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
        >
          <i class="ph-bold ph-eye text-xl" id="togglePinIcon"></i>
        </button>
      </div>

      <div class="flex gap-2 mt-4">
        <button
          type="button"
          onclick="cerrarModalPin()"
          class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all"
        >
          Cancelar
        </button>
        <button
          type="button"
          onclick="verificarPin()"
          class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-bold transition-all shadow-md hover:shadow-lg"
        >
          Verificar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// ============================================
// SISTEMA DE NOTIFICACIONES DEL SERVIDOR
// ============================================
document.addEventListener('DOMContentLoaded', function() {
  <?php if (isset($_GET['error'])): ?>
    <?php
    $errores = [
      'PIN incorrecto' => ['titulo' => 'PIN Incorrecto', 'mensaje' => 'El PIN ingresado no es válido', 'tipo' => 'error'],
      'default' => ['titulo' => 'Error', 'mensaje' => htmlspecialchars($_GET['error']), 'tipo' => 'error']
    ];
    $error = $_GET['error'];
    $notif = $errores[$error] ?? $errores['default'];
    ?>
    mostrarNotificacion('<?= $notif['titulo'] ?>', '<?= $notif['mensaje'] ?>', '<?= $notif['tipo'] ?>');
  <?php endif; ?>

  <?php if (isset($_GET['success'])): ?>
    mostrarNotificacion('¡Éxito!', 'Los cambios se guardaron correctamente', 'success');
  <?php endif; ?>
});

// Estado actual de protección
const estadoProteccionInicial = <?= $cliente['protegido'] ? 'true' : 'false' ?>;
let intentandoDesbloquear = false;

// ============================================
// MANEJO DE PROTECCIÓN Y PIN
// ============================================
function manejarCambioProteccion(checkbox) {
  // Si está intentando desbloquear (estaba checked y ahora no)
  if (estadoProteccionInicial && !checkbox.checked) {
    // Prevenir el cambio hasta verificar PIN
    checkbox.checked = true;
    intentandoDesbloquear = true;
    mostrarModalPin();
  }
  // Si está bloqueando (no estaba checked y ahora sí)
  else if (!estadoProteccionInicial && checkbox.checked) {
    // Permitir bloquear sin PIN y guardar automáticamente
    intentandoDesbloquear = false;
    mostrarToast('Guardando cambios...', 'info');

    // Enviar el formulario automáticamente
    setTimeout(() => {
      document.getElementById('clienteForm').submit();
    }, 500);
  }
}

function mostrarModalPin() {
  const modal = document.getElementById('pinModal');
  const pinInput = document.getElementById('pinInput');
  modal.classList.remove('hidden');
  setTimeout(() => pinInput.focus(), 100);
}

function cerrarModalPin() {
  const modal = document.getElementById('pinModal');
  const pinInput = document.getElementById('pinInput');
  const checkbox = document.getElementById('protegidoCheck');

  modal.classList.add('hidden');
  pinInput.value = '';

  // Restaurar el estado del checkbox
  if (intentandoDesbloquear) {
    checkbox.checked = true;
  }
  intentandoDesbloquear = false;
}

function togglePinVisibility() {
  const pinInput = document.getElementById('pinInput');
  const icon = document.getElementById('togglePinIcon');

  if (pinInput.type === 'password') {
    pinInput.type = 'text';
    icon.classList.remove('ph-eye');
    icon.classList.add('ph-eye-slash');
  } else {
    pinInput.type = 'password';
    icon.classList.remove('ph-eye-slash');
    icon.classList.add('ph-eye');
  }
}

function verificarPin() {
  const pinInput = document.getElementById('pinInput');
  const pin = pinInput.value.trim();

  if (!pin) {
    mostrarToast('Por favor ingresa el PIN', 'error');
    pinInput.focus();
    return;
  }

  // Validar PIN contra el backend
  fetch('<?= url('/clientes/verificar-pin') ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ pin: pin })
  })
  .then(res => {
    if (!res.ok) {
      throw new Error('Error en la respuesta del servidor');
    }
    return res.json();
  })
  .then(data => {
    if (data.valid) {
      // PIN correcto
      const checkbox = document.getElementById('protegidoCheck');
      const adminPinInput = document.getElementById('adminPin');

      checkbox.checked = false;
      adminPinInput.value = pin; // Guardar PIN para enviar en el form
      intentandoDesbloquear = false;

      cerrarModalPin();
      mostrarToast('Cliente desbloqueado correctamente', 'success');

      // Recargar la página para actualizar todos los estados
      setTimeout(() => {
        document.getElementById('clienteForm').submit();
      }, 1000);
    } else {
      // PIN incorrecto
      mostrarToast('PIN incorrecto. Intenta nuevamente.', 'error');
      pinInput.value = '';
      pinInput.focus();

      // Animar el input como error
      pinInput.classList.add('border-red-500', 'animate-shake');
      setTimeout(() => {
        pinInput.classList.remove('border-red-500', 'animate-shake');
        pinInput.classList.add('border-slate-200');
      }, 500);
    }
  })
  .catch(error => {
    console.error('Error al verificar PIN:', error);
    mostrarToast('Error de conexión. Por favor intenta de nuevo.', 'error');
    pinInput.value = '';
    pinInput.focus();
  });
}

// Permitir verificar con Enter
document.getElementById('pinInput')?.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    verificarPin();
  }
});

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    cerrarModalPin();
  }
});

// ============================================
// MANEJO DE FOTO CON EFECTOS PREMIUM
// ============================================
const cameraInput = document.getElementById('cameraInput');
const galleryInput = document.getElementById('galleryInput');
const preview = document.getElementById('preview');
const fotoBase64Input = document.getElementById('fotoBase64');

function procesarImagen(file) {
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(event) {
    // Animación de carga premium
    preview.innerHTML = `
      <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent"></div>
      </div>
    `;

    setTimeout(() => {
      preview.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover animate-fade-in">`;
      fotoBase64Input.value = event.target.result;
      document.getElementById('eliminarFotoInput').value = '0';

      // Mostrar el botón de eliminar foto
      const btnEliminarFoto = document.getElementById('btnEliminarFoto');
      if (btnEliminarFoto) {
        btnEliminarFoto.classList.remove('hidden');
      }

      // Mostrar toast de éxito
      mostrarToast('Foto cargada correctamente', 'success');
    }, 500);
  };
  reader.readAsDataURL(file);
}

cameraInput?.addEventListener('change', function(e) {
  if (!<?= $cliente['protegido'] ? 'true' : 'false' ?>) {
    procesarImagen(e.target.files[0]);
  }
});

galleryInput?.addEventListener('change', function(e) {
  if (!<?= $cliente['protegido'] ? 'true' : 'false' ?>) {
    procesarImagen(e.target.files[0]);
  }
});

// Eliminar foto
function eliminarFoto() {
  // Restaurar preview al estado inicial
  preview.innerHTML = '<i class="ph-bold ph-user text-white text-3xl"></i>';

  // Marcar para eliminación en el servidor
  document.getElementById('eliminarFotoInput').value = '1';
  fotoBase64Input.value = '';

  // Ocultar el botón de eliminar
  const btnEliminarFoto = document.getElementById('btnEliminarFoto');
  if (btnEliminarFoto) {
    btnEliminarFoto.classList.add('hidden');
  }

  // Feedback visual
  mostrarToast('Foto marcada para eliminar', 'info');

  // Pequeña vibración háptica si está disponible
  if ('vibrate' in navigator) {
    navigator.vibrate(50);
  }
}

// ============================================
// VALIDACIÓN EN TIEMPO REAL CON ICONOS
// ============================================
// Se inicializa en DOMContentLoaded junto con DNI

// ==========================================
// CONSULTA MANUAL DE DNI/RUC CON BOTÓN
// ==========================================
const btnConsultarDni = document.getElementById('btn-consultar-dni');

btnConsultarDni?.addEventListener('click', async function() {
    const input = document.getElementById('dni_ruc');
    if(!input) return;
    const valor = input.value.trim();

    if(!valor) {
        mostrarToast('Ingrese un DNI o RUC válido', 'warning');
        return;
    }

    // Mostrar loading
    btnConsultarDni.disabled = true;
    btnConsultarDni.innerHTML = '<i class="ph ph-spinner animate-spin text-sm"></i>';

    try {
        const response = await fetch('<?= url('/clientes/consultar-dni') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ dni: valor })
        });

        const result = await response.json();

        if (result.success && result.data) {
            const nombreInput = document.getElementById('nombre');
            nombreInput.value = result.data.nombre_completo;
            nombreInput.dispatchEvent(new Event('input'));

            if (result.data.direccion) {
                const direccionInput = document.getElementById('direccion');
                if (direccionInput) {
                    direccionInput.value = result.data.direccion;
                    direccionInput.dispatchEvent(new Event('input'));
                }
            }
             mostrarToast('Documento consultado correctamente', 'success');
        } else {
             mostrarToast(result.message || 'No se encontró información', 'warning');
        }
    } catch (error) {
        console.error('Error al consultar:', error);
        mostrarToast('Error al consultar', 'error');
    } finally {
        btnConsultarDni.disabled = false;
        btnConsultarDni.innerHTML = '<i class="ph-bold ph-magnifying-glass text-sm"></i>';
    }
});

// ============================================
// VALIDACIÓN TELÉFONO CON FEEDBACK VISUAL
// ============================================
const telefonoInput = document.getElementById('telefonoInput');
const whatsappIcon = document.getElementById('whatsapp-icon');
const telefonoContainer = document.getElementById('telefonoContainer');
const telefonoHint = document.getElementById('telefono-hint');
const telefonoHintText = document.getElementById('telefono-hint-text');

// Variable para saber si teléfono está validado
let telefonoValidado = false;
let primerNumeroIncorrecto = false; // Nueva variable para detectar error en primer dígito

telefonoInput?.addEventListener('input', function(e) {
    let value = this.value; // Valor original sin limpiar aún

    // 1. Detección de pegado masivo (+51...)
    // Si tiene +51 lo limpiamos primero para evaluar el número real
    if (value.includes('+51')) {
       value = value.replace('+51', '');
    }

    // Limpiar no numéricos
    value = value.replace(/\D/g, '');

    // 2. Validación diferenciada (Paste vs Typing)
    // DETECTAR SI ES PEGADO para aplicar reglas estrictas
    if (e.inputType === 'insertFromPaste' || e.inputType === 'insertFromDrop') {
      // Reglas estrictas:
      // A) Longitud mayor a 9
      // B) No empieza con 9
      if (value.length > 9 || (value.length > 0 && !value.startsWith('9'))) {
          this.value = ''; // Borrar todo
          
          // Resetear estados visuales
          whatsappIcon.classList.add('opacity-0');
          whatsappIcon.classList.remove('opacity-100');
          telefonoContainer.classList.add('border-red-500');
          telefonoContainer.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
          telefonoHint.classList.add('text-red-600');
          telefonoHint.classList.remove('text-slate-500', 'text-green-600');
          
          let msg = 'Número inválido.';
          if (value.length > 9) msg = 'Número demasiado largo. Solo 9 dígitos.';
          else if (!value.startsWith('9')) msg = 'El número debe empezar con 9.';

          if (telefonoHintText) telefonoHintText.innerHTML = `<i class="ph-bold ph-warning"></i> ${msg}`;
          telefonoValidado = false;
          
          // Toast solicitado
          if(typeof mostrarToast === 'function') {
              mostrarToast(msg, 'error');
          }
          return;
      }
    }

    // Si no es pegado (escritura normal), mantenemos la lógica suave de truncado
    if (value.length > 9) {
      value = value.substring(0, 9);
    }

    // Si hay error en el primer número y el usuario escribe otro dígito
    if (primerNumeroIncorrecto && value.length > 1) {
      // Borrar todo y empezar con 9
      value = '9';
      primerNumeroIncorrecto = false;
    }

    // Validar que el primer dígito sea 9
    if (value.length > 0 && !value.startsWith('9')) {
      primerNumeroIncorrecto = true;
      // Mostrar error inmediatamente
      whatsappIcon.classList.add('opacity-0');
      whatsappIcon.classList.remove('opacity-100');
      telefonoContainer.classList.add('border-red-500');
      telefonoContainer.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
      telefonoHint.classList.add('text-red-600');
      telefonoHint.classList.remove('text-slate-500', 'text-green-600');
      if (telefonoHintText) telefonoHintText.innerHTML = '<i class="ph-bold ph-warning"></i> El primer número debe ser 9';
      telefonoValidado = false;
      this.value = value;
      return;
    } else {
      primerNumeroIncorrecto = false;
    }

    // Formatear con espacios (987 654 321)
    if (value.length > 6) {
      value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6);
    } else if (value.length > 3) {
      value = value.substring(0, 3) + ' ' + value.substring(3);
    }

    this.value = value;

    // Validación visual
    const digitsOnly = value.replace(/\s/g, '');

    if (digitsOnly.length === 9 && digitsOnly.startsWith('9')) {
      // Válido - 9 dígitos que empiezan con 9 ✓
      whatsappIcon.classList.remove('opacity-0');
      whatsappIcon.classList.add('opacity-100');
      if(whatsappIcon.play) whatsappIcon.play();
      telefonoContainer.classList.add('border-green-500');
      telefonoContainer.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
      telefonoHint.classList.add('text-green-600');
      telefonoHint.classList.remove('text-slate-500', 'text-red-600');
      if (telefonoHintText) telefonoHintText.innerHTML = '<i class="ph-bold ph-check-circle"></i> Número correcto';
      telefonoValidado = true;
    } else if (digitsOnly.length > 0) {
      // Inválido - menos de 9 dígitos
      whatsappIcon.classList.add('opacity-0');
      whatsappIcon.classList.remove('opacity-100');
      if(whatsappIcon.stop) whatsappIcon.stop();
      telefonoContainer.classList.add('border-red-500');
      telefonoContainer.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
      telefonoHint.classList.add('text-red-600');
      telefonoHint.classList.remove('text-slate-500', 'text-green-600');
      if (telefonoHintText) telefonoHintText.innerHTML = `Ingresa ${9 - digitsOnly.length} dígito${9 - digitsOnly.length > 1 ? 's' : ''} sin el +51`;
      telefonoValidado = false;
    } else {
      // Vacío
      whatsappIcon.classList.add('opacity-0');
      whatsappIcon.classList.remove('opacity-100');
      if(whatsappIcon.stop) whatsappIcon.stop();
      telefonoContainer.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4', 'ring-green-500/20', 'ring-red-500/20', 'ring-blue-500/20');
      telefonoContainer.classList.add('border-slate-200');
      telefonoHint.classList.remove('text-green-600', 'text-red-600');
      telefonoHint.classList.add('text-slate-500');
      if (telefonoHintText) telefonoHintText.textContent = 'Ingresa 9 dígitos sin el +51';
      telefonoValidado = false;
    }
});

// Manejar focus - agregar ring del color correspondiente
telefonoInput?.addEventListener('focus', function() {
  const value = this.value.replace(/\s/g, '');

  if (value.length === 0) {
    // Vacío - borde y ring azul
    telefonoContainer.classList.add('border-blue-500', 'ring-4', 'ring-blue-500/20');
    telefonoContainer.classList.remove('border-slate-200');
  } else if (telefonoValidado) {
    // Válido - ring verde
    telefonoContainer.classList.add('ring-4', 'ring-green-500/20');
  } else {
    // Inválido - ring rojo
    telefonoContainer.classList.add('ring-4', 'ring-red-500/20');
  }
});

// Manejar blur - quitar ring y restaurar borde si está vacío
telefonoInput?.addEventListener('blur', function() {
  const value = this.value.replace(/\s/g, '');

  telefonoContainer.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');

  // Si está vacío, volver a borde gris
  if (value.length === 0) {
    telefonoContainer.classList.remove('border-blue-500');
    telefonoContainer.classList.add('border-slate-200');
  }
});

// Validación email
const emailInput = document.getElementById('email');
const emailCheck = document.getElementById('email-check');
const emailError = document.getElementById('email-error');

// Variable para saber si email está validado
let emailValidado = false;

// Función para validar formato de email
function validarEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

emailInput?.addEventListener('input', function() {
  const value = this.value.trim();

  if (value === '') {
    // Campo vacío - estado neutral
    emailCheck.classList.add('hidden');
    if(emailCheck.stop) emailCheck.stop();
    emailError.classList.add('hidden');
    this.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'ring-4', 'ring-green-500/20', 'ring-red-500/20', 'ring-blue-500/20');
    this.classList.add('border-slate-200');
    emailValidado = false;
  } else if (validarEmail(value)) {
    // Email válido
    emailCheck.classList.remove('hidden');
    if(emailCheck.play) emailCheck.play();
    emailError.classList.add('hidden');
    this.classList.add('border-green-500');
    this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
    emailValidado = true;
  } else {
    // Email inválido
    emailCheck.classList.add('hidden');
    if(emailCheck.stop) emailCheck.stop();
    emailError.classList.remove('hidden');
    this.classList.add('border-red-500');
    this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
    emailValidado = false;
  }
});

// Manejar focus - agregar ring y borde del color correspondiente
emailInput?.addEventListener('focus', function() {
  const value = this.value.trim();

  if (value.length === 0) {
    // Vacío - borde y ring azul
    this.classList.add('border-blue-500', 'ring-4', 'ring-blue-500/20');
    this.classList.remove('border-slate-200');
  } else if (emailValidado) {
    // Válido - ring verde
    this.classList.add('ring-4', 'ring-green-500/20');
  } else {
    // Inválido - ring rojo
    this.classList.add('ring-4', 'ring-red-500/20');
  }
});

// Manejar blur - quitar ring y restaurar borde si está vacío
emailInput?.addEventListener('blur', function() {
  const value = this.value.trim();

  this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');

  // Si está vacío, volver a borde gris
  if (value.length === 0) {
    this.classList.remove('border-blue-500');
    this.classList.add('border-slate-200');
  }
});

// Validación dirección
const direccionInput = document.getElementById('direccion');
const direccionCheck = document.getElementById('direccion-check');
const direccionError = document.getElementById('direccion-error');

// Variable para saber si dirección está validada
let direccionValidado = false;

// Función para validar que tenga al menos 4 letras (no números)
function validarDireccion(texto) {
  // Contar solo las letras (sin números ni espacios)
  const letras = texto.match(/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/g);
  return letras && letras.length >= 4;
}

direccionInput?.addEventListener('input', function() {
  const value = this.value.trim();

  if (value.length === 0) {
    // Campo vacío - estado neutral
    direccionCheck.classList.add('hidden');
    if(direccionCheck.stop) direccionCheck.stop();
    direccionError.classList.add('hidden');
    this.classList.remove('border-green-500', 'border-red-500', 'ring-4', 'ring-green-500/20', 'ring-red-500/20', 'ring-blue-500/20');
    this.classList.add('border-slate-200');
    direccionValidado = false;
  } else if (validarDireccion(value)) {
    // Válido - al menos 4 letras
    direccionCheck.classList.remove('hidden');
    if(direccionCheck.play) direccionCheck.play();
    direccionError.classList.add('hidden');
    this.classList.add('border-green-500');
    this.classList.remove('border-slate-200', 'border-red-500', 'border-blue-500');
    direccionValidado = true;
  } else {
    // Inválido - menos de 4 letras
    direccionCheck.classList.add('hidden');
    if(direccionCheck.stop) direccionCheck.stop();
    direccionError.classList.remove('hidden');
    this.classList.add('border-red-500');
    this.classList.remove('border-slate-200', 'border-green-500', 'border-blue-500');
    direccionValidado = false;
  }
});

// Manejar focus - agregar ring y borde del color correspondiente
direccionInput?.addEventListener('focus', function() {
  const value = this.value.trim();

  if (value.length === 0) {
    // Vacío - borde y ring azul
    this.classList.add('border-blue-500', 'ring-4', 'ring-blue-500/20');
    this.classList.remove('border-slate-200');
  } else if (direccionValidado) {
    // Válido - ring verde
    this.classList.add('ring-4', 'ring-green-500/20');
  } else {
    // Inválido - ring rojo
    this.classList.add('ring-4', 'ring-red-500/20');
  }
});

// Manejar blur - quitar ring y restaurar borde si está vacío
direccionInput?.addEventListener('blur', function() {
  const value = this.value.trim();

  this.classList.remove('ring-4', 'ring-blue-500/20', 'ring-green-500/20', 'ring-red-500/20');

  // Si está vacío, volver a borde gris
  if (value.length === 0) {
    this.classList.remove('border-blue-500');
    this.classList.add('border-slate-200');
  }
});

// ============================================
// GEOLOCALIZACIÓN PREMIUM
// ============================================
function obtenerUbicacion() {
  const btnUbicacion = document.getElementById('btnUbicacion');
  const iconUbicacion = document.getElementById('iconUbicacion');
  const textoUbicacion = document.getElementById('textoUbicacion');
  const coordsContainer = document.getElementById('coordsContainer');

  if (!navigator.geolocation) {
    mostrarToast('Tu navegador no soporta geolocalización', 'error');
    return;
  }

  // Animación de carga premium
  btnUbicacion.disabled = true;
  iconUbicacion.className = 'ph-bold ph-circle-notch animate-spin text-xl relative z-10';
  textoUbicacion.textContent = 'Obteniendo ubicación...';
  btnUbicacion.classList.add('opacity-75');

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = position.coords.latitude.toFixed(6);
      const lng = position.coords.longitude.toFixed(6);

      document.getElementById('latitud').value = lat;
      document.getElementById('longitud').value = lng;

      // Éxito con animación
      iconUbicacion.className = 'ph-bold ph-check-circle text-xl relative z-10';
      textoUbicacion.textContent = 'Ubicación actualizada';
      btnUbicacion.classList.remove('from-green-500', 'to-emerald-500');
      btnUbicacion.classList.add('from-green-600', 'to-emerald-600');

      // Animar inputs con efecto de pulso
      const inputs = coordsContainer.querySelectorAll('input');
      inputs.forEach(input => {
        input.classList.add('animate-pulse');
        setTimeout(() => input.classList.remove('animate-pulse'), 1000);
      });

      setTimeout(() => {
        btnUbicacion.disabled = false;
        btnUbicacion.classList.remove('opacity-75');
        iconUbicacion.className = 'ph-bold ph-crosshair text-xl relative z-10';
        textoUbicacion.textContent = 'Actualizar ubicación';
        btnUbicacion.classList.remove('from-green-600', 'to-emerald-600');
        btnUbicacion.classList.add('from-green-500', 'to-emerald-500');
      }, 2000);

      mostrarToast('Ubicación obtenida correctamente', 'success');
    },
    (error) => {
      btnUbicacion.disabled = false;
      btnUbicacion.classList.remove('opacity-75');
      iconUbicacion.className = 'ph-bold ph-crosshair text-xl relative z-10';
      textoUbicacion.textContent = 'Obtener mi ubicación';

      let mensaje = 'Error al obtener ubicación';
      if (error.code === 1) mensaje = 'Permiso de ubicación denegado';
      if (error.code === 2) mensaje = 'Ubicación no disponible';
      if (error.code === 3) mensaje = 'Tiempo de espera agotado';

      mostrarToast(mensaje, 'error');
    }
  );
}

// ============================================
// NOTIFICACIONES GESTIONADAS POR toast.js
// ============================================
// Las funciones mostrarToast() y mostrarNotificacion() están disponibles globalmente
// a través del script toast.js cargado en el layout principal

// ============================================
// VALIDACIÓN ANTES DE ENVIAR
// ============================================
document.getElementById('clienteForm')?.addEventListener('submit', function(e) {
  let hasErrors = false;

  // Validar nombre
  if (!FormValidator.validateOnSubmit(e, 'nombre', {
      empty: 'Se necesita un nombre valido',
      invalid: 'El nombre debe contener al menos 4 letras'
  })) {
    hasErrors = true;
  }

  // Validar DNI/RUC
  if (!FormValidator.validateOnSubmit(e, 'dni_ruc', {
      empty: 'Se necesita un DNI o RUC',
      invalid: 'El campo debe tener 8 (DNI) u 11 (RUC) dígitos'
  }, 'dni_ruc')) {
      if(!hasErrors) hasErrors = true;
  }

  if (hasErrors) {
    return false;
  }

  // Mostrar indicador de guardado
  const submitBtn = this.querySelector('button[type="submit"]');
  if (!<?= $cliente['protegido'] ? 'true' : 'false' ?>) {
    submitBtn.innerHTML = `
      <i class="ph-bold ph-circle-notch animate-spin relative z-10"></i>
      <span class="relative z-10">Guardando...</span>
    `;
    submitBtn.disabled = true;
  }
});

// Inicializar validaciones
document.addEventListener('DOMContentLoaded', () => {
    // Setup Nombre
    if (typeof FormValidator !== 'undefined') {
        FormValidator.setupNameInput('nombre', {
            checkId: 'nombre-check',
            errorId: 'nombre-error',
            nextInputId: 'dni_ruc',
            hintId: 'nombre-hint'
        });
    } else {
        console.error('FormValidator no está cargado');
    }

    // Setup DNI/RUC
    FormValidator.setupDniRucInput('dni_ruc', {
        checkId: 'dni-check',
        errorId: 'dni-error',
        nextInputId: 'email',
        hintId: 'dni-hint'
    });
});
</script>

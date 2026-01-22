<?php
// Configuración del header
$title = 'Nuevo Cliente';
$subtitle = 'Completa la información';
$back_url = url('/clientes');
$section = 'clientes';

// Cargar helper de configuración
require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
use App\Helpers\SettingsHelper;

include __DIR__ . '/../partials/load_header.php';
?>

<!-- Contenedor de notificaciones -->
<div id="notificationContainer" class="fixed top-2 right-0 z-[60] space-y-2 max-w-xs"></div>
 
<script src="<?= asset('js/utils/form_validator.js') ?>"></script>
<script src="<?= asset('js/utils/contact_picker.js') ?>"></script>
<script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
<!-- Contenido principal -->
<main class="pt-0 px-5 pb-6 max-w-md mx-auto">

  <form action="<?= url('/clientes/store') ?>" method="POST" id="clienteForm" class="space-y-3" novalidate>

    <!-- Foto de perfil con diseño premium -->
    <div class="relative bg-gradient-to-br from-white to-slate-50/50 rounded-2xl p-4 shadow-sm border border-blue-100 overflow-hidden animate-fade-in-up">
      <!-- Decoración de fondo -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 rounded-full -mr-16 -mt-16"></div>

        <div class="flex items-center gap-4">
          <!-- Preview de foto -->
          <div class="relative group flex-shrink-0">
            <div id="preview" onclick="document.getElementById('galleryInput').click()" class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center overflow-hidden shadow-lg ring-2  ring-blue-100 transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl cursor-pointer active:scale-95">
              <i class="ph-bold ph-user text-white text-3xl"></i>
            </div>
            <!-- Indicador de clic -->
            <div class="absolute inset-0 rounded-2xl bg-blue-500/0 group-hover:bg-blue-500/10 transition-all duration-300 flex items-center justify-center pointer-events-none">
              <div class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:scale-110">
                <i class="ph-bold ph-camera text-blue-600 text-sm"></i>
              </div>
            </div>
            <!-- Botón eliminar foto (solo visible cuando hay foto) -->
            <button
              type="button"
              id="btnEliminarFoto"
              onclick="eliminarFoto()"
              class="hidden absolute -top-2 -right-2 w-7 h-7 bg-gradient-to-br from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110 active:scale-95 z-10"
              title="Eliminar foto"
            >
              <i class="ph-bold ph-x text-white text-sm"></i>
            </button>
          </div>
          <!-- Botones de captura -->
          <div class="flex-1 flex gap-2">
            <button type="button" onclick="document.getElementById('cameraInput').click()" class="flex-1 px-3 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-medium transition-all duration-300 shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-1.5 text-sm">
              <i class="ph-bold ph-camera"></i>
              <span>Cámara</span>
            </button>

            <button type="button" onclick="document.getElementById('galleryInput').click()" class="flex-1 px-3 py-2.5 bg-white border-2 border-slate-200 hover:border-blue-300 text-slate-700 rounded-xl font-medium transition-all duration-300 hover:shadow-md hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-1.5 text-sm">
              <i class="ph-bold ph-image text-blue-600"></i>
              <span>Galería</span>
            </button>
          </div>

          <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden">
          <input type="file" id="galleryInput" accept="image/*" class="hidden">
          <input type="hidden" name="foto_base64" id="fotoBase64">
        </div>
    </div>

    <!-- Información básica -->
    <div class="relative bg-gradient-to-br from-white to-slate-50/50 rounded-2xl p-4 shadow-sm border border-slate-200 overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s">
      <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-indigo-500/5 to-blue-500/5 rounded-full -mr-12 -mt-12"></div>

      <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2 text-sm">
        <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center">
          <i class="ph-bold ph-user-circle text-white text-xs"></i>
        </div>
        <span>Información Personal</span>
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
              class="text-xs w-full px-4 py-3.5  border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 bg-white placeholder-slate-400"
              placeholder="Ej: Juan Pérez García"
              autocomplete="name"
            >
            <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
              <dotlottie-player id="nombre-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
              <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="nombre-error"></i>
            </div>
          </div>
          <p class="text-xs text-slate-500 mt-1 ml-1" id="nombre-hint">Mínimo 4 caracteres</p>
        </div>

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
                class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 bg-white placeholder-slate-400"
                placeholder="8 u 11 dígitos"
              >
              <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
                <dotlottie-player id="dni-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
                <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="dni-error"></i>
              </div>
            </div>
            <?php if (SettingsHelper::isDniSearchEnabled()): ?>
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
       <div id="telefonoContainer" class="grid grid-cols-[72px_1fr_44px] items-center relative border-2 border-slate-200 rounded-xl bg-white transition flex-1">

        <!-- Prefijo -->
        <div class="flex items-center justify-center gap-2 text-sm text-slate-600 font-medium pointer-events-none">
          <img src="https://flagcdn.com/w20/pe.png" alt="PE" class="w-5 h-4 rounded">
          <span>+51</span>
        </div>

        <!-- Input -->
        <input
          type="tel"
          id="telefonoInput"
          class="w-full py-3.5 text-sm outline-none bg-transparent placeholder-slate-400"
          placeholder="987 654 321"
        >

        <!-- WhatsApp Icon -->
        <div class="flex items-center justify-center pr-4">
          <dotlottie-player id="whatsapp-icon" src="<?= asset('assets/lottie/Whatsapp.json') ?>" background="transparent" speed="1" style="width: 45px; height: 45px;" loop="false" class="opacity-0 transition-opacity"></dotlottie-player>
        </div>

      </div>

            <!-- Botón Importar Contacto -->
            <button type="button" id="btnImportarContacto"
                    class="hidden px-4 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all shadow-sm hover:shadow-md"
                    title="Importar desde Agenda">
              <i class="ph-bold ph-address-book text-sm"></i>
            </button>
          </div>

          <p class="text-xs mt-1 text-right transition-colors" id="telefono-hint">
            <i class="ph-bold ph-info"></i>
            <span id="telefono-hint-text">Ingresa 9 dígitos sin el +51</span>
          </p>
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
              class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 bg-white placeholder-slate-400"
              placeholder="cliente@ejemplo.com"
              autocomplete="email"
            >
            <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-all">
              <dotlottie-player id="email-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px;" loop="false" class="hidden"></dotlottie-player>
              <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="email-error"></i>
            </div>
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
              class="text-xs w-full px-4 py-3.5 pr-14 border-2 border-slate-200 rounded-xl focus:outline-none transition-all duration-300 bg-white placeholder-slate-400 resize-none"
              placeholder="Av. Principal 123, Distrito, Ciudad"
            ></textarea>
            <div class="absolute right-2 top-3 transition-all">
              <dotlottie-player id="direccion-check" src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1" style="width: 80px; height: 80px; margin-right: -30px; margin-top: -30px;" loop="false" class="hidden"></dotlottie-player>
              <i class="ph-bold ph-x-circle text-red-500 text-xl hidden" id="direccion-error"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

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
          class="flex-1 px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 relative overflow-hidden group text-sm"
        >
          <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
          <i class="ph-bold ph-check relative z-10"></i>
          <span class="relative z-10">Guardar</span>
        </button>
      </div>
    </div>

  </form>

</main>

<script>
// ============================================
// MANEJO DE FOTO
// ============================================
const cameraInput = document.getElementById('cameraInput');
const galleryInput = document.getElementById('galleryInput');
const preview = document.getElementById('preview');
const fotoBase64Input = document.getElementById('fotoBase64');
const btnEliminarFoto = document.getElementById('btnEliminarFoto');

function procesarImagen(file) {
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(event) {
    // Animación de carga
    preview.innerHTML = `
      <div class="w-full h-full flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent"></div>
      </div>
    `;

    setTimeout(() => {
      preview.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover animate-fade-in">`;
      fotoBase64Input.value = event.target.result;

      // Mostrar botón de eliminar foto con animación
      btnEliminarFoto.classList.remove('hidden');
      btnEliminarFoto.classList.add('animate-scale-in');

      // Pequeña vibración háptica si está disponible
      if ('vibrate' in navigator) {
        navigator.vibrate(30);
      }
    }, 500);
  };
  reader.readAsDataURL(file);
}

// Función para eliminar la foto cargada
function eliminarFoto() {
  // Restaurar preview al estado inicial
  preview.innerHTML = `<i class="ph-bold ph-user text-white text-3xl"></i>`;

  // Limpiar valores
  fotoBase64Input.value = '';
  cameraInput.value = '';
  galleryInput.value = '';

  // Ocultar botón de eliminar con animación
  btnEliminarFoto.classList.add('hidden');
  btnEliminarFoto.classList.remove('animate-scale-in');

  // Feedback visual
  mostrarToast('Foto eliminada', 'info');

  // Pequeña vibración háptica si está disponible
  if ('vibrate' in navigator) {
    navigator.vibrate(50);
  }
}

cameraInput?.addEventListener('change', function(e) {
  procesarImagen(e.target.files[0]);
});

galleryInput?.addEventListener('change', function(e) {
  procesarImagen(e.target.files[0]);
});

// ============================================
// VALIDACIÓN EN TIEMPO REAL CON ICONOS
// ============================================
// Configuración de validación de Nombre usando FormValidator
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



// Validación teléfono con feedback visual completo
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

          telefonoHintText.innerHTML = `<i class="ph-bold ph-warning"></i> ${msg}`;
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
    telefonoHintText.innerHTML = '<i class="ph-bold ph-warning"></i> El primer número debe ser 9';
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
    telefonoHintText.innerHTML = '<i class="ph-bold ph-check-circle"></i> Número correcto';
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
    telefonoHintText.innerHTML = `Ingresa ${9 - digitsOnly.length} dígito${9 - digitsOnly.length > 1 ? 's' : ''} sin el +51`;
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
    telefonoHintText.textContent = 'Ingresa 9 dígitos sin el +51';
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
    this.classList.remove('border-slate-200', 'border-red-500', 'ring-blue-500/20', 'ring-red-500/20');
    direccionValidado = true;
  } else {
    // Inválido - menos de 4 letras
    direccionCheck.classList.add('hidden');
    if(direccionCheck.stop) direccionCheck.stop();
    direccionError.classList.remove('hidden');
    this.classList.add('border-red-500');
    this.classList.remove('border-slate-200', 'border-green-500', 'ring-4', 'ring-green-500/20', 'ring-blue-500/20');
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
// GEOLOCALIZACIÓN
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

  // Animación de carga
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

      // Éxito
      iconUbicacion.className = 'ph-bold ph-check-circle text-xl relative z-10';
      textoUbicacion.textContent = 'Ubicación obtenida';
      btnUbicacion.classList.remove('from-green-500', 'to-emerald-500');
      btnUbicacion.classList.add('from-green-600', 'to-emerald-600');

      // Animar inputs
      coordsContainer.classList.add('animate-fade-in');

      setTimeout(() => {
        btnUbicacion.disabled = false;
        btnUbicacion.classList.remove('opacity-75');
        iconUbicacion.className = 'ph-bold ph-crosshair text-xl relative z-10';
        textoUbicacion.textContent = 'Actualizar ubicación';
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
// ANIMACIONES Y TOAST GESTIONADOS POR toast.js
// ============================================
// function mostrarToast() y estilos eliminados para evitar conflicto con toast.js

// ============================================
// VALIDACIÓN ANTES DE ENVIAR
// ============================================
document.getElementById('clienteForm')?.addEventListener('submit', function(e) {
  let hasErrors = false;

  // Validar nombre
  if (!FormValidator.validateOnSubmit(e, 'nombre', {
      empty: 'Se necesita un nombre valido.',
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
});


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



// Inicializar validaciones
document.addEventListener('DOMContentLoaded', () => {
    // Si ya existe la inicialización de nombre en otro lado, esto complementa
    // Si no, deberíamos inicializar todo aquí, pero por ahora solo lo nuevo:
    
    FormValidator.setupDniRucInput('dni_ruc', {
        checkId: 'dni-check',
        errorId: 'dni-error',
        nextInputId: 'email',
        hintId: 'dni-hint'
    });
});
</script>
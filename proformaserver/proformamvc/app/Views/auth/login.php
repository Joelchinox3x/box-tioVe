<?php
// No navbar en login
$hideNavbar = true;
?>

<div class="flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-sm">
    
    <!-- Logo o Título -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4">
        <i class="ph-bold ph-lock text-3xl text-white"></i>
      </div>
      <h1 class="text-3xl font-bold text-slate-800">Iniciar Sesión</h1>
      <p class="text-slate-500 mt-2">Accede a tu cuenta</p>
    </div>

    <!-- Mensajes de error/éxito -->
    <?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 flex items-start gap-3">
      <i class="ph-bold ph-warning-circle text-xl mt-0.5"></i>
      <span class="text-sm"><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 flex items-start gap-3">
      <i class="ph-bold ph-check-circle text-xl mt-0.5"></i>
      <span class="text-sm"><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <!-- Formulario de Login -->
    <form method="POST" action="<?= url('/login') ?>" class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100">
      
      <!-- Username -->
      <div class="mb-4">
        <label for="username" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-user mr-1"></i> Usuario
        </label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          required
          autofocus
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          placeholder="Ingrese su usuario"
        >
      </div>

      <!-- Password -->
      <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-lock mr-1"></i> Contraseña
        </label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          required
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          placeholder="Ingrese su contraseña"
        >
      </div>

      <!-- Botón de Login -->
      <button
        type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30"
      >
        <i class="ph-bold ph-sign-in text-lg"></i>
        Iniciar Sesión
      </button>

      <!-- Link a registro (siempre visible) -->
      <div class="mt-6 text-center">
        <p class="text-sm text-slate-600">
          ¿No tienes cuenta?
          <?php if (isset($registrationEnabled) && ($registrationEnabled == '1' || $registrationEnabled == 1)): ?>
            <a href="<?= url('/register') ?>" class="text-blue-600 hover:text-blue-700 font-medium">
              Regístrate aquí
            </a>
          <?php else: ?>
            <span class="text-slate-400 line-through cursor-pointer hover:text-slate-500 font-medium" onclick="showRegistrationDisabledModal()">
              Regístrate aquí
            </span>
          <?php endif; ?>
        </p>
      </div>

    </form>

  </div>
</div>

<!-- Modal de Registro Deshabilitado -->
<div id="registrationDisabledModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 animate-fade-in">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
        <i class="ph-bold ph-lock text-3xl text-red-600"></i>
      </div>
      <h3 class="text-xl font-bold text-slate-800 mb-2">Registro Deshabilitado</h3>
      <p class="text-slate-600 text-sm mb-6">
        Lo sentimos, el registro de nuevos usuarios está temporalmente deshabilitado por el administrador del sistema.
      </p>
      <p class="text-slate-500 text-xs mb-6">
        Si necesitas una cuenta, por favor contacta al administrador.
      </p>
      <button onclick="closeRegistrationDisabledModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition flex items-center justify-center gap-2">
        <i class="ph-bold ph-check text-lg"></i>
        Entendido
      </button>
    </div>
  </div>
</div>

<style>
  /* Ocultar navbar en esta vista */
  body { padding-bottom: 0 !important; }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  .animate-fade-in {
    animation: fadeIn 0.2s ease-out;
  }
</style>

<script>
function showRegistrationDisabledModal() {
  document.getElementById('registrationDisabledModal').classList.remove('hidden');
}

function closeRegistrationDisabledModal() {
  document.getElementById('registrationDisabledModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera de él
document.getElementById('registrationDisabledModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeRegistrationDisabledModal();
  }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeRegistrationDisabledModal();
  }
});
</script>

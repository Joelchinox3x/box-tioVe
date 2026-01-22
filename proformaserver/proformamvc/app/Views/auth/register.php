<?php
// No navbar en registro
$hideNavbar = true;
?>

<div class="flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-sm">
    
    <!-- Logo o Título -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-2xl mb-4">
        <i class="ph-bold ph-user-plus text-3xl text-white"></i>
      </div>
      <h1 class="text-3xl font-bold text-slate-800">Crear Cuenta</h1>
      <p class="text-slate-500 mt-2">Regístrate para acceder</p>
    </div>

    <!-- Mensajes de error -->
    <?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 flex items-start gap-3">
      <i class="ph-bold ph-warning-circle text-xl mt-0.5"></i>
      <span class="text-sm"><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <!-- Formulario de Registro -->
    <form method="POST" action="<?= url('/register') ?>" class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100">
      
      <!-- Nombre completo -->
      <div class="mb-4">
        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-identification-card mr-1"></i> Nombre Completo
        </label>
        <input 
          type="text" 
          id="nombre" 
          name="nombre" 
          required
          autofocus
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
          placeholder="Ej: Juan Pérez"
        >
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-envelope mr-1"></i> Email
        </label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          required
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
          placeholder="correo@ejemplo.com"
        >
      </div>

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
          minlength="3"
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
          placeholder="Mínimo 3 caracteres"
        >
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-lock mr-1"></i> Contraseña
        </label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          required
          minlength="6"
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
          placeholder="Mínimo 6 caracteres"
        >
      </div>

      <!-- Confirmar Password -->
      <div class="mb-6">
        <label for="password_confirm" class="block text-sm font-medium text-slate-700 mb-2">
          <i class="ph ph-lock-key mr-1"></i> Confirmar Contraseña
        </label>
        <input 
          type="password" 
          id="password_confirm" 
          name="password_confirm" 
          required
          minlength="6"
          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
          placeholder="Repita la contraseña"
        >
      </div>

      <!-- Botón de Registro -->
      <button 
        type="submit" 
        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-green-600/30"
      >
        <i class="ph-bold ph-user-plus text-lg"></i>
        Crear Cuenta
      </button>

      <!-- Link a login -->
      <div class="mt-6 text-center">
        <p class="text-sm text-slate-600">
          ¿Ya tienes cuenta? 
          <a href="<?= url('/login') ?>" class="text-green-600 hover:text-green-700 font-medium">
            Inicia sesión
          </a>
        </p>
      </div>

    </form>

  </div>
</div>

<style>
  /* Ocultar navbar en esta vista */
  body { padding-bottom: 0 !important; }
</style>

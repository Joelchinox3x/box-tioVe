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

  <!-- Estilos Globales -->
  <link rel="stylesheet" href="<?= asset('css/global.css') ?>">

  <style>
    /* Animaciones mejoradas */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    .animate-slide-in { animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    .animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-500 { animation-delay: 0.5s; }
    .delay-600 { animation-delay: 0.6s; }

    .glass-ultra {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.05) 100%);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
    }

    .gradient-animated {
      background: linear-gradient(-45deg, #1e293b, #1e40af, #334155, #0f172a);
      background-size: 400% 400%;
      animation: gradient 15s ease infinite;
    }

    .card-premium {
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .card-premium:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      opacity: 0.1;
    }

    .particle-1 {
      width: 100px; height: 100px;
      background: linear-gradient(135deg, #3b82f6, #334155);
      top: 10%; right: 10%;
      animation: float 6s ease-in-out infinite;
    }

    .particle-2 {
      width: 60px; height: 60px;
      background: linear-gradient(135deg, #10b981, #06b6d4);
      bottom: 20%; left: 5%;
      animation: float 8s ease-in-out infinite;
      animation-delay: 1s;
    }

    .particle-3 {
      width: 80px; height: 80px;
      background: linear-gradient(135deg, #f59e0b, #ef4444);
      top: 50%; left: 80%;
      animation: float 7s ease-in-out infinite;
      animation-delay: 2s;
    }

    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite;
    }

    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
      50% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">

  <div class="max-w-md mx-auto min-h-screen relative overflow-hidden">

    <!-- Partículas flotantes -->
    <div class="particle particle-1"></div>
    <div class="particle particle-2"></div>
    <div class="particle particle-3"></div>

    <!-- Header con gradiente animado -->
    <header class="gradient-animated relative text-white pt-10 pb-20 px-6 rounded-b-[3rem] shadow-2xl overflow-hidden">

      <?php include __DIR__ . '/../partials/home_settings_button.php'; ?>

      <div class="absolute top-0 right-0 w-80 h-80 bg-blue-400 rounded-full mix-blend-overlay filter blur-3xl opacity-30 -mr-20 -mt-20"></div>
      <div class="absolute bottom-0 left-0 w-60 h-60 bg-slate-600 rounded-full mix-blend-overlay filter blur-3xl opacity-30 -ml-16 -mb-16"></div>
      <div class="absolute top-1/2 left-1/2 w-40 h-40 bg-cyan-400 rounded-full mix-blend-overlay filter blur-3xl opacity-20"></div>

      <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>

      <div class="relative z-10">
        <div class="flex justify-between items-start mb-8 animate-fade-in-up">
          <div class="flex-1">
            <!-- Saludo -->
            <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-2">
              <?php
              $hora = date('G');
              if ($hora >= 5 && $hora < 12) echo "Buenos días ☀️";
              else if ($hora >= 12 && $hora < 19) echo "Buenas tardes 🌤️";
              else echo "Buenas noches 🌙";
              ?>
            </p>

            <h1 class="text-3xl font-black flex items-center gap-3 mb-1">
              <span class="bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
              <span class="w-2.5 h-2.5 rounded-full bg-green-400 pulse-glow"></span>
            </h1>
            <p class="text-blue-100/80 text-sm font-medium">
              Sistema de Proformas Empresarial
            </p>
          </div>

          <!-- Logo -->
          <div class="glass-ultra p-3 rounded-2xl cursor-pointer hover:scale-110 transition-all duration-300 animate-scale-in delay-200 ml-3">
            <img src="<?= asset($app_logo) ?>" alt="Logo" class="w-16 h-16 object-contain">
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3 animate-fade-in-up delay-300">
          <a href="<?= url('/proformas') ?>" class="glass-ultra p-4 rounded-2xl hover:bg-white/30 transition-all duration-500 cursor-pointer group card-premium">
            <div class="flex flex-col items-center text-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400/30 to-blue-600/30 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                <i class="ph-fill ph-file-text text-3xl text-blue-100"></i>
              </div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-blue-100/90 mb-2">Proformas</span>
              <span class="text-3xl font-black text-white mb-1"><?= $stats['total_proformas'] ?? 0 ?></span>
              <p class="text-[10px] text-blue-100/70 font-semibold">generadas</p>
            </div>
          </a>

          <a href="<?= url('/clientes') ?>" class="glass-ultra p-4 rounded-2xl hover:bg-white/30 transition-all duration-500 cursor-pointer group card-premium">
            <div class="flex flex-col items-center text-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-400/30 to-emerald-600/30 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                <i class="ph-fill ph-users text-3xl text-green-100"></i>
              </div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-green-100/90 mb-2">Clientes</span>
              <span class="text-3xl font-black text-white mb-1"><?= $stats['total_clientes'] ?? 0 ?></span>
              <p class="text-[10px] text-green-100/70 font-semibold">activos</p>
            </div>
          </a>

          <a href="<?= url('/inventario') ?>" class="glass-ultra p-4 rounded-2xl hover:bg-white/30 transition-all duration-500 cursor-pointer group card-premium">
            <div class="flex flex-col items-center text-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400/30 to-red-600/30 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                <i class="ph-fill ph-package text-3xl text-orange-100"></i>
              </div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-orange-100/90 mb-2">Inventario</span>
              <span class="text-3xl font-black text-white mb-1"><?= $stats['total_productos'] ?? 0 ?></span>
              <p class="text-[10px] text-orange-100/70 font-semibold">equipos</p>
            </div>
          </a>
        </div>
      </div>
    </header>

    <div class="px-6 -mt-10 relative z-30 animate-scale-in delay-400">
      <div class="bg-white rounded-3xl shadow-2xl border border-white/60 p-4 backdrop-blur-xl">
        <div class="grid grid-cols-3 gap-3">
         
        <!-- Nueva Proforma -->
        <a href="<?= url('/proformas/create') ?>" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 hover:shadow-xl hover:shadow-blue-200/50 transition-all duration-500 card-premium">
            <div class="flex flex-col items-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500 shadow-lg shadow-blue-500/50">
                <i class="ph-bold ph-plus text-2xl text-white"></i>
              </div>
              <span class="text-xs font-bold text-slate-700 text-center leading-tight">Nueva<br>Proforma</span>
            </div>
          </a>

          <a href="<?= url('/clientes/create') ?>" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-50 to-green-100/50 p-4 hover:shadow-xl hover:shadow-green-200/50 transition-all duration-500 card-premium">
            <div class="flex flex-col items-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500 shadow-lg shadow-green-500/50">
                <i class="ph-bold ph-user-plus text-2xl text-white"></i>
              </div>
              <span class="text-xs font-bold text-slate-700 text-center leading-tight">Agregar<br>Cliente</span>
            </div>
          </a>

          <a href="<?= url('/inventario/create') ?>" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100/50 p-4 hover:shadow-xl hover:shadow-orange-200/50 transition-all duration-500 card-premium">
            <div class="flex flex-col items-center">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500 shadow-lg shadow-orange-500/50">
                <i class="ph-bold ph-package text-2xl text-white"></i>
              </div>
              <span class="text-xs font-bold text-slate-700 text-center leading-tight">Agregar<br>Maquinaria</span>
            </div>
          </a>
        </div>
      </div>
    </div>

    <section class="px-6 mt-8 pb-8 animate-fade-in-up delay-500">
      <div class="flex justify-between items-center mb-5">
        <div>
          <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
            <span class="w-1 h-6 bg-gradient-to-b from-blue-500 to-slate-600 rounded-full"></span>
            Actividad Reciente
          </h3>
          <p class="text-xs text-slate-500 mt-1 ml-3">Últimas proformas generadas</p>
        </div>
        <a href="<?= url('/proformas') ?>" class="text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-4 py-2 rounded-xl hover:bg-blue-100 transition-all duration-300 flex items-center gap-1">
          Ver todo <i class="ph-bold ph-arrow-right text-sm"></i>
        </a>
      </div>

      <?php if (!empty($stats['proformas_recientes']) && count($stats['proformas_recientes']) > 0): ?>
        <div class="space-y-3">
          <?php foreach ($stats['proformas_recientes'] as $index => $proforma): ?>
            <a href="<?= url("/proformas/viewPdf/{$proforma['id']}") ?>"
               target="_blank"
               class="block bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all duration-500 overflow-hidden group card-premium animate-slide-in"
               style="animation-delay: <?= 0.6 + ($index * 0.1) ?>s">
              <div class="p-2 flex items-center justify-between">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                  <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center group-hover:from-blue-500 group-hover:to-slate-700 transition-all duration-500 shadow-lg">
                    <i class="ph-bold ph-receipt text-2xl text-slate-500 group-hover:text-white transition-colors duration-500"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-base text-slate-800 truncate mb-1 group-hover:text-blue-600 transition-colors">
                      <?= htmlspecialchars($proforma['cliente_nombre'] ?? 'Cliente') ?>
                    </h4>
                    <div class="flex items-center gap-2">
                      <span class="text-xs text-slate-400 font-semibold">
                        <?= date('d M, Y', strtotime($proforma['fecha_creacion'] ?? 'now')) ?>
                      </span>
                      <span class="text-slate-300">•</span>
                      <span class="text-xs text-blue-600 font-bold">
                        <?= htmlspecialchars('TRA' . str_pad($proforma['id'], 5, '0', STR_PAD_LEFT)) ?>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="text-right ml-4">
                  <span class="block text-lg font-black bg-gradient-to-r from-slate-700 to-slate-900 bg-clip-text text-transparent mb-1">
                    <?= $proforma['moneda'] ?? 'PEN' ?><br><?= number_format($proforma['total'] ?? 0, 0) ?>
                  </span>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-green-700">Activo</span>
                  </span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="bg-gradient-to-br from-white to-blue-50/30 rounded-3xl p-10 text-center border border-blue-100 shadow-lg animate-scale-in delay-600">
          <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center mx-auto mb-5">
            <i class="ph-duotone ph-folder-dashed text-4xl text-blue-500"></i>
          </div>
          <h4 class="font-black text-slate-800 text-lg mb-2">¡Comienza Ahora!</h4>
          <p class="text-sm text-slate-500 mb-6 max-w-xs mx-auto leading-relaxed">
            No tienes proformas aún. Crea tu primera proforma y empieza a gestionar tu negocio de manera profesional.
          </p>
          <a href="<?= url('/proformas/create') ?>"
             class="inline-flex items-center gap-2 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-slate-700 px-6 py-3 rounded-xl hover:shadow-xl hover:shadow-blue-500/50 transition-all duration-500 hover:scale-105">
            <i class="ph-bold ph-plus text-lg"></i>
            Crear Primera Proforma
          </a>
        </div>
      <?php endif; ?>
    </section>

  </div>

</body>
</html>
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
    /* Animaciones */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .animate-fade-in {
      animation: fadeInUp 0.4s ease-out forwards;
      opacity: 0;
    }

    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }

    /* Glassmorphism */
    .glass-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* Header compacto */
    .header-compact {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }
  </style>
</head>
<body class="bg-slate-50">

  <div class="max-w-md mx-auto min-h-screen bg-white shadow-xl">

    <!-- Header Compacto -->
    <header class="header-compact relative text-white pt-6 pb-12 px-5 rounded-b-3xl overflow-hidden">

      <?php include __DIR__ . '/../partials/home_settings_button.php'; ?>

      <!-- Efecto de fondo -->
      <div class="absolute top-0 right-0 w-48 h-48 bg-slate-600 rounded-full opacity-10 blur-3xl -mr-16 -mt-16"></div>

      <!-- Contenido -->
      <div class="relative z-10">

        <!-- Top bar compacto -->
        <div class="flex justify-between items-center mb-4 animate-fade-in">
          <div class="flex-1">
            <p class="text-slate-300 text-xs font-medium mb-1">
              <?php
              $hora = date('G');
              if ($hora >= 5 && $hora < 12) echo "Buenos días ☀️";
              else if ($hora >= 12 && $hora < 19) echo "Buenas tardes 🌤️";
              else echo "Buenas noches 🌙";
              ?>
            </p>
            <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h1>
          </div>

          <!-- Logo compacto -->
          <div class="glass-card p-1.5 rounded-xl">
            <img src="<?= asset($app_logo) ?>" alt="Logo" class="w-14 h-14 object-contain">
          </div>
        </div>

        <!-- Estadísticas compactas -->
        <div class="grid grid-cols-3 gap-2 animate-fade-in delay-100">

          <!-- Proformas -->
          <a href="<?= url('/proformas') ?>" class="glass-card p-2.5 rounded-lg hover:bg-white/20 transition-all duration-300">
            <div class="flex flex-col items-center text-center">
              <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center mb-1.5">
                <i class="ph-fill ph-file-text text-xl text-slate-200"></i>
              </div>
              <span class="text-[9px] font-semibold uppercase text-slate-300 mb-0.5">Proformas</span>
              <span class="text-xl font-bold text-white"><?= $stats['total_proformas'] ?? 0 ?></span>
            </div>
          </a>

          <!-- Clientes -->
          <a href="<?= url('/clientes') ?>" class="glass-card p-2.5 rounded-lg hover:bg-white/20 transition-all duration-300">
            <div class="flex flex-col items-center text-center">
              <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center mb-1.5">
                <i class="ph-fill ph-users text-xl text-slate-200"></i>
              </div>
              <span class="text-[9px] font-semibold uppercase text-slate-300 mb-0.5">Clientes</span>
              <span class="text-xl font-bold text-white"><?= $stats['total_clientes'] ?? 0 ?></span>
            </div>
          </a>

          <!-- Inventario -->
          <a href="<?= url('/inventario') ?>" class="glass-card p-2.5 rounded-lg hover:bg-white/20 transition-all duration-300">
            <div class="flex flex-col items-center text-center">
              <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center mb-1.5">
                <i class="ph-fill ph-package text-xl text-slate-200"></i>
              </div>
              <span class="text-[9px] font-semibold uppercase text-slate-300 mb-0.5">Inventario</span>
              <span class="text-xl font-bold text-white"><?= $stats['total_productos'] ?? 0 ?></span>
            </div>
          </a>

        </div>
      </div>
    </header>

    <!-- Accesos rápidos -->
    <div class="px-5 -mt-6 relative z-20 animate-fade-in delay-200">
      <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-3">
        <div class="grid grid-cols-3 gap-2.5">

          <!-- Nueva Proforma -->
          <a href="<?= url('/proformas/create') ?>" class="group rounded-xl bg-slate-50 hover:bg-slate-100 p-3 transition-all duration-300 border border-slate-200 hover:border-slate-300 hover:shadow-md">
            <div class="flex flex-col items-center">
              <div class="w-11 h-11 rounded-xl bg-slate-700 group-hover:bg-slate-800 flex items-center justify-center mb-1.5 transition-colors shadow-sm">
                <i class="ph-bold ph-plus text-lg text-white"></i>
              </div>
              <span class="text-[11px] font-semibold text-slate-700 text-center leading-tight">Nueva<br>Proforma</span>
            </div>
          </a>

          <!-- Agregar Cliente -->
          <a href="<?= url('/clientes/create') ?>" class="group rounded-xl bg-slate-50 hover:bg-slate-100 p-3 transition-all duration-300 border border-slate-200 hover:border-slate-300 hover:shadow-md">
            <div class="flex flex-col items-center">
              <div class="w-11 h-11 rounded-xl bg-slate-700 group-hover:bg-slate-800 flex items-center justify-center mb-1.5 transition-colors shadow-sm">
                <i class="ph-bold ph-user-plus text-lg text-white"></i>
              </div>
              <span class="text-[11px] font-semibold text-slate-700 text-center leading-tight">Agregar<br>Cliente</span>
            </div>
          </a>

          <!-- Agregar Producto -->
          <a href="<?= url('/inventario/create') ?>" class="group rounded-xl bg-slate-50 hover:bg-slate-100 p-3 transition-all duration-300 border border-slate-200 hover:border-slate-300 hover:shadow-md">
            <div class="flex flex-col items-center">
              <div class="w-11 h-11 rounded-xl bg-slate-700 group-hover:bg-slate-800 flex items-center justify-center mb-1.5 transition-colors shadow-sm">
                <i class="ph-bold ph-package text-lg text-white"></i>
              </div>
              <span class="text-[11px] font-semibold text-slate-700 text-center leading-tight">Agregar<br>Producto</span>
            </div>
          </a>

        </div>
      </div>
    </div>

    <!-- Proformas Recientes -->
    <div class="px-5 mt-6 pb-24 animate-fade-in delay-300">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-bold text-slate-800">Proformas Recientes</h2>
        <a href="<?= url('/proformas') ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-800">Ver todas →</a>
      </div>

      <?php if (!empty($stats['proformas_recientes'])): ?>
        <div class="space-y-2">
          <?php foreach ($stats['proformas_recientes'] as $proforma): ?>
            <a href="<?= url('/proformas/viewPdf/' . $proforma['id']) ?>" 
              target="_blank"
              class="block bg-white rounded-xl border border-slate-200 p-3 hover:border-slate-300 hover:shadow-md transition-all duration-300">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold text-slate-700">TRA<?= str_pad($proforma['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 group-hover:bg-emerald-100 group-hover:border-emerald-300 mt-1 transition-colors">
                          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                          <span class="text-[9px] font-semibold text-emerald-700">Activo</span>
                    </span>
                  </div>
                  <p class="text-xs text-slate-600 font-medium"><?= htmlspecialchars($proforma['cliente_nombre'] ?? 'Sin nombre') ?></p>
                  <p class="text-[10px] text-slate-400"><?= !empty($proforma['fecha_creacion']) ? date('d/m/Y', strtotime($proforma['fecha_creacion'])) : 'Sin fecha' ?></p>
                </div>
                <div class="text-right">
                  <p class="text-lg font-bold text-slate-800"><?= $proforma['moneda'] ?? 'PEN' ?><br><?= number_format($proforma['total'] ?? 0, 0) ?></p>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-8">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <i class="ph-bold ph-file-text text-3xl text-slate-400"></i>
          </div>
          <p class="text-sm text-slate-600">No hay proformas recientes</p>
          <a href="<?= url('/proformas/create') ?>" class="inline-block mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700">Crear primera proforma →</a>
        </div>
      <?php endif; ?>
    </div>

  </div>

</body>
</html>

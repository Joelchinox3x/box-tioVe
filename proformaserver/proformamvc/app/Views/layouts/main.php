<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  
  <title><?= $page_title ?? 'Tradimacova App' ?></title>
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <!-- Estilos Globales del Proyecto -->
  <link rel="stylesheet" href="<?= asset('css/global.css') ?>">
 

  <?= $extra_css ?? '' ?>
</head>
<body class="text-slate-800 pb-32 antialiased selection:bg-blue-500 selection:text-white">

  <div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative shadow-2xl border-x border-slate-100 overflow-hidden">
    
    <?php 
    // Incluir contenido de la vista específica
    echo $content ?? ''; 
    ?>
    
  </div>

  <?php
  // No mostrar navbar en login/register
  if (!isset($hideNavbar) || !$hideNavbar):
      // Determinar qué navbar mostrar según configuración de sesión
      // NOTA: La sesión ya está iniciada en index.php
      $navbar_style = $_SESSION['navbar_style'] ?? 'navbar';
      $navbar_file = __DIR__ . "/../partials/{$navbar_style}.php";

      // Verificar que el archivo exista, si no usar el default
      if (file_exists($navbar_file)) {
          include $navbar_file;
      } else {
          include __DIR__ . '/../partials/navbar.php';
      }
  endif;
  ?>

  <!-- Configuración de Notificaciones (Desde Settings) -->
  <?php
  // Cargar el estilo de toast desde la configuración
  require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
  use App\Helpers\SettingsHelper;
  $toastTheme = SettingsHelper::getToastStyle();
  ?>
  <script>window.TOAST_THEME = '<?= htmlspecialchars($toastTheme) ?>';</script>

  <!-- Sistema de Notificaciones Centralizado -->
  <script src="<?= asset('js/utils/toast.js') ?>"></script>

  <!-- Utilidades Helper -->
  <script src="<?= asset('js/utils/image-optimizer.js') ?>"></script>

  <?= $extra_js ?? '' ?>

</body>
</html>
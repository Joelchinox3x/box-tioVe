<?php
// proformas.php - LISTADO DE PROFORMAS

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

// --- LÓGICA PARA BORRAR ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // 1. PRIMERO: Consultamos la ruta del PDF antes de borrar el registro
        $stmtGet = $pdo->prepare("SELECT pdf_path FROM proformas WHERE id = ?");
        $stmtGet->execute([$id]);
        $registro = $stmtGet->fetch(PDO::FETCH_ASSOC);

        // 2. SEGUNDO: Si existe una ruta guardada, intentamos borrar el archivo físico
        if ($registro && !empty($registro['pdf_path'])) {
            // Construimos la ruta absoluta para evitar errores de carpeta
            // __DIR__ es la carpeta actual donde está proformas.php
            $archivo_fisico = __DIR__ . '/' . $registro['pdf_path']; 
            
            if (file_exists($archivo_fisico)) {
                unlink($archivo_fisico); // <--- ESTO BORRA EL ARCHIVO
            }
        }

        // 3. TERCERO: Ahora sí, borramos de la base de datos
        // (Opcional: Borrar detalles si usas tabla relacionada sin ON DELETE CASCADE)
        // $pdo->prepare("DELETE FROM producto_specs WHERE producto_id = ...") 
        
        $pdo->prepare("DELETE FROM proformas WHERE id = ?")->execute([$id]);
        
        header("Location: proformas.php?msg=deleted");
        exit;

    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
}
// --- CONSULTAR HISTORIAL ---
try {
    $stmtProformas = $pdo->query("SELECT p.*, c.nombre as cliente 
                                  FROM proformas p 
                                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                                  ORDER BY p.id DESC");
    $listaProformas = $stmtProformas->fetchAll(PDO::FETCH_ASSOC);
    $total_proformas = count($listaProformas);
} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>Mis Proformas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
  </style>
</head>
<body class="text-slate-800 pb-32">

<div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

  <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="index.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
                <i class="ph-bold ph-house text-xl"></i>
            </a>
            <h1 class="text-xl font-bold">Mis Proformas</h1>
        </div>
        <div class="bg-blue-600 text-xs font-bold px-3 py-1 rounded-full shadow-lg shadow-blue-500/40 border border-blue-400">
            <?= $total_proformas ?>  Proformas
        </div>
      </div>
  </header>

  <?php if(isset($_GET['msg'])): ?>
    <?php if($_GET['msg']=='created'): ?>
        <div id="alerta" class="m-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl text-sm font-semibold shadow-sm">
            Proforma Creada con éxito.
        </div>
    <?php elseif($_GET['msg']=='deleted'): ?>
        <div id="alerta" class="m-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-semibold shadow-sm">
            Proforma eliminada.
        </div>
    <?php endif; ?>
    <script>
        setTimeout(function() {
            var el = document.getElementById('alerta');
            if(el) { el.style.opacity = '0'; setTimeout(()=>el.remove(), 1000); }
        }, 3000);
    </script>
  <?php endif; ?>

  <div class="p-4">
      <a href="agregar_proforma.php" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-2xl text-center shadow-lg shadow-blue-500/30 mb-6 hover:scale-[1.02] transition active:scale-95 flex items-center justify-center gap-2">
          + Nueva Proforma
      </a>

      <h3 class="font-bold text-gray-800 text-sm mb-4 uppercase tracking-wide opacity-80 flex items-center justify-between">
            Todas las Proformas
            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= $total_proformas ?></span>
      </h3>

      <div class="space-y-2"> 
      <?php if(empty($listaProformas)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center opacity-50">
            <i class="ph-duotone ph-files text-6xl text-slate-300 mb-2"></i>
            <p class="text-slate-500 font-medium">No hay proformas creadas.</p>
        </div>
      <?php else: ?>
        <?php foreach($listaProformas as $i => $p): ?>
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-blue-200 transition-all duration-300 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                <div class="pl-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-400 text-[10px]">#<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime($p['fecha'] ?? 'now')) ?></span>
                    </div>
                    <div class="mb-2">
                        <h3 class="font-bold text-slate-800 text-sm leading-tight line-clamp-2">
                            <?= htmlspecialchars($p['cliente'] ?? 'Sin Cliente') ?>
                        </h3>
                    </div>
                    <div class="flex justify-between items-end border-t border-slate-50 pt-2">
                        <div>
                            <span class="block text-[11px] text-slate-400 uppercase font-bold">Total</span>
                            <span class="block text-xl font-bold text-blue-600 leading-none">
                                S/. <?= number_format($p['total'], 2) ?>
                            </span>
                        </div>
                        <div class="flex gap-1.5 items-center">
    
    <?php if(!empty($p['pdf_path'])): ?>
        
        <?php 
            // 1. Construir la URL completa para que funcione al compartir
            // Asegúrate de que '/proforma-app/' sea el nombre real de tu carpeta en el servidor
            $url_completa = 'https://'.$_SERVER['HTTP_HOST'].'/proforma-app/'.ltrim($p['pdf_path'],'/'); 
        ?>

        <a href="https://wa.me/?text=Hola,%20le%20envío%20su%20cotización:%20<?=urlencode($url_completa)?>" 
           target="_blank" 
           class="px-3 py-2 bg-green-50 text-green-600 rounded-lg text-xs font-bold hover:bg-green-100 transition flex items-center gap-1"
           title="Enviar por WhatsApp">
            <i class="ph-bold ph-whatsapp-logo text-sm"></i> Wsp
        </a>

        <a href="<?=$p['pdf_path']?>" 
           target="_blank" 
           class="px-3 py-2 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 transition flex items-center gap-1"
           title="Ver PDF">
            <i class="ph-bold ph-file-pdf text-sm"></i> PDF
        </a>

    <?php endif; ?>

    <a href="proformas.php?action=delete&id=<?=$p['id']?>" 
       class="h-8 w-8 flex items-center justify-center bg-gray-50 text-gray-400 rounded-lg hover:bg-red-50 hover:text-red-500 transition border border-gray-100"
       onclick="return confirm('¿Estás seguro de eliminar esta proforma?');"
       title="Eliminar">
        <i class="ph-bold ph-trash text-sm"></i>
    </a>

</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
      <?php endif; ?>
      </div>
  </div>
</div>

<?php include 'navbar.php'; ?>

</body>
</html>
<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: inventario.php");
    exit;
}

$id = $_GET['id'];

// 1. Obtener Datos del Equipo
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$eq = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eq) {
    die("Equipo no encontrado");
}

// 2. Obtener Ficha Técnica (Specs)
$stmtSpec = $pdo->prepare("SELECT * FROM producto_specs WHERE producto_id = ? ORDER BY orden ASC");
$stmtSpec->execute([$id]);
$specs = $stmtSpec->fetchAll(PDO::FETCH_ASSOC);

// 3. Procesar Imágenes (Lógica JSON corregida)
$fotos = [];
$imgMain = 'https://via.placeholder.com/400x300?text=Sin+Imagen';

if (!empty($eq['imagenes'])) {
    // Intentar decodificar JSON
    $decoded = json_decode($eq['imagenes'], true);
    
    if (is_array($decoded) && count($decoded) > 0) {
        $fotos = $decoded;
        $imgMain = $fotos[0]; // La primera es la principal
    } elseif (is_string($eq['imagenes'])) {
        // Soporte legacy por si hay alguna guardada como texto plano
        $fotos[] = $eq['imagenes'];
        $imgMain = $eq['imagenes'];
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  <title><?= htmlspecialchars($eq['nombre']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    
    /* Scroll horizontal oculto para la galería */
    .snap-x::-webkit-scrollbar { display: none; }
    .snap-x { -ms-overflow-style: none; scrollbar-width: none; }
    
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
  </style>
</head>
<body class="text-slate-800 pb-32">

  <div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative shadow-2xl border-x border-slate-100">
    
    <header class="glass-header text-white pt-6 pb-4 px-6 fixed top-0 w-full max-w-md z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50 flex justify-between items-center">
        <a href="inventario.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        
        <h1 class="text-lg font-bold truncate px-4">Detalle del Equipo</h1>

        <a href="editar_equipo.php?id=<?= $eq['id'] ?>" class="bg-orange-500/20 p-2 rounded-full hover:bg-orange-500/40 text-orange-200 hover:text-white transition backdrop-blur-md border border-orange-500/30">
            <i class="ph-bold ph-pencil-simple text-xl"></i>
        </a>
    </header>

    <div class="relative w-full h-80 bg-slate-900 overflow-hidden group">
        
        <div class="flex overflow-x-auto snap-x snap-mandatory w-full h-full" id="gallery-container">
            <?php if(count($fotos) > 0): ?>
                <?php foreach($fotos as $foto): ?>
                    <div class="snap-center w-full h-full flex-shrink-0 relative">
                        <img src="<?= htmlspecialchars($foto) ?>" class="w-full h-full object-cover opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#F8FAFC] via-transparent to-black/30"></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-600">
                    <i class="ph-duotone ph-image text-6xl"></i>
                </div>
            <?php endif; ?>
        </div>

        <?php if(count($fotos) > 1): ?>
            <div class="absolute bottom-10 right-4 bg-black/60 backdrop-blur-md text-white text-[10px] px-2 py-1 rounded-full border border-white/20 flex items-center gap-1 z-10">
                <i class="ph-fill ph-images"></i> <?= count($fotos) ?> Fotos
            </div>
            <div class="absolute bottom-10 left-4 text-white/50 text-[10px] animate-pulse z-10">
                <i class="ph-bold ph-arrows-left-right"></i> Desliza para ver más
            </div>
        <?php endif; ?>
    </div>

    <div class="px-5 -mt-8 relative z-10 pb-6">
        
        <div class="bg-white rounded-3xl p-6 shadow-lg border border-slate-100 animate-fade-in-up">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="inline-block bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider mb-1 border border-blue-100">
                        <?= htmlspecialchars($eq['sku']) ?>
                    </span>
                    <h1 class="text-2xl font-bold text-slate-800 leading-tight"><?= htmlspecialchars($eq['nombre']) ?></h1>
                </div>
            </div>

            <div class="flex items-center justify-between mt-2 pb-4 border-b border-slate-50">
                <div class="flex items-center gap-2 text-slate-400 text-sm font-medium">
                    <i class="ph-fill ph-tag"></i>
                    <?= htmlspecialchars($eq['modelo']) ?>
                </div>
                <div class="text-2xl font-bold text-blue-600">
                    <?= $eq['moneda'] === 'USD' ? '$' : 'S/.' ?><?= number_format($eq['precio'], 2) ?>
                </div>
            </div>

            <div class="mt-4 text-sm text-slate-500 leading-relaxed">
                <h3 class="text-xs font-bold text-slate-900 uppercase mb-1 flex items-center gap-1">
                    <i class="ph-bold ph-text-align-left text-blue-500"></i> Descripción
                </h3>
                <?= nl2br(htmlspecialchars($eq['descripcion'])) ?>
            </div>
        </div>

        <?php if(count($specs) > 0): ?>
            <h3 class="mt-6 mb-3 font-bold text-slate-400 uppercase text-[10px] tracking-wider ml-2 flex items-center gap-1">
                <i class="ph-fill ph-list-dashes"></i> Ficha Técnica
            </h3>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-fade-in-up" style="animation-delay: 100ms;">
                <table class="w-full text-sm text-left">
                    <?php foreach($specs as $i => $s): ?>
                    <tr class="<?= $i % 2 == 0 ? 'bg-slate-50/50' : 'bg-white' ?> border-b border-slate-50 last:border-0">
                        <td class="px-4 py-3 font-bold text-slate-500 w-1/3 text-xs uppercase"><?= htmlspecialchars($s['atributo']) ?></td>
                        <td class="px-4 py-3 text-slate-700 font-medium"><?= htmlspecialchars($s['valor']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if(count($fotos) > 1): ?>
            <h3 class="mt-6 mb-3 font-bold text-slate-400 uppercase text-[10px] tracking-wider ml-2 flex items-center gap-1">
                <i class="ph-fill ph-grid-four"></i> Todas las fotos
            </h3>
            <div class="grid grid-cols-4 gap-2 animate-fade-in-up" style="animation-delay: 200ms;">
                <?php foreach($fotos as $idx => $f): ?>
                    <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm cursor-pointer hover:opacity-80 transition"
                         onclick="scrollToImage(<?= $idx ?>)">
                        <img src="<?= htmlspecialchars($f) ?>" class="w-full h-full object-cover">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-lg border-t border-slate-200 p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] z-40">
        <div class="max-w-md mx-auto">
            <a href="proformas.php?action=new&preselect_product=<?= $eq['id'] ?>" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 rounded-2xl text-center shadow-lg shadow-blue-500/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="ph-bold ph-file-plus text-xl"></i>
                Agregar a Cotización
            </a>
        </div>
    </div>

  </div>

  <script>
    // Función simple para mover el scroll de la galería al hacer click en las miniaturas
    function scrollToImage(index) {
        const container = document.getElementById('gallery-container');
        const width = container.offsetWidth;
        container.scrollTo({
            left: width * index,
            behavior: 'smooth'
        });
    }
  </script>

</body>
</html>
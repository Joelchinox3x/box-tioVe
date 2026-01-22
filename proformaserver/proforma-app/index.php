<?php
require 'config.php';

// --- LÓGICA DE NEGOCIO (BACKEND) ---

// 1. Saludo según hora
$hora = date('G');
if ($hora >= 5 && $hora < 12) $saludo = "Buenos días ☀️";
else if ($hora >= 12 && $hora < 19) $saludo = "Buenas tardes 🌤️";
else $saludo = "Buenas noches 🌙";

// 2. Obtener Estadísticas (KPIs)
try {
    // Contar clientes
    $total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $total_proformas = $pdo->query("SELECT COUNT(*) FROM proformas")->fetchColumn();
    $total_equipos   = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn(); // O la tabla que uses para inventario
    
    // Obtener las últimas 3 proformas para el listado rápido
    // Ajusta los nombres de columnas (id, cliente_nombre, fecha, total) a tu BD real
    $stmt = $pdo->query("SELECT p.*, c.nombre as cliente_nombre 
                         FROM proformas p 
                         LEFT JOIN clientes c ON p.cliente_id = c.id 
                         ORDER BY p.id DESC LIMIT 3");
    $recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Si falla la BD (o no existen tablas aún), usamos datos dummy para que no rompa el diseño
    $total_clientes = 0;
    $total_proformas = 0;
    $total_equipos = 0;
    $recientes = [];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  <title>Dashboard | Tradimacova</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F3F4F6; }
    
    /* Animación de entrada suave */
    .fade-in-up { animation: fadeInUp 0.5s ease-out forwards; opacity: 0; transform: translateY(20px); }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }

    /* Glassmorphism para la tarjeta del header */
    .glass-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Ocultar scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="text-slate-800 pb-28 antialiased selection:bg-blue-500 selection:text-white">

  <div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative shadow-2xl border-x border-slate-100 overflow-hidden">

    <header class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 text-white pt-8 pb-16 px-6 rounded-b-[2.5rem] shadow-xl overflow-hidden">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -mr-16 -mt-16 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-red-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -ml-10 -mb-10"></div>

        <div class="relative z-10 flex justify-between items-center mb-6">
            <div>
                <p class="text-blue-200 text-xs font-medium uppercase tracking-wider mb-0.5"><?= $saludo ?></p>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    Tradimacova <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                </h1>
            </div>
            <div class="bg-white/10 p-2 rounded-full backdrop-blur-md border border-white/10 cursor-pointer hover:bg-white/20 transition">
                <img src="assets/img/logo.png" alt="Logo" class="w-8 h-8 object-contain">
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-3 gap-2 px-2 fade-in-up">
            
            <a href="proformas.php" class="glass-card p-2 rounded-xl flex flex-col items-center justify-start relative overflow-hidden group hover:bg-white/20 transition cursor-pointer aspect-[3/4] border border-white/40">
            
                <div class="mb-1 text-blue-300 group-hover:scale-110 transition duration-500 opacity-80">
                    <i class="ph-fill ph-file-text text-4xl"></i>
                </div>

                <div class="flex items-center gap-2 text-blue-200 mb-0.5">
                   <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Proformas</span>
                </div>

                <div class="text-center">
                    <span class="block text-xl font-bold text-white tracking-tight mb-1"><?= $total_proformas ?></span>
                    <p class="text-[11px] text-blue-100/70 font-medium leading-tight">Generadas en total</p>
                </div>
            </a>

            <a href="clientes.php" class="glass-card p-2 rounded-xl flex flex-col items-center justify-start relative overflow-hidden group hover:bg-white/20 transition cursor-pointer aspect-[3/4] border border-white/40">
                <div class="mb-1 text-green-300 group-hover:scale-110 transition duration-500 opacity-80">
                   <i class="ph-fill ph-users text-4xl"></i>
                </div>
                <div class="flex items-center gap-2 text-green-200 mb--0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-green-200">Clientes</span>
                </div>
                <div class="text-center">
                    <span class="block text-xl font-bold text-white tracking-tight mb-1"><?= $total_clientes ?></span>
                    <p class="text-[11px] text-green-100/70 font-medium leading-tight">Cartera activa</p>
                </div>
            </a>

            <a href="inventario.php" class="glass-card p-2 rounded-xl flex flex-col items-center justify-start relative overflow-hidden group hover:bg-white/20 transition cursor-pointer aspect-[3/4] border border-white/40">
                <div class="mb-1 text-red-300 group-hover:scale-110 transition duration-500 opacity-80">
                    <i class="ph-fill ph-tractor text-4xl"></i> 
                </div>

                <div class="flex items-center gap-2 text-red-200 mb--0.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-red-200">Inventario</span>
                </div>
                
                <div class="text-center">  
                    <span class="block text-xl font-bold text-white tracking-tight mb-1"><?= $total_equipos ?></span>
                    <p class="text-[11px] text-red-100/70 font-medium leading-tight">Equipos guardados</p>
                </div>
            </a>

        </div>
    </header>

    <div class="px-6 -mt-8 relative z-20 fade-in-up delay-100">
        <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-2 grid grid-cols-4 gap-2">
            
            <a href="proformas.php?action=new" class="flex flex-col items-center justify-center py-4 px-2 rounded-2xl hover:bg-blue-50 group transition">
                <div class="bg-blue-100 text-blue-600 p-3 rounded-full mb-2 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-blue-200 transition duration-300">
                    <i class="ph-bold ph-plus"></i>
                </div>
                <span class="text-[12px] font-bold text-slate-600 text-center">Nueva Proforma</span>
            </a>

            </a>
            <a href="inventario.php" class="flex flex-col items-center justify-center py-3 px-1 rounded-2xl hover:bg-orange-50 group transition">
                <div class="bg-orange-100 text-orange-600 p-2.5 rounded-full mb-1.5 shadow-sm group-hover:scale-110 transition">
                    <i class="ph-bold ph-package text-lg"></i>
                </div>
                <span class="text-[12px] font-bold text-slate-600 leading-tight text-center">Ver<br>Inventario</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center py-4 px-2 rounded-2xl hover:bg-purple-50 group transition">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-full mb-2 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-purple-200 transition duration-300">
                    <i class="ph-bold ph-chart-pie-slice"></i>
                </div>
                <span class="text-[12px] font-bold text-slate-600">Reportes</span>
            </a>

        </div>
    </div>

    <section class="px-6 mt-8 fade-in-up delay-200">
        <div class="flex justify-between items-end mb-4">
            <h3 class="text-lg font-bold text-slate-800">Recientes</h3>
            <a href="proformas.php" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">Ver todo</a>
        </div>

        <?php if(count($recientes) > 0): ?>
            <div class="flex flex-col gap-3">
                <?php foreach($recientes as $r): ?>
                <a href="ver_proforma.php?id=<?= $r['id'] ?>" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between hover:shadow-md hover:border-blue-100 transition group">
                    <div class="flex items-center gap-4">
                        <div class="bg-slate-100 text-slate-500 p-3 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                            <i class="ph-bold ph-receipt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-800 line-clamp-1"><?= htmlspecialchars($r['cliente_nombre']) ?></h4>
                            <p class="text-[10px] text-slate-400 font-medium">
                                <?= date('d M, Y', strtotime($r['fecha'] ?? 'now')) ?> • #<?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-sm text-slate-800">$<?= number_format($r['total'] ?? 0, 2) ?></span>
                        <span class="inline-block px-2 py-0.5 rounded-md bg-green-50 text-green-600 text-[9px] font-bold mt-1">Activo</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm">
                <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                    <i class="ph-duotone ph-folder-dashed text-3xl text-blue-400"></i>
                </div>
                <h4 class="font-bold text-slate-800 text-sm mb-1">Sin movimientos</h4>
                <p class="text-xs text-slate-400 mb-4 px-4">Aún no has creado proformas este mes. ¡Empieza ahora!</p>
                <a href="proformas.php?action=new" class="text-xs font-bold text-blue-600 border border-blue-100 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition">
                    + Crear Nueva
                </a>
            </div>
        <?php endif; ?>
    </section>

    <div class="h-8"></div>

  </div> 

  <?php include 'navbar.php'; ?>

</body>
</html>
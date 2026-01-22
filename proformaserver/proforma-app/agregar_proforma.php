<?php
// agregar_proforma.php - DISEÑO ACTUALIZADO
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';

// Variables para preselección (si vienes desde inventario)
$preselect_id = $_GET['preselect_product'] ?? null;
$cliente_id = $_GET['cliente_id'] ?? null;
$preselected_item_json = 'null'; 

try {
    // 1. Obtener Clientes
    $stmtClientes = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener Productos
    $stmtProductos = $pdo->query("SELECT * FROM productos ORDER BY nombre ASC");
    $products = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Lógica de Preselección
    if ($preselect_id) {
        foreach ($products as $p) {
            if ($p['id'] == $preselect_id) {
                $preselected_item_json = json_encode([
                    'id' => $p['id'],
                    'name' => $p['nombre'], 
                    'price' => $p['precio'],
                    'sku' => $p['sku'] ?? ''
                ]);
                break;
            }
        }
    }
} catch (PDOException $e) {
    die("Error BD: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>Nueva Cotización</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
    
    /* Estilo para inputs numéricos sin flechas */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  </style>
</head>
<body class="text-slate-800 pb-32">

<div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

  <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50">
      <div class="flex items-center gap-4">
            <a href="proformas.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
                <i class="ph-bold ph-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-bold">Nueva Cotización</h1>
      </div>
  </header>


  <?php if(isset($_GET['msg']) && $_GET['msg']=='error_vacio'): ?>
    <div id="alerta_error" class="mx-4 mt-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-sm font-bold shadow-sm flex items-center gap-2 animate-fade-in-up">
        <i class="ph-fill ph-warning-circle text-xl"></i>
        <span>¡Oye! Faltan datos. Selecciona un cliente y agrega productos.</span>
    </div>
    <script>
        // Desaparecer alerta a los 4 segundos
        setTimeout(function() {
            var el = document.getElementById('alerta_error');
            if(el) { 
                el.style.transition = "opacity 0.5s ease";
                el.style.opacity = '0'; 
                setTimeout(()=>el.remove(), 500); 
            }
        }, 4000);
    </script>
  <?php endif; ?>

  <div class="pt-2 px-4 space-y-4">

        
        <form method="post" action="guardar_proforma.php" class="space-y-3 animate-fade-in-up">
            
            <div class="bg-white p-2 px-4 rounded-3xl shadow-sm border border-slate-100 group hover:border-blue-100 transition">
            <div class="flex justify-between items-center mb-2">    
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-user text-blue-500"></i> Cliente
                </h2>   
                
            </div>
                 <div class="relative">
                    <select name="cliente_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 pl-3 appearance-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-medium transition" required>
                        <option value="">-- Seleccionar Cliente --</option>
                        <?php foreach($clientes as $c): ?>
                            <option value="<?=$c['id']?>" <?= ((int)$cliente_id === (int)$c['id']) ? 'selected' : '' ?>><?=htmlspecialchars($c['nombre'])?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                </div>
                
            </div>

            <div class="bg-white p-2 px-4 rounded-3xl shadow-sm border border-slate-100 group hover:border-indigo-100 transition">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph-fill ph-list-dashes text-indigo-500"></i> Ítems
                    </h2>
         
                </div>

                <div class="relative mb-4">
                    <select id="product_select" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 appearance-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-medium transition">
                        <option value="">+ Agregar producto del inventario...</option>
                        <?php foreach($products as $p): 
                            $jsonVal = htmlspecialchars(json_encode([
                                'id' => $p['id'], 
                                'name' => $p['nombre'], 
                                'price' => $p['precio']
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <option value="<?=$jsonVal?>">
                            <?=htmlspecialchars($p['nombre'])?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                </div>

                <div id="items_container" class="space-y-3 mb-4"></div>

                <button type="button" id="add_manual" class="w-full py-3 text-xs font-bold text-slate-400 hover:text-indigo-600 border border-dashed border-slate-300 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-pencil-simple"></i> Agregar ítem manual (Texto libre)
                </button>
            </div>

            
            <div class="bg-white p-2 px-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
                <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <i class="ph-fill ph-paint-brush-broad text-purple-500"></i> Diseño del PDF
                </h2>
                
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="design_template" value="orange" checked class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition hover:shadow-md text-center">
                            <div class="h-8 w-8 bg-orange-500 rounded-full mx-auto mb-2 flex items-center justify-center text-white">
                                <i class="ph-bold ph-star"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700 block">Orange</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="design_template" value="blue" class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition hover:shadow-md text-center">
                            <div class="h-8 w-8 bg-blue-600 rounded-full mx-auto mb-2 flex items-center justify-center text-white">
                                <i class="ph-bold ph-buildings"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700 block">Azul Pro</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="design_template" value="simple" class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-gray-500 peer-checked:bg-gray-50 transition hover:shadow-md text-center">
                            <div class="h-8 w-8 bg-gray-200 rounded-full mx-auto mb-2 flex items-center justify-center text-gray-600">
                                <i class="ph-bold ph-printer"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700 block">Económico</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Botón flotante inferior con Total -->
            <div class="sticky bottom-24 z-30 space-y-3">
                 <div class="bg-slate-900 text-white p-5 rounded-3xl shadow-xl shadow-slate-900/20 flex justify-between items-center border border-slate-700/50 backdrop-blur-md">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Estimado</p>
                        <div class="text-2xl font-bold flex items-baseline gap-1">
                            <span class="text-lg text-slate-400">S/.</span>
                            <span id="display_total">0.00</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition transform active:scale-95 flex items-center gap-2 border border-white/10">
                        <i class="ph-bold ph-check-circle text-lg"></i> Generar
                    </button>
                </div>
            </div>
        </form>

        <script>
            const container = document.getElementById('items_container');
            
            function addItemRow(item) {
                const idx = Date.now(); 
                // Plantilla actualizada con el diseño nuevo
                const html = `
                <div class="item-row bg-slate-50 p-4 rounded-2xl border border-slate-200 relative animate-fade-in-up group hover:shadow-md transition duration-300">
                    
                    <button type="button" onclick="this.closest('.item-row').remove(); calcTotal();" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg hover:scale-110 transition z-10"><i class="ph-bold ph-x text-xs"></i></button>
                    
                    <input type="hidden" name="items[${idx}][id]" value="${item.id||0}">
                    
                    <div class="mb-3">
                         <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Descripción</label>
                         <input type="text" name="items[${idx}][desc]" value="${item.name}" class="w-full bg-white border border-slate-200 rounded-xl p-2.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-100 outline-none transition" placeholder="Descripción del producto">
                    </div>
                    
                    <div class="flex gap-3">
                        <div class="w-24">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Cant.</label>
                            <input type="number" name="items[${idx}][qty]" value="1" oninput="calcTotal()" class="input-qty w-full bg-white border border-slate-200 rounded-xl p-2.5 text-center text-sm font-bold text-slate-800 focus:ring-2 focus:ring-indigo-100 outline-none">
                        </div>
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Precio Unit.</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">S/.</span>
                                <input type="number" step="0.01" name="items[${idx}][price]" value="${item.price}" oninput="calcTotal()" class="input-price w-full bg-white border border-slate-200 rounded-xl p-2.5 pl-8 text-right text-sm font-bold text-slate-800 focus:ring-2 focus:ring-indigo-100 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-3 mt-3 border-t border-slate-200/60">
                         <label class="flex items-center gap-2 cursor-pointer group/check">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="items[${idx}][ficha]" checked class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow transition-all checked:border-blue-500 checked:bg-blue-500 hover:shadow-md">
                                <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="ph-bold ph-check text-[10px]"></i>
                                </span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover/check:text-blue-600 transition">Incluir Ficha</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer group/check">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="items[${idx}][fotos]" checked class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow transition-all checked:border-indigo-500 checked:bg-indigo-500 hover:shadow-md">
                                <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="ph-bold ph-check text-[10px]"></i>
                                </span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover/check:text-indigo-600 transition">Incluir Fotos</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer group/check bg-white p-2 rounded-lg border border-slate-100 hover:border-purple-200 transition">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="items[${idx}][galeria]" class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow transition-all checked:border-purple-500 checked:bg-purple-500 hover:shadow-md">
                                <span class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="ph-bold ph-check text-[10px]"></i>
                                </span>
                            </div>
                            <div class="leading-tight">
                                <span class="text-[10px] font-bold text-slate-500 group-hover/check:text-purple-600 transition block">Galería Extra</span>
                                <span class="text-[9px] text-slate-300 font-medium">+ Hojas adicionales</span>
                            </div>
                        </label>
                    </div>
                </div>`;
                container.insertAdjacentHTML('beforeend', html);
                calcTotal();
            }

            function calcTotal() {
                let total = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const q = parseFloat(row.querySelector('.input-qty').value) || 0;
                    const p = parseFloat(row.querySelector('.input-price').value) || 0;
                    total += q * p;
                });
                document.getElementById('display_total').innerText = total.toFixed(2);
            }

            document.getElementById('product_select').addEventListener('change', (e) => {
                if(!e.target.value) return;
                addItemRow(JSON.parse(e.target.value));
                e.target.value = '';
            });
            document.getElementById('add_manual').addEventListener('click', () => {
                addItemRow({name: '', price: 0});
            });

            // Preselección si venimos de otra página
            const preselectedItem = <?= $preselected_item_json ?>;
            if (preselectedItem) {
                setTimeout(() => { addItemRow(preselectedItem); }, 100);
            }
        </script>
  </div>
</div>

<?php include 'navbar.php'; ?>

</body>
</html>
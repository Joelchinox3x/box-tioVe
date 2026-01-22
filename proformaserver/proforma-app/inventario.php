<?php
require 'config.php';

// CONTRASEÑA MAESTRA (Asegúrate de tenerla en config.php o usa esta por defecto)
$pass_real = isset($delete_password) ? $delete_password : '123'; 

// --- 1. LÓGICA DE ACCIONES (ELIMINAR / BLOQUEAR / DESBLOQUEAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $pass_ingresada = $_POST['password'] ?? '';
    
    // VERIFICAR CONTRASEÑA (Obligatoria para Eliminar y para Desbloquear)
    // Para "Bloquear" (poner candado) podríamos no pedir clave, pero el usuario pidió seguridad.
    // Aquí aplicamos la regla: Cualquier cambio de estado crítico requiere clave.
    
    if ($pass_ingresada === $pass_real) {
        
        // A) ELIMINAR
        if ($_POST['action'] === 'bulk_delete') {
            $ids = explode(',', $_POST['ids']);
            $count = 0;
            foreach ($ids as $id) {
                // Verificar si está bloqueado antes de borrar
                $stmtCheck = $pdo->prepare("SELECT bloqueado, imagenes FROM productos WHERE id = ?");
                $stmtCheck->execute([$id]);
                $prod = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($prod && $prod['bloqueado'] == 0) { // Solo borra si NO está bloqueado
                    // Borrar fotos
                    if (!empty($prod['imagenes'])) {
                        $fotos = json_decode($prod['imagenes'], true);
                        if (is_array($fotos)) { foreach ($fotos as $ruta) if (file_exists($ruta)) unlink($ruta); }
                        elseif (is_string($prod['imagenes'])) { if (file_exists($prod['imagenes'])) unlink($prod['imagenes']); }
                    }
                    // Borrar registro
                    $stmtDel = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                    if ($stmtDel->execute([$id])) $count++;
                }
            }
            header("Location: inventario.php?msg=bulk_deleted&count=$count");
            exit;
        }

        // B) CAMBIAR ESTADO DE BLOQUEO (LOCK / UNLOCK)
        if ($_POST['action'] === 'toggle_lock') {
            $ids = explode(',', $_POST['ids']);
            $nuevo_estado = (int)$_POST['target_status']; // 1 = Bloquear, 0 = Desbloquear
            
            $sql = "UPDATE productos SET bloqueado = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            foreach ($ids as $id) {
                $stmt->execute([$nuevo_estado, $id]);
            }
            
            $msg = ($nuevo_estado == 1) ? 'locked' : 'unlocked';
            header("Location: inventario.php?msg=$msg");
            exit;
        }

    } else {
        $error = "⛔ Contraseña incorrecta. Acceso denegado.";
    }
}

// --- 2. CONSULTAR INVENTARIO ---
$stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
$equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_equipos = count($equipos);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>Inventario</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
    .custom-checkbox:checked { background-color: #2563EB; border-color: #2563EB; }
  </style>
</head>
<body class="text-slate-800 pb-40">

<div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

  <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50">
      <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
              <a href="index.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
                  <i class="ph-bold ph-house text-xl"></i>
              </a>
              <h1 class="text-xl font-bold">Inventario</h1>
          </div>
          
          <button onclick="toggleSelectionMode()" id="btn-select-mode" class="bg-white/10 px-3 py-1.5 rounded-xl text-xs font-bold border border-white/20 hover:bg-white/20 transition flex items-center gap-1">
             <i class="ph-bold ph-check-square"></i> Seleccionar
          </button>
      </div>
  </header>

  <?php if(isset($_GET['msg'])): ?>
    <div class="m-4 p-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold shadow-sm flex items-center gap-2 animate-fade-in-up">
        <?php if($_GET['msg']=='locked'): ?>
            <i class="ph-fill ph-lock-key text-orange-500"></i> Equipos bloqueados.
        <?php elseif($_GET['msg']=='unlocked'): ?>
            <i class="ph-fill ph-lock-key-open text-green-500"></i> Equipos desbloqueados.
        <?php elseif($_GET['msg']=='bulk_deleted'): ?>
            <i class="ph-fill ph-trash text-red-500"></i> Equipos eliminados.
        <?php endif; ?>
    </div>
    <script>setTimeout(() => { document.querySelector('.animate-fade-in-up').style.display='none'; }, 3000);</script>
  <?php endif; ?>
  
  <?php if(isset($error)): ?>
    <div class="m-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-2xl text-sm font-bold shadow-sm animate-fade-in-up">
        <?= $error ?>
    </div>
  <?php endif; ?>

  <div class="p-4 space-y-4">

    <a href="agregar_equipos.php" id="btn-add-new" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-2xl text-center shadow-lg shadow-blue-500/30 hover:scale-[1.02] transition active:scale-95 flex items-center justify-center gap-2">
        <i class="ph-bold ph-plus-circle text-xl"></i> Nuevo Equipo
    </a>
   
    <h3 class="font-bold text-gray-800 text-sm mb-4 uppercase tracking-wide opacity-80 flex items-center justify-between">
            Todos los Equipos
            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= $total_equipos ?></span>
    </h3>

    <div class="space-y-3">
        <?php if(empty($equipos)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center opacity-50">
                <i class="ph-duotone ph-tractor text-6xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 font-medium">El inventario está vacío.</p>
            </div>
        <?php else: ?>
            
            <?php foreach($equipos as $i => $eq): 
                $imgUrl = 'https://via.placeholder.com/150?text=Sin+Foto';
                if (!empty($eq['imagenes'])) {
                    $fotos = json_decode($eq['imagenes'], true);
                    if (is_array($fotos) && count($fotos) > 0) $imgUrl = $fotos[0];
                    elseif (is_string($eq['imagenes'])) $imgUrl = $eq['imagenes'];
                }
                
                // Determinar estado de bloqueo
                $isLocked = (isset($eq['bloqueado']) && $eq['bloqueado'] == 1);
            ?>
            
            <div class="bg-white p-3 rounded-2xl shadow-sm border <?= $isLocked ? 'border-orange-200 bg-orange-50/30' : 'border-slate-100' ?> hover:shadow-md transition-all duration-300 group relative overflow-hidden animate-fade-in-up"
                 style="animation-fill-mode: both; animation-delay: <?= $i * 50 ?>ms">
                
                <div class="selection-check absolute left-3 top-1/2 -translate-y-1/2 z-20 hidden">
                <input type="checkbox" 
       value="<?= $eq['id'] ?>" 
       data-name="<?= htmlspecialchars($eq['nombre']) ?>"
       data-model="<?= htmlspecialchars($eq['modelo']) ?>"
       data-price="<?= ($eq['moneda'] === 'USD' ? '$' : 'S/.') . ' ' . number_format($eq['precio'], 2) ?>"
       class="custom-checkbox item-checkbox w-6 h-6 rounded-md border-2 border-slate-300 appearance-none cursor-pointer"
       onchange="updateActionPanel()">   </div>

                <div class="card-content flex gap-3 pl-3 transition-all duration-300 relative">
                    
                    <div class="absolute left-[-12px] top-0 bottom-0 w-1 <?= $isLocked ? 'bg-orange-400' : 'bg-blue-500' ?>"></div>

                    <a href="detalle_equipo.php?id=<?= $eq['id'] ?>" class="relative w-20 h-20 flex-shrink-0">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" class="w-full h-full object-cover rounded-xl border border-slate-100 shadow-sm bg-slate-50">
                        <div class="absolute top-0 bottom-0 left-0 right-0 bg-white/80 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                            <i class="ph-bold ph-eye text-2xl text-slate-600"></i>
                        </div>
                    </a>

                    <a href="detalle_equipo.php?id=<?= $eq['id'] ?>" class="flex-1 min-w-0 flex flex-col justify-center">
                        <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-slate-400 text-[10px] uppercase tracking-wider">SKU: <?= htmlspecialchars($eq['sku']) ?></span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm leading-tight line-clamp-2 mb-1"><?= htmlspecialchars($eq['nombre']) ?></h3>
                        <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($eq['modelo']) ?></p>
                        <div class="mt-1"><span class="text-blue-600 font-bold text-sm"><?= ($eq['moneda'] === 'USD') ? '$' : 'S/.' ?> <?= number_format($eq['precio'], 2) ?></span></div>
                    </a>

                    <div class="individual-actions flex flex-col justify-between items-end pl-1 py-1 gap-1">
                        
                        <a href="agregar_proforma.php?action=new&preselect_product=<?= $eq['id'] ?>" class="h-7 w-7 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                            <i class="ph-bold ph-file-plus text-sm"></i>
                        </a>

                        <a href="editar_equipo.php?id=<?= $eq['id'] ?>" class="h-7 w-7 flex items-center justify-center bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition">
                            <i class="ph-bold ph-pencil-simple text-sm"></i>
                        </a>

                        <?php if($isLocked): ?>
                            <button onclick="singleUnlock(<?= $eq['id'] ?>)" class="h-7 w-7 flex items-center justify-center bg-red-50 text-red-500 rounded-lg border border-red-100 hover:bg-red-500 hover:text-white transition shadow-sm" title="Desbloquear">
                                <i class="ph-fill ph-lock-key text-sm"></i>
                            </button>
                        <?php else: ?>
                            <div class="flex flex-col gap-1">
                              
                                
                                <button onclick="singleDelete(<?= $eq['id'] ?>)" class="h-7 w-7 flex items-center justify-center bg-gray-50 text-gray-400 rounded-lg hover:bg-red-50 hover:text-red-500 transition border border-gray-100" title="Eliminar">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
  </div>
</div>

<div id="bulk-actions-bar" class="fixed bottom-24 left-0 w-full z-40 transform translate-y-32 transition-transform duration-300">
    <div class="max-w-[90%] md:max-w-sm mx-auto bg-slate-900 text-white rounded-2xl shadow-2xl p-3 flex justify-between items-center border border-slate-700">
        <div class="pl-2 pr-4 border-r border-slate-700">
            <span id="selected-count" class="text-xl font-bold text-blue-400">0</span>
            <span class="text-[10px] text-slate-400 block uppercase">Items</span>
        </div>
        <div class="flex items-center gap-1 justify-end flex-1">
            
            <button onclick="bulkAction('bulk_delete')" class="p-2.5 rounded-xl hover:bg-white/10 text-red-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-trash text-xl"></i>
                <span class="text-[9px] font-bold">Borrar</span>
            </button>

            <button onclick="bulkQuote()" class="p-2.5 rounded-xl hover:bg-white/10 text-blue-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-file-plus text-xl"></i>
                <span class="text-[9px] font-bold">Cotizar</span>
            </button>

            <button onclick="bulkWhatsapp()" class="p-2.5 rounded-xl hover:bg-white/10 text-green-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-whatsapp-logo text-xl"></i>
                <span class="text-[9px] font-bold">Wsp</span>
            </button>

            <button onclick="bulkLockToggle()" class="p-2.5 rounded-xl hover:bg-white/10 text-orange-400 transition flex flex-col items-center gap-1">
                <i class="ph-fill ph-lock-key text-xl"></i>
                <span class="text-[9px] font-bold">Lock</span>
            </button>
            
            <button onclick="alert('Funcionalidad de Publicidad pronto...')" class="p-2.5 rounded-xl hover:bg-white/10 text-yellow-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-megaphone text-xl"></i>
                <span class="text-[9px] font-bold">Ads</span>
            </button>
        </div>
    </div>
</div>

<form id="form-global-action" method="POST" action="inventario.php" class="hidden">
    <input type="hidden" name="action" id="input-action">
    <input type="hidden" name="ids" id="input-ids">
    <input type="hidden" name="password" id="input-pass">
    <input type="hidden" name="target_status" id="input-target-status"> </form>

    <?php include 'navbar.php'; ?>

<script>
    let isSelectionMode = false;

    function toggleSelectionMode() {
        isSelectionMode = !isSelectionMode;
        const btn = document.getElementById('btn-select-mode');
        const checkboxes = document.querySelectorAll('.selection-check');
        const contents = document.querySelectorAll('.card-content');
        const individualActions = document.querySelectorAll('.individual-actions');
        const btnAddNew = document.getElementById('btn-add-new');

        if (isSelectionMode) {
            btn.innerHTML = '<i class="ph-bold ph-x"></i> Cancelar';
            btn.classList.add('bg-red-500', 'border-red-400'); btn.classList.remove('bg-white/10');
            checkboxes.forEach(el => el.classList.remove('hidden'));
            contents.forEach(el => el.classList.add('pl-10'));
            individualActions.forEach(el => el.classList.add('opacity-20', 'pointer-events-none'));
            btnAddNew.classList.add('hidden');
        } else {
            btn.innerHTML = '<i class="ph-bold ph-check-square"></i> Seleccionar';
            btn.classList.remove('bg-red-500', 'border-red-400'); btn.classList.add('bg-white/10');
            checkboxes.forEach(el => { el.classList.add('hidden'); el.querySelector('input').checked = false; });
            contents.forEach(el => el.classList.remove('pl-10'));
            individualActions.forEach(el => el.classList.remove('opacity-20', 'pointer-events-none'));
            btnAddNew.classList.remove('hidden');
            updateActionPanel();
        }
    }

    function updateActionPanel() {
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        document.getElementById('selected-count').innerText = count;
        const bar = document.getElementById('bulk-actions-bar');
        count > 0 ? bar.classList.remove('translate-y-32') : bar.classList.add('translate-y-32');
    }

    // --- ACCIONES CON CONTRASEÑA ---

    function submitProtectedAction(action, ids, targetStatus = null, actionName = "realizar esta acción") {
        // Ahora el prompt te dice explícitamente qué vas a hacer
        const password = prompt(`🔒 SEGURIDAD: Ingrese su contraseña para ${actionName}:`);
        
        if (password) {
            document.getElementById('input-action').value = action;
            document.getElementById('input-ids').value = ids;
            document.getElementById('input-pass').value = password;
            
            if(targetStatus !== null) {
                document.getElementById('input-target-status').value = targetStatus;
            }
            
            document.getElementById('form-global-action').submit();
        }
    }

    // A. ACCIONES INDIVIDUALES
    function singleDelete(id) {
        submitProtectedAction('bulk_delete', id, null, "ELIMINAR");
    }
    
    function singleLock(id) {
        submitProtectedAction('toggle_lock', id, 1, "BLOQUEAR");
    }

    function singleUnlock(id) {
        submitProtectedAction('toggle_lock', id, 0, "DESBLOQUEAR");
    }

    // B. ACCIONES MASIVAS
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value).join(',');
    }

    function bulkAction(actionName) {
        const ids = getSelectedIds();
        if(!ids) return;
        submitProtectedAction(actionName, ids);
    }

    function bulkLockToggle() {
        // 1. Obtener los checkboxes marcados
        const checked = document.querySelectorAll('.item-checkbox:checked');
        if (checked.length === 0) return;

        // 2. DETECCIÓN INTELIGENTE:
        // Miramos el PRIMER elemento seleccionado para decidir la acción de grupo.
        // Buscamos si la tarjeta padre tiene la clase de borde naranja (que indica bloqueo).
        const firstCard = checked[0].closest('.group');
        const isCurrentlyLocked = firstCard.classList.contains('border-orange-200');

        // 3. Definir la acción contraria
        // Si está bloqueado (true) -> queremos Desbloquear (0)
        // Si está libre (false) -> queremos Bloquear (1)
        const targetStatus = isCurrentlyLocked ? 0 : 1; 
        
        // Texto para el prompt (opcional, para que se vea bonito)
        const actionName = isCurrentlyLocked ? "DESBLOQUEAR" : "BLOQUEAR";

        // 4. Obtener IDs y enviar
        const ids = Array.from(checked).map(cb => cb.value).join(',');

        // 5. Llamamos a la función protegida (Esta pedirá la contraseña directamente)
        // Le pasamos el 'actionName' extra para que el prompt te diga qué estás haciendo
        submitProtectedAction('toggle_lock', ids, targetStatus, actionName);
    }

    function bulkQuote() {
        const ids = getSelectedIds();
        if(ids) window.location.href = `proformas.php?action=new&multi_select=${ids}`;
    }
    
    function bulkWhatsapp() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) return;

    // 1. Encabezado del mensaje
    let mensaje = "👋 *Hola! Te comparto la lista de equipos que te interesaron:*\n\n";
    mensaje += "─────────────────────\n\n";

    // 2. Recorremos cada equipo seleccionado
    checked.forEach(cb => {
        // Obtenemos los datos que guardamos en el HTML
        const id = cb.value;
        const nombre = cb.getAttribute('data-name');
        const modelo = cb.getAttribute('data-model') || 'N/A';
        const precio = cb.getAttribute('data-price');
        
        // Generamos el enlace al detalle (ajusta la carpeta si es necesario)
        // Esto crea algo como: https://tuweb.com/detalle_equipo.php?id=15
        const urlEquipo = window.location.origin + window.location.pathname.replace('inventario.php', '') + 'detalle_equipo.php?id=' + id;

        // Construimos la ficha del equipo
        mensaje += `🚜 *${nombre}*\n`;
        mensaje += `📄 Modelo: ${modelo}\n`;
        mensaje += `💰 Precio: ${precio}\n`;
        mensaje += `🔗 Ver fotos y detalles:\n${urlEquipo}\n\n`;
        mensaje += "─────────────────────\n\n";
    });

    // 3. Pie de página
    mensaje += "💬 *Quedo atento a tus comentarios.*";

    // 4. Abrir WhatsApp
    const url = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}
</script>

</body>
</html>
<?php
// clientes.php - DISEÑO INVENTARIO (Compacto)
require 'config.php';

// CONSULTAR CLIENTES
$stmt = $pdo->query("SELECT * FROM clientes ORDER BY fecha_modificacion DESC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = count($clientes);
?>
<!doctype html>
<html lang="es">
<?php 
    $page_title = "Cartera de Clientes"; // Definimos el título
    include 'head.php'; // Incluimos el archivo mágico
?>
<body class="text-slate-800 pb-40">

<div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

  <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50">
      <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
              <a href="index.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
                  <i class="ph-bold ph-house text-xl"></i>
              </a>
              <h1 class="text-xl font-bold">Cartera de Clientes</h1>
          </div>
          
          <button onclick="toggleSelectionMode()" id="btn-select-mode" class="bg-white/10 px-3 py-1.5 rounded-xl text-xs font-bold border border-white/20 hover:bg-white/20 transition flex items-center gap-1">
             <i class="ph-bold ph-check-square"></i> Seleccionar
          </button>
      </div>
  </header>

  <?php if(isset($_GET['msg'])): ?>
    <div class="m-4 p-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold shadow-sm flex items-center gap-2 animate-fade-in-up cursor-pointer" onclick="this.remove()">
        <?php if($_GET['msg']=='created'): ?>
            <i class="ph-fill ph-check-circle text-green-500 text-lg"></i> ¡Cliente registrado correctamente!
        <?php elseif($_GET['msg']=='deleted'): ?>
            <i class="ph-fill ph-trash text-red-500 text-lg"></i> Cliente eliminado.
        <?php elseif($_GET['msg']=='updated'): ?>
            <i class="ph-fill ph-pencil-circle text-blue-500 text-lg"></i> Cliente actualizado.
        <?php elseif($_GET['msg']=='deleted_multiple'): ?>
            <i class="ph-fill ph-trash text-red-500 text-lg"></i> Clientes eliminados.
        <?php elseif($_GET['msg']=='locked'): ?>
            <i class="ph-fill ph-lock-key text-orange-500 text-lg"></i> ¡Acción Bloqueada! Cliente protegido.
        <?php endif; ?>
    </div>
    <script>
        setTimeout(() => { 
            const alert = document.querySelector('.animate-fade-in-up');
            if(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
  <?php endif; ?>

  <div class="p-4 space-y-4">

    <a href="agregar_cliente.php" id="btn-add-new" class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-2xl text-center shadow-lg shadow-blue-500/30 hover:scale-[1.02] transition active:scale-95 flex items-center justify-center gap-2">
        <i class="ph-bold ph-user-plus text-xl"></i> Nuevo Cliente
    </a>
   
    <h3 class="font-bold text-gray-800 text-sm mb-4 uppercase tracking-wide opacity-80 flex items-center justify-between px-1">
            Mis Clientes
            <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= $total_clientes ?></span>
    </h3>

    <div class="space-y-3">
        <?php if(empty($clientes)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center opacity-50">
                <i class="ph-duotone ph-users-three text-6xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 font-medium">No hay clientes registrados.</p>
            </div>
        <?php else: ?>
            
            <?php foreach($clientes as $i => $c): 
                $isLocked = ($c['protegido'] == 1);
                
                // Preparamos info para checkbox/compartir
                $lines = [];
                $lines[] = "👤 " . $c['nombre'];
                if(!empty($c['dni_ruc'])) $lines[] = "🆔 " . $c['dni_ruc'];
                if(!empty($c['telefono'])) $lines[] = "📱 " . $c['telefono'];
                $info_completa = implode("\n", $lines);
            ?>
            
            <div class="bg-white p-3 rounded-2xl shadow-sm border <?= $isLocked ? 'border-orange-200 bg-orange-50/20' : 'border-slate-100' ?> hover:shadow-md transition-all duration-300 group relative overflow-hidden animate-fade-in-up"
                 style="animation-fill-mode: both; animation-delay: <?= $i * 50 ?>ms">
                
                <div class="selection-check absolute left-3 top-1/2 -translate-y-1/2 z-20 hidden">
                    <input type="checkbox" 
                           value="<?= $c['id'] ?>" 
                           data-info="<?= htmlspecialchars($info_completa) ?>"
                           class="client-check custom-checkbox w-6 h-6 rounded-md border-2 border-slate-300 appearance-none cursor-pointer bg-white"
                           onchange="actualizarContador()">   
                </div>

                <div class="card-content flex gap-3 pl-3 transition-all duration-300 relative">
                    
                    <div class="absolute left-[-12px] top-0 bottom-0 w-1 <?= $isLocked ? 'bg-orange-400' : 'bg-blue-500' ?>"></div>

                    <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="relative w-16 h-16 flex-shrink-0">
                        <?php if(!empty($c['foto_url'])): ?>
                            <img src="<?= htmlspecialchars($c['foto_url']) ?>" class="w-full h-full object-cover rounded-xl border border-slate-100 shadow-sm bg-slate-50">
                        <?php else: ?>
                            <div class="w-full h-full rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-slate-100 flex items-center justify-center">
                                <span class="text-xl font-bold text-blue-300"><?= strtoupper(substr($c['nombre'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="absolute top-0 bottom-0 left-0 right-0 bg-white/80 rounded-xl flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                            <i class="ph-bold ph-pencil-simple text-xl text-slate-600"></i>
                        </div>
                    </a>

                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-slate-600 text-xs bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                <?= htmlspecialchars($c['dni_ruc']) ?: 'S/N' ?>
                            </span>
                        </div>
                        
                        <h3 class="font-bold text-slate-800 text-sm leading-tight line-clamp-2 mb-0.5">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </h3>
                        
                        <?php if(!empty($c['direccion'])): ?>
                            <p class="text-[10px] text-slate-400 truncate flex items-center gap-1">
                                <i class="ph-fill ph-map-pin opacity-70"></i>
                                <?= htmlspecialchars(substr($c['direccion'], 0, 25)) ?>...
                            </p>
                        <?php else: ?>
                            <p class="text-[10px] text-slate-300 italic">Sin dirección</p>
                        <?php endif; ?>
                    </div>

                    <div class="individual-actions flex flex-col justify-center items-end gap-1.5">
                        
                        <a href="editar_cliente.php?id=<?= $c['id'] ?>" class="h-7 w-7 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition border border-slate-100">
                            <i class="ph-bold ph-pencil-simple text-sm"></i>
                        </a>

                        <?php if($isLocked): ?>
                            <div class="h-7 w-7 flex items-center justify-center bg-orange-50 text-orange-400 rounded-lg border border-orange-100 cursor-not-allowed" title="Protegido">
                                <i class="ph-fill ph-lock-key text-sm"></i>
                            </div>
                        <?php else: ?>
                            <a href="guardar_cliente.php?action=delete&id=<?= $c['id'] ?>" onclick="return confirm('¿Borrar cliente?');" class="h-7 w-7 flex items-center justify-center bg-slate-50 text-slate-400 rounded-lg hover:bg-red-50 hover:text-red-500 transition border border-slate-100">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="mt-2 pt-2 border-t border-slate-100/50 flex gap-2">
                    <a href="agregar_proforma.php?action=new&cliente_id=<?= $c['id'] ?>" class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold py-1.5 rounded-lg flex items-center justify-center gap-1 transition">
                        <i class="ph-bold ph-file-plus"></i> Cotizar
                    </a>
                    
                    <?php if(!empty($c['telefono'])): ?>
                        <a href="https://wa.me/<?= str_replace(['+',' '], '', htmlspecialchars($c['telefono'])) ?>" target="_blank" class="flex-1 bg-green-50 hover:bg-green-100 text-green-600 text-[10px] font-bold py-1.5 rounded-lg flex items-center justify-center gap-1 transition">
                            <i class="ph-bold ph-whatsapp-logo"></i> Wsp
                        </a>
                    <?php endif; ?>
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
        <div class="flex items-center gap-2 justify-end flex-1">
            
            <button onclick="compartirSeleccionados()" class="p-2.5 rounded-xl hover:bg-white/10 text-indigo-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-share-network text-xl"></i>
                <span class="text-[9px] font-bold">Enviar</span>
            </button>

            <button onclick="borrarSeleccionados()" class="p-2.5 rounded-xl hover:bg-white/10 text-red-400 transition flex flex-col items-center gap-1">
                <i class="ph-bold ph-trash text-xl"></i>
                <span class="text-[9px] font-bold">Borrar</span>
            </button>

        </div>
    </div>
</div>

<form id="formBulkDelete" method="POST" action="guardar_cliente.php" class="hidden">
    <input type="hidden" name="action" value="delete_multiple">
    <div id="inputsContainer"></div>
</form>

<?php include 'navbar.php'; ?>

<script>
    // --- LÓGICA DE SELECCIÓN (Adaptada al diseño Inventario) ---
    let selectionMode = false;

    function toggleSelectionMode() {
        selectionMode = !selectionMode;
        const btn = document.getElementById('btn-select-mode');
        const checkboxes = document.querySelectorAll('.selection-check');
        const cards = document.querySelectorAll('.card-content'); // Para empujar contenido
        
        if (selectionMode) {
            // Activar modo
            btn.classList.add('bg-white', 'text-slate-900');
            btn.classList.remove('bg-white/10', 'text-white');
            btn.innerHTML = '<i class="ph-bold ph-x"></i> Cancelar';
            
            checkboxes.forEach(el => el.classList.remove('hidden'));
            cards.forEach(el => el.classList.add('pl-10')); // Dejar espacio al checkbox
        } else {
            // Desactivar modo
            btn.classList.remove('bg-white', 'text-slate-900');
            btn.classList.add('bg-white/10', 'text-white');
            btn.innerHTML = '<i class="ph-bold ph-check-square"></i> Seleccionar';
            
            checkboxes.forEach(el => {
                el.classList.add('hidden');
                el.querySelector('input').checked = false;
            });
            cards.forEach(el => el.classList.remove('pl-10'));
            
            // Ocultar barra
            document.getElementById('bulk-actions-bar').classList.add('translate-y-32');
        }
    }

    function actualizarContador() {
        const checked = document.querySelectorAll('.client-check:checked');
        const count = checked.length;
        document.getElementById('selected-count').innerText = count;
        
        const bar = document.getElementById('bulk-actions-bar');
        if (count > 0) {
            bar.classList.remove('translate-y-32'); // Mostrar
        } else {
            bar.classList.add('translate-y-32'); // Ocultar
        }
    }

    function compartirSeleccionados() {
        const checked = document.querySelectorAll('.client-check:checked');
        let texto = "📅 Mis contactos:\n\n";
        checked.forEach(chk => {
            texto += chk.getAttribute('data-info') + "\n-------------------------\n";
        });
        if (navigator.share) navigator.share({ text: texto });
        else { navigator.clipboard.writeText(texto); alert("Copiado al portapapeles"); }
    }

    function borrarSeleccionados() {
        const checked = document.querySelectorAll('.client-check:checked');
        if(confirm(`¿Borrar ${checked.length} clientes? (Los protegidos no se borrarán)`)) {
            const container = document.getElementById('inputsContainer');
            container.innerHTML = '';
            checked.forEach(chk => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = chk.value;
                container.appendChild(input);
            });
            document.getElementById('formBulkDelete').submit();
        }
    }
</script>

</body>
</html>
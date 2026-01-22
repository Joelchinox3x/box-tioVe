<?php
// editar_equipo.php
require 'config.php';

// 1. OBTENER ID Y DATOS ACTUALES
if (!isset($_GET['id'])) {
    header("Location: inventario.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$equipo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipo) {
    die("Equipo no encontrado.");
}

// --- NUEVO: Obtener especificaciones existentes ---
$stmtSpecs = $pdo->prepare("SELECT * FROM producto_specs WHERE producto_id = ? ORDER BY orden ASC");
$stmtSpecs->execute([$id]);
$current_specs = $stmtSpecs->fetchAll(PDO::FETCH_ASSOC);


// 2. PROCESAR EL FORMULARIO AL GUARDAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Recoger datos de texto
    $nombre = $_POST['nombre'];
    $modelo = $_POST['modelo'];
    $sku = $_POST['sku'];
    $precio = $_POST['precio'];
    $moneda = $_POST['moneda'];
    $descripcion = $_POST['descripcion'];

    // B. Procesar Imágenes
    $imagenes_finales = isset($_POST['imagenes_viejas']) ? $_POST['imagenes_viejas'] : [];

    if (isset($_FILES['imagenes_nuevas']) && count($_FILES['imagenes_nuevas']['name']) > 0) {
        $total_files = count($_FILES['imagenes_nuevas']['name']);
        if (!file_exists('uploads/equipos')) { mkdir('uploads/equipos', 0777, true); }

        for ($i = 0; $i < $total_files; $i++) {
            $tmp_name = $_FILES['imagenes_nuevas']['tmp_name'][$i];
            $name = $_FILES['imagenes_nuevas']['name'][$i];
            
            if (!empty($name)) {
                $nuevo_nombre = 'uploads/equipos/' . time() . '_' . rand(100, 999) . '_' . $name;
                if (move_uploaded_file($tmp_name, $nuevo_nombre)) {
                    $imagenes_finales[] = $nuevo_nombre;
                }
            }
        }
    }

    // C. Actualizar Base de Datos (Producto Principal)
    $json_imagenes = json_encode($imagenes_finales);
    $sql = "UPDATE productos SET 
            nombre = ?, modelo = ?, sku = ?, precio = ?, moneda = ?, descripcion = ?, imagenes = ? 
            WHERE id = ?";
    $stmtUpdate = $pdo->prepare($sql);
    $stmtUpdate->execute([$nombre, $modelo, $sku, $precio, $moneda, $descripcion, $json_imagenes, $id]);

    // --- D. ACTUALIZAR ESPECIFICACIONES TÉCNICAS ---
    // Estrategia: Borrar todas las specs viejas de este ID e insertar las que vienen en el formulario
    
    // 1. Borrar anteriores
    $stmtDelSpecs = $pdo->prepare("DELETE FROM producto_specs WHERE producto_id = ?");
    $stmtDelSpecs->execute([$id]);

    // 2. Insertar nuevas (si existen)
    if (isset($_POST['spec_atributo']) && isset($_POST['spec_valor'])) {
        $stmtInsertSpec = $pdo->prepare("INSERT INTO producto_specs (producto_id, atributo, valor, orden) VALUES (?, ?, ?, ?)");
        
        $atributos = $_POST['spec_atributo'];
        $valores = $_POST['spec_valor'];
        
        for ($i = 0; $i < count($atributos); $i++) {
            $attr = trim($atributos[$i]);
            $val = trim($valores[$i]);
            
            if (!empty($attr) && !empty($val)) {
                $stmtInsertSpec->execute([$id, $attr, $val, $i]);
            }
        }
    }

    // Redirigir
    header("Location: inventario.php?msg=updated");
    exit;
}

// Decodificar imágenes para mostrar
$imagenes_actuales = [];
if (!empty($equipo['imagenes'])) {
    $decoded = json_decode($equipo['imagenes'], true);
    if (is_array($decoded)) $imagenes_actuales = $decoded;
    elseif (is_string($equipo['imagenes'])) $imagenes_actuales[] = $equipo['imagenes'];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>Editar Equipo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
  </style>
</head>
<body class="text-slate-800 pb-32">

<div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

  <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50">
      <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
              <a href="inventario.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
                  <i class="ph-bold ph-arrow-left text-xl"></i>
              </a>
              <h1 class="text-xl font-bold">Editar Equipo</h1>
          </div>
          <div class="text-xs text-slate-400 font-mono">ID: <?= $id ?></div>
      </div>
  </header>

  <div class="p-4 space-y-4">

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 animate-fade-in-up">

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-pencil-simple text-blue-500"></i> Información
            </h2>
            
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($equipo['nombre']) ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Moneda</label>
                        <div class="relative">
                            <select name="moneda" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 appearance-none focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                                <option value="PEN" <?= $equipo['moneda'] == 'PEN' ? 'selected' : '' ?>>S/.</option>
                                <option value="USD" <?= $equipo['moneda'] == 'USD' ? 'selected' : '' ?>>$</option>
                            </select>
                            <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Precio</label>
                        <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($equipo['precio']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-right">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Modelo</label>
                        <input type="text" name="modelo" value="<?= htmlspecialchars($equipo['modelo']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">SKU</label>
                        <input type="text" name="sku" value="<?= htmlspecialchars($equipo['sku']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition resize-none"><?= htmlspecialchars($equipo['descripcion']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-list-dashes text-orange-500"></i> Ficha Técnica
                </h2>
                <button type="button" onclick="agregarFila()" class="text-[10px] bg-orange-50 text-orange-600 px-3 py-1.5 rounded-lg font-bold hover:bg-orange-100 transition inline-flex items-center gap-1">
                    <i class="ph-bold ph-plus"></i> Añadir
                </button>
            </div>

            <div id="contenedor-specs" class="space-y-2">
                </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-image text-purple-500"></i> Imágenes
            </h2>

            <?php if(!empty($imagenes_actuales)): ?>
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Guardadas:</p>
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <?php foreach($imagenes_actuales as $img): ?>
                        <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm group">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover">
                            <input type="hidden" name="imagenes_viejas[]" value="<?= htmlspecialchars($img) ?>">
                            <button type="button" onclick="this.parentElement.remove()" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-80 hover:opacity-100 transition shadow-sm">
                                <i class="ph-bold ph-x text-xs"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Agregar Nuevas:</p>
            <div class="relative border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-6 text-center hover:bg-slate-100 transition cursor-pointer group mb-4">
                <input type="file" name="imagenes_nuevas[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewNewImages(this)">
                <div class="text-slate-400 group-hover:text-purple-500 transition">
                    <i class="ph-duotone ph-plus-circle text-3xl mb-2"></i>
                    <p class="text-xs font-bold">Subir fotos</p>
                </div>
            </div>
            <div id="preview-nuevas" class="grid grid-cols-4 gap-2"></div>
        </div>

        <div class="sticky bottom-24 z-30 pt-2">
            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold py-4 rounded-2xl shadow-lg shadow-orange-500/30 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition">
                <i class="ph-bold ph-floppy-disk text-xl"></i> Guardar Cambios
            </button>
        </div>

    </form>

  </div>
</div>

<script>
    // --- LÓGICA DE FOTOS ---
    const dataTransfer = new DataTransfer(); 
    function previewNewImages(input) {
        const container = document.getElementById('preview-nuevas');
        for (let i = 0; i < input.files.length; i++) dataTransfer.items.add(input.files[i]);
        input.files = dataTransfer.files;
        renderNewPreviews(container, input);
    }
    function renderNewPreviews(container, input) {
        container.innerHTML = ''; 
        Array.from(dataTransfer.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = "relative aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm animate-fade-in-up group";
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover opacity-80">
                                 <span class="absolute bottom-1 left-1 bg-blue-500 text-white text-[8px] px-1.5 py-0.5 rounded font-bold">NUEVA</span>
                                 <button type="button" onclick="removeNewFile(${index}, this)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity text-xs"><i class="ph-bold ph-x"></i></button>`;
                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
    function removeNewFile(index, btn) {
        // Lógica simplificada: para eliminar del DataTransfer habría que repoblar. 
        // Para UX rápida, a veces basta con resetear todo o re-hacer la lógica completa como tenías.
        // Aquí uso tu lógica anterior:
        const input = document.querySelector('input[name="imagenes_nuevas[]"]');
        const container = document.getElementById('preview-nuevas');
        const newDataTransfer = new DataTransfer();
        const currentFiles = dataTransfer.files;
        for (let i = 0; i < currentFiles.length; i++) if (i !== index) newDataTransfer.items.add(currentFiles[i]);
        dataTransfer.items.clear();
        for (let i = 0; i < newDataTransfer.files.length; i++) dataTransfer.items.add(newDataTransfer.files[i]);
        input.files = dataTransfer.files;
        renderNewPreviews(container, input);
    }

    // --- LÓGICA DE FICHA TÉCNICA (SPECS) ---
    
    // 1. Array PHP a JS
    const existingSpecs = <?php echo json_encode($current_specs); ?>;

    // 2. Función para crear filas
    function agregarFila(atributo = '', valor = '') {
        const container = document.getElementById('contenedor-specs');
        const div = document.createElement('div');
        div.className = "flex gap-2 animate-fade-in-up";
        div.innerHTML = `
            <input type="text" name="spec_atributo[]" value="${atributo}" placeholder="Atributo (ej: Motor)" class="w-1/3 bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-orange-500 outline-none">
            <input type="text" name="spec_valor[]" value="${valor}" placeholder="Valor (ej: Cummins 300HP)" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-orange-500 outline-none">
            <button type="button" onclick="this.parentElement.remove()" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white w-9 rounded-xl transition flex items-center justify-center">
                <i class="ph-bold ph-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    // 3. Cargar datos existentes al iniciar
    document.addEventListener('DOMContentLoaded', () => {
        if (existingSpecs && existingSpecs.length > 0) {
            existingSpecs.forEach(spec => {
                agregarFila(spec.atributo, spec.valor);
            });
        } else {
            // Si no hay specs, agregamos una fila vacía por defecto
            agregarFila('', '');
        }
    });
</script>

<?php include 'navbar.php'; ?>

</body>
</html>
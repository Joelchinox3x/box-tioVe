<?php
require 'config.php';

// --- FUNCION AUXILIAR (Guardar Imagen) ---
function guardarImagenBase64($base64_string, $nombre_cliente) {
    if (empty($base64_string)) return null;
    $target_dir = "uploads/clientes/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $data = explode(',', $base64_string);
    $content = base64_decode(count($data) > 1 ? $data[1] : $data[0]);
    $image = imagecreatefromstring($content);
    if (!$image) return null;
    $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', substr($nombre_cliente, 0, 10));
    $filename = strtolower($clean_name) . '_' . time() . '.webp';
    $filepath = $target_dir . $filename;
    imagewebp($image, $filepath, 80);
    imagedestroy($image);
    return $filepath;
}

// 1. VERIFICAR ID
if (!isset($_GET['id'])) { header("Location: clientes.php"); exit; }
$id = $_GET['id'];

// 2. OBTENER DATOS ACTUALES
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) die("Cliente no encontrado.");

// ESTADO DE BLOQUEO (Para usar en el HTML)
$bloqueado = ($c['protegido'] == 1);
$attr_disabled = $bloqueado ? 'disabled' : '';

// [VISUAL] Actualizado a paleta Slate para coincidir con Inventario
$css_input = $bloqueado 
    ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-red-200 border' // BLOQUEADO
    : 'bg-slate-50 text-slate-700 border border-slate-200 focus:border-blue-500'; // LIBRE

// [VISUAL] Estilo específico para GPS
$css_gps = $bloqueado 
    ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' 
    : 'bg-white border-slate-200 text-slate-700 focus:border-blue-500';

// 3. PROCESAR ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $foto_final = $c['foto_url']; 

        // A. ¿Pidieron eliminar la foto actual?
        if (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == '1') {
            if (!empty($c['foto_url']) && file_exists($c['foto_url'])) {
                unlink($c['foto_url']); 
            }
            $foto_final = null; 
        }

        // B. ¿Subieron una NUEVA foto?
        if (!empty($_POST['foto_base64'])) {
            if (!empty($c['foto_url']) && file_exists($c['foto_url']) && $foto_final !== null) {
                unlink($c['foto_url']);
            }
            $foto_final = guardarImagenBase64($_POST['foto_base64'], $_POST['nombre']);
        }

        $es_protegido = isset($_POST['protegido']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, dni_ruc=?, direccion=?, telefono=?, email=?, foto_url=?, latitud=?, longitud=?, protegido=? WHERE id=?");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['dni_ruc'],
            $_POST['direccion'],
            $_POST['telefono_full'],
            $_POST['email'],
            $foto_final,
            $_POST['latitud'],
            $_POST['longitud'],
            $es_protegido,
            $id
        ]);
        
        header("Location: clientes.php?msg=updated"); 
        exit;
    } catch (Exception $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  <title>Editar Cliente</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
  <style>
      body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
      .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
      .iti { width: 100%; }
      .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags.png"); }
      @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags@2x.png"); }
      }
      /* Ajuste input telefono para que combine con el tema */
      .iti__country-list { z-index: 60; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
  </style>
</head>
<body class="text-slate-800 pb-32">

  <div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

    <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50 flex items-center gap-4">
        <a href="clientes.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        <h1 class="text-xl font-bold">Editar Cliente</h1>
    </header>

    <?php if(isset($error)): ?>
        <div class="m-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="p-4 space-y-4">
        
        <form method="POST" action="" class="space-y-4" id="formEdit" onsubmit="procesarFormulario(event)">
            
            <input type="hidden" name="foto_base64" id="foto_base64">
            <input type="hidden" name="telefono_full" id="telefono_full">
            <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0">

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center relative">
                
                <div class="relative inline-block mb-4">
                    <label for="inputImage" class="group relative block transition-transform active:scale-95 <?= $bloqueado ? 'pointer-events-none opacity-80' : 'cursor-pointer' ?>">
                        <div id="avatarPreview" class="w-28 h-28 rounded-full bg-slate-50 border-2 <?= empty($c['foto_url']) ? 'border-dashed border-slate-300' : 'border-solid border-blue-500' ?> flex items-center justify-center overflow-hidden group-hover:border-blue-400 transition shadow-inner">
                            <?php if(!empty($c['foto_url'])): ?>
                                <img src="<?= htmlspecialchars($c['foto_url']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-4xl font-bold text-slate-300"><?= strtoupper(substr($c['nombre'],0,1)) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg border-2 border-white <?= $bloqueado ? 'hidden' : '' ?>" id="iconCamara">
                            <i class="ph-bold ph-camera text-sm"></i>
                        </div>
                    </label>
                    
                    <button type="button" id="btnDeleteImg" onclick="eliminarFoto(event)" class="<?= (empty($c['foto_url']) || $bloqueado) ? 'hidden' : '' ?> absolute top-0 left-0 -mt-1 -ml-1 bg-red-500 text-white p-1.5 rounded-full shadow-md hover:bg-red-600 transition z-20 border-2 border-white">
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>
                </div>
                
                <input type="file" id="inputImage" accept="image/*" class="hidden" <?= $attr_disabled ?>>
                
                <span id="msgFoto" class="text-[10px] font-bold uppercase tracking-wider transition-colors <?= $bloqueado ? 'text-red-400' : 'text-slate-400' ?>">
                    <?= $bloqueado ? 'Edición Bloqueada' : 'Toca para cambiar foto' ?>
                </span>
            </div>

            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-identification-card text-blue-500"></i> Datos Generales
                </h2>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Razón Social <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($c['nombre']) ?>" required class="w-full rounded-xl p-3 text-sm outline-none font-semibold transition <?= $css_input ?>" <?= $attr_disabled ?>>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">RUC / DNI <span class="text-red-500">*</span></label>
                        <input type="text" name="dni_ruc" value="<?= htmlspecialchars($c['dni_ruc']) ?>" required class="w-full rounded-xl p-3 text-sm outline-none transition font-mono <?= $css_input ?>" <?= $attr_disabled ?>>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Teléfono</label>
                        <input type="tel" id="telefono" class="w-full rounded-xl p-3 text-sm outline-none transition <?= $css_input ?>" <?= $attr_disabled ?>>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($c['email']) ?>" class="w-full rounded-xl p-3 text-sm outline-none transition <?= $css_input ?>" <?= $attr_disabled ?>>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-map-pin text-orange-500"></i> Ubicación
                </h2>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Dirección</label>
                    <textarea name="direccion" rows="2" class="w-full rounded-xl p-3 text-sm outline-none transition resize-none <?= $css_input ?>" <?= $attr_disabled ?>><?= htmlspecialchars($c['direccion']) ?></textarea>
                </div>

                <div class="p-4 rounded-2xl border transition-colors <?= $bloqueado ? 'bg-red-50 border-red-100' : 'bg-blue-50 border-blue-100' ?>">
                    
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-[10px] font-bold uppercase flex items-center gap-1 <?= $bloqueado ? 'text-red-400' : 'text-blue-600' ?>">
                            Coordenadas GPS
                        </label>
                        <?php if(!empty($c['telefono'])): ?>
                            <a href="https://wa.me/<?= str_replace(['+',' '], '', $c['telefono']) ?>?text=Hola%20<?= urlencode($c['nombre']) ?>,%20por%20favor%20compárteme%20tu%20ubicación%20actual%20por%20aquí%20para%20actualizar%20tu%20ficha.%20Gracias." target="_blank" class="text-[10px] px-2 py-1.5 rounded-lg font-bold flex items-center gap-1 transition shadow-sm <?= $bloqueado ? 'bg-white text-red-400 border border-red-100' : 'bg-white text-green-600 border border-green-100 hover:text-green-700' ?>">
                                <i class="ph-bold ph-whatsapp-logo text-sm"></i> Pedir
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-2 mb-3">
                        <input type="text" id="lat" name="latitud" value="<?= htmlspecialchars($c['latitud'] ?? '') ?>" class="w-1/2 rounded-lg p-2.5 text-xs border outline-none font-mono transition-colors <?= $css_gps ?>" placeholder="Latitud" <?= $attr_disabled ?>>
                        <input type="text" id="lng" name="longitud" value="<?= htmlspecialchars($c['longitud'] ?? '') ?>" class="w-1/2 rounded-lg p-2.5 text-xs border outline-none font-mono transition-colors <?= $css_gps ?>" placeholder="Longitud" <?= $attr_disabled ?>>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="btnDetectar" onclick="detectarUbicacion()" 
                                class="text-xs font-bold py-2.5 px-3 rounded-lg transition shadow-sm flex items-center justify-center gap-1 border <?= $bloqueado ? 'bg-white border-red-200 text-red-300 cursor-not-allowed' : 'bg-blue-600 border-transparent hover:bg-blue-500 text-white' ?>"
                                <?= $attr_disabled ?>>
                                <i class="ph-bold ph-crosshair text-sm"></i>
                                <?= $bloqueado ? '' : 'Detectar' ?>
                        </button>

                        <button type="button" id="btnPegar" onclick="pegarLinkMaps()" class="flex-1 text-xs font-bold py-2.5 px-3 rounded-lg transition shadow-sm flex items-center justify-center gap-1 border <?= $bloqueado ? 'bg-white border-red-200 text-red-300 cursor-not-allowed' : 'bg-white border-blue-200 text-blue-600 hover:bg-blue-50' ?>" <?= $attr_disabled ?>>
                            <?php if($bloqueado): ?>
                                <i class="ph-bold ph-lock-key"></i> Edición Bloqueada
                            <?php else: ?>
                                <i class="ph-bold ph-link"></i> Pegar Link de Maps
                            <?php endif; ?>
                        </button>
                        
                        <?php if(!empty($c['latitud'])): ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= $c['latitud'] ?>,<?= $c['longitud'] ?>" target="_blank" class="bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-200 text-xs font-bold py-2 px-3 rounded-lg transition shadow-sm flex items-center justify-center" title="Ver en Google Maps">
                            <i class="ph-bold ph-map-trifold text-lg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-2xl border flex items-center justify-between transition-colors shadow-sm <?= $bloqueado ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200' ?>">
                
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="protegido" value="1" id="checkProtegidoReal" class="hidden" <?= $c['protegido']==1 ? 'checked' : '' ?>>
                    
                    <div onclick="toggleSeguridad()" class="relative inline-flex items-center cursor-pointer group">
                        <div id="switchTrack" class="w-12 h-7 rounded-full transition-colors shadow-inner <?= $bloqueado ? 'bg-red-500' : 'bg-slate-300' ?>"></div>
                        <div id="switchDot" class="absolute left-1 top-1 bg-white w-5 h-5 rounded-full shadow-md transition-transform duration-300 <?= $bloqueado ? 'translate-x-5' : '' ?>"></div>
                    </div>
                    
                    <div onclick="toggleSeguridad()" class="cursor-pointer">
                        <label class="text-[10px] font-bold uppercase block <?= $bloqueado ? 'text-red-500' : 'text-slate-500' ?>">Seguridad</label>
                        <label class="text-xs font-bold cursor-pointer select-none <?= $bloqueado ? 'text-red-700' : 'text-slate-700' ?>">
                            <?= $bloqueado ? 'CLIENTE PROTEGIDO' : 'Proteger (Anti-borrado)' ?>
                        </label>
                    </div>
                </div>

                <div onclick="toggleSeguridad()" class="cursor-pointer text-2xl <?= $bloqueado ? 'text-red-500 animate-pulse' : 'text-slate-400' ?>">
                    <i class="ph-fill <?= $bloqueado ? 'ph-lock-key' : 'ph-lock-key-open' ?>"></i>
                </div>
            </div>
            
            <div class="flex gap-3 pt-2">
                <a href="clientes.php" class="w-1/3 py-4 rounded-xl text-center text-sm font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-sm">Cancelar</a>
                
                <button type="submit" id="btnGuardar" 
                    class="w-2/3 font-bold py-4 rounded-xl transition transform active:scale-95 flex items-center justify-center gap-2 border shadow-lg 
                    <?= $bloqueado 
                        ? 'bg-slate-100 border-dashed border-slate-300 text-slate-400 cursor-not-allowed' 
                        : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-blue-500/30 border-transparent hover:scale-[1.02]' 
                    ?>" 
                    <?= $attr_disabled ?>>
                    
                    <?php if($bloqueado): ?>
                        <i class="ph-bold ph-lock-key"></i> Edición Bloqueada
                    <?php else: ?>
                        <i class="ph-bold ph-floppy-disk"></i> Guardar Cambios
                    <?php endif; ?>

                </button>
            </div>

        </form>
    </div>

  </div>

  <div id="cropModal" class="hidden fixed inset-0 z-[60] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-fade-in-up">
          <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white z-10">
              <h3 class="font-bold text-slate-800 flex items-center gap-2"><i class="ph-bold ph-crop text-blue-500"></i> Recortar Imagen</h3>
              <button type="button" onclick="cerrarModal()" class="text-slate-400 hover:text-red-500 transition"><i class="ph-bold ph-x text-xl"></i></button>
          </div>
          <div class="flex-1 bg-slate-900 relative overflow-hidden" style="height: 350px;">
              <img id="imageToCrop" class="max-w-full block">
          </div>
          <div class="p-4 flex justify-end gap-3 bg-white border-t border-slate-100 z-10">
              <button type="button" onclick="cerrarModal()" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition">Cancelar</button>
              <button type="button" onclick="recortarYGuardar()" class="px-6 py-2.5 text-xs font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition">Confirmar Recorte</button>
          </div>
      </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  
  <script>
    const ADMIN_PIN = "<?= $admin_pin ?>";

    // --- FUNCIÓN PARA BLOQUEAR/DESBLOQUEAR VISUALMENTE (Actualizado a Slate) ---
    function toggleInputs(disable) {
        const inputs = document.querySelectorAll('#formEdit input:not([type=hidden]), #formEdit textarea, #btnDetectar, #btnPegar, #btnGuardar');
        const lblImg = document.querySelector('label[for="inputImage"]');
        const iconCam = document.getElementById('iconCamara');
        const btnDel = document.getElementById('btnDeleteImg');
        const msgFoto = document.getElementById('msgFoto');

        if(disable) {
            // MODO BLOQUEADO
            lblImg.classList.add('pointer-events-none', 'opacity-80');
            if(iconCam) iconCam.classList.add('hidden'); 
            if(btnDel) btnDel.classList.add('hidden');
            if(msgFoto) { msgFoto.innerText = 'Edición Bloqueada'; msgFoto.classList.replace('text-slate-400', 'text-red-400'); }
        } else {
            // MODO DESBLOQUEADO
            lblImg.classList.remove('pointer-events-none', 'opacity-80');
            if(iconCam) iconCam.classList.remove('hidden');
            if(msgFoto) { msgFoto.innerText = 'Toca para cambiar foto'; msgFoto.classList.replace('text-red-400', 'text-slate-400'); }

            const hayFoto = document.querySelector('#avatarPreview img');
            if(btnDel && hayFoto) btnDel.classList.remove('hidden');
        }

        inputs.forEach(el => {
            el.disabled = disable;
            if(disable) {
                // Estilo Deshabilitado (Slate Oscuro/Rojo)
                el.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'border-red-200');
                el.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200', 'focus:border-blue-500', 'bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white');
                
                // Caso especial para botón guardar
                if(el.id === 'btnGuardar') {
                    el.classList.add('border-dashed', 'border-slate-300');
                    el.innerHTML = '<i class="ph-bold ph-lock-key"></i> Edición Bloqueada';
                }
            } else {
                // Estilo Habilitado
                el.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'border-red-200', 'border-dashed', 'border-slate-300');
                
                if(el.id === 'btnGuardar') {
                    el.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-white');
                    el.innerHTML = '<i class="ph-bold ph-floppy-disk"></i> Guardar Cambios';
                } else if (el.id === 'btnDetectar' || el.id === 'btnPegar') {
                    // Botones GPS
                     el.classList.add('bg-white', 'text-blue-600', 'border-blue-200');
                } else {
                    // Inputs normales
                    el.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200', 'focus:border-blue-500');
                }
            }
        });
    }

    // --- FUNCIÓN DE SEGURIDAD (AUTOGUARDADO) ---
    function toggleSeguridad() {
        const checkReal = document.getElementById('checkProtegidoReal');
        const currentlyLocked = checkReal.checked;
        const accion = currentlyLocked ? "DESBLOQUEAR" : "PROTEGER";
        
        const inputPin = prompt(`Para ${accion} y GUARDAR, ingrese el PIN:`);
        
        if (inputPin === ADMIN_PIN) {
            checkReal.checked = !currentlyLocked;
            toggleInputs(false); 
            const btn = document.getElementById('btnGuardar');
            btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i> Guardando...';
            btn.disabled = false;
            document.getElementById('telefono_full').value = iti.getNumber();
            document.getElementById('formEdit').submit();

        } else if (inputPin !== null) {
            alert("PIN Incorrecto.");
        }
    }
    
    // 1. TELÉFONO
    const phoneInput = document.querySelector("#telefono");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "pe",
        preferredCountries: ["pe", "us", "es", "mx"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        separateDialCode: true
    });
    iti.setNumber("<?= $c['telefono'] ?>");

    // 2. GPS: DETECTAR UBICACIÓN
    function detectarUbicacion() {
        if (!navigator.geolocation) { alert("Navegador no soporta Geo."); return; }
        const btn = document.getElementById('btnDetectar');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
        btn.disabled = true;

        const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };

        function success(position) {
            document.getElementById('lat').value = position.coords.latitude;
            document.getElementById('lng').value = position.coords.longitude;
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('¡Ubicación encontrada!');
        };
        function error(err) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert("Error obteniendo ubicación GPS.");
        };
        navigator.geolocation.getCurrentPosition(success, error, options);
    }

    function procesarFormulario(e) {
        document.getElementById('telefono_full').value = iti.getNumber();
        return true;
    }

    // 3. CROPPER
    let cropper;
    const inputImage = document.getElementById('inputImage');
    const cropModal = document.getElementById('cropModal');
    const imageToCrop = document.getElementById('imageToCrop');
    const avatarPreview = document.getElementById('avatarPreview');
    const fotoBase64 = document.getElementById('foto_base64');

    inputImage.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            imageToCrop.src = URL.createObjectURL(file);
            cropModal.classList.remove('hidden');
            if (cropper) { cropper.destroy(); }
            cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.8 });
            inputImage.value = ''; 
        }
    });

    function cerrarModal() {
        cropModal.classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
    }

    // 4. ELIMINAR FOTO
    function eliminarFoto(e) {
        e.preventDefault();
        document.getElementById('inputImage').value = '';
        document.getElementById('foto_base64').value = '';
        document.getElementById('eliminar_foto').value = '1';
        
        const inicial = "<?= strtoupper(substr($c['nombre'], 0, 1)) ?>";
        const preview = document.getElementById('avatarPreview');
        preview.innerHTML = `<span class="text-4xl font-bold text-slate-300">${inicial}</span>`;
        preview.classList.add('border-dashed', 'border-slate-300');
        preview.classList.remove('border-solid', 'border-blue-500');
        document.getElementById('btnDeleteImg').classList.add('hidden');
    }

    function recortarYGuardar() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
        const base64 = canvas.toDataURL('image/webp', 0.85);
        fotoBase64.value = base64;
        avatarPreview.innerHTML = `<img src="${base64}" class="w-full h-full object-cover">`;
        document.getElementById('eliminar_foto').value = '0'; 
        document.getElementById('btnDeleteImg').classList.remove('hidden'); 
        
        // Estilo visual del preview al tener foto
        avatarPreview.classList.remove('border-dashed', 'border-slate-300');
        avatarPreview.classList.add('border-solid', 'border-blue-500');
        
        cerrarModal();
    }

    // 5. PEGAR LINK MAPS
    async function pegarLinkMaps() {
        const text = prompt("Pega el enlace de ubicación (WhatsApp/Maps):");
        if (!text) return;
        const urlMatch = text.match(/(https?:\/\/[^\s]+)/);
        const urlToProcess = urlMatch ? urlMatch[0] : text;

        const btn = document.getElementById('btnPegar');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
        btn.disabled = true;

        try {
            const response = await fetch(`resolver_maps.php?url=${encodeURIComponent(urlToProcess)}`);
            const data = await response.json();
            if (data.success) {
                document.getElementById('lat').value = data.lat;
                document.getElementById('lng').value = data.lng;
                alert(`¡Ubicación encontrada!\nLat: ${data.lat}\nLng: ${data.lng}`);
            } else {
                alert("No pudimos extraer las coordenadas automáticamente.");
            }
        } catch (error) {
            alert("Error de conexión al procesar mapa.");
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }    
  </script>

  <?php include 'navbar.php'; ?>
</body>
</html>
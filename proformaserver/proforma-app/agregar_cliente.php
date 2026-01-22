<?php
// agregar_cliente.php - DISEÑO PRO
require 'config.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
  <title>Nuevo Cliente</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
  
  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #F8FAFC; }
    .glass-header { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
    
    /* Animación de entrada */
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Ajustes para el input de teléfono */
    .iti { width: 100%; }
    .iti__country-list { 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
    }
    .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags.png"); }
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
      .iti__flag { background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags@2x.png"); }
    }
  </style>
</head>
<body class="text-slate-800 pb-32">

  <div class="max-w-md mx-auto min-h-screen bg-[#F8FAFC] relative border-x border-slate-100 shadow-2xl">

    <header class="glass-header text-white pt-6 pb-4 px-6 sticky top-0 z-50 rounded-b-3xl shadow-lg border-b border-slate-700/50 flex items-center gap-4">
        <a href="clientes.php" class="bg-white/10 p-2 rounded-full hover:bg-white/20 transition backdrop-blur-md">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        <h1 class="text-xl font-bold">Nuevo Cliente</h1>
    </header>

    <?php if(isset($_GET['error'])): ?>
        <div class="m-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="p-4 animate-fade-in-up">
        
        <form method="POST" action="guardar_cliente.php" id="formCliente" onsubmit="procesarFormulario(event)" class="space-y-4">
            <input type="hidden" name="action" value="crear">
            <input type="hidden" name="foto_base64" id="foto_base64">
            <input type="hidden" name="telefono_full" id="telefono_full">
            
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center relative">
                
                <button type="button" id="btnContactos" onclick="importarContacto()" class="hidden absolute top-4 right-4 text-indigo-600 bg-indigo-50 p-2 rounded-xl hover:bg-indigo-100 transition" title="Importar de Agenda">
                    <i class="ph-bold ph-address-book text-xl"></i>
                </button>

                <div class="relative inline-block mb-3">
                    <label for="inputImage" class="cursor-pointer group relative block transition-transform active:scale-95">
                        <div id="avatarPreview" class="w-28 h-28 rounded-full bg-slate-50 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden group-hover:border-blue-400 transition shadow-inner">
                            <i class="ph-duotone ph-user text-4xl text-slate-300"></i>
                        </div>
                        <div class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg border-2 border-white">
                            <i class="ph-bold ph-camera text-sm"></i>
                        </div>
                    </label>
                    
                    <button type="button" id="btnDeleteImg" onclick="eliminarFoto()" class="hidden absolute top-0 left-0 -mt-1 -ml-1 bg-red-500 text-white p-1.5 rounded-full shadow-md hover:bg-red-600 transition z-20 border-2 border-white">
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>
                </div>
                
                <input type="file" id="inputImage" accept="image/*" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Toca para subir foto</span>
            </div>

            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-identification-card text-blue-500"></i> Información General
                </h2>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Razón Social <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition font-semibold text-slate-700 placeholder-slate-300" placeholder="Ej: Constructora SAC">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">RUC / DNI <span class="text-red-500">*</span></label>
                        <input type="text" name="dni_ruc" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-blue-500 transition font-mono text-slate-700 placeholder-slate-300" placeholder="Num. Doc.">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Teléfono</label>
                        <input type="tel" id="telefono" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-blue-500 transition text-slate-700">
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 space-y-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-map-pin text-orange-500"></i> Ubicación y Contacto
                </h2>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Email</label>
                    <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-blue-500 transition text-slate-700 placeholder-slate-300" placeholder="correo@ejemplo.com">
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Dirección</label>
                    <textarea name="direccion" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm outline-none focus:border-blue-500 transition resize-none text-slate-700 placeholder-slate-300" placeholder="Av. Principal 123..."></textarea>
                </div>
            </div>

            <div class="p-4 bg-orange-50 rounded-2xl border border-orange-100 flex items-start gap-3">
                <div class="pt-0.5">
                    <input type="checkbox" name="protegido" value="1" id="checkProtegido" class="w-5 h-5 text-orange-500 rounded border-orange-300 focus:ring-orange-500 cursor-pointer">
                </div>
                <div>
                    <label for="checkProtegido" class="font-bold text-orange-800 text-sm flex items-center gap-1 cursor-pointer select-none">
                        <i class="ph-fill ph-shield-check"></i> Cliente Protegido
                    </label>
                    <p class="text-[11px] text-orange-600 mt-0.5 leading-tight">Activa esto para evitar que este cliente sea eliminado accidentalmente de la base de datos.</p>
                </div>
            </div>

            <button type="submit" class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="ph-bold ph-check-circle text-xl"></i>
                Guardar Cliente
            </button>
        </form>
    </div>

  </div>

  <div id="cropModal" class="hidden fixed inset-0 z-[60] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-fade-in-up">
          <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white z-10">
              <h3 class="font-bold text-slate-800 flex items-center gap-2">
                  <i class="ph-bold ph-crop text-blue-500"></i> Recortar Imagen
              </h3>
              <button type="button" onclick="cerrarModal()" class="text-slate-400 hover:text-red-500 transition">
                  <i class="ph-bold ph-x text-xl"></i>
              </button>
          </div>
          <div class="flex-1 bg-slate-900 relative overflow-hidden" style="height: 350px;">
              <img id="imageToCrop" class="max-w-full block">
          </div>
          <div class="p-4 flex justify-end gap-3 bg-white border-t border-slate-100 z-10">
              <button type="button" onclick="cerrarModal()" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl transition">Cancelar</button>
              <button type="button" onclick="recortarYGuardar()" class="px-6 py-2.5 text-xs font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition">Confirmar</button>
          </div>
      </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
  
  <script>
    // 1. INICIALIZAR INPUT DE TELÉFONO
    const phoneInput = document.querySelector("#telefono");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "pe",
        preferredCountries: ["pe", "us", "es", "mx", "co"],
        separateDialCode: true
    });

    // 2. PROCESAR FORMULARIO
    function procesarFormulario(e) {
        const fullNumber = iti.getNumber();
        document.getElementById('telefono_full').value = fullNumber;
        return true; 
    }
    
    // 3. LOGICA CROPPER (FOTO)
    let cropper;
    const inputImage = document.getElementById('inputImage');
    const cropModal = document.getElementById('cropModal');
    const imageToCrop = document.getElementById('imageToCrop');
    const avatarPreview = document.getElementById('avatarPreview');
    const fotoBase64 = document.getElementById('foto_base64');
    const btnDeleteImg = document.getElementById('btnDeleteImg');

    inputImage.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            const url = URL.createObjectURL(file);
            imageToCrop.src = url;
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

    function recortarYGuardar() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
        const base64 = canvas.toDataURL('image/webp', 0.85);
        fotoBase64.value = base64;
        
        avatarPreview.innerHTML = `<img src="${base64}" class="w-full h-full object-cover">`;
        avatarPreview.classList.remove('border-dashed', 'border-slate-300');
        avatarPreview.classList.add('border-solid', 'border-blue-500');
        btnDeleteImg.classList.remove('hidden');
        
        cerrarModal();
    }

    function eliminarFoto() {
        inputImage.value = '';
        fotoBase64.value = '';
        
        // Restaurar icono original (Phosphor)
        avatarPreview.innerHTML = '<i class="ph-duotone ph-user text-4xl text-slate-300"></i>';
        avatarPreview.classList.add('border-dashed', 'border-slate-300');
        avatarPreview.classList.remove('border-solid', 'border-blue-500');
        btnDeleteImg.classList.add('hidden');
    }

    // 4. IMPORTAR CONTACTOS (MOBILE)
    const btnContactos = document.getElementById('btnContactos');
    // Verificar soporte
    const isSupported = ('contacts' in navigator && 'ContactsManager' in window);
    
    if (isSupported) {
        btnContactos.classList.remove('hidden');
    }

    async function importarContacto() {
        const props = ['name', 'tel'];
        const opts = { multiple: false };

        try {
            const contacts = await navigator.contacts.select(props, opts);
            if (contacts.length) {
                const contacto = contacts[0];
                if (contacto.name) document.getElementsByName('nombre')[0].value = contacto.name[0];
                
                if (contacto.tel) {
                    let rawNumber = contacto.tel[0].replace(/\s+/g, '').replace(/-/g, '');
                    iti.setNumber(rawNumber);
                    document.getElementById('telefono_full').value = iti.getNumber();
                }
            }
        } catch (ex) {
            console.log("Importación cancelada", ex);
        }
    }
  </script>

  <?php include 'navbar.php'; ?>
</body>
</html>
<?php
// agregar_equipos.php
require 'config.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <title>Agregar Maquinaria</title>
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
              <h1 class="text-xl font-bold">Nueva Maquinaria</h1>
          </div>
      </div>
  </header>

  <div class="p-4 space-y-4">

    <a href="importar_woo.php" class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-center justify-between group hover:bg-indigo-100 transition shadow-sm">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-500 text-white p-2 rounded-lg">
                <i class="ph-bold ph-download-simple text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Automático</p>
                <h3 class="font-bold text-indigo-900 text-sm">Importar de WooCommerce</h3>
            </div>
        </div>
        <i class="ph-bold ph-caret-right text-indigo-300 group-hover:text-indigo-600 transition"></i>
    </a>

    <form action="guardar_equipo.php" method="POST" enctype="multipart/form-data" class="space-y-4 animate-fade-in-up">

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-info text-blue-500"></i> Información General
            </h2>
            
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Nombre del Equipo</label>
                    <input type="text" name="nombre" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition" placeholder="Ej: Excavadora CAT">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Moneda</label>
                        <div class="relative">
                            <select name="moneda" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 appearance-none focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                                <option value="PEN">S/.</option>
                                <option value="USD">$</option>
                            </select>
                            <i class="ph-bold ph-caret-down absolute right-3 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Precio</label>
                        <input type="number" step="0.01" name="precio" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-right" placeholder="0.00">
                    </div>
                </div>
                
		<div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Modelo</label>
                        <input type="text" name="modelo" id="input_modelo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition" placeholder="Ej: 320D">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">SKU / Código</label>
                        <input type="text" name="sku" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition" placeholder="Ej: PROD-001">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-1 mb-1 block">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none font-medium transition resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-image text-purple-500"></i> Imágenes (Max 4)
            </h2>
            
            <div class="relative border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl p-6 text-center hover:bg-slate-100 transition cursor-pointer group mb-4">
                <input type="file" id="input-fotos" name="imagenes[]" multiple accept="image/*" 
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                    onchange="previewImages(this)">
                    
                <div class="text-slate-400 group-hover:text-purple-500 transition">
                    <i class="ph-duotone ph-upload-simple text-3xl mb-2"></i>
                    <p class="text-xs font-bold">Toca para subir fotos</p>
                </div>
            </div>

            <div id="preview-container" class="grid grid-cols-4 gap-2">
                </div>
        </div>

        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2"><i class="ph-fill ph-list-dashes text-orange-500"></i> Ficha Técnica</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modalParser').classList.remove('hidden')" class="text-gray-600 text-xs font-bold hover:bg-gray-100 px-3 py-1.5 rounded-lg transition border border-gray-200 flex items-center gap-1" title="Pegar desde Excel/Web">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Pegar Tabla
                    </button>
                    <button type="button" onclick="agregarFila()" class="text-[10px] bg-orange-50 text-orange-600 px-3 py-1.5 rounded-lg font-bold hover:bg-orange-100 transition inline-flex items-center gap-1">
                        <i class="ph-bold ph-plus"></i> Añadir Atributo
                    </button>
                </div>
            </div>

            <div id="contenedor-specs" class="space-y-2">
                </div>
            
            <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega detalles como Motor, Potencia, Peso, etc.</p>
        </div>

        <div class="sticky bottom-24 z-30">
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition">
                <i class="ph-bold ph-check-circle text-xl"></i>
                Guardar Equipo
            </button>
        </div>

    </form>

  </div>
</div>

<?php include 'navbar.php'; ?>

<div id="modalParser" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 transform transition-all scale-100">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Pegar Datos Técnicos</h3>
        <p class="text-xs text-gray-500 mb-3">Copia tu tabla (Excel, PDF o Web) y pégala aquí. El sistema detectará automáticamente las columnas.</p>
        
        <textarea id="textoPegado" class="w-full h-40 border border-gray-300 rounded-xl p-3 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" placeholder="Ejemplo:
            Modelo    HQ-YL1000
            Motor     Honda GX160
            Peso      250kg">
        </textarea>
        
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('modalParser').classList.add('hidden')" class="text-gray-500 font-semibold text-sm hover:bg-gray-100 px-4 py-2 rounded-lg transition">Cancelar</button>
            <button type="button" onclick="procesarPegado()" class="bg-blue-600 text-white font-bold text-sm px-6 py-2 rounded-lg hover:bg-blue-700 shadow-md transition">Procesar</button>
        </div>
    </div>
</div>

<script>
    let contador = 0;

    // Agregar una fila inicial al cargar
    document.addEventListener('DOMContentLoaded', () => {
        agregarFila('Año', '');
    });

    // Función Ficha Técnica
    function agregarFila(attr = '', val = '') {
        const c = document.getElementById('contenedor-specs');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center group animate-fade-in-up';
        div.innerHTML = `
            <div class="relative w-1/2">
                <input type="text" name="specs[${contador}][attr]" value="${attr}" placeholder="Atributo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none transition">
            </div>
            <div class="relative w-1/2">
                <input type="text" name="specs[${contador}][val]" value="${val}" placeholder="Valor" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none transition">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition shadow-sm border border-red-100">
                <i class="ph-bold ph-x text-xs"></i>
            </button>
        `;
        c.appendChild(div);
        contador++;
    }
// Variable global para acumular los archivos
const dataTransfer = new DataTransfer(); 

function previewImages(input) {
    const container = document.getElementById('preview-container');
    const maxFiles = 4;
    const newFiles = input.files;

    // 1. Verificar si al sumar los nuevos excedemos el límite
    if (dataTransfer.files.length + newFiles.length > maxFiles) {
        alert(`⚠️ Solo puedes subir un máximo de ${maxFiles} fotos en total.`);
        // Restauramos los archivos que ya teníamos en el input
        input.files = dataTransfer.files; 
        return;
    }

    // 2. Agregar los nuevos archivos al acumulador (DataTransfer)
    for (let i = 0; i < newFiles.length; i++) {
        // Evitar duplicados (opcional, pero recomendado)
        let fileExists = false;
        for (let j = 0; j < dataTransfer.files.length; j++) {
            if (dataTransfer.files[j].name === newFiles[i].name && 
                dataTransfer.files[j].size === newFiles[i].size) {
                fileExists = true;
                break;
            }
        }
        if (!fileExists) {
            dataTransfer.items.add(newFiles[i]);
        }
    }

    // 3. Actualizar el input real con todos los archivos acumulados
    // Esto es CRUCIAL para que cuando envíes el formulario, vayan todas las fotos
    input.files = dataTransfer.files;

    // 4. Renderizar las vistas previas
    renderPreviews(container, input);
}

function renderPreviews(container, input) {
    container.innerHTML = ''; // Limpiamos para volver a pintar todo actualizado

    Array.from(dataTransfer.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Contenedor de la imagen
            const div = document.createElement('div');
            div.className = "relative aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm animate-fade-in-up group";
            
            // Imagen
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = "w-full h-full object-cover";
            
            // Botón para eliminar (X roja)
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '<i class="ph-bold ph-x"></i>';
            removeBtn.className = "absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity text-xs";
            removeBtn.onclick = function(ev) {
                ev.preventDefault(); // Evitar submit
                removeFile(index, input, container);
            };

            div.appendChild(img);
            div.appendChild(removeBtn);
            container.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
}

function removeFile(index, input, container) {
    // Crear un nuevo DataTransfer sin el archivo eliminado
    const newDataTransfer = new DataTransfer();
    const currentFiles = dataTransfer.files;

    for (let i = 0; i < currentFiles.length; i++) {
        if (i !== index) {
            newDataTransfer.items.add(currentFiles[i]);
        }
    }

    // Actualizar la variable global y el input
    dataTransfer.items.clear();
    for (let i = 0; i < newDataTransfer.files.length; i++) {
        dataTransfer.items.add(newDataTransfer.files[i]);
    }
    input.files = dataTransfer.files;

    // Volver a pintar
    renderPreviews(container, input);
}

// LÓGICA DE PARSING (Extracción de datos)
function procesarPegado() {
    const texto = document.getElementById('textoPegado').value;
    if(!texto.trim()) return;

    const filas = texto.split('\n'); // Separar por líneas
    const contenedor = document.getElementById('contenedor-specs');
    
    // Opcional: Limpiar lo que había antes
    contenedor.innerHTML = ''; 
    
    filas.forEach(fila => {
        // Separamos por tabulación (Excel/Web suele usar tabs)
        // También limpiamos espacios vacíos extra
        let cols = fila.split(/\t+/).map(c => c.trim()).filter(c => c !== '');
        
        // Si no detectó tabs, intentamos separar por 2 o más espacios (PDFs raros)
        if(cols.length < 2) {
             cols = fila.split(/\s{2,}/).map(c => c.trim()).filter(c => c !== '');
        }

        // CASO 1: Tabla simple (Atributo | Valor)
        if (cols.length === 2) {
            agregarFila(cols[0], cols[1]);
        }
        // CASO 2: Tabla Doble (Atributo | Valor | Atributo | Valor) - Como la que me mostraste
        else if (cols.length >= 4) {
            agregarFila(cols[0], cols[1]); // Primer par
            agregarFila(cols[2], cols[3]); // Segundo par
        }
        // CASO 3: A veces se pega todo junto, intentamos salvar si hay 3 columnas (Attr | Val | Unidad)
        else if(cols.length === 3) {
             agregarFila(cols[0], cols[1] + ' ' + cols[2]);
        }
    });

    // Cerrar modal y limpiar
    document.getElementById('modalParser').classList.add('hidden');
    document.getElementById('textoPegado').value = '';
}
 
</script>

</body>
</html>
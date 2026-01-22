/**
 * Logic for Technical Specifications
 * Handles adding rows and parsing pasted text key-value pairs.
 */

// Global counter for specs (Legacy support removed - using auto-indexing)
// if (typeof contador === 'undefined') { var contador = 0; }

/**
 * Adds a new row to the specs container.
 * @param {string} attr - Initial attribute name
 * @param {string} val - Initial value
 */
function agregarFila(attr = '', val = '') {
    const c = document.getElementById('contenedor-specs');
    // Check if the container exists (it might not if partial isn't loaded)
    if (!c) return;

    const div = document.createElement('div');
    // Using simple names to match what is currently in tech_specs.php
    // name="spec_atributo[]" and name="spec_valor[]"

    div.className = 'flex gap-2 items-center group animate-fade-in-up';
    div.innerHTML = `
    <div class="relative w-1/2">
      <input type="text" name="spec_atributo[]" value="${attr}" placeholder="Atributo" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-blue-200 outline-none">
    </div>
    <div class="relative w-1/2">
      <input type="text" name="spec_valor[]" value="${val}" placeholder="Valor" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-blue-200 outline-none">
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition">
      <i class="ph-bold ph-x text-xs"></i>
    </button>
  `;
    c.appendChild(div);
    // contador++;
}

/**
 * Parses text from the modal and populates specs.
 */
function procesarPegado() {
    let texto = document.getElementById('textoPegado').value;
    if (!texto.trim()) return;

    // Normalizar saltos de línea y limpiar espacios
    const lineas = texto.split('\n').map(l => l.trim()).filter(l => l);
    const contenedor = document.getElementById('contenedor-specs');

    // contenedor.innerHTML = ''; // Append mode requested by user

    // Heurística: Determinar modo de parseo
    // Si la mayoría de líneas tienen tabs o dobles espacios, es modo "Misma Linea"
    // Si no, asumimos modo "Linea Alternada" (Linea 1: Attr, Linea 2: Val)
    let contMismaLinea = 0;
    lineas.forEach(l => {
        if (l.includes('\t') || l.includes('  ')) contMismaLinea++;
    });

    const esModoMismaLinea = contMismaLinea > (lineas.length / 3); // Si al menos 1/3 tiene tabs/espacios dobles

    if (esModoMismaLinea) {
        // MODO 1: Misma línea (tab/espacios)
        lineas.forEach(fila => {
            let cols = fila.split(/\t+/).map(c => c.trim()).filter(c => c !== '');
            if (cols.length < 2) cols = fila.split(/\s{2,}/).map(c => c.trim()).filter(c => c !== '');

            if (cols.length >= 2) {
                agregarFila(cols[0], cols[1]); // Attr + Val
                if (cols.length >= 4) agregarFila(cols[2], cols[3]); // Attr2 + Val2
            } else if (cols.length === 1) {
                // Caso raro: linea con solo 1 columna válida en modo tab? ignorar o poner vacio
            }
        });
    } else {
        // MODO 2: Líneas Alternadas (Attr \n Val)
        for (let i = 0; i < lineas.length; i += 2) {
            const attr = lineas[i];
            const val = (i + 1 < lineas.length) ? lineas[i + 1] : '';
            if (attr) agregarFila(attr, val);
        }
    }

    // Close modal
    document.getElementById('modalParser').classList.add('hidden');
    document.getElementById('textoPegado').value = '';
}

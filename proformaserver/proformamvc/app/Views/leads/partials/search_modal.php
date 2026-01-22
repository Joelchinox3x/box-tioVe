<!-- Modal ÚNICO de Búsqueda con Confirmación -->
<div id="modalConfirmacionBusqueda" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[70] flex items-end sm:items-center justify-center">
    <div class="bg-white w-full sm:max-w-md sm:rounded-2xl rounded-t-3xl shadow-2xl animate-slide-up max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4 sm:rounded-t-2xl rounded-t-3xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i id="modalHeaderIcon" class="ph-bold ph-info text-xl"></i>
                    </div>
                    <div>
                        <h3 id="modalHeaderTitle" class="font-bold text-lg">Confirmar Búsqueda</h3>
                        <p class="text-xs text-indigo-100" id="confirmModalDniRuc"></p>
                    </div>
                </div>
                <button onclick="cerrarModalConfirmacion()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-all">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-5">

            <!-- FASE 1: Confirmación (visible al inicio) -->
            <div id="faseConfirmacion" class="space-y-4">

                <!-- Información de la API -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="ph-bold ph-database text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1">Fuente de datos</p>
                            <p class="text-slate-800 font-bold text-sm" id="confirmFuente"></p>
                            <p class="text-slate-600 text-xs mt-1" id="confirmCampos"></p>
                        </div>
                    </div>
                </div>

                <!-- Datos Actuales -->
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4 border-2 border-slate-200">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
                        Datos actuales del lead
                    </p>

                    <div class="space-y-2">
                        <!-- Nombre Actual -->
                        <div>
                            <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Nombre</label>
                            <p id="confirmNombreActual" class="text-slate-800 font-bold text-sm"></p>
                        </div>

                        <!-- Dirección Actual (si existe) -->
                        <div id="confirmDireccionActualContainer" class="hidden">
                            <label class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Dirección</label>
                            <p id="confirmDireccionActual" class="text-slate-700 text-xs leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                <!-- Advertencia -->
                <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <i class="ph-bold ph-warning text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-amber-900 font-bold text-xs">Los datos encontrados sobrescribirán la información actual</p>
                            <p class="text-amber-700 text-[11px] mt-1">Esta acción actualizará el registro en la base de datos.</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-2">
                    <button
                        onclick="cerrarModalConfirmacion()"
                        class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all"
                    >
                        Cancelar
                    </button>
                    <button
                        onclick="confirmarYBuscar()"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                    >
                        <i class="ph-bold ph-magnifying-glass"></i>
                        Buscar
                    </button>
                </div>

            </div>

            <!-- FASE 2: Loading (oculto) -->
            <div id="faseBuscando" class="hidden text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-full mb-4">
                    <i class="ph ph-spinner animate-spin text-3xl text-blue-600"></i>
                </div>
                <p class="text-slate-600 font-medium">Consultando datos...</p>
                <p class="text-slate-400 text-sm mt-1">Esto puede tomar unos segundos</p>
            </div>

            <!-- FASE 3: Resultados Encontrados (oculto) -->
            <div id="faseResultados" class="hidden space-y-4">
                <!-- Nombre -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-200">
                    <label class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2 block flex items-center gap-1.5">
                        <i class="ph-bold ph-user text-sm"></i>
                        Nombre Completo
                    </label>
                    <p id="resultadoNombre" class="text-slate-800 font-bold text-lg leading-tight"></p>
                </div>

                <!-- Dirección (solo para RUC) -->
                <div id="resultadoDireccionContainer" class="hidden bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-4 border-2 border-violet-200">
                    <label class="text-xs font-bold text-violet-700 uppercase tracking-wide mb-2 block flex items-center gap-1.5">
                        <i class="ph-bold ph-map-pin text-sm"></i>
                        Dirección
                    </label>
                    <p id="resultadoDireccion" class="text-slate-800 font-medium text-sm leading-tight"></p>
                </div>

                <!-- Acciones -->
                <div class="flex gap-3 pt-2">
                    <button
                        onclick="cerrarModalConfirmacion()"
                        class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all"
                    >
                        Cancelar
                    </button>
                    <button
                        onclick="actualizarLead()"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-xl"
                    >
                        <i class="ph-bold ph-check-circle"></i>
                        Actualizar
                    </button>
                </div>
            </div>

            <!-- FASE 4: Error (oculto) -->
            <div id="faseError" class="hidden text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-50 rounded-full mb-4">
                    <i class="ph-bold ph-warning-circle text-3xl text-red-600"></i>
                </div>
                <p class="text-slate-800 font-bold text-lg mb-1">No se encontraron datos</p>
                <p id="errorMsg" class="text-slate-500 text-sm mb-4"></p>
                <button
                    onclick="cerrarModalConfirmacion()"
                    class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all"
                >
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal para Actualizar Celular -->
<div id="modalPhoneUpdate" class="fixed inset-0 z-[70] hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-scale-up">
        
        <!-- Header -->
        <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
                <i class="ph-duotone ph-whatsapp-logo text-green-500 text-lg"></i>
                Actualizar Celular
            </h3>
            <button type="button" onclick="closePhoneModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5">
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-2 text-green-500">
                     <i class="ph-fill ph-phone text-2xl"></i>
                </div>
                <p class="text-sm text-slate-600" id="phoneModalMessage">
                    Este cliente no tiene un número registrado. Por favor ingrésalo para continuar.
                </p>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase">Número de Celular (WhatsApp)</label>
                
                <!-- Input Container Refactorizado -->
                <!-- Input Container Wrapper -->
                <div class="flex gap-2">
                    <div id="telefonoContainerModal" class="grid grid-cols-[72px_1fr_44px] items-center relative border-2 border-slate-200 rounded-xl bg-slate-50 transition-all duration-300 flex-1">
                        
                        <!-- Prefijo -->
                        <div class="flex items-center justify-center gap-2 text-sm text-slate-500 font-medium pointer-events-none">
                            <img src="https://flagcdn.com/w20/pe.png" alt="PE" class="w-5 h-4 rounded shadow-sm">
                            <span>+51</span>
                        </div>

                        <!-- Input -->
                        <input type="tel" id="phoneUpdateInput" class="w-full py-3 text-sm outline-none bg-transparent placeholder-slate-400 font-bold text-slate-800" placeholder="999 888 777">

                        <!-- WhatsApp Icon -->
                        <div class="flex items-center justify-center pr-3">
                             <!-- Fallback icon se reemplazará por lottie si está disponible -->
                            <i id="whatsappIconStatic" class="ph-fill ph-whatsapp-logo text-green-500 text-xl opacity-50"></i>
                        </div>
                    </div>

                    <!-- Botón Importar Contacto (Solo Móvil) -->
                    <button type="button" id="btnImportarContactoModal"
                            class="hidden px-3.5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center"
                            title="Importar desde Agenda">
                        <i class="ph-bold ph-address-book text-xl"></i>
                    </button>
                </div>

                <!-- Mensaje de Validación -->
                <p class="text-[10px] mt-1 text-right transition-colors text-slate-500" id="telefonoHintModal">
                    <i class="ph-bold ph-info"></i>
                    <span id="telefonoHintTextModal">Ingresa 9 dígitos sin el +51</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 px-4 py-3 border-t border-slate-100 flex gap-2">
            <button type="button" onclick="closePhoneModal()" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                Cancelar
            </button>
            <button type="button" onclick="saveClientPhone()" class="flex-1 py-2 text-xs font-bold text-white bg-green-500 hover:bg-green-600 rounded-xl shadow-md shadow-green-200 transition-all flex items-center justify-center gap-2">
                Guardar y Enviar <i class="ph-bold ph-paper-plane-right"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function closePhoneModal() {
        const modal = document.getElementById('modalPhoneUpdate');
        if (modal) modal.classList.add('hidden');
    }
</script>

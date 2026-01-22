<!-- MODAL SOLICITAR PROFORMA (REFACTORED) -->
<!-- items-center -> items-start pt-24 para subirlo -->
<div id="proformaModal" class="hidden fixed inset-0 z-[60] flex items-start justify-center pt-20 px-4 py-2">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

    <!-- Modal Card -->
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all scale-100 animate-fade-in-up">
        
        <!-- Header Premium -->
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 relative overflow-hidden">
            <!-- Decoración -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full -ml-12 -mb-12"></div>
            </div>

            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="ph-bold ph-file-text text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg leading-tight">Solicitar Proforma</h3>
                        <p class="text-indigo-100 text-xs">Completa tus datos para contactarte</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-lg p-1.5">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-5 py-2 space-y-1.5">
            
            <!-- Nombre -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Tu Nombre o Empresa</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph-bold ph-user text-indigo-300"></i>
                    </div>
                    <input type="text" id="clientName" 
                        maxlength="50"
                        class="block w-full pl-10 pr-16 py-2 bg-slate-50 border-0 text-slate-900 text-sm rounded-xl ring-1 ring-inset ring-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 focus:bg-white outline-none transition-all font-medium" 
                        placeholder="Ej: Juan Pérez / Empresa SAC">
                        
                    <!-- Iconos de Validación (Derecha) -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 transition-all pointer-events-none flex items-center justify-center">
                        <i class="ph-bold ph-check-circle text-green-500 text-lg hidden" id="name-check"></i>
                        <i class="ph-bold ph-x-circle text-red-500 text-lg hidden" id="name-error-icon"></i>
                        
                        <!-- Lottie Success Check Name -->
                        <div id="name-lottie-success" class="hidden absolute -right-6 w-16 h-16">
                            <lottie-player src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1.5" style="width: 100%; height: 100%;" autoplay></lottie-player>
                        </div>
                    </div>
                </div>
                <p id="nameError" class="hidden text-[10px] text-red-500 font-bold mt-1 ml-1"></p>
            </div>

            <!-- DNI/RUC -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">DNI o RUC</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i id="dniIcon" class="ph-bold ph-identification-card text-indigo-300 transition-colors"></i>
                    </div>
                    <input type="tel" id="docNumber" 
                        maxlength="11"
                        class="block w-full pl-10 pr-16 py-2 bg-slate-50 border-0 text-slate-900 text-sm rounded-xl ring-1 ring-inset ring-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 focus:bg-white outline-none transition-all font-medium" 
                        placeholder="8 u 11 dígitos">
                        
                    <!-- Iconos de Validación -->
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 transition-all pointer-events-none flex items-center justify-center">
                        <i class="ph-bold ph-check-circle text-green-500 text-lg hidden" id="dni-check"></i>
                        <i class="ph-bold ph-x-circle text-red-500 text-lg hidden" id="dni-error"></i>
                        
                        <!-- Lottie Success Check -->
                        <div id="dni-lottie-success" class="hidden absolute -right-6 w-16 h-16">
                            <lottie-player src="<?= asset('assets/lottie/check.json') ?>" background="transparent" speed="1.5" style="width: 100%; height: 100%;" autoplay></lottie-player>
                        </div>
                    </div>
                </div>
                <p id="dniErrorText" class="hidden text-[10px] text-red-500 font-bold mt-1 ml-1"></p>
            </div>

        </div>
        <!-- Footer -->
        <div class="px-5 py-2 gap-2 bg-slate-50 border-t border-slate-100 flex">
            <button onclick="closeModal()" class="flex-1 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition active:scale-95 text-sm">
                Cancelar
            </button>
            <button onclick="sendProformaRequest()" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-green-200 transition active:scale-95 text-sm flex items-center justify-center gap-2">
                <i class="ph-bold ph-whatsapp-logo"></i>
                Solicitar
            </button>
        </div>
        
    </div>
</div>

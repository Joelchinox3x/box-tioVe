<?php
// app/Views/public/partials/footer_cta.php
// ADDED: AI Chatbot Widget (Replaces simple WhatsApp button)

// Asegurar acceso a Helpers
if (!class_exists('\App\Helpers\SettingsHelper')) {
    require_once __DIR__ . '/../../../../Helpers/SettingsHelper.php';
}
$managerName = \App\Helpers\SettingsHelper::getManagerName();
$managerPhone = \App\Helpers\SettingsHelper::getManagerWhatsapp();
?>

<!-- Phosphor Icons (Necesario para los iconos del chat) -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<?php if (\App\Helpers\SettingsHelper::isChatbotEnabled()): ?>
<!-- Widget Container -->
<div id="ai-widget-container" class="fixed bottom-4 right-4 md:bottom-8 md:right-8 z-[100] font-sans">
    
    <!-- Chat Window (Hidden by default) -->
    <div id="ai-chat-window" class="hidden flex flex-col absolute bottom-20 right-0 w-[340px] md:w-[380px] h-[500px] max-h-[80vh] md:max-h-[600px] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transition-all origin-bottom-right transform scale-95 opacity-0 z-[101]">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-4 flex items-center justify-between text-white shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <i class="ph-fill ph-robot text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-tight">Asistente Virtual</h3>
                    <p class="text-[10px] text-blue-100 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        En línea ahora
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-1">
                <!-- WhatsApp Fallback Button -->
                <a href="https://wa.me/51<?= $managerPhone ?>" target="_blank" title="Hablar por WhatsApp" class="p-2 hover:bg-white/10 rounded-full transition-colors">
                    <i class="ph-bold ph-whatsapp-logo text-lg"></i>
                </a>
                
                <!-- Close Button -->
                <button onclick="toggleAiChat()" class="p-2 hover:bg-white/10 rounded-full transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="ai-messages" class="flex-1 overflow-y-auto p-4 bg-slate-50 flex flex-col gap-3">
            <!-- Welcome Message -->
            <div class="flex items-end gap-2 max-w-[85%] self-start animate-fade-in-up">
                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 border border-indigo-200">
                    <i class="ph-fill ph-robot text-xs"></i>
                </div>
                <div class="bg-white p-3 rounded-2xl rounded-bl-sm border border-slate-200 shadow-sm text-sm text-slate-700">
                    <p>👋 ¡Hola! Soy el asistente de Tradimacova.</p>
                    <p class="mt-1">Puedo buscar equipos en inventario, darte precios o tomar tus datos.</p>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex gap-2 self-start pl-8 flex-wrap animate-fade-in-up delay-100">
                <button onclick="sendQuickMessage('¿Qué precios tienen?')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors">
                    💰 Consultar Precios
                </button>
                <button onclick="sendQuickMessage('Quiero contactar a un vendedor')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition-colors">
                    👤 Asesor Humano
                </button>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-slate-100 shrink-0">
            <!-- Typing Indicator -->
            <div id="ai-typing" class="hidden text-[10px] text-slate-400 mb-2 pl-2 font-medium">
                Escribiendo una respuesta...
            </div>
            
            <div class="relative flex items-center gap-2">
                <input type="text" id="ai-input" 
                       class="w-full bg-slate-100 border-0 rounded-xl px-4 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all placeholder:text-slate-400"
                       placeholder="Escribe tu consulta..."
                       onkeypress="handleAiKeyPress(event)">
                       
                <button onclick="sendAiMessage()" id="ai-send-btn" 
                        class="absolute right-2 p-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-md shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ph-bold ph-paper-plane-right"></i>
                </button>
            </div>
            <div class="text-center mt-2">
                 <p class="text-[9px] text-slate-400">Powered by Google Gemini 🤖</p>
            </div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button onclick="toggleAiChat()" id="ai-toggle-btn" class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-indigo-600 to-purple-700 rounded-full flex items-center justify-center text-white shadow-2xl shadow-indigo-900/30 hover:scale-110 hover:-rotate-3 active:scale-95 transition-all duration-300 group ring-4 ring-white/20 relative z-50">
        <!-- Icons -->
        <i class="ph-fill ph-chat-teardrop-dots text-2xl md:text-3xl absolute transition-all duration-300 group-hover:opacity-0 scale-100 group-hover:scale-50"></i>
        <i class="ph-fill ph-robot text-2xl md:text-3xl absolute transition-all duration-300 opacity-0 group-hover:opacity-100 scale-50 group-hover:scale-100"></i>
        
        <!-- Badge -->
        <span class="absolute top-0 right-0 flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
        </span>
    </button>
</div>

<script>
    let chatHistory = [];
    const chatWindow = document.getElementById('ai-chat-window');
    const messagesDiv = document.getElementById('ai-messages');
    const input = document.getElementById('ai-input');
    const typingIndicator = document.getElementById('ai-typing');
    const sendBtn = document.getElementById('ai-send-btn');
    
    // Auto-open on first visit (Optional)
    // setTimeout(() => { if(chatHistory.length === 0) toggleAiChat(); }, 3000);

    function toggleAiChat() {
        chatWindow.classList.toggle('hidden');
        
        if (!chatWindow.classList.contains('hidden')) {
            // Animacion Entrada
            requestAnimationFrame(() => {
                chatWindow.classList.remove('scale-95', 'opacity-0');
                chatWindow.classList.add('scale-100', 'opacity-100');
                input.focus();
            });
            // Scroll to bottom
            scrollToBottom();
        } else {
            // Animacion Salida
            chatWindow.classList.add('scale-95', 'opacity-0');
            chatWindow.classList.remove('scale-100', 'opacity-100');
        }
    }

    function handleAiKeyPress(e) {
        if(e.key === 'Enter') sendAiMessage();
    }
    
    function sendQuickMessage(text) {
        input.value = text;
        sendAiMessage();
    }

    async function sendAiMessage() {
        const text = input.value.trim();
        if(!text) return;

        // UI Updates
        addMessage(text, 'user');
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        try {
            const response = await fetch('/chat/message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    message: text,
                    history: chatHistory,
                    context: 'public'
                })
            });
            
            const data = await response.json();
            
            typingIndicator.classList.add('hidden');
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();

            if(data.reply) {
                addMessage(data.reply, 'bot');
            } else {
                addMessage('⚠️ Error de comunicación. Intenta de nuevo.', 'bot');
            }

        } catch (e) {
            console.error(e);
            typingIndicator.classList.add('hidden');
            input.disabled = false;
            sendBtn.disabled = false;
            addMessage('❌ Error de red. Verifica tu conexión.', 'bot');
        }
    }

    function addMessage(text, sender) {
        const div = document.createElement('div');
        div.className = `max-w-[85%] self-${sender === 'user' ? 'end' : 'start'} animate-fade-in-up`;
        
        // Markdown-like formatting (Simple)
        // Convert *bold* to <b>bold</b>
        let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
        formattedText = formattedText.replace(/\*(.*?)\*/g, '<i>$1</i>');
        // New lines
        formattedText = formattedText.replace(/\n/g, '<br>');

        const isBot = sender === 'bot';
        
        // Avatar logic
        const avatar = isBot 
            ? `<div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 border border-indigo-200 mt-1"><i class="ph-fill ph-robot text-[10px]"></i></div>`
            : '';

        div.innerHTML = `
            <div class="flex items-start gap-2 ${isBot ? '' : 'flex-row-reverse'}">
                ${avatar}
                <div class="${isBot ? 'bg-white text-slate-700 border border-slate-200' : 'bg-indigo-600 text-white shadow-md shadow-indigo-200'} p-3 rounded-2xl ${isBot ? 'rounded-bl-sm' : 'rounded-br-sm'} shadow-sm text-sm">
                    ${formattedText}
                </div>
            </div>
        `;
        
        messagesDiv.appendChild(div);
        
        // Save to history (Gemini format)
        chatHistory.push({ role: sender === 'user' ? 'user' : 'model', content: text });
        
        scrollToBottom();
    }
    
    function scrollToBottom() {
        // Forzar scroll al fondo inmediatamente
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Asegurar scroll después de la renderización (por si la animación afecta la altura)
        setTimeout(() => {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }, 50);
    }
</script>
<?php else: ?>
    <?php include __DIR__ . '/widget_whatsapp.php'; ?>
<?php endif; ?>

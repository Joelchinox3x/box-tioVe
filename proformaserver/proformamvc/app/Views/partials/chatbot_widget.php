<?php
// Asegurar helper
if (!class_exists('\App\Helpers\SettingsHelper')) {
    // Ajustar ruta si es necesario según donde se incluye
    // Como se incluye en home/index.php (app/Views/home), la ruta a Helpers es ../../Helpers
    require_once __DIR__ . '/../../Helpers/SettingsHelper.php';
}
$managerName = \App\Helpers\SettingsHelper::getManagerName();
?>

<style>
    /* Widget Styles */
    #chat-widget-container {
        position: fixed;
        bottom: 80px;
        right: 20px;
        z-index: 9999;
        font-family: 'Inter', sans-serif;
    }

    /* Floating Button (Manager Mode: Dark/Gold) */
    #chat-toggle-btn {
        width: 60px;
        height: 60px;
        background: #0f172a; /* Slate 900 */
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        border: 2px solid #fbbf24; /* Amber 400 */
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
        color: #fbbf24;
        font-size: 28px;
    }
    #chat-toggle-btn:hover { transform: scale(1.05); }

    /* Chat Window (Bottom Sheet Mode) */
    #chat-window {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 50vh;
        max-height: 600px;
        background: white;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        box-shadow: 0 -5px 30px rgba(0,0,0,0.15);
        display: none; /* Hidden by default */
        flex-direction: column;
        overflow: hidden;
        border-top: 3px solid #fbbf24;
        z-index: 10000;
    }
    
    #chat-window.open { display: flex; animation: slideUp 0.3s ease-out; }
    
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }

    /* Header (Manager Mode) */

    .chat-header {
        background: #0f172a;
        padding: 15px;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 3px solid #fbbf24;
    }
    .chat-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
    .chat-header p { margin: 0; font-size: 12px; opacity: 0.8; }
    
    /* Messages Area */
    #chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f9fafb;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* Message Bubbles */
    .message {
        max-width: 80%;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.4;
    }
    .message.user {
        background: #4f46e5;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }
    .message.bot {
        background: white;
        color: #1f2937;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Input Area */
    .chat-input-area {
        padding: 15px;
        border-top: 1px solid #e5e7eb;
        background: white;
        display: flex;
        gap: 10px;
    }
    #chat-input {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 10px 15px;
        font-size: 14px;
        outline: none;
    }
    #chat-input:focus { border-color: #4f46e5; }
    
    #chat-send-btn {
        background: white;
        border: none;
        color: #4f46e5;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: bg 0.2s;
    }
    #chat-send-btn:hover { background: #eff6ff; }
    
    /* Typing Indicator */
    .typing { font-size: 12px; color: #6b7280; margin-left: 10px; display: none; }
</style>

<?php if (\App\Helpers\SettingsHelper::isChatbotEnabled()): ?>
<div id="chat-widget-container">
    <!-- Ventana de Chat -->
    <div id="chat-window">
        <div class="chat-header">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                <i class="ph-bold ph-robot text-xl"></i>
            </div>
            <div>
                <h3>Asistente Virtual</h3>
                <p>En línea • Evento Box</p>
            </div>
            <button onclick="toggleChat()" class="ml-auto text-white hover:bg-white/10 rounded-full p-1">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        
        <div id="chat-messages">
            <div class="message bot">
                ¡Hola <?= htmlspecialchars($managerName) ?>! 🤵 Bienvenido a tu panel.
                <div class="mt-2 flex flex-col gap-2">
                    <button onclick="sendQuickAction('¿Hay nuevos leads recientes?')" class="text-left text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg border border-slate-200 transition-colors">
                        📋 Consultar Leads Recientes
                    </button>
                    <button onclick="sendQuickAction('¿Alguna proforma pendiente?')" class="text-left text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg border border-slate-200 transition-colors">
                        📄 Ver Proformas Pendientes
                    </button>
                </div>
            </div>
        </div>
        <div class="typing" id="typing-indicator">Escribiendo...</div>

        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Escribe tu consulta..." onkeypress="handleKeyPress(event)">
            <button id="chat-send-btn" onclick="sendMessage()">
                <i class="ph-fill ph-paper-plane-right text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Botón Flotante -->
    <div id="chat-toggle-btn" onclick="toggleChat()">
        <i class="ph-fill ph-chat-circle-dots"></i>
    </div>
</div>
<?php endif; ?>

<script>
    let chatHistory = [];
    const chatWindow = document.getElementById('chat-window');
    const messagesDiv = document.getElementById('chat-messages');
    const input = document.getElementById('chat-input');
    const typingIndicator = document.getElementById('typing-indicator');

    function toggleChat() {
        chatWindow.classList.toggle('open');
        
        if(chatWindow.classList.contains('open')) {
            // Scroll al fondo de la página
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            // NO auto-focus para evitar teclado en móvil
        }
    }

    function handleKeyPress(e) {
        if(e.key === 'Enter') sendMessage();
    }

    function sendQuickAction(text) {
        input.value = text;
        sendMessage();
    }

    async function sendMessage() {
        const text = input.value.trim();
        if(!text) return;

        // 1. Mostrar mensaje usuario
        addMessage(text, 'user');
        input.value = '';
        
        // 2. Mostrar indicador escribiendo
        typingIndicator.style.display = 'block';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        try {
            // 3. Llamar al backend
            const response = await fetch('/chat/message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    message: text,
                    history: chatHistory,
                    context: 'manager'
                })
            });
            
            const data = await response.json();
            
            // 4. Ocultar indicador y mostrar respuesta
            typingIndicator.style.display = 'none';
            if(data.reply) {
                addMessage(data.reply, 'bot');
            } else {
                addMessage('Lo siento, hubo un error de conexión.', 'bot');
            }

        } catch (e) {
            typingIndicator.style.display = 'none';
            addMessage('Error al conectar con el servidor.', 'bot');
            console.error(e);
        }
    }

    function addMessage(text, sender) {
        const div = document.createElement('div');
        div.className = `message ${sender}`;
        div.innerText = text; // Safe text
        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Guardar en historial (limítalo a últimos 10 mensajes si quieres)
        chatHistory.push({ role: sender, content: text });
    }
</script>

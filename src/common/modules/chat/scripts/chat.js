function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;
    const chatWindow = document.getElementById('chat-window');
    const userMessage = document.createElement('div');
    userMessage.className = 'chat-message user';
    userMessage.textContent = message;
    chatWindow.appendChild(userMessage);
    input.value = '';
    chatWindow.scrollTop = chatWindow.scrollHeight;
    const respuestas = [
        'Agente: Gracias por tu mensaje. Estamos revisando tu caso.',
        'Agente: Hemos recibido tu consulta, te responderemos pronto.',
        'Agente: Tu mensaje ha sido enviado correctamente.',
        'Agente: Un agente humano te contactará en breve.',
        'Agente: Gracias por esperar, estamos procesando tu solicitud.',
        'Agente: Balatro'
    ];
    const respuestaAleatoria = respuestas[Math.floor(Math.random() * respuestas.length)];
    setTimeout(() => {
        const agentMessage = document.createElement('div');
        agentMessage.className = 'chat-message agent';
        agentMessage.textContent = respuestaAleatoria;
        chatWindow.appendChild(agentMessage);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }, 1000);
}

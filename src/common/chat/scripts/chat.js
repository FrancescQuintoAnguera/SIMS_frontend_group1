function openSidebar() {
    const mySidebar = document.querySelector('my-sidebar');
    if (mySidebar && mySidebar.shadowRoot) {
    const sidebar = mySidebar.shadowRoot.querySelector('.sidebar');
    const overlay = mySidebar.shadowRoot.querySelector('.overlay');
    if (sidebar && overlay) {
        sidebar.classList.add('open');
        overlay.classList.add('visible');
    }
    }
}

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

    setTimeout(() => {
    const agentMessage = document.createElement('div');
    agentMessage.className = 'chat-message agent';
    agentMessage.textContent = 'Agente: Gracias por tu mensaje. Estamos revisando tu caso.';
    chatWindow.appendChild(agentMessage);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    }, 1000);
}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Chatbox EzyRide</title>
  <script src="/src/common/sidebar/scripts/sidebar.js"></script>


  <style>
    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: #1D3854;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    button {
      background: #2563eb;
      color: white;
      border: none;
      padding: 1vh 1vw;
      font-size: 1.5vh;
      border-radius: 0.6vh;
      cursor: pointer;
      margin: 2vh 2vw;
    }

    button:hover {
      background: #1d4ed8;
    }

    h2 {
      color: white;
    }

    .container {
      display: flex;
      width: 100%;
      flex: 1;
      z-index: 1;
    }

    .chat-sidebar {
      width: 33vw;
      background: #1D3854;
      border-right: 0.1vw solid #ccc;
      padding: 2vh 2vw;
      box-sizing: border-box;
      overflow-y: auto;
    }

    .chat-sidebar h2 {
      margin-top: 0;
      font-size: 2vh;
    }

    .case {
      background: #76AFFF;
      margin-bottom: 1vh;
      padding: 1vh 1vw;
      border-radius: 0.6vh;
      cursor: pointer;
      font-size: 1.5vh;
      color: white;
    }

    .case:hover {
      background: #365683ff;
    }

    .chatbox {
      width: 67vw;
      display: flex;
      flex-direction: column;
      padding: 2vh 2vw;
      box-sizing: border-box;
    }

    .chat-window {
      flex: 1;
      border: 0.1vw solid #ccc;
      border-radius: 0.6vh;
      padding: 1vh 1vw;
      overflow-y: auto;
      background: #ffffff;
      margin-bottom: 1vh;
      color: black;
    }

    .chat-message {
      margin-bottom: 1vh;
      padding: 0.8vh 1vw;
      border-radius: 1vh;
      max-width: 70%;
      word-wrap: break-word;
      font-size: 1.6vh;
    }

    .user {
      background: #A6EE36;
      align-self: flex-end;
      color: black;
    }

    .agent {
      background: #03A0FF;
      align-self: flex-start;
    }

    .chat-input {
      display: flex;
    }

    .chat-input input {
      background: #1D3854;
      border: 0.1vw solid #03A0FF;
      flex: 1;
      padding: 1vh 1vw;
      border-radius: 0.6vh;
      margin-right: 0.5vw;
      font-size: 1.6vh;
      color: white;
    }

    .chat-input button {
      padding: 1vh 2vw;
      border-radius: 0.6vh;
      border: none;
      background: #A6EE36;
      color: white;
      cursor: pointer;
      font-size: 1.6vh;
    }

    .chat-input button:hover {
      background: #415e0eff;
    }

    .size-6 {
      width: 24px;
      height: 24px;
    }
  </style>
</head>
<body>
  <?php
    $sidebar_path = '/mnt/c/Users/moreno/Desktop/sprint1/frontend/SIMS_frontend_group1/src/common/sidebar/templates/sidebar.php';
    if (file_exists($sidebar_path)) {
      include $sidebar_path;
    } else {
      echo '<my-sidebar></my-sidebar>';
    }
  ?>

  <div class="container">
    <div class="chat-sidebar">
      <button onclick="openSidebar()">☰</button>
      <h2>Historial de casos</h2>
      <div class="case">Caso #101 - Pendiente</div>
      <div class="case">Caso #102 - En progreso</div>
      <div class="case">Caso #103 - Resuelto</div>
      <div class="case">Caso #104 - Pendiente</div>
      <div class="case">Caso #105 - En espera</div>
    </div>

    <div class="chatbox">
      <div class="chat-window" id="chat-window">
      </div>
      <div class="chat-input">
        <input type="text" id="message-input" placeholder="Escribe un mensaje...">
        <button onclick="sendMessage()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <script>
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
  </script>
</body>
</html>
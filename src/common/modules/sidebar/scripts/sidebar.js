class MySidebar extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: "open" });
  }

  connectedCallback() {
    const container = document.createElement("div");
    container.innerHTML = `
      <style>
        :host {
          font-family: 'Poppins';
        }

        .overlay {
          position: fixed;
          inset: 0;
          background: rgba(0, 0, 0, 0.4);
          display: none;
          z-index: 100;
        }

        .overlay.visible {
          display: block;
        }

        .sidebar {
          position: fixed;
          top: 0;
          left: 0;
          width: min(85vw, 400px);
          height: 100vh;
          background-color: #111827;
          transform: translateX(-100%);
          transition: transform 0.3s ease;
          display: flex;
          z-index: 200;
          flex-direction: column;
          justify-content: space-between;
        }

        .sidebar.open {
          transform: translateX(0);
        }

        .close-button {
          position: absolute;
          top: 1vh;
          right: 1vw;
          background: none;
          border: none;
          color: white;
          font-size: clamp(1.5rem, 5vw, 2rem);
          cursor: pointer;
          line-height: 1;
          padding: 0.25rem;
        }

        .header {
          background-color: #1f2937;
          padding: 1.5vh 1rem;
        }

        .header h1 {
          font-size: clamp(1.125rem, 4vw, 1.5rem);
          font-weight: 700;
          color: white;
          margin: 0;
        }

        .header .logo-ezy {
          color: white;
        }

        .header .logo-ride {
          color: #60a5fa;
        }

        /* Menú */
        .menu {
          display: flex;
          flex-direction: column;
          margin-top: 1vh;
          flex: 1;
          overflow-y: auto;
        }

        .menu-button {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 1.5vh 1rem;
          background: none;
          border: none;
          color: white;
          text-align: left;
          cursor: pointer;
          transition: background 0.2s ease, transform 0.1s ease;
          width: 100%;
        }

        .menu-button:hover {
          background: rgba(255, 255, 255, 0.1);
        }

        .menu-button:active {
          transform: scale(0.98);
        }

        .menu-icon {
          display: flex;
          align-items: center;
          justify-content: center;
          width: clamp(2rem, 8vw, 2.5rem);
          height: clamp(2rem, 8vw, 2.5rem);
          border-radius: 0.5rem;
          flex-shrink: 0;
        }

        .menu-icon.bg-lime {
          background-color: #a3e635;
        }

        .menu-icon svg {
          width: clamp(1rem, 4vw, 1.25rem);
          height: clamp(1rem, 4vw, 1.25rem);
        }

        .menu-icon.bg-lime svg {
          color: #111827;
        }

        .menu-text {
          display: flex;
          flex-direction: column;
        }

        .menu-text-main {
          font-weight: 600;
          font-size: clamp(0.875rem, 3.5vw, 1rem);
          line-height: 1.2;
        }

        .menu-text-sub {
          font-size: clamp(0.7rem, 3vw, 0.825rem);
          color: #9ca3af;
          line-height: 1.2;
        }

        .footer {
          background-color: #1f2937;
          padding: 1.5vh 1rem;
          display: flex;
          align-items: center;
          gap: 0.75rem;
        }

        .footer-button {
          background: none;
          border: none;
          cursor: pointer;
          border-radius: 0.5rem;
          padding: 0.5rem;
          transition: background 0.2s ease;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .footer-button:hover {
          background-color: #374151;
        }

        .footer-button svg {
          width: clamp(1.125rem, 1.75vw, 1.5rem);
          height: clamp(1.125rem, 1.75vw, 1.5rem);
          color: white;
        }

        .flag-img {
          width: clamp(1.5rem, 2.5vw, 2rem);
          height: clamp(1rem, 1.75vw, 1.5rem);
          border-radius: 0.25rem;
        }

        .search-input-container {
          padding: 1vh 1rem;
          display: none;
        }

        .search-input-container.visible {
          display: block;
        }
        .search-input {
          width: 100%;
          padding: 1vh 1rem;
          border-radius: 0.5rem;
          border: 1px solid #9ca3af;
          background-color: #1f2937;
          color: white;
          font-size: clamp(0.875rem, 1.5vw, 1rem);
          font-family: 'Poppins', sans-serif;
        }

        .search-input::placeholder {
          color: #9ca3af;
        }
      </style>

      <div class="overlay"></div>

      <div class="sidebar">
        <button class="close-button" onclick="this.getRootNode().host.cerrar(this.getRootNode().querySelector('.sidebar'), this.getRootNode().querySelector('.overlay'))">×</button>
        <div class="header">
          <h1>
            <span class="logo-ezy">Ezy</span><span class="logo-ride">Ride</span>
          </h1>
        </div>

        <div class="menu">
          <button class="menu-button" data-action="mapa">
            <div class="menu-icon bg-lime">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m-6 0l6-3"/>
              </svg>
            </div>
            <div class="menu-text">
              <div class="menu-text-main">Mapa</div>
            </div>
          </button>

          <button class="menu-button" data-action="buscar">
            <div class="menu-icon bg-lime">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            <div class="menu-text">
              <div class="menu-text-main">Buscar</div>
              <div class="menu-text-sub">dirección</div>
            </div>
          </button>
          <div class="search-input-container">
            <input type="text" class="search-input" placeholder="Introduce una dirección...">
          </div>

          <button class="menu-button" data-action="idioma">
            <div class="menu-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="menu-text">
              <div class="menu-text-main">Idioma</div>
            </div>
          </button>

          <button class="menu-button" data-action="atencion" onclick="window.location.href='/chat'">
            <div class="menu-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
              </svg>
            </div>
            <div class="menu-text">
              <div class="menu-text-main">Atención</div>
              <div class="menu-text-sub">al cliente</div>
            </div>
          </button>
          
          <button class="menu-button" data-action="atencion" onclick="window.location.href='/kanban'">
            <div class="menu-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
              </svg>
            </div>
            <div class="menu-text">
              <div class="menu-text-main">Kanban</div>
            </div>
          </button>
        </div>

        <div class="footer">
          <button class="footer-button" data-action="settings">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </button>

          <button class="footer-button" data-action="language">
            <img class="flag-img" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 750 500'%3E%3Cpath fill='%23c60b1e' d='M0 0h750v500H0z'/%3E%3Cpath fill='%23ffc400' d='M0 125h750v250H0z'/%3E%3C/svg%3E" alt="España">
          </button>
        </div>
      </div>
    `;

    this.shadowRoot.appendChild(container);

    const sidebar = this.shadowRoot.querySelector(".sidebar");
    const overlay = this.shadowRoot.querySelector(".overlay");
    const searchInputContainer = this.shadowRoot.querySelector(".search-input-container");

    overlay.addEventListener("click", () => {
      this.cerrar(sidebar, overlay);
    });

    const buttons = this.shadowRoot.querySelectorAll(".menu-button");
    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const action = btn.getAttribute("data-action");
        if (action === "buscar") {
          searchInputContainer.classList.toggle("visible");
        } else if (action) {
          document.dispatchEvent(
            new CustomEvent("menu-action", { detail: { action } })
          );
        }
      });
    });

    const footerButtons = this.shadowRoot.querySelectorAll(".footer-button");
    footerButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const action = btn.getAttribute("data-action");
        if (action) {
          document.dispatchEvent(
            new CustomEvent("footer-action", { detail: { action } })
          );
        }
      });
    });
  }

  cerrar(sidebar, overlay) {
    sidebar.classList.remove("open");
    overlay.classList.remove("visible");
    const searchInputContainer = this.shadowRoot.querySelector(".search-input-container");
    if (searchInputContainer) {
      searchInputContainer.classList.remove("visible");
    }
  }

  abrir() {
    const sidebar = this.shadowRoot.querySelector(".sidebar");
    const overlay = this.shadowRoot.querySelector(".overlay");
    sidebar.classList.add("open");
    overlay.classList.add("visible");
  }
}

customElements.define("my-sidebar", MySidebar);
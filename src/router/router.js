const routes = {
    "/login": "/modules/login/template/login.php",
    "/chatbot": "/modules/chatbox/template/chatbox.php"
}

async function navigateTo(urlPath) {
    const route = routes[urlPath] || routes["/test"];
    
    try {
        const response = await fetch(route);
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        const html = await response.text();
        document.getElementById("app").innerHTML = html;
        history.pushState({}, "", urlPath);
    } catch (error) {
        console.error('Error cargando la página:', error);
        document.getElementById("app").innerHTML = `
            <div style="padding: 20px; text-align: center;">
                <h1>Error al cargar la página</h1>
                <p>${error.message}</p>
            </div>
        `;
    }
}

document.addEventListener("click", (e) => {
    const link = e.target.closest("a");
    if(link && link.href.startsWith(window.location.origin)){
        e.preventDefault();
        navigateTo(link.getAttribute("href"));
    }
});

window.addEventListener("popstate", () => {
    navigateTo(window.location.pathname);
});

window.addEventListener("DOMContentLoaded", () => {
    navigateTo(window.location.pathname);
})

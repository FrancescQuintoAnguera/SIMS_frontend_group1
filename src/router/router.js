// TODO: Refactor all the routes is needed an array of objects

const routes = {
    "/login": "/modules/login/template/login.php",
    "/home": "/modules/home/template/home.php"
}

async function navigateTo(urlPath) {

    // TODO: We need to apply the cookies 

    if (urlPath === "/" || urlPath === "") {
        urlPath = "/login";
    }
    
    const route = routes[urlPath];
    
    //TODO: We need to make an a 404 page

    if (!route) {
        document.getElementById("app").innerHTML = `
            <div style="padding: 20px; text-align: center;">
                <h1>404 - Página no encontrada</h1>
                <p>La ruta "${urlPath}" no existe.</p>
                <a href="/login" style="color: blue; text-decoration: underline;">Volver al Login</a>
            </div>
        `;
        history.pushState({}, "", urlPath);
        return;
    }
    
    try {
        const response = await fetch(route);
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        const html = await response.text();
        const appContainer = document.getElementById("app");
        appContainer.innerHTML = html;
        
        executeScripts(appContainer);
        
        history.pushState({}, "", urlPath);
    } catch (error) {
        console.error('Error cargando la página:', error);
        document.getElementById("app").innerHTML = `
            <div style="padding: 20px; text-align: center;">
                <h1>Error al cargar el documento</h1>
                <p>${error.message}</p>
            </div>
        `;
    }
}

function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    
    scripts.forEach((oldScript) => {
        const newScript = document.createElement('script');
        
        if (oldScript.src) {
            newScript.src = oldScript.src;
        } else {
            newScript.textContent = oldScript.textContent;
        }

        Array.from(oldScript.attributes).forEach(attr => {
            if (attr.name !== 'src') {
                newScript.setAttribute(attr.name, attr.value);
            }
        });

        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

window.navigateTo = navigateTo;

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

(async () => {
    const { navigateTo } = await import('/router/router.js');
    const { getCurrentUser } = await import('/auth/auth.js');
    

    const usernameDisplay = document.getElementById('username-display');
    if (usernameDisplay) {
        try {
            const user = await getCurrentUser();
            if (user && user.username) {
                usernameDisplay.textContent = `Hola, ${user.username}`;
            } else {

                usernameDisplay.textContent = '';
                usernameDisplay.outerHTML = '<button id="register-button">Regístrate</button>';
                
                // Agregar event listener al nuevo botón
                const newRegisterButton = document.getElementById('register-button');
                if (newRegisterButton) {
                    newRegisterButton.addEventListener('click', () => {
                        navigateTo('/register');
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando usuario:', error);
            usernameDisplay.textContent = 'Usuario';
        }
    }
    
    const registerButton = document.getElementById('register-button');
    if (registerButton) {
        registerButton.addEventListener('click', () => {
            navigateTo('/register');
        });
    }
})();

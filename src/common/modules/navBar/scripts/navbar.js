(async () => {
    const { navigateTo } = await import('/router/router.js');
    
    const registerButton = document.getElementById('register-button');
    if (registerButton) {
        registerButton.addEventListener('click', () => {
            navigateTo('/register');
        });
    }
})();

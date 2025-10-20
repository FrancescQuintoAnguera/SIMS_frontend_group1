(async () => {
    // Import dinámico
    const { login, loginAsGuest } = await import('/auth/auth.js');
    const { navigateTo } = await import('/router/router.js');
    
    setTimeout(() => {
        const loginForm = document.querySelector('.login-container form');
        const guestButton = document.querySelector('.enter-as-guest');

        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const identifier = loginForm.querySelector('input[name="email"]').value;
                const password = loginForm.querySelector('input[name="password"]').value;
                
                const result = await login(identifier, password);
                
                if (result.success) {
                    navigateTo('/home');
                } else {
                    alert(result.message);
                }
            });
        }

        if (guestButton) {
            guestButton.addEventListener('click', async () => {
                const result = await loginAsGuest();
                if (result.success) {
                    navigateTo('/home');
                }
            });
        }
    }, 0);
})();

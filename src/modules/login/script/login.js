setTimeout(() => {
    const loginForm = document.querySelector('.login-container form');
    const guestButton = document.querySelector('.enter-as-guest');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const identifier = loginForm.querySelector('input[name="email"]').value;
            const password = loginForm.querySelector('input[name="password"]').value;
            
            const result = window.auth.login(identifier, password);
            
            if (result.success) {
                window.navigateTo('/home');
            } else {
                alert(result.message);
            }
        });
    }

    if (guestButton) {
        guestButton.addEventListener('click', () => {
            window.auth.loginAsGuest();
            window.navigateTo('/home');
        });
    }
}, 0);

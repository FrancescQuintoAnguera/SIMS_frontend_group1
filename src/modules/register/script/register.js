setTimeout(() => {
    const registerForm = document.querySelector('.register-container form');
    
    // Función para limpiar todos los mensajes de error
    function clearErrors() {
        document.getElementById('username-error').textContent = '';
        document.getElementById('email-error').textContent = '';
        document.getElementById('password-error').textContent = '';
        document.getElementById('repassword-error').textContent = '';
    }
    
    // Función para mostrar un error en un campo específico
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + '-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.color = 'red';
            errorElement.style.fontSize = '12px';
            errorElement.style.display = 'block';
            errorElement.style.marginTop = '5px';
        }
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            clearErrors();
            
            const username = registerForm.querySelector('input[name="username"]').value.trim();
            const email = registerForm.querySelector('input[name="email"]').value.trim();
            const password = registerForm.querySelector('input[name="password"]').value;
            const password2 = registerForm.querySelector('input[name="password2"]').value;
            

            if (password !== password2) {
                showError('repassword', 'Las contraseñas no coinciden');
                return;
            }
            

            const result = window.auth.register(username, email, password);
            
            if (result.success) {
 
                window.navigateTo('/home');
            } else if (result.errors) {
                if (result.errors.username) {
                    showError('username', result.errors.username);
                }
                if (result.errors.email) {
                    showError('email', result.errors.email);
                }
                if (result.errors.password) {
                    showError('password', result.errors.password);
                }
            } else {
                alert(result.message);
            }
        });
    }
}, 0);
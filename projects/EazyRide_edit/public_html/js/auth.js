/**
 * Authentication Module for VoltiaCar Application
 * Handles login, registration, password recovery, and session management
 */

/* global Utils */

const Auth = {
    /**
     * Login user
     */
    async login(username, password) {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                // Store user info
                Utils.setLocalStorage('user', data.user, 30);
                Utils.setLocalStorage('isAuthenticated', true, 30);
                
                // Redirect to dashboard
                window.location.href = '../dashboard/gestio.html';
                return { success: true };
            } else {
                Utils.showToast(data.message || 'Error d\'inici de sessió', 'error');
                return { success: false, message: data.message };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Login error:', error);
            Utils.showToast('Error de connexió amb el servidor', 'error');
            return { success: false, message: 'Error de connexió' };
        }
    },
    
    /**
     * Register new user
     */
    async register(userData) {
        try {
            Utils.showLoading();
            
            const response = await fetch('/php/api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(userData)
            });
            
            const data = await response.json();
            Utils.hideLoading();
            
            if (data.success) {
                Utils.showToast('Registre completat amb èxit', 'success');
                
                // Redirect to login or verification page
                setTimeout(() => {
                    if (userData.has_license) {
                        window.location.href = './verificar-conduir.html';
                    } else {
                        window.location.href = './login.html';
                    }
                }, 1500);
                
                return { success: true };
            } else {
                Utils.showToast(data.message || 'Error en el registre', 'error');
                return { success: false, message: data.message };
            }
        } catch (error) {
            Utils.hideLoading();
            console.error('Registration error:', error);
            Utils.showToast('Error de connexió amb el servidor', 'error');
            return { success: false, message: 'Error de connexió' };
        }
    },
    
    /**
     * Logout user
     */
    async logout() {
        try {
            const response = await fetch('/php/api/logout.php', {
                method: 'POST',
                credentials: 'include'
            });
            
            if (response.ok) {
                // Clear local storage
                localStorage.removeItem('user');
                localStorage.removeItem('isAuthenticated');
                localStorage.removeItem('lastActivity');
                
                // Redirect to home
                window.location.href = '../../index.html';
            } else {
                Utils.showToast('Error en tancar la sessió', 'error');
            }
        } catch (error) {
            console.error('Logout error:', error);
            Utils.showToast('Error de connexió amb el servidor', 'error');
        }
    },
    
    /**
     * Request password recovery
     */
    async requestPasswordRecovery(email) {
        try {
            Utils.showLoading();
            
            // Simulate API call (implement actual endpoint)
            console.log('Requesting password recovery for:', email);
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            Utils.hideLoading();
            Utils.showToast('S\'ha enviat un correu de recuperació', 'success');
            
            return { success: true };
        } catch (error) {
            Utils.hideLoading();
            console.error('Password recovery error:', error);
            Utils.showToast('Error en la recuperació de contrasenya', 'error');
            return { success: false };
        }
    },
    
    /**
     * Reset password with token
     */
    async resetPassword(token, newPassword) {
        try {
            Utils.showLoading();
            
            // Simulate API call (implement actual endpoint)
            console.log('Resetting password with token:', token, 'New password length:', newPassword.length);
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            Utils.hideLoading();
            Utils.showToast('Contrasenya actualitzada amb èxit', 'success');
            
            setTimeout(() => {
                window.location.href = './login.html';
            }, 1500);
            
            return { success: true };
        } catch (error) {
            Utils.hideLoading();
            console.error('Password reset error:', error);
            Utils.showToast('Error en actualitzar la contrasenya', 'error');
            return { success: false };
        }
    },
    
    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        const isAuth = Utils.getLocalStorage('isAuthenticated');
        return isAuth === true;
    },
    
    /**
     * Get current user
     */
    getCurrentUser() {
        return Utils.getLocalStorage('user');
    },
    
    /**
     * Check if user is admin
     */
    isAdmin() {
        const user = this.getCurrentUser();
        return user && user.is_admin === 1;
    },
    
    /**
     * Require authentication (redirect if not authenticated)
     */
    requireAuth() {
        if (!this.isAuthenticated()) {
            Utils.showToast('Has d\'iniciar sessió', 'warning');
            setTimeout(() => {
                window.location.href = '/pages/auth/login.html';
            }, 1000);
            return false;
        }
        return true;
    },
    
    /**
     * Require admin (redirect if not admin)
     */
    requireAdmin() {
        if (!this.requireAuth()) {
            return false;
        }
        
        if (!this.isAdmin()) {
            Utils.showToast('No tens permisos d\'administrador', 'error');
            setTimeout(() => {
                window.location.href = '/pages/dashboard/gestio.html';
            }, 1000);
            return false;
        }
        return true;
    },
    
    /**
     * Validate registration form
     */
    validateRegistrationForm(formData) {
        const errors = [];
        
        // Username
        if (!formData.username || formData.username.length < 3) {
            errors.push('El nom d\'usuari ha de tenir almenys 3 caràcters');
        }
        
        // Email
        if (!formData.email || !Utils.isValidEmail(formData.email)) {
            errors.push('Correu electrònic no vàlid');
        }
        
        // Password
        if (!formData.password || formData.password.length < 6) {
            errors.push('La contrasenya ha de tenir almenys 6 caràcters');
        }
        
        // Password confirmation
        if (formData.password !== formData.password_confirm) {
            errors.push('Les contrasenyes no coincideixen');
        }
        
        // Name
        if (!formData.name || formData.name.length < 2) {
            errors.push('El nom és obligatori');
        }
        
        // Surname
        if (!formData.surname || formData.surname.length < 2) {
            errors.push('Els cognoms són obligatoris');
        }
        
        // Phone
        if (formData.phone && !Utils.isValidPhone(formData.phone)) {
            errors.push('Número de telèfon no vàlid');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    },
    
    /**
     * Validate login form
     */
    validateLoginForm(username, password) {
        const errors = [];
        
        if (!username || username.trim() === '') {
            errors.push('El nom d\'usuari és obligatori');
        }
        
        if (!password || password.trim() === '') {
            errors.push('La contrasenya és obligatòria');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
};

/**
 * Setup login form
 */
function setupLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        // Validate
        const validation = Auth.validateLoginForm(username, password);
        if (!validation.isValid) {
            Utils.showToast(validation.errors[0], 'error');
            return;
        }
        
        // Login
        await Auth.login(username, password);
    });
}

/**
 * Setup registration form
 */
function setupRegistrationForm() {
    const form = document.getElementById('registerForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            username: document.getElementById('username')?.value.trim(),
            email: document.getElementById('email')?.value.trim(),
            password: document.getElementById('password')?.value,
            password_confirm: document.getElementById('password_confirm')?.value,
            name: document.getElementById('name')?.value.trim(),
            surname: document.getElementById('surname')?.value.trim(),
            phone: document.getElementById('phone')?.value.trim(),
            has_license: document.getElementById('has_license')?.checked || false
        };
        
        // Validate
        const validation = Auth.validateRegistrationForm(formData);
        if (!validation.isValid) {
            Utils.showToast(validation.errors[0], 'error');
            return;
        }
        
        // Register
        await Auth.register(formData);
    });
}

/**
 * Setup password recovery form
 */
function setupPasswordRecoveryForm() {
    const form = document.getElementById('recoveryForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        
        if (!email || !Utils.isValidEmail(email)) {
            Utils.showToast('Correu electrònic no vàlid', 'error');
            return;
        }
        
        await Auth.requestPasswordRecovery(email);
    });
}

/**
 * Setup logout buttons
 */
function setupLogoutButtons() {
    const logoutButtons = document.querySelectorAll('#logoutButton, [data-logout]');
    
    logoutButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            await Auth.logout();
        });
    });
}

/**
 * Initialize authentication module
 */
document.addEventListener('DOMContentLoaded', () => {
    setupLoginForm();
    setupRegistrationForm();
    setupPasswordRecoveryForm();
    setupLogoutButtons();
});

// Export for use in other scripts
window.Auth = Auth;

const API_URL = '/api/auth.php';

export const REGEX = {
    EMAIL: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    USERNAME: /^[a-zA-Z0-9_-]{3,20}$/,
    PASSWORD: /^.{4,}$/
};

export async function login(identifier, password) {
    try {
        const response = await fetch(`${API_URL}?action=login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ identifier, password })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error en login:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function loginAsGuest() {
    try {
        const response = await fetch(`${API_URL}?action=loginAsGuest`, {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error en loginAsGuest:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function logout() {
    try {
        const response = await fetch(`${API_URL}?action=logout`, {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        localStorage.removeItem('userSession');
        console.log("👋 Logout exitoso - Cookie y sesión eliminadas");
        return data;
    } catch (error) {
        console.error('Error en logout:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function register(username, email, password) {
    const errors = {};

    if (!username) {
        errors.username = "Campo vacío";
    }
    if (!email) {
        errors.email = "Campo vacío";
    }
    if (!password) {
        errors.password = "Campo vacío";
    }
    
    if (Object.keys(errors).length > 0) {
        return { success: false, errors };
    }

    if (!REGEX.USERNAME.test(username)) {
        errors.username = "El username debe tener 3-20 caracteres (letras, números, _ -)";
    }

    if (!REGEX.EMAIL.test(email)) {
        errors.email = "El email no es válido";
    } 

    if (!REGEX.PASSWORD.test(password)) {
        errors.password = "La contraseña debe tener al menos 4 caracteres";
    }

    if (Object.keys(errors).length > 0) {
        return { success: false, errors };
    }
    
    try {
        const response = await fetch(`${API_URL}?action=register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, email, password })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error en register:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function getCurrentUser() {
    try {
        const response = await fetch(`${API_URL}?action=getCurrentUser`, {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        return data.success ? data.user : null;
    } catch (error) {
        console.error('Error en getCurrentUser:', error);
        return null;
    }
}

export async function isAuthenticated() {
    const user = await getCurrentUser();
    return user !== null;
}

// Mantener compatibilidad con código existente que usa window.auth
window.auth = {
    login,
    loginAsGuest,
    logout,
    register,
    getCurrentUser,
    isAuthenticated
};

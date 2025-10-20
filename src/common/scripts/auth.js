// General functions

function generateToken() {
    return 'token_' + Math.random().toString(36).substr(2) + Date.now().toString(36);
}

function setCookie(name, value, days = 7) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}


function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
}


//Especific functions

function getUsers() {
    const users = localStorage.getItem('mockUsers');
    if (!users) {

        const defaultUsers = [
            { id: 1, username: "client", email: "client@test.dev", password: "1234", role: 2 },
            { id: 2, username: "worker", email: "worker@test.dev", password: "1234", role: 3 },
            { id: 3, username: "car-admin", email: "caradmin@test.dev", password: "1234", role: 3 },
            { id: 4, username: "super-admin", email: "super@test.dev", password: "1234", role: 4 },
            { id: 5, username: "guest", email: "", password: "", role: 1 }
        ];
        localStorage.setItem('mockUsers', JSON.stringify(defaultUsers));
        return defaultUsers;
    }
    return JSON.parse(users);
}


function saveUsers(users) {
    localStorage.setItem('mockUsers', JSON.stringify(users));
}


function getSessions() {
    const sessions = localStorage.getItem('mockSessions');
    return sessions ? JSON.parse(sessions) : {};
}


function saveSessions(sessions) {
    localStorage.setItem('mockSessions', JSON.stringify(sessions));
}


function login(identifier, password) {
    const users = getUsers();
    const user = users.find(u => 
        (u.email === identifier || u.username === identifier) && u.password === password
    );
    
    if (!user) {
        return { success: false, message: "Credenciales incorrectas" };
    }
    
    const token = generateToken();
    
    const sessions = getSessions();
    sessions[token] = {
        userId: user.id,
        username: user.username,
        email: user.email,
        role: user.role,
        createdAt: Date.now()
    };
    saveSessions(sessions);

    setCookie('authToken', token, 0.5);

    return { 
        success: true, 
        token,
        user: {
            id: user.id,
            username: user.username,
            email: user.email,
            role: user.role
        }
    };
}


function loginAsGuest() {
    const users = getUsers();
    const guestUser = users.find(u => u.role === 1);
    
    const token = generateToken();
    const sessions = getSessions();
    sessions[token] = {
        userId: guestUser.id,
        username: guestUser.username,
        email: guestUser.email,
        role: guestUser.role,
        createdAt: Date.now()
    };
    saveSessions(sessions);
    setCookie('authToken', token, 1); 
    
    return { success: true, token };
}


function getCurrentUser() {
    const token = getCookie('authToken');
    if (!token) return null;
    
    const sessions = getSessions();
    const session = sessions[token];
    
    if (!session) {
        deleteCookie('authToken');
        return null;
    }
    
    return {
        userId: session.userId,
        username: session.username,
        email: session.email,
        role: session.role
    };
}


function isAuthenticated() {
    return getCurrentUser() !== null;
}


function logout() {
    const token = getCookie('authToken');
    if (token) {

        const sessions = getSessions();
        delete sessions[token];
        saveSessions(sessions);
        
        deleteCookie('authToken');
    }
    

    localStorage.removeItem('userSession');
    
    console.log("👋 Logout exitoso - Cookie y sesión eliminadas");
}


function register(username, email, password) {
    const users = getUsers();
    
    if (!username || !email || !password) {
        return { success: false, message: "Todos los campos son obligatorios" };
    }
    
    if (password.length < 4) {
        return { success: false, message: "La contraseña debe tener al menos 4 caracteres" };
    }
    
    if (users.find(u => u.email === email)) {
        return { success: false, message: "El email ya está registrado" };
    }
    
    if (users.find(u => u.username === username)) {
        return { success: false, message: "El nombre de usuario ya existe" };
    }
    
    const newUser = {
        id: Math.max(...users.map(u => u.id)) + 1,
        username,
        email,
        password,
        role: 2
    };
    
    users.push(newUser);
    saveUsers(users);
    
    
    return login(email, password);
}

window.auth = {
    login,
    loginAsGuest,
    logout,
    register,
    getCurrentUser,
    isAuthenticated,
    getCookie,
    setCookie,
    deleteCookie
};

// Init users

(function initializeDefaultUsers() {
    const users = localStorage.getItem('mockUsers');
    if (!users) {
        const defaultUsers = [
            { id: 1, username: "client", email: "client@test.dev", password: "1234", role: 2 },
            { id: 2, username: "worker", email: "worker@test.dev", password: "1234", role: 3 },
            { id: 3, username: "car-admin", email: "caradmin@test.dev", password: "1234", role: 3 },
            { id: 4, username: "super-admin", email: "super@test.dev", password: "1234", role: 4 },
            { id: 5, username: "guest", email: "", password: "", role: 1 }
        ];
        localStorage.setItem('mockUsers', JSON.stringify(defaultUsers));
        console.log("✅ Usuarios por defecto inicializados en localStorage");
    }
})();

function toggleMenu() {
    const dropdown = document.getElementById('menuDropdown');
    dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}

function searchAddress() {
    alert('Función para buscar dirección - Implementar búsqueda de mapas aquí.');
    toggleMenu();
}

function changeLanguage() {
    const lang = prompt('Selecciona idioma: es (Español), en (English)');
    if (lang) {
        document.documentElement.lang = lang;
        alert('Idioma cambiado a: ' + lang);
    }
    toggleMenu();
}

function contactSupport() {
    alert('Contacto con soporte: support@ezyride.com');
    toggleMenu();
}

function showProfile(username) {
    alert('Perfil de ' + username);
    toggleMenu();
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('menuDropdown');
    const hamburger = document.querySelector('.hamburger-menu');
    if (!hamburger.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
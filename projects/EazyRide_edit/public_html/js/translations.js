// Sistema de traducción EazyRide - ARCHIVO COMPLETO
const translations = {
    ca: {
        nav: { dashboard: 'Tauler', vehicles: 'Vehicles', history: 'Historial', profile: 'Perfil', myProfile: 'El meu Perfil', premium: '⭐ Premium', buyPoints: 'Comprar Punts', logout: 'Tancar Sessió' },
        footer: { about: 'Sobre EazyRide', aboutText: 'Mobilitat sostenible i compartida', links: 'Enllaços', accessibility: 'Accessibilitat', premium: 'Premium', contact: 'Contacte', social: 'Xarxes Socials', rights: 'Tots els drets reservats' },
        profile: { title: 'El Meu Perfil', personalInfo: 'Informació Personal', name: 'Nom', email: 'Correu', phone: 'Telèfon', license: 'Permís', verified: 'Verificat', notVerified: 'No Verificat', verifyNow: 'Verificar', points: 'Punts EazyRide', availableTime: 'Temps Disponible', premium: 'Premium', active: 'Activa', inactive: 'Inactiva', activatePremium: 'Activar Premium', editProfile: 'Editar', save: 'Desar', cancel: 'Cancel·lar', loading: 'Carregant...', not_defined: 'No definit', error_occurred: 'Error', profile_updated: 'Perfil actualitzat', connection_error: 'Error de connexió', premium_active: 'Usuari Premium Actiu', premium_expires: 'Caduca el', days_remaining: 'dies restants', full_name: 'Nom complet', dni: 'DNI/NIE', birth_date: 'Data de naixement', address: 'Adreça', gender: 'Sexe', available_time: 'Temps disponible', buy_points: 'Comprar Punts', my_profile: 'El Meu Perfil', edit: 'Editar' },
        premium: { title: 'EazyRide Premium', subtitle: 'Desbloqueja tots els avantatges', monthly: 'Mensual', yearly: 'Anual', perMonth: '/mes', perYear: '/any', save: 'Estalvia', selectPlan: 'Seleccionar', benefits: 'Avantatges' },
        buyPoints: { title: 'Comprar Punts', currentBalance: 'Saldo Actual', points: 'punts', availableTime: 'Temps Disponible', minutes: 'minuts', packages: 'Paquets', basic: 'Bàsic', medium: 'Mitjà', large: 'Gran', extra: 'Extra', discount: 'dto', premiumDiscount: 'Descompte Premium', select: 'Seleccionar', buyNow: 'Comprar', processing: 'Processant...', success: 'Compra exitosa!', error: 'Error en compra', confirmPurchase: 'Confirmar', confirmText: 'Comprar {points} punts per {price}€?', confirm: 'Sí', cancel: 'No' },
        dashboard: { title: 'Tauler', welcome: 'Benvingut/da', stats: 'Estadístiques', activeRentals: 'Actius', totalTrips: 'Viatges', pointsBalance: 'Punts', quickActions: 'Accions', rentVehicle: 'Llogar', buyPoints: 'Comprar Punts', viewHistory: 'Historial' },
        vehicles: { title: 'Localitzar Vehicles', available: 'Disponible', inUse: 'En Ús', battery: 'Bateria', distance: 'Distància', reserve: 'Reservar', details: 'Detalls' },
        common: { loading: 'Carregant...', error: 'Error', success: 'Èxit', save: 'Desar', cancel: 'Cancel·lar', confirm: 'Confirmar', close: 'Tancar', management: 'Gestió', rights: '© 2025 EzyRide. Tots els drets reservats.' },
        toast: { loginSuccess: 'Sessió iniciada', loginError: 'Error sessió', saveSuccess: 'Desat', saveError: 'Error', purchaseSuccess: 'Compra realitzada! Has rebut {points} EazyPoints', purchaseError: 'Error en la compra', premiumActivated: 'Premium activat', premiumError: 'Error Premium', pointsAdded: 'Punts afegits', connectionError: 'Error de connexió amb el servidor' }
    },
    es: {
        nav: { dashboard: 'Panel', vehicles: 'Vehículos', history: 'Historial', profile: 'Perfil', myProfile: 'Mi Perfil', premium: '⭐ Premium', buyPoints: 'Comprar Puntos', logout: 'Cerrar Sesión' },
        footer: { about: 'Sobre EazyRide', aboutText: 'Movilidad sostenible y compartida', links: 'Enlaces', accessibility: 'Accesibilidad', premium: 'Premium', contact: 'Contacto', social: 'Redes Sociales', rights: 'Todos los derechos reservados' },
        profile: { title: 'Mi Perfil', personalInfo: 'Información Personal', name: 'Nombre', email: 'Correo', phone: 'Teléfono', license: 'Licencia', verified: 'Verificado', notVerified: 'No Verificado', verifyNow: 'Verificar', points: 'Puntos EazyRide', availableTime: 'Tiempo Disponible', premium: 'Premium', active: 'Activa', inactive: 'Inactiva', activatePremium: 'Activar Premium', editProfile: 'Editar', save: 'Guardar', cancel: 'Cancelar', loading: 'Cargando...', not_defined: 'No definido', error_occurred: 'Error', profile_updated: 'Perfil actualizado', connection_error: 'Error de conexión', premium_active: 'Usuario Premium Activo', premium_expires: 'Caduca el', days_remaining: 'días restantes', full_name: 'Nombre completo', dni: 'DNI/NIE', birth_date: 'Fecha de nacimiento', address: 'Dirección', gender: 'Sexo', available_time: 'Tiempo disponible', buy_points: 'Comprar Puntos', my_profile: 'Mi Perfil', edit: 'Editar' },
        premium: { title: 'EazyRide Premium', subtitle: 'Desbloquea todas las ventajas', monthly: 'Mensual', yearly: 'Anual', perMonth: '/mes', perYear: '/año', save: 'Ahorra', selectPlan: 'Seleccionar', benefits: 'Ventajas' },
        buyPoints: { title: 'Comprar Puntos', currentBalance: 'Saldo Actual', points: 'puntos', availableTime: 'Tiempo Disponible', minutes: 'minutos', packages: 'Paquetes', basic: 'Básico', medium: 'Medio', large: 'Grande', extra: 'Extra', discount: 'dto', premiumDiscount: 'Descuento Premium', select: 'Seleccionar', buyNow: 'Comprar', processing: 'Procesando...', success: '¡Compra exitosa!', error: 'Error en compra', confirmPurchase: 'Confirmar', confirmText: '¿Comprar {points} puntos por {price}€?', confirm: 'Sí', cancel: 'No' },
        dashboard: { title: 'Panel', welcome: 'Bienvenido/a', stats: 'Estadísticas', activeRentals: 'Activos', totalTrips: 'Viajes', pointsBalance: 'Puntos', quickActions: 'Acciones', rentVehicle: 'Alquilar', buyPoints: 'Comprar Puntos', viewHistory: 'Historial' },
        vehicles: { title: 'Localizar Vehículos', available: 'Disponible', inUse: 'En Uso', battery: 'Batería', distance: 'Distancia', reserve: 'Reservar', details: 'Detalles' },
        common: { loading: 'Cargando...', error: 'Error', success: 'Éxito', save: 'Guardar', cancel: 'Cancelar', confirm: 'Confirmar', close: 'Cerrar', management: 'Gestión', rights: '© 2025 EzyRide. Todos los derechos reservados.' },
        toast: { loginSuccess: 'Sesión iniciada', loginError: 'Error sesión', saveSuccess: 'Guardado', saveError: 'Error', purchaseSuccess: '¡Compra realizada! Has recibido {points} EazyPoints', purchaseError: 'Error en la compra', premiumActivated: 'Premium activado', premiumError: 'Error Premium', pointsAdded: 'Puntos añadidos', connectionError: 'Error de conexión con el servidor' }
    },
    en: {
        nav: { dashboard: 'Dashboard', vehicles: 'Vehicles', history: 'History', profile: 'Profile', myProfile: 'My Profile', premium: '⭐ Premium', buyPoints: 'Buy Points', logout: 'Logout' },
        footer: { about: 'About EazyRide', aboutText: 'Sustainable shared mobility', links: 'Links', accessibility: 'Accessibility', premium: 'Premium', contact: 'Contact', social: 'Social Media', rights: 'All rights reserved' },
        profile: { title: 'My Profile', personalInfo: 'Personal Info', name: 'Name', email: 'Email', phone: 'Phone', license: 'License', verified: 'Verified', notVerified: 'Not Verified', verifyNow: 'Verify', points: 'EazyRide Points', availableTime: 'Available Time', premium: 'Premium', active: 'Active', inactive: 'Inactive', activatePremium: 'Activate Premium', editProfile: 'Edit', save: 'Save', cancel: 'Cancel', loading: 'Loading...', not_defined: 'Not defined', error_occurred: 'Error', profile_updated: 'Profile updated', connection_error: 'Connection error', premium_active: 'Premium User Active', premium_expires: 'Expires on', days_remaining: 'days remaining', full_name: 'Full name', dni: 'ID/NIE', birth_date: 'Birth date', address: 'Address', gender: 'Gender', available_time: 'Available time', buy_points: 'Buy Points', my_profile: 'My Profile', edit: 'Edit' },
        premium: { title: 'EazyRide Premium', subtitle: 'Unlock all benefits', monthly: 'Monthly', yearly: 'Yearly', perMonth: '/month', perYear: '/year', save: 'Save', selectPlan: 'Select', benefits: 'Benefits' },
        buyPoints: { title: 'Buy Points', currentBalance: 'Current Balance', points: 'points', availableTime: 'Available Time', minutes: 'minutes', packages: 'Packages', basic: 'Basic', medium: 'Medium', large: 'Large', extra: 'Extra', discount: 'off', premiumDiscount: 'Premium Discount', select: 'Select', buyNow: 'Buy Now', processing: 'Processing...', success: 'Purchase successful!', error: 'Purchase error', confirmPurchase: 'Confirm', confirmText: 'Buy {points} points for {price}€?', confirm: 'Yes', cancel: 'No' },
        dashboard: { title: 'Dashboard', welcome: 'Welcome', stats: 'Statistics', activeRentals: 'Active', totalTrips: 'Trips', pointsBalance: 'Points', quickActions: 'Actions', rentVehicle: 'Rent', buyPoints: 'Buy Points', viewHistory: 'History' },
        vehicles: { title: 'Locate Vehicles', available: 'Available', inUse: 'In Use', battery: 'Battery', distance: 'Distance', reserve: 'Reserve', details: 'Details' },
        common: { loading: 'Loading...', error: 'Error', success: 'Success', save: 'Save', cancel: 'Cancel', confirm: 'Confirm', close: 'Close', management: 'Management', rights: '© 2025 EzyRide. All rights reserved.' },
        toast: { loginSuccess: 'Login successful', loginError: 'Login error', saveSuccess: 'Saved', saveError: 'Error', purchaseSuccess: 'Purchase complete! You received {points} EazyPoints', purchaseError: 'Purchase error', premiumActivated: 'Premium activated', premiumError: 'Premium error', pointsAdded: 'Points added', connectionError: 'Server connection error' }
    }
};

class TranslationSystem {
    constructor() {
        this.currentLang = this.loadLanguage();
        this.init();
    }
    
    init() {
        this.setLanguage(this.currentLang, false);
        this.setupLanguageSelector();
        this.translatePage();
    }
    
    loadLanguage() {
        const saved = localStorage.getItem('userLanguage');
        if (saved && translations[saved]) return saved;
        const browserLang = navigator.language.split('-')[0];
        return translations[browserLang] ? browserLang : 'ca';
    }
    
    setLanguage(lang, reload = true) {
        if (!translations[lang]) return;
        this.currentLang = lang;
        localStorage.setItem('userLanguage', lang);
        
        // Actualizar todos los elementos de idioma actual
        document.querySelectorAll('#currentLang, #currentLangText').forEach(el => {
            el.textContent = lang.toUpperCase();
        });
        
        if (reload) this.translatePage();
    }
    
    translatePage() {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const translation = this.getTranslation(key);
            if (translation) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.placeholder = translation;
                } else {
                    el.textContent = translation;
                }
            }
        });
    }
    
    getTranslation(key) {
        const keys = key.split('.');
        let value = translations[this.currentLang];
        for (const k of keys) {
            if (value && value[k]) value = value[k];
            else return null;
        }
        return value;
    }
    
    translate(key, replacements = {}) {
        let translation = this.getTranslation(key);
        if (!translation) return key;
        Object.keys(replacements).forEach(placeholder => {
            translation = translation.replace(`{${placeholder}}`, replacements[placeholder]);
        });
        return translation;
    }
    
    t(key, replacements = {}) {
        return this.translate(key, replacements);
    }
    
    setupLanguageSelector() {
        document.querySelectorAll('.lang-option, [onclick*="changeLanguage"]').forEach(option => {
            if (option.hasAttribute('onclick')) return; // Ya tiene handler
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const lang = option.getAttribute('data-lang');
                if (lang) {
                    this.setLanguage(lang);
                    document.getElementById('langDropdown')?.classList.remove('show');
                    document.getElementById('langMenu')?.style.setProperty('display', 'none');
                }
            });
        });
    }
}

let i18n, currentLang;

document.addEventListener('DOMContentLoaded', () => {
    i18n = new TranslationSystem();
    currentLang = i18n.currentLang;
});

function changeLanguage(lang) {
    if (i18n) {
        i18n.setLanguage(lang);
        currentLang = lang;
    } else {
        localStorage.setItem('userLanguage', lang);
        location.reload();
    }
}

function t(key, replacements = {}) {
    return i18n ? i18n.translate(key, replacements) : key;
}

window.i18n = i18n;
window.currentLang = currentLang;
window.translations = translations;
window.changeLanguage = changeLanguage;
window.t = t;

/**
 * Main JavaScript file for VoltiaCar Application
 * Handles common functionality across all pages
 */

// Global configuration
const APP_CONFIG = {
    apiBaseUrl: '/php/api',
    assetsPath: '/assets',
    sessionTimeout: 30 * 60 * 1000, // 30 minutes
    colors: {
        primary: '#1565C0',
        secondary: '#10B981',
        gray: '#6B7280',
        lightGray: '#F5F5F5',
        white: '#FFFFFF',
        black: '#000000'
    }
};

/**
 * Utility Functions
 */
const Utils = {
    /**
     * Format date to DD/MM/YYYY
     */
    formatDate(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    },
    
    /**
     * Format time to HH:MM
     */
    formatTime(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    },
    
    /**
     * Format currency (EUR)
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('ca-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },
    
    /**
     * Format minutes to readable time
     */
    formatMinutes(minutes) {
        if (minutes < 60) {
            return `${minutes} min`;
        }
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        if (mins === 0) {
            return `${hours}h`;
        }
        return `${hours}h ${mins}min`;
    },
    
    /**
     * Validate email
     */
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    /**
     * Validate phone number (Spanish format)
     */
    isValidPhone(phone) {
        const re = /^(\+34|0034|34)?[6789]\d{8}$/;
        return re.test(phone.replace(/\s/g, ''));
    },
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 3000) {
        // Remove existing toasts
        const existingToast = document.getElementById('toast-notification');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.id = 'toast-notification';
        toast.className = `fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 opacity-100`;
        
        // Set color based on type
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        toast.className += ` ${colors[type] || colors.info}`;
        
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="font-semibold">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    /**
     * Show loading spinner
     */
    showLoading(container = document.body) {
        const loading = document.createElement('div');
        loading.id = 'loading-spinner';
        loading.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loading.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1565C0]"></div>
            </div>
        `;
        container.appendChild(loading);
    },
    
    /**
     * Hide loading spinner
     */
    hideLoading() {
        const loading = document.getElementById('loading-spinner');
        if (loading) {
            loading.remove();
        }
    },
    
    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Get query parameter from URL
     */
    getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },
    
    /**
     * Set query parameter in URL
     */
    setQueryParam(param, value) {
        const url = new URL(window.location);
        url.searchParams.set(param, value);
        window.history.pushState({}, '', url);
    },
    
    /**
     * Local storage with expiry
     */
    setLocalStorage(key, value, expiryMinutes = null) {
        const item = {
            value: value,
            expiry: expiryMinutes ? Date.now() + (expiryMinutes * 60 * 1000) : null
        };
        localStorage.setItem(key, JSON.stringify(item));
    },
    
    getLocalStorage(key) {
        const itemStr = localStorage.getItem(key);
        if (!itemStr) {
            return null;
        }
        
        try {
            const item = JSON.parse(itemStr);
            
            // Check expiry
            if (item.expiry && Date.now() > item.expiry) {
                localStorage.removeItem(key);
                return null;
            }
            
            return item.value;
        } catch {
            return null;
        }
    },
    
    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showToast('Copiat al portapapers', 'success');
            return true;
        } catch (err) {
            console.error('Failed to copy:', err);
            return false;
        }
    },
    
    /**
     * Scroll to element smoothly
     */
    scrollToElement(element, offset = 0) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const top = element.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });
        }
    }
};

/**
 * API Helper
 */
const API = {
    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        };
        
        const config = { ...defaultOptions, ...options };
        
        // Add body if present
        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }
        
        try {
            const response = await fetch(`${APP_CONFIG.apiBaseUrl}${endpoint}`, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * GET request
     */
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },
    
    /**
     * POST request
     */
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: data
        });
    },
    
    /**
     * PUT request
     */
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: data
        });
    },
    
    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

/**
 * Session Manager
 */
const SessionManager = {
    checkInterval: null,
    
    /**
     * Initialize session monitoring
     */
    init() {
        this.startMonitoring();
        this.setupActivityListeners();
    },
    
    /**
     * Start monitoring session
     */
    startMonitoring() {
        // Check session every 5 minutes
        this.checkInterval = setInterval(() => {
            this.checkSession();
        }, 5 * 60 * 1000);
    },
    
    /**
     * Check if session is still valid
     */
    async checkSession() {
        try {
            const response = await API.get('/gestio.php');
            if (!response.success) {
                this.handleSessionExpired();
            }
        } catch (error) {
            console.error('Session check failed:', error);
        }
    },
    
    /**
     * Handle expired session
     */
    handleSessionExpired() {
        clearInterval(this.checkInterval);
        Utils.showToast('La teva sessió ha expirat', 'warning', 5000);
        
        setTimeout(() => {
            window.location.href = '/pages/auth/login.html';
        }, 2000);
    },
    
    /**
     * Setup activity listeners to track user activity
     */
    setupActivityListeners() {
        const events = ['mousedown', 'keydown', 'scroll', 'touchstart'];
        
        events.forEach(event => {
            document.addEventListener(event, Utils.debounce(() => {
                this.updateLastActivity();
            }, 1000));
        });
    },
    
    /**
     * Update last activity timestamp
     */
    updateLastActivity() {
        Utils.setLocalStorage('lastActivity', Date.now());
    }
};

/**
 * Initialize app when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialize session manager for authenticated pages
    const isAuthPage = window.location.pathname.includes('/pages/dashboard/') ||
                       window.location.pathname.includes('/pages/profile/') ||
                       window.location.pathname.includes('/pages/vehicle/');
    
    if (isAuthPage) {
        SessionManager.init();
    }
    
    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Handle back button
    window.addEventListener('popstate', (event) => {
        // Handle browser back/forward navigation
        console.log('Navigation:', event);
    });
});

// Export for use in other scripts
window.APP_CONFIG = APP_CONFIG;
window.Utils = Utils;
window.API = API;
window.SessionManager = SessionManager;

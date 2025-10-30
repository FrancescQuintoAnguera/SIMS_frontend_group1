/**
 * Accessibility Manager
 * Provides accessibility features including font size control, high contrast mode,
 * keyboard navigation enhancements, and screen reader optimization
 */

class AccessibilityManager {
    constructor() {
        this.settings = {
            fontSize: 'normal',
            highContrast: false,
            reducedMotion: false,
            keyboardNavigation: true
        };
        
        this.fontSizes = {
            small: 0.875,
            normal: 1,
            large: 1.125,
            xlarge: 1.25
        };
        
        this.init();
    }
    
    /**
     * Initialize accessibility manager
     */
    init() {
        // Load saved preferences
        this.loadPreferences();
        
        // Apply saved preferences
        this.applyAllSettings();
        
        // Create accessibility panel
        this.createAccessibilityPanel();
        
        // Setup keyboard navigation
        this.setupKeyboardNavigation();
        
        // Check for system preferences
        this.checkSystemPreferences();
        
        // Add event listeners
        this.setupEventListeners();
    }
    
    /**
     * Load preferences from localStorage
     */
    loadPreferences() {
        const saved = localStorage.getItem('accessibility_preferences');
        if (saved) {
            try {
                this.settings = JSON.parse(saved);
            } catch (e) {
                console.error('Error loading accessibility preferences:', e);
            }
        }
    }
    
    /**
     * Save preferences to localStorage
     */
    savePreferences() {
        localStorage.setItem('accessibility_preferences', JSON.stringify(this.settings));
    }
    
    /**
     * Apply all accessibility settings
     */
    applyAllSettings() {
        this.applyFontSize();
        this.applyHighContrast();
        this.applyReducedMotion();
        this.applyKeyboardNavigation();
    }
    
    /**
     * Apply font size setting
     */
    applyFontSize() {
        const multiplier = this.fontSizes[this.settings.fontSize] || 1;
        document.documentElement.style.fontSize = (16 * multiplier) + 'px';
        document.body.classList.remove('text-size-small', 'text-size-normal', 'text-size-large', 'text-size-xlarge');
        document.body.classList.add('text-size-' + this.settings.fontSize);
    }
    
    /**
     * Increase font size
     */
    increaseFontSize() {
        const sizes = Object.keys(this.fontSizes);
        const currentIndex = sizes.indexOf(this.settings.fontSize);
        if (currentIndex < sizes.length - 1) {
            this.settings.fontSize = sizes[currentIndex + 1];
            this.applyFontSize();
            this.savePreferences();
            this.announceToScreenReader('Font size increased to ' + this.settings.fontSize);
        }
    }
    
    /**
     * Decrease font size
     */
    decreaseFontSize() {
        const sizes = Object.keys(this.fontSizes);
        const currentIndex = sizes.indexOf(this.settings.fontSize);
        if (currentIndex > 0) {
            this.settings.fontSize = sizes[currentIndex - 1];
            this.applyFontSize();
            this.savePreferences();
            this.announceToScreenReader('Font size decreased to ' + this.settings.fontSize);
        }
    }
    
    /**
     * Reset font size to normal
     */
    resetFontSize() {
        this.settings.fontSize = 'normal';
        this.applyFontSize();
        this.savePreferences();
        this.announceToScreenReader('Font size reset to normal');
    }
    
    /**
     * Apply high contrast mode
     */
    applyHighContrast() {
        if (this.settings.highContrast) {
            document.body.classList.add('high-contrast-mode');
        } else {
            document.body.classList.remove('high-contrast-mode');
        }
    }
    
    /**
     * Toggle high contrast mode
     */
    toggleHighContrast() {
        this.settings.highContrast = !this.settings.highContrast;
        this.applyHighContrast();
        this.savePreferences();
        this.announceToScreenReader('High contrast mode ' + (this.settings.highContrast ? 'enabled' : 'disabled'));
    }
    
    /**
     * Apply reduced motion setting
     */
    applyReducedMotion() {
        if (this.settings.reducedMotion) {
            document.body.classList.add('reduce-motion');
        } else {
            document.body.classList.remove('reduce-motion');
        }
    }
    
    /**
     * Toggle reduced motion
     */
    toggleReducedMotion() {
        this.settings.reducedMotion = !this.settings.reducedMotion;
        this.applyReducedMotion();
        this.savePreferences();
        this.announceToScreenReader('Reduced motion ' + (this.settings.reducedMotion ? 'enabled' : 'disabled'));
    }
    
    /**
     * Apply keyboard navigation enhancements
     */
    applyKeyboardNavigation() {
        if (this.settings.keyboardNavigation) {
            document.body.classList.add('keyboard-navigation-enhanced');
        } else {
            document.body.classList.remove('keyboard-navigation-enhanced');
        }
    }
    
    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        // Add skip to main content link
        this.addSkipLink();
        
        // Enhance focus indicators
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('using-keyboard');
            }
        });
        
        document.addEventListener('mousedown', () => {
            document.body.classList.remove('using-keyboard');
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt + A: Open accessibility panel
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                this.toggleAccessibilityPanel();
            }
            
            // Alt + +: Increase font size
            if (e.altKey && (e.key === '+' || e.key === '=')) {
                e.preventDefault();
                this.increaseFontSize();
            }
            
            // Alt + -: Decrease font size
            if (e.altKey && e.key === '-') {
                e.preventDefault();
                this.decreaseFontSize();
            }
            
            // Alt + 0: Reset font size
            if (e.altKey && e.key === '0') {
                e.preventDefault();
                this.resetFontSize();
            }
            
            // Alt + C: Toggle high contrast
            if (e.altKey && e.key === 'c') {
                e.preventDefault();
                this.toggleHighContrast();
            }
        });
    }
    
    /**
     * Add skip to main content link
     */
    addSkipLink() {
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Skip to main content';
        skipLink.addEventListener('click', (e) => {
            e.preventDefault();
            const mainContent = document.getElementById('main-content') || document.querySelector('main');
            if (mainContent) {
                mainContent.setAttribute('tabindex', '-1');
                mainContent.focus();
            }
        });
        document.body.insertBefore(skipLink, document.body.firstChild);
    }
    
    /**
     * Check system preferences
     */
    checkSystemPreferences() {
        // Check for prefers-reduced-motion
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            if (!localStorage.getItem('accessibility_preferences')) {
                this.settings.reducedMotion = true;
                this.applyReducedMotion();
            }
        }
        
        // Check for prefers-contrast
        if (window.matchMedia('(prefers-contrast: high)').matches) {
            if (!localStorage.getItem('accessibility_preferences')) {
                this.settings.highContrast = true;
                this.applyHighContrast();
            }
        }
    }
    
    /**
     * Create accessibility panel UI
     */
    createAccessibilityPanel() {
        // Create floating button
        const button = document.createElement('button');
        button.id = 'accessibility-toggle';
        button.className = 'accessibility-toggle-btn';
        button.setAttribute('aria-label', 'Open accessibility settings');
        button.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
        `;
        button.addEventListener('click', () => this.toggleAccessibilityPanel());
        document.body.appendChild(button);
        
        // Create panel
        const panel = document.createElement('div');
        panel.id = 'accessibility-panel';
        panel.className = 'accessibility-panel hidden';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', 'Accessibility settings');
        panel.innerHTML = `
            <div class="accessibility-panel-header">
                <h2>Accessibility Settings</h2>
                <button class="close-btn" aria-label="Close accessibility settings">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l8 8M14 6l-8 8"></path>
                    </svg>
                </button>
            </div>
            <div class="accessibility-panel-content">
                <div class="accessibility-section">
                    <h3>Font Size</h3>
                    <div class="button-group">
                        <button id="font-decrease" class="btn-secondary">A-</button>
                        <button id="font-reset" class="btn-secondary">Reset</button>
                        <button id="font-increase" class="btn-secondary">A+</button>
                    </div>
                    <p class="current-setting">Current: <span id="current-font-size">${this.settings.fontSize}</span></p>
                </div>
                
                <div class="accessibility-section">
                    <h3>Display Options</h3>
                    <label class="toggle-label">
                        <input type="checkbox" id="high-contrast-toggle" ${this.settings.highContrast ? 'checked' : ''}>
                        <span>High Contrast Mode</span>
                    </label>
                    <label class="toggle-label">
                        <input type="checkbox" id="reduced-motion-toggle" ${this.settings.reducedMotion ? 'checked' : ''}>
                        <span>Reduce Motion</span>
                    </label>
                </div>
                
                <div class="accessibility-section">
                    <h3>Keyboard Shortcuts</h3>
                    <ul class="shortcuts-list">
                        <li><kbd>Alt</kbd> + <kbd>A</kbd> - Open accessibility panel</li>
                        <li><kbd>Alt</kbd> + <kbd>+</kbd> - Increase font size</li>
                        <li><kbd>Alt</kbd> + <kbd>-</kbd> - Decrease font size</li>
                        <li><kbd>Alt</kbd> + <kbd>0</kbd> - Reset font size</li>
                        <li><kbd>Alt</kbd> + <kbd>C</kbd> - Toggle high contrast</li>
                    </ul>
                </div>
                
                <div class="accessibility-section">
                    <button id="reset-all" class="btn-danger">Reset All Settings</button>
                </div>
            </div>
        `;
        document.body.appendChild(panel);
        
        // Add ARIA live region for announcements
        const liveRegion = document.createElement('div');
        liveRegion.id = 'accessibility-announcements';
        liveRegion.className = 'sr-only';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        document.body.appendChild(liveRegion);
    }
    
    /**
     * Setup event listeners for panel controls
     */
    setupEventListeners() {
        // Font size controls
        document.getElementById('font-decrease')?.addEventListener('click', () => this.decreaseFontSize());
        document.getElementById('font-increase')?.addEventListener('click', () => this.increaseFontSize());
        document.getElementById('font-reset')?.addEventListener('click', () => this.resetFontSize());
        
        // High contrast toggle
        document.getElementById('high-contrast-toggle')?.addEventListener('change', () => this.toggleHighContrast());
        
        // Reduced motion toggle
        document.getElementById('reduced-motion-toggle')?.addEventListener('change', () => this.toggleReducedMotion());
        
        // Reset all button
        document.getElementById('reset-all')?.addEventListener('click', () => this.resetAllSettings());
        
        // Close button
        document.querySelector('#accessibility-panel .close-btn')?.addEventListener('click', () => this.toggleAccessibilityPanel());
        
        // Close panel when clicking outside
        document.getElementById('accessibility-panel')?.addEventListener('click', (e) => {
            if (e.target.id === 'accessibility-panel') {
                this.toggleAccessibilityPanel();
            }
        });
        
        // Update current font size display
        this.updateFontSizeDisplay();
    }
    
    /**
     * Toggle accessibility panel visibility
     */
    toggleAccessibilityPanel() {
        const panel = document.getElementById('accessibility-panel');
        if (panel) {
            panel.classList.toggle('hidden');
            const isOpen = !panel.classList.contains('hidden');
            
            if (isOpen) {
                panel.focus();
                this.announceToScreenReader('Accessibility panel opened');
            } else {
                this.announceToScreenReader('Accessibility panel closed');
            }
        }
    }
    
    /**
     * Update font size display
     */
    updateFontSizeDisplay() {
        const display = document.getElementById('current-font-size');
        if (display) {
            display.textContent = this.settings.fontSize;
        }
    }
    
    /**
     * Reset all accessibility settings
     */
    resetAllSettings() {
        this.settings = {
            fontSize: 'normal',
            highContrast: false,
            reducedMotion: false,
            keyboardNavigation: true
        };
        
        this.applyAllSettings();
        this.savePreferences();
        
        // Update UI
        document.getElementById('high-contrast-toggle').checked = false;
        document.getElementById('reduced-motion-toggle').checked = false;
        this.updateFontSizeDisplay();
        
        this.announceToScreenReader('All accessibility settings reset to default');
    }
    
    /**
     * Announce message to screen readers
     */
    announceToScreenReader(message) {
        const liveRegion = document.getElementById('accessibility-announcements');
        if (liveRegion) {
            liveRegion.textContent = message;
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }
    
    /**
     * Add ARIA labels to elements
     */
    enhanceAriaLabels() {
        // Add ARIA labels to buttons without text
        document.querySelectorAll('button:not([aria-label])').forEach(button => {
            if (!button.textContent.trim() && button.title) {
                button.setAttribute('aria-label', button.title);
            }
        });
        
        // Add ARIA labels to links without text
        document.querySelectorAll('a:not([aria-label])').forEach(link => {
            if (!link.textContent.trim() && link.title) {
                link.setAttribute('aria-label', link.title);
            }
        });
        
        // Add role to navigation elements
        document.querySelectorAll('nav:not([role])').forEach(nav => {
            nav.setAttribute('role', 'navigation');
        });
        
        // Add role to main content
        const main = document.querySelector('main:not([role])');
        if (main) {
            main.setAttribute('role', 'main');
            main.id = 'main-content';
        }
    }
}

// Initialize accessibility manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.accessibilityManager = new AccessibilityManager();
    });
} else {
    window.accessibilityManager = new AccessibilityManager();
}

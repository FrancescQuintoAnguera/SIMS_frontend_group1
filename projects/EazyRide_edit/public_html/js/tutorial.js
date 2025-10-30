/**
 * Tutorial System for VoltiaCar Application
 * Shows on first visit and can be restarted anytime
 */

const Tutorial = {
    currentStep: 0,
    steps: [],
    overlay: null,
    tooltip: null,
    isActive: false,
    tutorialKey: 'tutorial_completed',
    tutorialData: null,
    tutorialType: null,
    
    /**
     * Initialize tutorial system
     */
    async init(tutorialType, options = {}) {
        const defaultOptions = {
            showOnFirstVisit: true,
            storageKey: `tutorial_${tutorialType}_completed`,
            skipButton: true,
            autoStart: true,
        };
        
        const config = { ...defaultOptions, ...options };
        this.tutorialKey = config.storageKey;
        this.tutorialType = tutorialType;
        
        // Load tutorial content from embedded data
        this.loadTutorialContent(tutorialType);
        
        if (!this.tutorialData) {
            console.warn('Tutorial content not loaded');
            return;
        }
        
        this.steps = this.tutorialData.steps || [];
        
        // Check if tutorial should be shown
        if (config.showOnFirstVisit && config.autoStart) {
            if (!this.hasCompletedTutorial()) {
                // Delay to ensure page is fully loaded
                setTimeout(() => {
                    this.start();
                }, 500);
            }
        }
    },

    /**
     * Load tutorial content from embedded data (Catalan only)
     */
    loadTutorialContent(tutorialType) {
        // Embedded tutorial data in Catalan
        const tutorialData = {
            common: {
                progress: 'Pas {{current}} de {{total}}',
                navigation: {
                    skip: 'Saltar tutorial',
                    previous: '← Anterior',
                    next: 'Següent →',
                    finish: 'Finalitzar',
                    close: 'Tancar'
                },
                messages: {
                    welcome: 'Benvingut a VoltiaCar! Vols fer un ràpid tutorial per conèixer totes les funcions?'
                },
                buttons: {
                    startTutorial: 'Començar Tutorial'
                }
            },
            dashboard: {
                title: 'Tutorial del Tauler',
                steps: []
            },
            vehicleLocation: {
                title: 'Tutorial de Localització de Vehicles',
                steps: []
            },
            vehicleControl: {
                title: 'Tutorial de Control de Vehicles',
                steps: []
            },
            purchaseTime: {
                title: 'Tutorial de Compra de Temps',
                steps: []
            },
            vehicleSearch: {
                title: 'Tutorial de Cerca de Vehicles',
                steps: []
            },
            booking: {
                title: 'Tutorial de Reserva',
                steps: []
            },
            profile: {
                title: 'Tutorial del Perfil',
                steps: []
            },
            registration: {
                title: 'Tutorial de Registre',
                steps: []
            },
            login: {
                title: 'Tutorial d\'Inici de Sessió',
                steps: []
            },
            accessibility: {
                title: 'Tutorial d\'Accessibilitat',
                steps: []
            }
        };
        
        this.tutorialData = tutorialData[tutorialType];
        this.commonData = tutorialData.common;
    },
    
    /**
     * Check if user has completed tutorial
     */
    hasCompletedTutorial() {
        return localStorage.getItem(this.tutorialKey) === 'true';
    },
    
    /**
     * Mark tutorial as completed
     */
    markAsCompleted() {
        localStorage.setItem(this.tutorialKey, 'true');
    },
    
    /**
     * Start tutorial
     */
    start() {
        if (this.isActive || this.steps.length === 0) return;
        
        this.isActive = true;
        this.currentStep = 0;
        
        // Create overlay
        this.createOverlay();
        
        // Show first step
        this.showStep(0);
    },
    
    /**
     * Create overlay
     */
    createOverlay() {
        // Remove existing overlay if any
        this.removeOverlay();
        
        // Create overlay element
        this.overlay = document.createElement('div');
        this.overlay.id = 'tutorial-overlay';
        this.overlay.className = 'tutorial-overlay';
        this.overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(this.overlay);
        
        // Fade in
        setTimeout(() => {
            this.overlay.style.opacity = '1';
        }, 10);
    },
    
    /**
     * Remove overlay
     */
    removeOverlay() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }
        
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
    },
    
    /**
     * Show specific step
     */
    showStep(stepIndex) {
        if (stepIndex < 0 || stepIndex >= this.steps.length) return;
        
        this.currentStep = stepIndex;
        const step = this.steps[stepIndex];
        
        // Remove previous tooltip
        if (this.tooltip) {
            this.tooltip.remove();
        }
        
        // Get target element
        const target = typeof step.element === 'string' 
            ? document.querySelector(step.element)
            : step.element;
        
        if (!target) {
            console.warn('Tutorial target not found:', step.element);
            this.next();
            return;
        }
        
        // Highlight target
        this.highlightElement(target);
        
        // Create tooltip
        this.createTooltip(target, step);
    },
    
    /**
     * Highlight target element
     */
    highlightElement(element) {
        // Remove previous highlights
        document.querySelectorAll('.tutorial-highlight').forEach(el => {
            el.classList.remove('tutorial-highlight');
        });
        
        // Add highlight class
        element.classList.add('tutorial-highlight');
        
        // Add highlight styles if not already added
        if (!document.getElementById('tutorial-styles')) {
            const style = document.createElement('style');
            style.id = 'tutorial-styles';
            style.textContent = `
                .tutorial-highlight {
                    position: relative;
                    z-index: 9999 !important;
                    box-shadow: 0 0 0 4px #1565C0, 0 0 0 8px rgba(21, 101, 192, 0.3) !important;
                    border-radius: 8px !important;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Scroll to element
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },
    
    /**
     * Create tooltip
     */
    createTooltip(target, step) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tutorial-tooltip';
        tooltip.style.cssText = `
            position: fixed;
            background-color: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            max-width: 400px;
            min-width: 300px;
            animation: fadeIn 0.3s ease;
        `;
        
        // Calculate position
        const rect = target.getBoundingClientRect();
        const tooltipHeight = 250; // Approximate
        
        let top, left;
        
        // Position below element if there's space, otherwise above
        if (rect.bottom + tooltipHeight + 20 < window.innerHeight) {
            top = rect.bottom + 20;
        } else if (rect.top - tooltipHeight - 20 > 0) {
            top = rect.top - tooltipHeight - 20;
        } else {
            // Center vertically if no space above or below
            top = (window.innerHeight - tooltipHeight) / 2;
        }
        
        // Center horizontally relative to element
        left = rect.left + (rect.width / 2);
        
        // Ensure tooltip stays within viewport
        left = Math.max(20, Math.min(left, window.innerWidth - 320));
        top = Math.max(20, Math.min(top, window.innerHeight - tooltipHeight - 20));
        
        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
        tooltip.style.transform = 'translateX(-50%)';
        
        // Get localized text
        const progressText = this.getProgressText();
        const skipText = this.commonData?.navigation?.skip || 'Saltar tutorial';
        const prevText = this.commonData?.navigation?.previous || '← Anterior';
        const nextText = this.commonData?.navigation?.next || 'Següent →';
        const finishText = this.commonData?.navigation?.finish || 'Finalitzar';
        const closeTitle = this.commonData?.navigation?.close || 'Tancar';
        
        // Create content
        const content = `
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="
                        background-color: #10B981;
                        color: white;
                        padding: 6px 14px;
                        border-radius: 20px;
                        font-size: 14px;
                        font-weight: bold;
                    ">
                        ${progressText}
                    </span>
                    <button 
                        id="tutorial-close"
                        style="
                            background: none;
                            border: none;
                            font-size: 28px;
                            cursor: pointer;
                            color: #6B7280;
                            padding: 0;
                            width: 32px;
                            height: 32px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transition: color 0.2s;
                        "
                        title="${closeTitle}"
                        onmouseover="this.style.color='#1F2937'"
                        onmouseout="this.style.color='#6B7280'"
                    >×</button>
                </div>
                <h3 style="
                    margin: 0 0 12px 0;
                    font-size: 20px;
                    font-weight: bold;
                    color: #1565C0;
                ">${step.title || ''}</h3>
                <p style="
                    margin: 0;
                    font-size: 16px;
                    line-height: 1.6;
                    color: #374151;
                ">${step.intro}</p>
            </div>
            <div style="
                display: flex;
                gap: 12px;
                justify-content: space-between;
                align-items: center;
            ">
                <button 
                    id="tutorial-skip"
                    style="
                        background: none;
                        border: none;
                        color: #6B7280;
                        font-size: 14px;
                        cursor: pointer;
                        padding: 8px 16px;
                        text-decoration: underline;
                        transition: color 0.2s;
                    "
                    onmouseover="this.style.color='#1F2937'"
                    onmouseout="this.style.color='#6B7280'"
                >${skipText}</button>
                <div style="display: flex; gap: 8px;">
                    ${this.currentStep > 0 ? `
                        <button 
                            id="tutorial-prev"
                            style="
                                background-color: #E5E7EB;
                                color: #374151;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 8px;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: background-color 0.2s;
                            "
                            onmouseover="this.style.backgroundColor='#D1D5DB'"
                            onmouseout="this.style.backgroundColor='#E5E7EB'"
                        >${prevText}</button>
                    ` : ''}
                    <button 
                        id="tutorial-next"
                        style="
                            background-color: #1565C0;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 8px;
                            font-size: 15px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: background-color 0.2s;
                        "
                        onmouseover="this.style.backgroundColor='#0D47A1'"
                        onmouseout="this.style.backgroundColor='#1565C0'"
                    >${this.currentStep < this.steps.length - 1 ? nextText : finishText}</button>
                </div>
            </div>
        `;
        
        tooltip.innerHTML = content;
        document.body.appendChild(tooltip);
        this.tooltip = tooltip;
        
        // Add event listeners
        this.setupTooltipListeners();
    },
    
    /**
     * Get progress text
     */
    getProgressText() {
        const template = this.commonData?.progress || 'Pas {{current}} de {{total}}';
        return template
            .replace('{{current}}', this.currentStep + 1)
            .replace('{{total}}', this.steps.length);
    },
    
    /**
     * Setup tooltip event listeners
     */
    setupTooltipListeners() {
        // Next button
        const nextBtn = document.getElementById('tutorial-next');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.next());
        }
        
        // Previous button
        const prevBtn = document.getElementById('tutorial-prev');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previous());
        }
        
        // Skip button
        const skipBtn = document.getElementById('tutorial-skip');
        if (skipBtn) {
            skipBtn.addEventListener('click', () => this.skip());
        }
        
        // Close button
        const closeBtn = document.getElementById('tutorial-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.skip());
        }
    },
    
    /**
     * Go to next step
     */
    next() {
        if (this.currentStep < this.steps.length - 1) {
            this.showStep(this.currentStep + 1);
        } else {
            this.complete();
        }
    },
    
    /**
     * Go to previous step
     */
    previous() {
        if (this.currentStep > 0) {
            this.showStep(this.currentStep - 1);
        }
    },
    
    /**
     * Skip tutorial
     */
    skip() {
        this.complete();
    },
    
    /**
     * Complete tutorial
     */
    complete() {
        this.isActive = false;
        this.markAsCompleted();
        this.cleanup();
    },
    
    /**
     * Cleanup tutorial elements
     */
    cleanup() {
        // Remove highlights
        document.querySelectorAll('.tutorial-highlight').forEach(el => {
            el.classList.remove('tutorial-highlight');
        });
        
        // Remove overlay and tooltip
        this.removeOverlay();
        
        // Remove styles
        const styles = document.getElementById('tutorial-styles');
        if (styles) {
            styles.remove();
        }
    },
    
    /**
     * Reset tutorial (for testing or restart)
     */
    reset() {
        localStorage.removeItem(this.tutorialKey);
        this.cleanup();
    },
    
    /**
     * Restart tutorial from beginning
     */
    restart() {
        this.reset();
        this.currentStep = 0;
        this.start();
    },
    
    /**
     * Show welcome modal for first-time users
     */
    showWelcomeModal() {
        const welcomeMsg = this.commonData?.messages?.welcome || 
            'Benvingut a VoltiaCar! Vols fer un ràpid tutorial per conèixer totes les funcions?';
        const startBtn = this.commonData?.buttons?.startTutorial || 'Començar Tutorial';
        const skipBtn = this.commonData?.navigation?.skip || 'Saltar';
        
        const modal = document.createElement('div');
        modal.id = 'tutorial-welcome-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        `;
        
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        `;
        
        content.innerHTML = `
            <h2 style="margin: 0 0 16px 0; font-size: 24px; color: #1565C0;">
                ${this.tutorialData?.title || 'Tutorial'}
            </h2>
            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #374151;">
                ${welcomeMsg}
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button id="tutorial-welcome-skip" style="
                    background: #E5E7EB;
                    color: #374151;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                ">${skipBtn}</button>
                <button id="tutorial-welcome-start" style="
                    background: #10B981;
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                ">${startBtn}</button>
            </div>
        `;
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Event listeners
        document.getElementById('tutorial-welcome-start').addEventListener('click', () => {
            modal.remove();
            this.start();
        });
        
        document.getElementById('tutorial-welcome-skip').addEventListener('click', () => {
            modal.remove();
            this.markAsCompleted();
        });
    }
};

/**
 * Add CSS animations
 */
const tutorialStyles = document.createElement('style');
tutorialStyles.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .tutorial-highlight {
        position: relative;
        z-index: 9999 !important;
        box-shadow: 0 0 0 4px #10B981, 0 0 0 8px rgba(16, 185, 129, 0.3) !important;
        border-radius: 8px !important;
        transition: box-shadow 0.3s ease;
    }
    
    .tutorial-tooltip {
        animation: fadeIn 0.3s ease;
    }
`;
document.head.appendChild(tutorialStyles);

/**
 * Auto-initialize tutorial based on current page
 */
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;
    
    // Dashboard
    if (path.includes('gestio.html')) {
        Tutorial.init('dashboard');
    }
    
    // Vehicle location
    else if (path.includes('localitzar-vehicle.html')) {
        // Wait for map to load
        setTimeout(() => {
            Tutorial.init('vehicleLocation');
        }, 2000);
    }
    
    // Vehicle control
    else if (path.includes('administrar-vehicle.html')) {
        Tutorial.init('vehicleControl');
    }
    
    // Purchase time
    else if (path.includes('purchase-time.html')) {
        Tutorial.init('purchaseTime');
    }
    
    // Vehicle search
    else if (path.includes('search.php')) {
        Tutorial.init('vehicleSearch');
    }
    
    // Booking
    else if (path.includes('booking.php')) {
        Tutorial.init('booking');
    }
    
    // Profile
    else if (path.includes('perfil.html') || path.includes('profile')) {
        Tutorial.init('profile');
    }
    
    // Registration
    else if (path.includes('registre.html') || path.includes('register')) {
        Tutorial.init('registration');
    }
    
    // Login
    else if (path.includes('login.html') || path.includes('inici-sessio')) {
        Tutorial.init('login');
    }
    
    // Accessibility
    else if (path.includes('accessibility') || path.includes('accessibilitat')) {
        Tutorial.init('accessibility');
    }
});

/**
 * Add tutorial restart button to help menu
 */
function addTutorialRestartButton() {
    // Check if help button exists
    const helpButton = document.querySelector('[data-tutorial-restart]');
    if (helpButton) {
        helpButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (Tutorial.tutorialType) {
                Tutorial.restart();
            }
        });
    }
}

// Initialize restart button when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addTutorialRestartButton);
} else {
    addTutorialRestartButton();
}

// Export for use in other scripts
window.Tutorial = Tutorial;

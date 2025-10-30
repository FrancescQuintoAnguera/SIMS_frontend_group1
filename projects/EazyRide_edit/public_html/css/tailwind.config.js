/* global tailwind */
/**
 * Tailwind CSS Configuration for VoltiaCar Application
 * Custom theme with brand colors and accessibility settings
 */

tailwind.config = {
    theme: {
        extend: {
            colors: {
                // Primary brand colors
                primary: {
                    50: '#E3F2FD',
                    100: '#BBDEFB',
                    200: '#90CAF9',
                    300: '#64B5F6',
                    400: '#42A5F5',
                    500: '#1565C0', // Main primary color
                    600: '#1E88E5',
                    700: '#1976D2',
                    800: '#1565C0',
                    900: '#0D47A1',
                },
                secondary: {
                    50: '#ECFDF5',
                    100: '#D1FAE5',
                    200: '#A7F3D0',
                    300: '#6EE7B7',
                    400: '#34D399',
                    500: '#10B981', // Main secondary color
                    600: '#059669',
                    700: '#047857',
                    800: '#065F46',
                    900: '#064E3B',
                },
                // Custom grays
                gray: {
                    50: '#F9FAFB',
                    100: '#F3F4F6',
                    200: '#E5E7EB',
                    300: '#D1D5DB',
                    400: '#9CA3AF',
                    500: '#6B7280',
                    600: '#4B5563',
                    700: '#374151',
                    800: '#1F2937',
                    900: '#111827',
                },
            },
            fontFamily: {
                sans: [
                    '-apple-system',
                    'BlinkMacSystemFont',
                    '"Segoe UI"',
                    'Roboto',
                    '"Helvetica Neue"',
                    'Arial',
                    'sans-serif',
                ],
            },
            fontSize: {
                // Accessibility-friendly font sizes (minimum 18px base)
                'xs': ['0.875rem', { lineHeight: '1.5' }],      // 15.75px
                'sm': ['1rem', { lineHeight: '1.5' }],          // 18px
                'base': ['1.125rem', { lineHeight: '1.6' }],    // 20.25px
                'lg': ['1.25rem', { lineHeight: '1.6' }],       // 22.5px
                'xl': ['1.5rem', { lineHeight: '1.5' }],        // 27px
                '2xl': ['1.75rem', { lineHeight: '1.4' }],      // 31.5px
                '3xl': ['2rem', { lineHeight: '1.3' }],         // 36px
                '4xl': ['2.5rem', { lineHeight: '1.2' }],       // 45px
                '5xl': ['3rem', { lineHeight: '1.1' }],         // 54px
                '6xl': ['3.5rem', { lineHeight: '1' }],         // 63px
            },
            spacing: {
                // Additional spacing options
                '18': '4.5rem',
                '88': '22rem',
                '100': '25rem',
                '112': '28rem',
                '128': '32rem',
            },
            borderRadius: {
                // Custom border radius
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            boxShadow: {
                // Custom shadows
                'inner-lg': 'inset 0 4px 8px 0 rgba(0, 0, 0, 0.1)',
                'outline-primary': '0 0 0 3px rgba(21, 101, 192, 0.5)',
                'outline-secondary': '0 0 0 3px rgba(16, 185, 129, 0.5)',
            },
            minHeight: {
                // Minimum touch target sizes
                'touch': '44px',
                'touch-lg': '60px',
            },
            minWidth: {
                // Minimum touch target sizes
                'touch': '44px',
                'touch-lg': '60px',
            },
            maxWidth: {
                // Custom max widths
                '8xl': '88rem',
                '9xl': '96rem',
            },
            zIndex: {
                // Custom z-index values
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },
            animation: {
                // Custom animations
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'slide-left': 'slideLeft 0.3s ease-out',
                'slide-right': 'slideRight 0.3s ease-out',
                'bounce-slow': 'bounce 2s infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { 
                        opacity: '0',
                        transform: 'translateY(20px)',
                    },
                    '100%': { 
                        opacity: '1',
                        transform: 'translateY(0)',
                    },
                },
                slideDown: {
                    '0%': { 
                        opacity: '0',
                        transform: 'translateY(-20px)',
                    },
                    '100%': { 
                        opacity: '1',
                        transform: 'translateY(0)',
                    },
                },
                slideLeft: {
                    '0%': { 
                        opacity: '0',
                        transform: 'translateX(20px)',
                    },
                    '100%': { 
                        opacity: '1',
                        transform: 'translateX(0)',
                    },
                },
                slideRight: {
                    '0%': { 
                        opacity: '0',
                        transform: 'translateX(-20px)',
                    },
                    '100%': { 
                        opacity: '1',
                        transform: 'translateX(0)',
                    },
                },
            },
            transitionDuration: {
                '400': '400ms',
                '600': '600ms',
                '800': '800ms',
                '900': '900ms',
            },
            screens: {
                // Custom breakpoints
                'xs': '475px',
                '3xl': '1920px',
            },
        },
    },
    plugins: [],
    // Safelist important classes that might be added dynamically
    safelist: [
        'bg-primary-500',
        'bg-secondary-500',
        'text-primary-500',
        'text-secondary-500',
        'border-primary-500',
        'border-secondary-500',
        'ring-primary-500',
        'ring-secondary-500',
        'animate-fade-in',
        'animate-slide-up',
        'animate-slide-down',
        'animate-pulse',
        'animate-bounce',
    ],
};

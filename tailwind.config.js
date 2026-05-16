import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bonus: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                gold: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                    800: '#92400e',
                    900: '#78350f',
                },
                surface: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    150: '#eef2f6',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                }
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.2s ease-in',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'bounce-slow': 'bounce 2s infinite',
                'float': 'float 6s ease-in-out infinite',
                'float-slow': 'float 8s ease-in-out infinite',
                'float-delayed': 'float 6s ease-in-out 3s infinite',
                'float-reverse': 'floatReverse 7s ease-in-out infinite',
                'breathing': 'breathing 3s ease-in-out infinite',
                'drift': 'drift 20s ease-in-out infinite',
                'drift-reverse': 'drift 25s ease-in-out 5s infinite reverse',
                'live-in': 'liveIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'live-out': 'liveOut 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'glow-pulse': 'glowPulse 2s ease-in-out infinite',
                'tilt-in': 'tiltIn 0.6s ease-out',
                'counter-up': 'counterUp 2s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                    '50%': { transform: 'translateY(-18px) rotate(3deg)' },
                },
                floatReverse: {
                    '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                    '50%': { transform: 'translateY(18px) rotate(-3deg)' },
                },
                breathing: {
                    '0%, 100%': { transform: 'scale(1)', opacity: '0.5' },
                    '50%': { transform: 'scale(1.08)', opacity: '0.8' },
                },
                drift: {
                    '0%': { transform: 'translate(0, 0)' },
                    '25%': { transform: 'translate(30px, -20px)' },
                    '50%': { transform: 'translate(-20px, 10px)' },
                    '75%': { transform: 'translate(15px, -15px)' },
                    '100%': { transform: 'translate(0, 0)' },
                },
                liveIn: {
                    '0%': { transform: 'translateX(120%) translateY(-50%)', opacity: '0' },
                    '100%': { transform: 'translateX(0) translateY(-50%)', opacity: '1' },
                },
                liveOut: {
                    '0%': { transform: 'translateX(0) translateY(-50%)', opacity: '1' },
                    '100%': { transform: 'translateX(120%) translateY(-50%)', opacity: '0' },
                },
                glowPulse: {
                    '0%, 100%': { boxShadow: '0 0 20px rgba(99, 102, 241, 0.15)' },
                    '50%': { boxShadow: '0 0 40px rgba(99, 102, 241, 0.3)' },
                },
                tiltIn: {
                    '0%': { transform: 'perspective(1000px) rotateY(-5deg) rotateX(5deg)', opacity: '0' },
                    '100%': { transform: 'perspective(1000px) rotateY(0deg) rotateX(0deg)', opacity: '1' },
                },
                counterUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [],
};

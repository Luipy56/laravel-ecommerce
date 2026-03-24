import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
        './storage/framework/views/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                serralleria: {
                    primary: '#fb5412',
                    'primary-content': '#fff',
                    secondary: '#882200',
                    'secondary-content': '#fff',
                    accent: '#fb5412',
                    'accent-content': '#fff',
                    neutral: 'oklch(45% 0.04 240)',
                    'neutral-content': 'oklch(98% 0.01 240)',
                    'base-100': 'oklch(99% 0.005 240)',
                    'base-200': 'oklch(96% 0.01 240)',
                    'base-300': 'oklch(92% 0.02 240)',
                    'base-content': 'oklch(22% 0.05 240)',
                    info: 'oklch(70% 0.2 220)',
                    'info-content': 'oklch(98% 0.01 220)',
                    success: 'oklch(65% 0.25 140)',
                    'success-content': 'oklch(98% 0.01 140)',
                    warning: 'oklch(80% 0.25 80)',
                    'warning-content': 'oklch(20% 0.05 80)',
                    error: 'oklch(65% 0.3 30)',
                    'error-content': 'oklch(98% 0.01 30)',
                    '--rounded-box': '0.5rem',
                    '--rounded-btn': '0.25rem',
                    '--rounded-badge': '0.25rem',
                    '--border': '1px',
                },
            },
        ],
    },
};

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Sora', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    violet: '#8B5CF6',
                    magenta: '#D946EF',
                },
                ink: {
                    DEFAULT: '#14151C',
                    surface: '#1D1E27',
                    line: '#2B2C38',
                    muted: '#9A9AB0',
                },
                paper: {
                    DEFAULT: '#F3F1FA',
                    surface: '#FFFFFF',
                    line: '#E7E4F2',
                    muted: '#6B6B7B',
                },
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #8B5CF6 0%, #D946EF 100%)',
            },
        },
    },

    plugins: [forms],
};

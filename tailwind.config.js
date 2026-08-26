import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                'apple-blue': '#0066cc',
                'apple-blue-focus': '#0071e3',
                'apple-blue-dark': '#2997ff',
                'apple-ink': '#1d1d1f',
                'apple-canvas': '#ffffff',
                'apple-parchment': '#f5f5f7',
                'apple-pearl': '#fafafc',
                'apple-tile-1': '#272729',
                'apple-tile-2': '#2a2a2c',
                'apple-tile-3': '#252527',
                'apple-gray-muted': '#cccccc',
                'apple-gray-muted-48': '#7a7a7a',
            },
            fontFamily: {
                sans: ['SF Pro Text', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                display: ['SF Pro Display', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            letterSpacing: {
                tightest: '-0.022em',
                'apple-tight': '-0.011em',
            },
            borderRadius: {
                'apple-sm': '8px',
                'apple-md': '11px',
                'apple-lg': '18px',
            },
            spacing: {
                'apple-md': '17px',
                'apple-section': '80px',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};

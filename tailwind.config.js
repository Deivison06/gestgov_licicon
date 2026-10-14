import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'sit-bg': '#0a1514',
                'sit-card': '#10201e',
                'sit-card-2': '#0d1b19',
                'sit-line': '#24403c',
                'sit-line-2': '#1a302d',
                'sit-ink': '#e6f0ee',
                'sit-ink-2': '#a8bdb9',
                'sit-ink-3': '#8aa29d',
                'sit-teal': '#5eead4',
                'sit-mint': '#2DC197',
                'sit-mint-hover': '#4bd2aa',
                'sit-on-mint': '#06302d',
                'sit-side-1': '#0b3b37',
                'sit-side-2': '#072926',
                'sit-side-3': '#051e1c',
            },
        },
    },

    plugins: [forms],
};

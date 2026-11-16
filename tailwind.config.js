import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                care: {
                    cream: '#F9F4EA',
                    pink: '#FF9ABC',
                    sage: '#78956A',
                    lime: '#D1EB8F',
                    olive: '#46571A',
                },
                brand: {
                    50: '#F9F4EA',
                    100: '#D1EB8F',
                    200: '#b8d48c',
                    300: '#9ab778',
                    400: '#78956A',
                    500: '#78956A',
                    600: '#5e7a52',
                    700: '#46571A',
                    800: '#46571A',
                    900: '#46571A',
                    950: '#354312',
                },
                navy: {
                    800: '#46571A',
                    900: '#3b4a16',
                    950: '#2d3910',
                }
            }
        },
    },

    plugins: [forms],
};

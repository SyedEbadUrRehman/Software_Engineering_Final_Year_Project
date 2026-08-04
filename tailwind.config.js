const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                handwriting: ['Caveat', 'cursive'],
                headingfont:['system-ui', 'Segoe UI', 'Roboto', 'sans-serif'],
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};

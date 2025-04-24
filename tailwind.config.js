// import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  theme: {
    extend: {
      //   fontFamily: {
      //     sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      //   },
      fontFamily: {
        sans: ['"Roboto"'],
        poppins: ['"Roboto"'],
        h1: ['"Roboto"'],
        display: ['"Roboto"'],
        body: ['"Roboto"'],
      },
      colors: {
        transparent: 'transparent',
        current: 'currentColor',
        black: '#000000',
        white: '#ffffff',
        primary: '#B13D27',
        primaryTr: '#B13D2722',
        bgprimary: '#B13D2726',
        greenlight: '#ddfff0',
        secondary: '#DE9A3C',
        whitegray: '#F8F8F826',
        gray: '#6E6B7B',
      },
    },
    screens: {
      sm: '440px',
      md: '768px',
      lg: '1280px',
    },
  },

  plugins: [forms, typography],
};

/** @type {import('tailwindcss').Config} */
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import flowbite from 'flowbite/plugin';
import preline from 'preline/plugin';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
    "./node_modules/flowbite/**/*.js",
    "./node_modules/preline/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: 'var(--main-color, #3b82f6)',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        jakarta: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
        instrument: ['"Instrument Serif"', 'Georgia', 'serif'],
      },
      keyframes: {
        'auth-fade-up': {
          from: { opacity: '0', transform: 'translateY(20px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        'auth-slide-in': {
          from: { opacity: '0', transform: 'translateX(-20px)' },
          to: { opacity: '1', transform: 'translateX(0)' },
        },
      },
      animation: {
        'auth-fade-up': 'auth-fade-up 0.5s ease forwards',
        'auth-slide-in': 'auth-slide-in 0.55s ease forwards',
      },
    },
  },
  plugins: [
    forms,
    typography,
    flowbite,
    preline,
  ],
  corePlugins: {
    preflight: true,
  },
}


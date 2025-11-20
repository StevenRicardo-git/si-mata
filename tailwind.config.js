/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./public/**/*.html",
    "./public/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2b398b',
      },
      fontFamily: {
        inter: ['Inter', 'sans-serif'],
      },
    },
  },
  safelist: [
    'bg-primary',
    'text-primary',
    'hover:bg-primary',
    'hover:text-primary',
    'animate-spin',
    'hidden',
    'block',
  ],
  plugins: [],
}

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './dashboards/**/*.php',
    './includes/**/*.php',
    './assets/js/**/*.js',
  ],
  safelist: [
    'bg-blue-500', 'bg-green-500', 'bg-yellow-500', // e.g., for dynamic icon colors
  ],
  theme: {
    extend: {
      colors: {
        'primary-wa': '#0D3B66',
        'secondary-wa': '#F4D35E',
        'accent-wa': '#EE964B',
      },
      fontFamily: {
        sans: ['Lato', 'sans-serif'],
        heading: ['Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

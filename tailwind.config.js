/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './dashboards/**/*.php',
    './includes/**/*.php',
    './assets/js/**/*.js',
  ],
  safelist: [
    'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500',
    'text-blue-500', 'text-green-500', 'text-yellow-500', 'text-red-500', 'text-purple-500',
    'hover:bg-blue-600', 'hover:bg-green-600', 'hover:bg-yellow-600', 'hover:bg-red-600', 'hover:bg-purple-600',
    'dark:bg-blue-900', 'dark:bg-green-900', 'dark:bg-yellow-900', 'dark:text-gray-100', 'dark:text-gray-300',
    'dark:border-gray-700'
  ],
  theme: {
    extend: {
      colors: {
        'primary-wa': '#0D47A1', // Updated to match the custom.css blue
        'secondary-wa': '#1976D2', 
        'accent-wa': '#42A5F5',
        'dark-bg': '#162037',
        'card-bg': '#1E2745',
      },
      fontFamily: {
        sans: ['Ubuntu', 'sans-serif'],
        michroma: ['Michroma', 'sans-serif'],
      },
    },
  },
  plugins: [],
  darkMode: 'class', // Enable dark mode based on class
}

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,php,js}", // Includes all HTML, PHP, and JS in src directory
    "./**/*.{html,php,js}", // Includes all HTML, PHP, and JS files in the project root and subdirectories
    "!./node_modules/**/*" // Excludes everything in node_modules
  ],
  
  theme: {
    extend: {},
  },
  variants: {
    display: ['responsive'],
  },
  plugins: [],
}

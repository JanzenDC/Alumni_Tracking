/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,php,js}",
    "!./node_modules/**/*",
    "./**/*.{html,php,js}"
  ],
  
  theme: {
    extend: {},
  },
  plugins: [],
}

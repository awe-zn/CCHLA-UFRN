/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js}"],
  theme: {
    screens: {
      'sm': '578px',
      // => @media (min-width: 578px) { ... }

      'lg': '768px',
      // => @media (min-width: 768px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1440px',
      // => @media (min-width: 1440px) { ... }
    },

    container: {
      padding: {
        DEFAULT: '1rem',
        sm: '1rem',
        lg: '2rem',
        xl: '9.125rem',
        '2xl': '9.125rem',
      },

    extend: {},
  },
  plugins: [],
}
}

//npx tailwindcss -i ./assets/style.css -o ./assets/output.css --watch
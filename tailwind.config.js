/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js}"],
  theme: {
    screens: {
      'sm': '640px',
      // => @media (min-width: 578px) { ... }

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '992px',
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
        md: '1rem',
        lg: '6.75rem',
        xl: '6.75rem',
        '2xl': '6.75rem',
      },

    extend: {},
  },
  plugins: [],
}
}

//npx tailwindcss -i ./assets/style.css -o ./assets/output.css --watch
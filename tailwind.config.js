/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./src/**/*.{php,html,js}",
    "./public/**/*.{php,html,js}",
    "./admin/**/*.{php,html,js}",
    "./template/**/*.{php,html,js}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Paleta basada en el logo de Technical del Perú
        brand: {
          // Azul marino oscuro (montañas/triángulos del logo)
          navy: {
            50:  '#eef1f8',
            100: '#d9dfed',
            200: '#b3bfdb',
            300: '#8d9fc9',
            400: '#6780b7',
            500: '#4a6299',
            600: '#3a4d7a',
            700: '#2D3250', // ← Color principal del logo
            800: '#232741',
            900: '#1a1d31',
            950: '#0f1120',
          },
          // Azul celeste (escudo geométrico del logo)
          blue: {
            50:  '#eef7ff',
            100: '#d9edff',
            200: '#bbdfff',
            300: '#8cccff',
            400: '#55b0ff',
            500: '#3B9FE7', // ← Color principal del logo
            600: '#1a7fd4',
            700: '#1567ab',
            800: '#17558d',
            900: '#194974',
            950: '#132e49',
          },
        },
      },
    },
  },
  plugins: [],
}

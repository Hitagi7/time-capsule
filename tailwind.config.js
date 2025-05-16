/** @type {import('tailwindcss').Config} */
export default {
  mode: 'jit',
  content: ['./index.html', './src/**/*.{js,vue,ts,jsx,tsx}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
        spaceMono: ['Space Mono', 'monospace'],
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [
    function ({ addUtilities }) {
      addUtilities({
        '.animated': {
          '@apply hover:cursor-pointer hover:opacity-80 hover:scale-105 transition-all duration-300 ease-in-out':
            {},
        },
      })
    },
  ],
}

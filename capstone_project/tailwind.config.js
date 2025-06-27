// tailwind.config.js
import daisyui from 'daisyui'

export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.ts',
    './resources/**/*.vue',
    './node_modules/flowbite/**/*.js',
    './node_modules/flowbite-vue/**/*.{js,ts,vue}',
    './node_modules/daisyui/dist/**/*.js',
  ],
  theme: {
    screens: {
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      '2xl': '1536px',
    },
      extend: {
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [
    daisyui,
  ],

}

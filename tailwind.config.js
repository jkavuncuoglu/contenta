module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.{vue,js,ts,jsx,tsx}',
    './resources/views/**/*.blade.php',
  ],
  theme: {
    borderColor: {
      DEFAULT: '#e5e7eb', // sets default border color
      border: '#e5e7eb', // enables border-border utility
    },
    extend: {
      colors: {
        foreground: '#1a202c',
        background: '#fff',
        // Logo-derived semantic colors
        // Primary (logo blue #265bfd)
        primary: {
          DEFAULT: '#265bfd',
          50: '#e9f0ff',
          100: '#d6e6ff',
          200: '#accfff',
          300: '#84b6ff',
          400: '#5b9cff',
          500: '#265bfd', // same as DEFAULT
          600: '#1f48d6',
          700: '#17369f',
          800: '#102468',
          900: '#071234',
        },
        'primary-foreground': '#ffffff',

        // Secondary (logo purple #3a256f)
        secondary: {
          DEFAULT: '#3a256f',
          500: '#3a256f',
          600: '#312052',
          700: '#27183a',
          800: '#1d1026',
        },
        'secondary-foreground': '#ffffff',

        // Accent / tertiary (logo cyan #0090f4)
        accent: '#0090f4',
        'accent-foreground': '#ffffff',

        // existing utilities
        accent2: '#0090f4',
        muted: '#f3f4f6',
        border: '#e5e7eb',
        input: '#f9fafb',
      },
    },
  },
  plugins: [],
};

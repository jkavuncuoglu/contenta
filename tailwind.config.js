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
        primary: '#2563eb',
        'primary-foreground': '#fff',
        accent: '#e0e7ff',
        muted: '#f3f4f6',
        border: '#e5e7eb',
        input: '#f9fafb',
      },
    },
  },
  plugins: [],
};

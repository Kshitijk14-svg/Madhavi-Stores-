/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
  ],
  safelist: [
    // Dynamically assigned in JS — must not be purged
    { pattern: /^(bg|text|border)-(emerald|red|gray|green)-(50|100|200|300|400|500|600|700|800|900)/ },
    { pattern: /^(opacity|translate|scale)-/ },
    'translate-y-4', 'translate-y-[-10px]', 'opacity-0', 'opacity-100',
    'bg-primary/95', 'text-secondary', 'border-secondary/20',
    'bg-red-950/95', 'text-red-200', 'border-red-500/20',
    'bg-white/95', 'border-primary/10',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#181818',
        secondary: '#b8986e',
        accent: '#d44d44',
        silk: '#f0ebe3',
        muted: '#888888',
        border: '#e5e5e5',
        background: '#faf8f5',
        surface: '#ffffff',
      },
      fontFamily: {
        display: ['"Cormorant Garamond"', 'serif'],
        sans: ['Manrope', 'sans-serif'],
      },
      letterSpacing: {
        luxury: '0.25em',
        widest: '0.35em',
        ultra: '0.5em',
      },
      transitionDuration: {
        '2000': '2000ms',
        '3000': '3000ms',
      },
    },
  },
}

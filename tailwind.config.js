export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: {
    extend: {
      colors: {
        primary: '#181818',
        secondary: '#b8986e',
        accent: '#5c3d2e',
        background: '#faf8f5',
        silk: '#f0ebe3',
        muted: '#888077',
        surface: '#ffffff',
        gold: '#c5a84f',
      },
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        body: ['"Outfit"', 'sans-serif'],
      },
      letterSpacing: {
        luxury: '0.25em',
        widest: '0.35em',
        ultra: '0.5em',
      },
      transitionDuration: {
        '2000': '2000ms',
        '3000': '3000ms',
      }
    },
  },
}

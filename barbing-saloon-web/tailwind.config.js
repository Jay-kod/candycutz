export default {
  content: ['./index.html', './src/**/*.{js,ts,vue}'],
  theme: {
    extend: {
      colors: {
        theme: {
          bg: 'var(--color-bg)',
          surface: 'var(--color-surface)',
          text: 'var(--color-text)',
          muted: 'var(--color-text-muted)',
          border: 'var(--color-border)',
        },
        obsidian: '#0D0D0D',
        charcoal: '#1A1A1A',
        steel: '#2A2A2A',
        graphite: '#3D3D3D',
        gold: {
          DEFAULT: '#FF9900',
          light: '#FFB84D',
          dark: '#CC7A00',
        },
        admin: {
          DEFAULT: '#FF6700',
          light: '#FFA666',
          dark: '#CC5200',
        },
        'admin-bg': '#070D0D',
        ivory: '#FAFAF8',
        cream: '#F0EDE6',
        success: '#16A34A',
        warning: '#D97706',
        danger: '#DC2626',
        neutral: '#6B7280',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        display: ['Playfair Display', 'serif'],
      },
    },
  },
  plugins: [],
};
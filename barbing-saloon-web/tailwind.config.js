export default {
  content: ['./index.html', './src/**/*.{js,ts,vue}'],
  theme: {
    extend: {
      keyframes: {
        'blink-red-border': {
          '0%, 100%': {
            borderColor: 'rgba(239, 68, 68, 0.9)',
            boxShadow: '0 0 18px rgba(239, 68, 68, 0.4), 0 0 6px rgba(239, 68, 68, 0.2), inset 0 0 12px rgba(239, 68, 68, 0.06)',
          },
          '50%': {
            borderColor: 'rgba(239, 68, 68, 0.15)',
            boxShadow: '0 0 4px rgba(239, 68, 68, 0.08), 0 0 2px rgba(239, 68, 68, 0.04), inset 0 0 4px rgba(239, 68, 68, 0)',
          },
        },
      },
      animation: {
        'blink-red-border': 'blink-red-border 1.5s ease-in-out infinite',
      },
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
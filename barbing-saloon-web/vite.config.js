import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  server: {
    allowedHosts: true,
    watch: {
      usePolling: true,
      interval: 200,
    },
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
      '/uploads': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
  plugins: [
    vue(),
  ],
  build: {
    // ── Chunk splitting for better caching ──
    // ── Minification ──
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,  // Remove console.log in production
        drop_debugger: true,
      },
    },
    // ── CSS optimization ──
    cssMinify: true,
    // ── Target modern browsers for smaller output ──
    target: 'es2020',
    // ── Increase chunk warning limit ──
    chunkSizeWarningLimit: 600,
  },
});
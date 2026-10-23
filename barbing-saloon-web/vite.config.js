import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const backendTarget = env.BACKEND_URL || process.env.BACKEND_URL || 'http://127.0.0.1:8000';

  return {
    server: {
      allowedHosts: true,
      watch: {
        usePolling: true,
        interval: 200,
      },
      proxy: {
        '/api': {
          target: backendTarget,
          changeOrigin: true,
          secure: false,
        },
        '/uploads': {
          target: backendTarget,
          changeOrigin: true,
          secure: false,
        },
        '/storage': {
          target: backendTarget,
          changeOrigin: true,
          secure: false,
        },
      },
    },
    plugins: [
      vue(),
    ],
    resolve: {
      alias: {
        '@': '/src',
      },
    },
    esbuild: {
      drop: ['console', 'debugger'],
    },
    build: {
      cssMinify: true,
      target: 'es2020',
      chunkSizeWarningLimit: 600,
    },
  };
});

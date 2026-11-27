import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const backendTarget = process.env.BACKEND_URL || env.BACKEND_URL || 'http://127.0.0.1:8000';

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
    build: {
      cssMinify: true,
      target: 'es2020',
      chunkSizeWarningLimit: 1000,
      rolldownOptions: {
        output: {
          minify: {
            compress: {
              dropConsole: true,
              dropDebugger: true,
            },
            mangle: true,
            codegen: true,
          },
          codeSplitting: {
            groups: [
              {
                name: 'vendor-vue',
                test: /[\\/]node_modules[\\/](?:vue|@vue|vue-router|pinia)[\\/]/,
              },
              {
                name: 'vendor-icons',
                test: /[\\/]node_modules[\\/](?:@heroicons|lucide-vue-next)[\\/]/,
              },
              {
                name: 'vendor-utils',
                test: /[\\/]node_modules[\\/](?:axios|dayjs|dompurify|yup|vee-validate|clsx|tailwind-merge)[\\/]/,
              },
              {
                name: 'vendor-pdf',
                test: /[\\/]node_modules[\\/](?:html2pdf\.js|jspdf|html2canvas)[\\/]/,
              },
              {
                name: 'vendor-maps',
                test: /[\\/]node_modules[\\/]leaflet[\\/]/,
              },
            ],
          },
        },
      },
    },
  };
});

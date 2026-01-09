import path from "path"
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  server: {
    allowedHosts: ['.ngrok-free.app'], // Allow Ngrok
    proxy: {
      '/api': {
        target: 'https://batasanaya.test', // Valet uses HTTPS
        changeOrigin: true,
        secure: false, // Allow self-signed certs
      },
      '/uploads': {
        target: 'https://batasanaya.test',
        changeOrigin: true,
        secure: false,
      }
    }
  }
})

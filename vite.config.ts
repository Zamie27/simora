import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            autoImport: true,
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Supaya bisa diakses dari luar container
        port: 5174,      // Port Vite disamakan dengan host di docker-compose
        strictPort: true, // WAJIB agar Vite tidak pindah port jika 5174 sibuk
        hmr: {
            host: 'localhost', // Browser kamu akan mencari HMR di localhost laptop
            port: 5174,      // Port HMR juga disamakan
        },
        watch: {
            usePolling: true, // WAJIB untuk Docker agar perubahan file langsung terdeteksi
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
    },
});

import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import vuetify from 'vite-plugin-vuetify';

import { VitePWA } from 'vite-plugin-pwa';

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
        VitePWA({
            outDir: 'public',
            buildBase: '/',
            scope: '/',
            registerType: 'autoUpdate',
            manifest: {
                name: 'SIMORA',
                short_name: 'SIMORA',
                description: 'Sistem Informasi Monitoring Atlet Sepeda',
                theme_color: '#f97316',
                background_color: '#ffffff',
                display: 'standalone',
                icons: [
                    {
                        src: '/images/simora_icon.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/images/simora_icon.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
            workbox: {
                navigateFallback: null,
                globDirectory: 'public/build',
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2,ttf,eot}'],
            },
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

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
            base: '/',
            scope: '/',
            outDir: 'public',
            registerType: 'autoUpdate',
            injectRegister: false, // We manually register in app.ts
            includeAssets: [
                'favicon.ico',
                'apple-touch-icon-180x180.png',
                'images/simora_icon.png',
                'images/pwa-192x192.png',
                'images/pwa-512x512.png',
                'images/pwa-maskable-192x192.png',
                'images/pwa-maskable-512x512.png',
                'robots.txt',
            ],
            manifest: false,
            workbox: {
                navigateFallback: null,
                globDirectory: 'public',
                globPatterns: [
                    'build/assets/**/*.{js,css,woff,woff2,ttf,eot}',
                    'images/**/*.png',
                ],
                modifyURLPrefix: {
                    'build/': '/build/',
                },
                runtimeCaching: [
                    {
                        // Cache page navigations (HTML) with Network First strategy
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
                            },
                            networkTimeoutSeconds: 3,
                        },
                    },
                    {
                        // Cache API requests with Network First
                        urlPattern: ({ url }) => url.pathname.startsWith('/api/'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 24 * 60 * 60, // 24 hours
                            },
                            networkTimeoutSeconds: 5,
                        },
                    },
                    {
                        // Cache images with Cache First
                        urlPattern: ({ request }) => request.destination === 'image',
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'images-cache',
                            expiration: {
                                maxEntries: 60,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
                            },
                        },
                    },
                    {
                        // Cache fonts with Cache First
                        urlPattern: ({ request }) => request.destination === 'font',
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'fonts-cache',
                            expiration: {
                                maxEntries: 20,
                                maxAgeSeconds: 365 * 24 * 60 * 60, // 1 year
                            },
                        },
                    },
                    {
                        // Cache Google Fonts stylesheets
                        urlPattern: /^https:\/\/fonts\.bunny\.net\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 365 * 24 * 60 * 60, // 1 year
                            },
                        },
                    },
                ],
            },
            devOptions: {
                enabled: true,
                type: 'module',
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

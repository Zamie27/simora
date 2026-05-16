import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { registerSW } from 'virtual:pwa-register';
import type { DefineComponent } from 'vue';
import { createApp, h, reactive } from 'vue';
import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import BugReportBubble from '@/components/BugReportBubble.vue';
import { initializeTheme } from '@/composables/useAppearance';
import vuetify from '@/plugins/vuetify';

// PWA Service Worker Registration with auto-update
const updateSW = registerSW({
    immediate: true,
    onRegisteredSW(swUrl, registration) {
        if (registration) {
            // Check for updates every hour
            setInterval(async () => {
                if (!(!registration.installing && navigator)) return;
                if ('connection' in navigator && !navigator.onLine) return;

                const resp = await fetch(swUrl, {
                    cache: 'no-store',
                    headers: { 'cache': 'no-store', 'cache-control': 'no-cache' },
                });

                if (resp?.status === 200) {
                    await registration.update();
                }
            }, 60 * 60 * 1000); // 1 hour
        }
    },
    onNeedRefresh() {
        // Auto-update when new content is available
        updateSW(true);
    },
    onOfflineReady() {
        console.log('PWA: App ready to work offline');
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // PWA Installation Logic
        let deferredPrompt: any = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            app.config.globalProperties.$deferredPrompt = e;
            console.log('PWA: beforeinstallprompt event fired');
        });

        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
            app.config.globalProperties.$deferredPrompt = null;
            console.log('PWA: Application installed successfully');
        });

        app.use(plugin)
            .use(vuetify)
            .mount(el);

        // Mount BugReportBubble globally so it appears on ALL pages
        const bugBubbleContainer = document.createElement('div');
        bugBubbleContainer.id = 'bug-report-bubble';
        document.body.appendChild(bugBubbleContainer);
        createApp(BugReportBubble).use(vuetify).mount(bugBubbleContainer);
    },
    progress: {
        color: '#FF6120',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

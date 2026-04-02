import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import BugReportBubble from '@/components/BugReportBubble.vue';
import { initializeTheme } from '@/composables/useAppearance';
import vuetify from '@/plugins/vuetify';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
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

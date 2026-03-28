import { createVuetify } from 'vuetify';

// Note: Component styles are handled by vite-plugin-vuetify (tree-shaked, scoped).
// Do NOT import 'vuetify/styles' here - it would inject global resets that break Tailwind.
// The styles config in vite.config.ts points to resources/css/vuetify.scss for overrides.

export default createVuetify({
    theme: {
        defaultTheme: 'simoraTheme',
        themes: {
            simoraTheme: {
                dark: true,
                colors: {
                    background: '#0F1414',
                    surface: '#171c1c',
                    primary: '#FF6120',
                    secondary: '#102844',
                    accent: '#FF6120',
                    error: '#7f1d1d',
                    success: '#4ade80',
                    warning: '#fb8c00',
                },
            },
        },
    },
    defaults: {
        VSelect: {
            variant: 'solo-filled',
            flat: true,
            density: 'comfortable',
            bgColor: '#171c1c',
            color: 'primary',
            hideDetails: true,
        },
        VDatePicker: {
            showAdjacentMonths: true,
            color: 'primary',
        },
    },
});

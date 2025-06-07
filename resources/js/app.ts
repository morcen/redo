import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Global error handler for DOM-related errors
        app.config.errorHandler = (err, instance, info) => {
            console.error('Vue error:', err);
            console.error('Component instance:', instance);
            console.error('Error info:', info);

            // Handle specific parentNode errors
            if (err instanceof TypeError && err.message.includes('parentNode')) {
                console.warn('Caught parentNode error, attempting to recover...');
                // Don't re-throw, just log and continue
                return;
            }

            // Re-throw other errors
            throw err;
        };

        app.use(plugin).use(ZiggyVue).mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Global window error handler for unhandled DOM errors
window.addEventListener('error', (event) => {
    if (event.error instanceof TypeError && event.error.message.includes('parentNode')) {
        console.warn('Caught unhandled parentNode error:', event.error);
        event.preventDefault(); // Prevent the error from being logged to console
        return false;
    }
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason instanceof TypeError && event.reason.message.includes('parentNode')) {
        console.warn('Caught unhandled parentNode promise rejection:', event.reason);
        event.preventDefault(); // Prevent the error from being logged to console
        return false;
    }
});

// This will set light / dark mode on page load...
initializeTheme();

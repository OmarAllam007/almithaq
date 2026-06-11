import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';
import { createPinia } from 'pinia';

import AppLayout from '@/pages/layout/AppLayout.vue';
import LandingLayout from '@/pages/layout/LandingLayout.vue';
import { createInertiaApp, Link, router } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { configureEcho } from '@laravel/echo-vue';
import AdminLayout from '@/pages/layout/AdminLayout.vue';

configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name: string): DefineComponent | Promise<DefineComponent> | { default: DefineComponent } => {
        const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue', { eager: true });
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page ${name} not found`);
        }

        console.log(name);
        if (name.includes('Admin')) {
            page.default.layout = AdminLayout;
        } else if (name.includes('Landing')) {
            page.default.layout = LandingLayout;
        } else if (name.includes('Login') || name.includes('Register')
            || name.includes('ForgotPassword') || name.includes('Auth/ResetPassword')
            || name.includes('Verification') || name.includes('Auth/Signup')) {
            page.default.layout = null;
        } else {
            page.default.layout = AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .component('ILink', Link)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Reinitialize Metronic KT components after every Inertia navigation
// (SPA navigations don't reload the page, so KTMenu/KTDrawer etc. must be re-run)
router.on('navigate', () => {
    setTimeout(() => {
        if (typeof (window as any).KTComponents !== 'undefined') {
            (window as any).KTComponents.init();
        } else {
            // Fallback: reinit individual components if KTComponents isn't available
            const kt = window as any;
            kt.KTMenu?.init?.();
            kt.KTDrawer?.init?.();
            kt.KTScroll?.init?.();
            kt.KTScrolltop?.init?.();
            kt.KTSticky?.init?.();
            kt.KTToggle?.init?.();
        }
    }, 50);
});

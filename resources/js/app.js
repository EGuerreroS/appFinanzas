import './bootstrap';
import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/dist/quasar.css';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { Quasar, Notify, Dialog } from 'quasar';
import quasarLang from 'quasar/lang/es';

createInertiaApp({
    title: (title) => (title ? `${title} · Finanzas` : 'Finanzas'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(Quasar, {
                plugins: { Notify, Dialog },
                lang: quasarLang,
                config: { notify: { position: 'top-right', timeout: 3500 } },
            })
            .mount(el);
    },
    progress: { color: '#26A69A' },
});

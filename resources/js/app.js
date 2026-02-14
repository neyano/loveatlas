import { createApp } from 'vue';
import { createPinia } from 'pinia';
import axios from 'axios';

import '../css/app.css';

// Axios defaults
axios.defaults.baseURL = '/api/v1';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

const pinia = createPinia();

// Register global components from components directory
const components = import.meta.glob('./components/**/*.vue', { eager: true });
const componentMap = {};
for (const path in components) {
    const name = path.split('/').pop().replace('.vue', '');
    componentMap[name] = components[path].default;
}

// Auto-mount Vue components on pages that have #app
const appEl = document.getElementById('app');
if (appEl) {
    const app = createApp({});

    // Do NOT mount Vue to #app: the main content is server-rendered by Blade.
    // Mounting an empty root would replace that content and make it disappear.
    // Vue components (QuoteCard, LikeButton, etc.) are used only where we explicitly
    // mount a small app (e.g. SearchBar in header, or future island mounts).

    // Mount SearchBar in header
    const searchRoot = document.getElementById('search-bar-root');
    if (searchRoot && componentMap['SearchBar']) {
        const searchApp = createApp({});
        searchApp.component('SearchBar', componentMap['SearchBar']);
        searchApp.mount(searchRoot);
    }

    // Mount VisitForm on the visit create page (Blade は #visit-form-app の外にコンテンツを出さないので、この中だけ Vue で描画)
    const visitFormRoot = document.getElementById('visit-form-app');
    if (visitFormRoot && componentMap['VisitForm']) {
        const locationId = Number(visitFormRoot.dataset.locationId || 0);
        const visitApp = createApp({
            components: { VisitForm: componentMap['VisitForm'] },
            template: '<visit-form :location-id="locationId" />',
            data: () => ({ locationId }),
        });
        visitApp.mount(visitFormRoot);
    }

    // HeaderUser を #header-auth にマウント (layouts/app.blade.php のヘッダー用)
    const headerAuthEl = document.getElementById('header-auth');
    if (headerAuthEl) {
        const headerApp = createApp({});

        headerApp.use(pinia);

        // HeaderUser コンポーネントのみ登録
        const components = import.meta.glob('./components/HeaderUser.vue', { eager: true });
        for (const path in components) {
            headerApp.component('HeaderUser', components[path].default);
        }

        headerApp.mount('#header-auth');
    }


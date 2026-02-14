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

// Auto-mount Vue components on pages that have #app
const appEl = document.getElementById('app');
if (appEl) {
    const app = createApp({});
    const pinia = createPinia();

    app.use(pinia);

    // Register global components
    const components = import.meta.glob('./components/**/*.vue', { eager: true });
    for (const path in components) {
        const name = path.split('/').pop().replace('.vue', '');
        app.component(name, components[path].default);
    }

    app.mount('#app');
}

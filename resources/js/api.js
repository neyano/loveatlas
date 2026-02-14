import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
});

// Fetch CSRF cookie before authenticated requests
export async function initCsrf() {
    await axios.get('/sanctum/csrf-cookie');
}

export default api;

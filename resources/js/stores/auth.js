import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api, { initCsrf } from '@/api';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const loading = ref(false);

    const isLoggedIn = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isModerator = computed(() => ['admin', 'moderator'].includes(user.value?.role));

    async function fetchUser() {
        try {
            const { data } = await api.get('/auth/me');
            user.value = data;
        } catch {
            user.value = null;
        }
    }

    async function login(credentials) {
        loading.value = true;
        try {
            await initCsrf();
            await api.post('/auth/login', credentials);
            await fetchUser();
        } finally {
            loading.value = false;
        }
    }

    async function register(data) {
        loading.value = true;
        try {
            await initCsrf();
            await api.post('/auth/register', data);
            await fetchUser();
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        await api.post('/auth/logout');
        user.value = null;
    }

    return { user, loading, isLoggedIn, isAdmin, isModerator, fetchUser, login, register, logout };
});

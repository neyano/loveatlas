import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api';

export const useMapStore = defineStore('map', () => {
    const quotes = ref([]);
    const loading = ref(false);
    const filters = ref({
        type: null,     // work type filter
        tag: null,      // tag filter
    });

    async function fetchQuotesByBounds(bounds) {
        loading.value = true;
        try {
            const { data } = await api.get('/map/quotes', {
                params: {
                    north: bounds.north,
                    south: bounds.south,
                    east: bounds.east,
                    west: bounds.west,
                    type: filters.value.type,
                    tag: filters.value.tag,
                },
            });
            quotes.value = data.data;
        } finally {
            loading.value = false;
        }
    }

    function setFilter(key, value) {
        filters.value[key] = value;
    }

    function clearFilters() {
        filters.value = { type: null, tag: null };
    }

    return { quotes, loading, filters, fetchQuotesByBounds, setFilter, clearFilters };
});

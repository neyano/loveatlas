import { ref } from 'vue';

export function useDebounce(fn, delay = 300) {
    const timer = ref(null);

    function debounced(...args) {
        if (timer.value) clearTimeout(timer.value);
        timer.value = setTimeout(() => fn(...args), delay);
    }

    function cancel() {
        if (timer.value) {
            clearTimeout(timer.value);
            timer.value = null;
        }
    }

    return { debounced, cancel };
}

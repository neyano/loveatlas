<template>
  <button
    type="button"
    class="favorite-button"
    :class="{ 'favorite-button--favorited': isFavorited, 'favorite-button--loading': loading }"
    :disabled="loading"
    :title="isFavorited ? 'お気に入りから削除' : 'お気に入りに追加'"
    @click="toggleFavorite"
  >
    <svg
      class="favorite-button__icon"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path
        v-if="isFavorited"
        d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"
        fill="currentColor"
        stroke="none"
      />
      <path v-else d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
    </svg>
  </button>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  quoteId: {
    type: Number,
    required: true,
  },
  initialFavorited: {
    type: Boolean,
    default: false,
  },
  initialFavoriteId: {
    type: Number,
    default: null,
  },
});

const loading = ref(false);
const isFavorited = ref(props.initialFavorited);
const favoriteId = ref(props.initialFavoriteId);

const toggleFavorite = async () => {
  try {
    loading.value = true;
    if (isFavorited.value && favoriteId.value) {
      await axios.delete(`/favorites/${favoriteId.value}`);
      isFavorited.value = false;
      favoriteId.value = null;
    } else {
      const { data } = await axios.post('/favorites', {
        quote_id: props.quoteId,
      });
      isFavorited.value = true;
      favoriteId.value = data.id;
    }
  } catch (error) {
    if (error.response?.status === 401) {
      window.location.href = '/login';
      return;
    }
    console.error('Favorite toggle failed:', error);
  } finally {
    loading.value = false;
  }
};

defineExpose({
  updateState: (favorited, id) => {
    isFavorited.value = favorited;
    favoriteId.value = id;
  },
});
</script>

<style scoped>
.favorite-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  padding: 0;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: white;
  color: var(--color-text);
  cursor: pointer;
  transition: all var(--transition);
}

.favorite-button:hover:not(:disabled) {
  border-color: var(--color-accent);
  color: var(--color-accent);
  background: var(--color-bg-secondary);
}

.favorite-button--favorited {
  border-color: var(--color-accent);
  background: var(--color-accent);
  color: white;
}

.favorite-button--favorited:hover:not(:disabled) {
  background: var(--color-accent-dark);
  border-color: var(--color-accent-dark);
  color: white;
}

.favorite-button--loading {
  opacity: 0.7;
  cursor: not-allowed;
}

.favorite-button__icon {
  width: 22px;
  height: 22px;
}
</style>

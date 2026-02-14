<template>
  <button
    type="button"
    class="like-button"
    :class="{ 'like-button--liked': isLiked, 'like-button--loading': loading }"
    :disabled="loading"
    @click="toggleLike"
  >
    <svg
      class="like-button__icon"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path v-if="isLiked" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="currentColor" stroke="none" />
      <path v-else d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
    </svg>
    <span class="like-button__count">{{ likesCount }}</span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  quoteId: {
    type: Number,
    required: true,
  },
  initialLiked: {
    type: Boolean,
    default: false,
  },
  initialCount: {
    type: Number,
    default: 0,
  },
});

const loading = ref(false);
const isLiked = ref(props.initialLiked);
const likesCount = ref(props.initialCount);

const toggleLike = async () => {
  try {
    loading.value = true;
    const { data } = await axios.post(`/quotes/${props.quoteId}/vote`);
    isLiked.value = data.liked;
    likesCount.value = data.likes_count;
  } catch (error) {
    if (error.response?.status === 401) {
      window.location.href = '/login';
      return;
    }
    console.error('Like toggle failed:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.like-button {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  font-size: var(--font-size-sm);
  font-weight: 500;
  border-radius: var(--border-radius);
  border: 1px solid var(--color-border);
  background: white;
  color: var(--color-text);
  cursor: pointer;
  transition: all var(--transition);
  min-height: 44px;
}

.like-button:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-bg-secondary);
}

.like-button--liked {
  border-color: var(--color-primary);
  background: var(--color-primary-light);
  color: var(--color-primary);
}

.like-button--liked:hover:not(:disabled) {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.like-button--loading {
  opacity: 0.7;
  cursor: not-allowed;
}

.like-button__icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.like-button__count {
  min-width: 1.5rem;
  text-align: left;
}
</style>

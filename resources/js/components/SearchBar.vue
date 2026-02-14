<template>
  <form class="search-bar" @submit.prevent="handleSearch">
    <input
      v-model="searchQuery"
      type="search"
      class="search-bar__input"
      placeholder="セリフ・作品・場所を検索..."
      aria-label="検索"
    />
    <button type="submit" class="search-bar__btn" aria-label="検索する">
      <svg
        class="search-bar__icon"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.35-4.35" />
      </svg>
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue';

const searchQuery = ref('');

const handleSearch = () => {
  const query = searchQuery.value?.trim() || '';
  if (query) {
    window.location.href = `/search?q=${encodeURIComponent(query)}`;
  }
};
</script>

<style scoped>
.search-bar {
  display: flex;
  align-items: center;
  max-width: 500px;
  width: 100%;
  background: white;
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius);
  overflow: hidden;
  transition: border-color var(--transition);
}

.search-bar:focus-within {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary-light);
}

.search-bar__input {
  flex: 1;
  padding: var(--space-2) var(--space-3);
  font-size: var(--font-size-sm);
  border: none;
  background: transparent;
  outline: none;
}

.search-bar__input::placeholder {
  color: var(--color-text-muted);
}

.search-bar__btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  padding: 0;
  border: none;
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all var(--transition);
}

.search-bar__btn:hover {
  background: var(--color-bg-tertiary);
  color: var(--color-primary);
}

.search-bar__icon {
  width: 20px;
  height: 20px;
}

@media (max-width: 768px) {
  .search-bar {
    max-width: 100%;
  }
}
</style>

<template>
  <article class="quote-card">
    <a :href="quoteLink" class="quote-card__link">
      <p class="quote-card__text">{{ displayText }}</p>
      <p v-if="quote.character_name" class="quote-card__character">
        — {{ quote.character_name }}
      </p>
      <div class="quote-card__meta">
        <span v-if="quote.work" class="quote-card__work">{{ quote.work.title }}</span>
        <span v-if="quote.location" class="quote-card__location">{{ quote.location.name }}</span>
      </div>
    </a>
    <div class="quote-card__actions">
      <LikeButton
        v-if="quote.id"
        :quote-id="quote.id"
        :initial-liked="quote.is_liked || false"
        :initial-count="quote.likes_count || 0"
      />
      <FavoriteButton
        v-if="quote.id"
        :quote-id="quote.id"
        :initial-favorited="quote.is_favorited || false"
        :initial-favorite-id="quote.favorite_id || null"
      />
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import LikeButton from './LikeButton.vue';
import FavoriteButton from './FavoriteButton.vue';

const props = defineProps({
  quote: {
    type: Object,
    required: true,
  },
});

const quoteLink = computed(() => {
  return props.quote?.id ? `/quotes/${props.quote.id}` : '#';
});

const displayText = computed(() => {
  return props.quote?.quote_text || props.quote?.text || '';
});
</script>

<style scoped>
.quote-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: var(--border-radius);
  padding: var(--space-4);
  box-shadow: var(--shadow-sm);
  transition: box-shadow var(--transition);
}

.quote-card:hover {
  box-shadow: var(--shadow);
}

.quote-card__link {
  display: block;
  text-decoration: none;
  color: inherit;
  margin-bottom: var(--space-3);
}

.quote-card__text {
  font-size: var(--font-size-base);
  line-height: 1.6;
  margin-bottom: var(--space-2);
}

.quote-card__character {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  font-style: italic;
  margin-bottom: var(--space-2);
}

.quote-card__meta {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
  display: flex;
  gap: var(--space-2);
  flex-wrap: wrap;
}

.quote-card__work,
.quote-card__location {
  display: inline;
}

.quote-card__actions {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding-top: var(--space-2);
  border-top: 1px solid var(--color-border-light);
}
</style>

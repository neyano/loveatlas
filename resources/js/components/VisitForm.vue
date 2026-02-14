<template>
  <form class="visit-form" @submit.prevent="submitVisit">
    <div class="visit-form__group form-group">
      <label class="form-label" for="visited_at">訪問日</label>
      <input
        id="visited_at"
        v-model="form.visited_at"
        type="date"
        class="form-input visit-form__input"
        required
        :max="maxDate"
      />
      <p v-if="errors.visited_at" class="form-error">{{ errors.visited_at }}</p>
    </div>

    <div class="visit-form__group form-group">
      <label class="form-label">評価</label>
      <div class="visit-form__stars">
        <button
          v-for="n in 5"
          :key="n"
          type="button"
          class="visit-form__star"
          :class="{ 'visit-form__star--active': n <= form.rating }"
          :aria-label="`${n}つ星`"
          @click="form.rating = n"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
          </svg>
        </button>
      </div>
    </div>

    <div class="visit-form__group form-group">
      <label class="form-label" for="notes">メモ</label>
      <textarea
        id="notes"
        v-model="form.notes"
        class="form-input visit-form__textarea"
        rows="4"
        maxlength="1000"
        placeholder="訪問の思い出を記録..."
      />
      <p class="visit-form__char-count">{{ form.notes?.length || 0 }} / 1000</p>
      <p v-if="errors.notes" class="form-error">{{ errors.notes }}</p>
    </div>

    <div class="visit-form__group form-group">
      <label class="form-label" for="photo">写真</label>
      <input
        id="photo"
        type="file"
        accept="image/*"
        class="visit-form__file"
        @change="onFileChange"
      />
      <p v-if="form.photoPreview" class="visit-form__preview">
        選択済み: {{ form.photoName }}
      </p>
      <p v-if="errors.photo" class="form-error">{{ errors.photo }}</p>
    </div>

    <div class="visit-form__actions">
      <button
        type="button"
        class="btn btn--secondary"
        @click="handleCancel"
      >
        キャンセル
      </button>
      <button
        type="submit"
        class="btn btn--primary"
        :disabled="submitting"
      >
        {{ submitting ? '保存中...' : '保存' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  locationId: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['success', 'cancel']);

const form = ref({
  visited_at: '',
  rating: null,
  notes: '',
  photo: null,
  photoName: null,
  photoPreview: null,
});

const errors = ref({});
const submitting = ref(false);

const maxDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const handleCancel = () => {
  emit('cancel');
  window.history.back();
};

const onFileChange = (e) => {
  const file = e.target.files?.[0];
  if (file) {
    form.value.photo = file;
    form.value.photoName = file.name;
    form.value.photoPreview = URL.createObjectURL(file);
  }
};

const submitVisit = async () => {
  errors.value = {};
  submitting.value = true;

  try {
    const formData = new FormData();
    formData.append('location_id', props.locationId);
    formData.append('visited_at', form.value.visited_at);
    if (form.value.rating) formData.append('rating', form.value.rating);
    if (form.value.notes) formData.append('notes', form.value.notes);
    if (form.value.photo) formData.append('photo', form.value.photo);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    await axios.post('/visits', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-XSRF-TOKEN': csrfToken || '',
      },
    });

    emit('success');
    window.location.href = '/';
  } catch (error) {
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const raw = error.response.data.errors;
      errors.value = Object.fromEntries(
        Object.entries(raw).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      );
    } else if (error.response?.status === 401) {
      window.location.href = '/login';
      return;
    } else {
      errors.value = { form: '保存に失敗しました。もう一度お試しください。' };
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  form.value.visited_at = new Date().toISOString().split('T')[0];
});
</script>

<style scoped>
.visit-form {
  max-width: 500px;
}

.visit-form__input,
.visit-form__textarea {
  width: 100%;
}

.visit-form__stars {
  display: flex;
  gap: var(--space-1);
}

.visit-form__star {
  width: 44px;
  height: 44px;
  padding: 0;
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius-sm);
  background: white;
  color: var(--color-text-muted);
  cursor: pointer;
  transition: all var(--transition);
}

.visit-form__star svg {
  width: 24px;
  height: 24px;
}

.visit-form__star:hover {
  border-color: var(--color-accent);
  color: var(--color-accent);
}

.visit-form__star--active {
  border-color: var(--color-accent);
  background: var(--color-accent);
  color: white;
}

.visit-form__char-count {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
  margin-top: var(--space-1);
}

.visit-form__file {
  width: 100%;
  padding: var(--space-2);
  font-size: var(--font-size-sm);
}

.visit-form__preview {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  margin-top: var(--space-1);
}

.visit-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
  margin-top: var(--space-6);
}
</style>

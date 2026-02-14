<template>
  <form @submit.prevent="handleSubmit" class="auth-form__body">
    <div v-if="successMessage" class="auth-form__success">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="auth-form__error">
      {{ errorMessage }}
    </div>

    <template v-if="!successMessage">
      <div class="form-group">
        <label for="email" class="form-label">メールアドレス</label>
        <input
          type="email"
          id="email"
          v-model="form.email"
          class="form-input"
          required
          autofocus
          placeholder="mail@example.com"
        >
        <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
      </div>

      <button type="submit" class="btn btn--primary auth-form__submit" :disabled="loading">
        {{ loading ? '送信中...' : 'リセットリンクを送信' }}
      </button>
    </template>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api, { initCsrf } from '@/api';

const form = reactive({ email: '' });
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const errors = ref({});

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  errors.value = {};

  try {
    await initCsrf();
    await api.post('/auth/forgot-password', { email: form.email });
    successMessage.value = 'パスワードリセット用のリンクをメールで送信しました。メールをご確認ください。';
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || '送信に失敗しました';
    } else if (e.response?.status === 429) {
      errorMessage.value = '送信回数の上限に達しました。しばらくしてから再度お試しください。';
    } else {
      errorMessage.value = '送信に失敗しました';
    }
  } finally {
    loading.value = false;
  }
}
</script>

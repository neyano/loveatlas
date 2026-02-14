<template>
  <form @submit.prevent="handleLogin" class="auth-form__body">
    <div v-if="errorMessage" class="auth-form__error">
      {{ errorMessage }}
    </div>

    <div class="form-group">
      <label for="login" class="form-label">メールアドレス</label>
      <input
        type="email"
        id="login"
        v-model="form.login"
        class="form-input"
        required
        autofocus
        placeholder="mail@example.com"
      >
      <p v-if="errors.login" class="form-error">{{ errors.login[0] }}</p>
    </div>

    <div class="form-group">
      <label for="password" class="form-label">パスワード</label>
      <input
        type="password"
        id="password"
        v-model="form.password"
        class="form-input"
        required
        placeholder="8文字以上"
      >
      <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
    </div>

    <div class="auth-form__options">
      <label class="auth-form__remember">
        <input type="checkbox" v-model="form.remember">
        <span>ログイン状態を保持</span>
      </label>
      <a href="/forgot-password" class="auth-form__forgot">パスワードを忘れた方</a>
    </div>

    <button type="submit" class="btn btn--primary auth-form__submit" :disabled="loading">
      {{ loading ? 'ログイン中...' : 'ログイン' }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api, { initCsrf } from '@/api';

const form = reactive({
  login: '',
  password: '',
  remember: false,
});

const loading = ref(false);
const errorMessage = ref('');
const errors = ref({});

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    await initCsrf();
    await api.post('/auth/login', {
      login: form.login,
      password: form.password,
      remember: form.remember,
    });

    // ログイン成功 → ホームへ
    window.location.href = '/';
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || 'ログインに失敗しました';
    } else if (e.response?.status === 429) {
      errorMessage.value = 'ログイン試行回数の上限に達しました。しばらくしてから再度お試しください。';
    } else {
      errorMessage.value = 'ログインに失敗しました';
    }
  } finally {
    loading.value = false;
  }
}
</script>

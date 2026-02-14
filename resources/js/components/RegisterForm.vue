<template>
  <form @submit.prevent="handleRegister" class="auth-form__body">
    <div v-if="errorMessage" class="auth-form__error">
      {{ errorMessage }}
    </div>

    <div class="form-group">
      <label for="username" class="form-label">ユーザー名 <span class="form-required">*</span></label>
      <input
        type="text"
        id="username"
        v-model="form.username"
        class="form-input"
        required
        autofocus
        placeholder="英数字・アンダースコア (3-50文字)"
        minlength="3"
        maxlength="50"
        pattern="[a-zA-Z0-9_]+"
      >
      <p v-if="errors.username" class="form-error">{{ errors.username[0] }}</p>
    </div>

    <div class="form-group">
      <label for="display_name" class="form-label">表示名 <span class="form-required">*</span></label>
      <input
        type="text"
        id="display_name"
        v-model="form.display_name"
        class="form-input"
        required
        placeholder="例: 田中太郎"
        maxlength="100"
      >
      <p v-if="errors.display_name" class="form-error">{{ errors.display_name[0] }}</p>
    </div>

    <div class="form-group">
      <label for="email" class="form-label">メールアドレス <span class="form-required">*</span></label>
      <input
        type="email"
        id="email"
        v-model="form.email"
        class="form-input"
        required
        placeholder="mail@example.com"
      >
      <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
    </div>

    <div class="form-group">
      <label for="password" class="form-label">パスワード <span class="form-required">*</span></label>
      <input
        type="password"
        id="password"
        v-model="form.password"
        class="form-input"
        required
        placeholder="8文字以上 (英字+数字)"
        minlength="8"
      >
      <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
    </div>

    <div class="form-group">
      <label for="password_confirmation" class="form-label">パスワード確認 <span class="form-required">*</span></label>
      <input
        type="password"
        id="password_confirmation"
        v-model="form.password_confirmation"
        class="form-input"
        required
        placeholder="パスワードを再入力"
      >
    </div>

    <button type="submit" class="btn btn--primary auth-form__submit" :disabled="loading">
      {{ loading ? '登録中...' : 'アカウントを作成' }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api, { initCsrf } from '@/api';

const form = reactive({
  username: '',
  display_name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);
const errorMessage = ref('');
const errors = ref({});

async function handleRegister() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    await initCsrf();
    await api.post('/auth/register', form);

    // 登録成功 → ホームへ
    window.location.href = '/';
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      errorMessage.value = e.response.data.message || '入力内容に誤りがあります';
    } else {
      errorMessage.value = '登録に失敗しました。しばらくしてから再度お試しください。';
    }
  } finally {
    loading.value = false;
  }
}
</script>

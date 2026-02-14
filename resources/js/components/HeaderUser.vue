<template>
  <div class="header-user">
    <template v-if="auth.isLoggedIn">
      <button class="header-user__trigger" @click="toggleDropdown" ref="triggerRef">
        <span class="header-user__avatar">
          <img v-if="auth.user?.avatar_path" :src="'/storage/' + auth.user.avatar_path" :alt="auth.user.display_name">
          <span v-else class="header-user__avatar-text">
            {{ auth.user?.display_name?.charAt(0) || '?' }}
          </span>
        </span>
        <span class="header-user__name">{{ auth.user?.display_name }}</span>
      </button>

      <div v-if="dropdownOpen" class="header-user__dropdown" ref="dropdownRef">
        <a href="/profile" class="header-user__dropdown-item">
          プロフィール
        </a>
        <a href="/profile/settings" class="header-user__dropdown-item">
          設定
        </a>
        <hr class="header-user__dropdown-divider">
        <button @click="handleLogout" class="header-user__dropdown-item header-user__dropdown-item--danger">
          ログアウト
        </button>
      </div>
    </template>

    <template v-else>
      <a href="/login" class="btn btn--secondary btn--sm">ログイン</a>
      <a href="/register" class="btn btn--primary btn--sm">新規登録</a>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/api';

const auth = useAuthStore();
const dropdownOpen = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

// 初回ロード時にユーザー情報を取得
onMounted(() => {
  auth.fetchUser();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value;
}

function handleClickOutside(event) {
  if (
    dropdownOpen.value &&
    triggerRef.value &&
    !triggerRef.value.contains(event.target) &&
    dropdownRef.value &&
    !dropdownRef.value.contains(event.target)
  ) {
    dropdownOpen.value = false;
  }
}

async function handleLogout() {
  try {
    await api.post('/auth/logout');
    window.location.href = '/';
  } catch {
    window.location.href = '/';
  }
}
</script>

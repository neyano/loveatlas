<template>
  <div class="report-btn" v-if="isLoggedIn">
    <button class="btn btn--secondary report-btn__trigger" @click="showModal = true">
      通報
    </button>

    <ModalDialog :show="showModal" title="セリフを通報" @close="showModal = false">
      <form @submit.prevent="submitReport">
        <div class="form-group">
          <label class="form-label">理由</label>
          <select v-model="form.reason" class="form-input" required>
            <option value="">選択してください</option>
            <option value="spam">スパム</option>
            <option value="inappropriate">不適切な内容</option>
            <option value="wrong_info">誤った情報</option>
            <option value="copyright">著作権違反</option>
            <option value="other">その他</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">詳細 (任意)</label>
          <textarea v-model="form.description" class="form-input" rows="3" maxlength="1000"></textarea>
        </div>
        <button type="submit" class="btn btn--primary" :disabled="submitting || !form.reason">
          {{ submitting ? '送信中...' : '通報する' }}
        </button>
      </form>
    </ModalDialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const props = defineProps({
  quoteId: { type: Number, required: true },
})

const emit = defineEmits(['reported'])
const authStore = useAuthStore()
const isLoggedIn = computed(() => authStore.isLoggedIn)

const showModal = ref(false)
const submitting = ref(false)
const form = ref({ reason: '', description: '' })

async function submitReport() {
  submitting.value = true
  try {
    await api.post('/reports', {
      quote_id: props.quoteId,
      reason: form.value.reason,
      description: form.value.description,
    })
    showModal.value = false
    form.value = { reason: '', description: '' }
    emit('reported')
  } catch (e) {
    alert(e.response?.data?.message || '通報に失敗しました。')
  } finally {
    submitting.value = false
  }
}
</script>

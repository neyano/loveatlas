<template>
  <Teleport to="body">
    <TransitionGroup name="toast" tag="div" class="toast-container">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="['toast', `toast--${toast.type}`]"
        @click="remove(toast.id)"
      >
        <span class="toast__message">{{ toast.message }}</span>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

function add(message, type = 'info', duration = 3000) {
  const id = nextId++
  toasts.value.push({ id, message, type })
  if (duration > 0) {
    setTimeout(() => remove(id), duration)
  }
}

function remove(id) {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

defineExpose({ add, remove })
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 72px;
  right: 16px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
  pointer-events: none;
}
.toast {
  padding: 12px 20px;
  border-radius: var(--border-radius, 8px);
  font-size: 14px;
  color: white;
  cursor: pointer;
  pointer-events: auto;
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
  max-width: 360px;
}
.toast--info { background: #3498DB; }
.toast--success { background: #2ECC71; }
.toast--warning { background: #F39C12; }
.toast--error { background: #E74C3C; }

.toast-enter-active { transition: all 0.3s ease; }
.toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>

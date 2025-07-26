<template>
  <transition name="fade">
    <div
      v-if="visible"
      :class="[
        'fixed top-6 right-6 text-white px-4 py-2 rounded shadow-lg z-[9999]',
        type.value === 'success' ? 'bg-green-600' :
        type.value === 'error' ? 'bg-red-600' :
        type.value === 'info' ? 'bg-blue-600' :
        'bg-gray-600'
      ]"
    >
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue'
import { toastRef } from '@/Composables/useGlobalToast'

const visible = ref(false)
const message = ref('')
const type = ref('success')

function showToast(msg, duration = 3000, toastType = 'success') {
  message.value = msg
  type.value = toastType
  visible.value = true
  setTimeout(() => {
    visible.value = false
  }, duration)
}

// Registra a instância globalmente
toastRef.value = { showToast }

defineExpose({ showToast })
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>


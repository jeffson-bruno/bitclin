<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[9999]"
    >
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const message = ref('')

function showToast(msg, duration = 3000) {
  message.value = msg
  visible.value = true
  setTimeout(() => (visible.value = false), duration)
}

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

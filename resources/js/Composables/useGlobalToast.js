import { ref } from 'vue'

export const toastRef = ref(null)

export function registerGlobalToast() {
  window.$toast = {
    show: (msg, type = 'success') => {
      toastRef.value?.showToast(msg, 3000, type)
    }
  }
}

import { ref } from 'vue'

export const toastRef = ref(null)

export function registerGlobalToast() {
  window.$toast = {
    show(msg, type = 'success', duration = 3000) {
      if (toastRef.value && toastRef.value.showToast) {
        toastRef.value.showToast(msg, duration, type)
      } else {
        console.warn('Toast ref not registered.')
      }
    }
  }
}

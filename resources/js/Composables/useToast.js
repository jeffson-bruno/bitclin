export function useToast() {
  const success = (msg) => window.$toast?.show(msg, 'success')
  const error = (msg) => window.$toast?.show(msg, 'error')
  const info = (msg) => window.$toast?.show(msg, 'info')

  return { success, error, info }
}

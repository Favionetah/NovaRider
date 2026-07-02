import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', () => {
  const mensaje = ref('')
  const tipo = ref('success')
  const visible = ref(false)
  let timeoutId = null

  function show(msg, type = 'success') {
    if (timeoutId) clearTimeout(timeoutId)
    mensaje.value = msg
    tipo.value = type
    visible.value = true
    // El mensaje ahora se mantiene hasta que el usuario realice otra acción o lo cierre manualmente
  }

  function hide() {
    if (timeoutId) clearTimeout(timeoutId)
    visible.value = false
  }

  function success(msg) {
    show(msg, 'success')
  }

  function error(msg) {
    show(msg, 'error')
  }

  return { mensaje, tipo, visible, show, hide, success, error }
})

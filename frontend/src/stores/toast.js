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
    timeoutId = setTimeout(() => {
      visible.value = false
    }, 4000)
  }

  function hide() {
    if (timeoutId) clearTimeout(timeoutId)
    visible.value = false
  }

  return { mensaje, tipo, visible, show, hide }
})

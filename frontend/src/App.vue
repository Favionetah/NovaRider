<script setup>
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import ToastMessage from '@/components/ToastMessage.vue'

const auth = useAuthStore()
const toast = useToastStore()
const route = useRoute()

// Ocultar toast al cambiar de ruta
watch(() => route.path, () => {
  toast.hide()
})

// Ocultar toast al realizar cualquier clic (otra acción)
onMounted(() => {
  window.addEventListener('click', (e) => {
    // Si el toast está visible y el clic no fue dentro del toast mismo
    if (toast.visible) {
      // Ignorar clics en botones que disparan acciones (opcional, pero el usuario pidió "hasta que haga otra acción")
      // Si el clic es fuera de la zona del toast, lo cerramos
      if (!e.target.closest('.toast')) {
        toast.hide()
      }
    }
  }, { capture: true })
})
</script>

<template>
  <div>
    <AppHeader v-if="auth.isAuthenticated" />
    <router-view />
    <ToastMessage />
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #F5F4F0;
}
</style>

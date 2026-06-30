<script setup>
import { watch, ref, nextTick } from 'vue'
import { useToastStore } from '@/stores/toast'

const toast = useToastStore()
const toastRef = ref(null)

watch(() => toast.visible, async (val) => {
  await nextTick()
  if (val && toastRef.value) {
    gsap.fromTo(toastRef.value,
      { y: 30, opacity: 0, scale: 0.95 },
      { y: 0, opacity: 1, scale: 1, duration: 0.35, ease: 'power3.out' }
    )
  }
})
</script>

<template>
  <Teleport to="body">
    <div v-if="toast.visible" ref="toastRef" class="toast" :class="'toast-' + toast.tipo">
      <div class="toast-icon">
        <svg v-if="toast.tipo === 'success'" viewBox="0 0 24 24" fill="none" width="20" height="20">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none" width="20" height="20">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="toast-mensaje">{{ toast.mensaje }}</span>
      <button class="toast-cerrar" @click="toast.hide()">&times;</button>
    </div>
  </Teleport>
</template>

<style scoped>
.toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  font-family: 'Inter', sans-serif;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  max-width: 420px;
}

.toast-success {
  background: #F0FFF4;
  border-left: 4px solid #059669;
  color: #065F46;
}

.toast-error {
  background: #FFF5F5;
  border-left: 4px solid #741102;
  color: #741102;
}

.toast-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  color: #059669;
}

.toast-error .toast-icon {
  color: #741102;
}

.toast-mensaje {
  flex: 1;
  line-height: 1.4;
}

.toast-cerrar {
  background: none;
  border: none;
  font-size: 20px;
  color: inherit;
  opacity: 0.5;
  cursor: pointer;
  padding: 0 0 0 4px;
  line-height: 1;
  transition: opacity 0.2s;
}

.toast-cerrar:hover {
  opacity: 1;
}
</style>

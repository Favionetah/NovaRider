<script setup>
import { onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

onMounted(async () => {
  await nextTick()
  gsap.from('.denied-card', { y: 30, opacity: 0, duration: 0.5, ease: 'power3.out' })
})
</script>

<template>
  <div class="denied">
    <div class="denied-card">
      <svg viewBox="0 0 24 24" fill="none" class="denied-icon">
        <circle cx="12" cy="12" r="10" stroke="#741102" stroke-width="1.5" />
        <path d="M8 8l8 8M16 8l-8 8" stroke="#741102" stroke-width="1.5" stroke-linecap="round" />
      </svg>
      <h1 class="denied-title">Acceso Denegado</h1>
      <p class="denied-desc">No tienes permisos para acceder a esta sección.</p>
      <button class="denied-btn" @click="router.push('/')">Volver al inicio</button>
    </div>
  </div>
</template>

<style scoped>
.denied {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F5F4F0;
  padding: 32px;
}

.denied-card {
  background: #FFFFFF;
  border-radius: 16px;
  padding: 48px;
  text-align: center;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  max-width: 420px;
  width: 100%;
}

.denied-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 16px;
}

.denied-title {
  font-size: 24px;
  font-weight: 700;
  color: #042D29;
  margin-bottom: 8px;
}

.denied-desc {
  font-size: 15px;
  color: #929079;
  margin-bottom: 24px;
}

.denied-btn {
  padding: 10px 24px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.denied-btn:hover {
  background: #052E2A;
}
</style>
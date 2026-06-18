<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const password = ref('')
const mensajeError = ref('')
const enviando = ref(false)

const btnRef = ref(null)

onMounted(async () => {
  await nextTick()

  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })

  tl.from('.login-card', { y: 50, opacity: 0, duration: 0.6 })
    .from('.login-logo', { scale: 0.3, opacity: 0, duration: 0.5, ease: 'back.out(2)' }, '-=0.25')
    .from('.login-title', { y: 25, opacity: 0, duration: 0.4 }, '-=0.15')
    .from('.login-subtitle', { y: 25, opacity: 0, duration: 0.4 }, '-=0.15')
    .from('.campo', { y: 20, opacity: 0, duration: 0.35, stagger: 0.08 }, '-=0.1')
    .from('.btn-ingresar', { y: 20, opacity: 0, duration: 0.35 }, '-=0.1')
})

function botonEnter() {
  gsap.to(btnRef.value, { scale: 1.02, y: -2, duration: 0.2, ease: 'power2.out' })
}

function botonLeave() {
  gsap.to(btnRef.value, { scale: 1, y: 0, duration: 0.2, ease: 'power2.out' })
}

async function iniciarSesion() {
  mensajeError.value = ''
  enviando.value = true

  try {
    await auth.login(username.value, password.value)
    router.push('/')
  } catch (err) {
    mensajeError.value = err.message
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-bg">
      <div class="login-bg-pattern" />
    </div>

    <div class="login-container">
      <div class="login-card">
        <div class="login-card-accent" />

        <div class="login-header">
          <div class="login-logo">
            <svg class="login-logo-icon" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="22" stroke="#741102" stroke-width="2.5" fill="none" />
              <circle cx="24" cy="24" r="8" fill="#042D29" />
              <path d="M24 2v8M24 38v8M2 24h8M38 24h8" stroke="#929079" stroke-width="1.5" stroke-linecap="round" />
              <path d="M8 8l6 6M34 34l6 6M8 40l6-6M34 14l6-6" stroke="#929079" stroke-width="1.5" stroke-linecap="round" />
              <circle cx="24" cy="24" r="3" fill="#741102" />
            </svg>
          </div>
          <h1 class="login-title">NovaRider</h1>
          <p class="login-subtitle">Taller de Motocicletas</p>
        </div>

        <form @submit.prevent="iniciarSesion" class="login-form">
          <div class="campo">
            <label for="username">Usuario</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 20 20" fill="none">
                <circle cx="10" cy="6" r="3.5" stroke="#929079" stroke-width="1.5" />
                <path d="M2 18c0-4 3.6-7 8-7s8 3 8 7" stroke="#929079" stroke-width="1.5" stroke-linecap="round" />
              </svg>
              <input
                id="username"
                v-model="username"
                type="text"
                placeholder="Ingrese su usuario"
                required
                autocomplete="username"
              />
            </div>
          </div>

          <div class="campo">
            <label for="password">Contrase&ntilde;a</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 20 20" fill="none">
                <rect x="3" y="8" width="14" height="10" rx="2" stroke="#929079" stroke-width="1.5" />
                <path d="M6 8V5a4 4 0 118 0v3" stroke="#929079" stroke-width="1.5" stroke-linecap="round" />
                <circle cx="10" cy="14" r="1.5" fill="#741102" />
              </svg>
              <input
                id="password"
                v-model="password"
                type="password"
                placeholder="Ingrese su contrase&ntilde;a"
                required
                autocomplete="current-password"
              />
            </div>
          </div>

          <transition name="fade">
            <p v-if="mensajeError" class="mensaje-error">
              <svg class="error-icon" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="8" r="7" stroke="#741102" stroke-width="1.5" />
                <path d="M8 5v3M8 11h0" stroke="#741102" stroke-width="1.5" stroke-linecap="round" />
              </svg>
              {{ mensajeError }}
            </p>
          </transition>

          <button
            ref="btnRef"
            type="submit"
            class="btn-ingresar"
            :disabled="enviando"
            @mouseenter="botonEnter"
            @mouseleave="botonLeave"
          >
            <span v-if="enviando" class="spinner" />
            <span v-else class="btn-text">Ingresar</span>
            <span v-if="enviando" class="btn-text-cargando">Ingresando...</span>
          </button>
        </form>
      </div>
    </div>
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
}

.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background: #042D29;
  overflow: hidden;
}

.login-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.login-bg::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(ellipse at 30% 20%, #052E2A 0%, transparent 60%),
              radial-gradient(ellipse at 70% 80%, #0a3d38 0%, transparent 50%);
}

.login-bg-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.03;
  background-image:
    radial-gradient(circle at 1px 1px, #FFFFFF 1px, transparent 0);
  background-size: 40px 40px;
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 400px;
  padding: 20px;
}

.login-card {
  position: relative;
  background: #FFFFFF;
  border-radius: 16px;
  overflow: hidden;
  box-shadow:
    0 4px 24px rgba(0, 0, 0, 0.3),
    0 2px 8px rgba(0, 0, 0, 0.15);
}

.login-card-accent {
  height: 4px;
  background: linear-gradient(90deg, #042D29, #741102);
}

.login-header {
  padding: 40px 40px 0;
  text-align: center;
}

.login-logo {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.login-logo-icon {
  width: 56px;
  height: 56px;
}

.login-title {
  font-size: 28px;
  font-weight: 700;
  color: #042D29;
  letter-spacing: -0.5px;
  margin-bottom: 4px;
}

.login-subtitle {
  font-size: 14px;
  color: #929079;
  font-weight: 400;
}

.login-form {
  padding: 32px 40px 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.campo {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.campo label {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  letter-spacing: 0.3px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  width: 20px;
  height: 20px;
  pointer-events: none;
  flex-shrink: 0;
}

.campo input {
  width: 100%;
  padding: 12px 12px 12px 40px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: all 0.2s ease;
  background: #F9FAFB;
}

.campo input::placeholder {
  color: #929079;
  font-size: 14px;
}

.campo input:focus {
  border-color: #042D29;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.mensaje-error {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #741102;
  background: #FFF5F5;
  border-left: 3px solid #741102;
  padding: 10px 14px;
  border-radius: 8px;
  animation: errorSlide 0.3s ease-out;
}

@keyframes errorSlide {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.btn-ingresar {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  overflow: hidden;
  transition: background 0.2s ease, box-shadow 0.2s ease;
}

.btn-ingresar::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.08) 50%, transparent 60%);
  transition: transform 0.4s ease;
  transform: translateX(-100%);
}

.btn-ingresar:hover::before {
  transform: translateX(100%);
}

.btn-ingresar:hover {
  background: #052E2A;
  box-shadow: 0 4px 16px rgba(4, 45, 41, 0.35);
}

.btn-ingresar:active {
  background: #741102;
  box-shadow: 0 2px 8px rgba(116, 17, 2, 0.3);
}

.btn-ingresar:disabled {
  background: #929079;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-ingresar:disabled::before {
  display: none;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2.5px solid rgba(255, 255, 255, 0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.btn-text-cargando {
  font-size: 15px;
  font-weight: 500;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const mensajeError = ref('')
const enviando = ref(false)
const btnRef = ref(null)

let anims = []

onMounted(async () => {
  await nextTick()

  const entryTl = gsap.timeline({ defaults: { ease: 'power3.out' } })
  entryTl
    .from('.login-panel', { x: -60, opacity: 0, duration: 0.8 })
    .from('.login-logo', { scale: 0.5, opacity: 0, duration: 0.5, ease: 'back.out(1.7)' }, '-=0.4')
    .from('.field-group', { y: 20, opacity: 0, duration: 0.4, stagger: 0.1 }, '-=0.2')
    .from('.btn-ingresar', { y: 20, opacity: 0, duration: 0.4 }, '-=0.1')

  await nextTick()
  anims = [
    gsap.to('.ring-1', { rotation: 360, duration: 25, repeat: -1, ease: 'none', svgOrigin: '300 200' }),
    gsap.to('.ring-2', { rotation: -360, duration: 18, repeat: -1, ease: 'none', svgOrigin: '300 200' }),
    gsap.to('.ring-3', { rotation: 360, duration: 10, repeat: -1, ease: 'none', svgOrigin: '300 200' }),

    gsap.to('.rect-1', {
      x: 340, y: 180,
      duration: 14,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut',
    }),
    gsap.to('.rect-2', {
      x: -280, y: 180,
      duration: 12,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut',
    }),
    gsap.to('.rect-3', {
      x: 100, y: -200,
      duration: 16,
      repeat: -1,
      yoyo: true,
      ease: 'sine.inOut',
    }),

    gsap.fromTo('.sweep-line',
      { x: -200 },
      { x: 800, duration: 4, repeat: -1, ease: 'none' }
    ),

    gsap.to('.particle-1', { x: 80, y: -60, duration: 6, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
    gsap.to('.particle-2', { x: -60, y: 90, duration: 7, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
    gsap.to('.particle-3', { x: -40, y: -80, duration: 5, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
    gsap.to('.particle-4', { x: 70, y: 50, duration: 8, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
    gsap.to('.particle-5', { x: -90, y: -40, duration: 6.5, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
    gsap.to('.particle-6', { x: 50, y: -90, duration: 5.5, repeat: -1, yoyo: true, ease: 'sine.inOut' }),

    gsap.to('.login-logo-icon', { scale: 1.04, duration: 3, repeat: -1, yoyo: true, ease: 'sine.inOut' }),
  ]
})

onBeforeUnmount(() => {
  anims.forEach(a => a.kill())
})

function botonEnter() {
  gsap.to(btnRef.value, { scale: 1.02, duration: 0.2, ease: 'power2.out' })
}

function botonLeave() {
  gsap.to(btnRef.value, { scale: 1, duration: 0.2, ease: 'power2.out' })
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

function irSeguimiento() {
  router.push('/seguimiento')
}
</script>

<template>
  <div class="login-page">
    <div class="login-panel">
      <div class="login-card">
        <div class="login-logo">
          <svg class="login-logo-icon" viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="22" stroke="#042D29" stroke-width="1.5" fill="none" />
            <circle cx="24" cy="24" r="10" fill="#042D29" opacity="0.06" />
            <path d="M24 6v6M24 36v6M6 24h6M36 24h6" stroke="#929079" stroke-width="1" stroke-linecap="round" />
            <circle cx="24" cy="24" r="3" fill="#741102" />
          </svg>
        </div>
        <h1 class="login-title">NovaRider</h1>
        <p class="login-subtitle">Taller de Motocicletas</p>

        <form @submit.prevent="iniciarSesion" class="login-form">
          <p v-if="mensajeError" class="mensaje-error">{{ mensajeError }}</p>

          <div class="field-group">
            <label for="username">Usuario</label>
            <input
              id="username"
              v-model="username"
              type="text"
              placeholder="Ingrese su usuario"
              required
              autocomplete="username"
            />
          </div>

          <div class="field-group">
            <label for="password">Contrase&ntilde;a</label>
            <div class="password-wrapper">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Ingrese su contrase&ntilde;a"
                required
                autocomplete="current-password"
              />
              <button
                type="button"
                class="btn-toggle-password"
                @click="showPassword = !showPassword"
                :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              >
                <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" class="eye-icon">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" class="eye-icon">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
              </button>
            </div>
          </div>

          <button
            ref="btnRef"
            type="submit"
            class="btn-ingresar"
            :disabled="enviando"
            @mouseenter="botonEnter"
            @mouseleave="botonLeave"
          >
            <span>{{ enviando ? 'Ingresando...' : 'Ingresar' }}</span>
            <span v-if="!enviando" class="btn-arrow">&rarr;</span>
            <span v-if="enviando" class="spinner" />
          </button>
          <button type="button" class="btn-seguimiento" @click="irSeguimiento">
            Consultar reparacion
          </button>
        </form>
      </div>
    </div>

    <div class="scene-panel">
      <div class="scene-overlay" />

      <svg viewBox="0 0 600 400" class="scene-svg" preserveAspectRatio="xMidYMid slice">
        <circle class="ring-1" cx="300" cy="200" r="150" fill="none" stroke="rgba(116,17,2,0.08)" stroke-width="1" />
        <circle class="ring-1" cx="300" cy="200" r="130" fill="none" stroke="rgba(255,255,255,0.02)" stroke-width="0.5" />

        <circle class="ring-2" cx="300" cy="200" r="90" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.8" />
        <circle class="ring-2" cx="300" cy="200" r="80" fill="none" stroke="rgba(116,17,2,0.06)" stroke-width="0.5" />

        <circle class="ring-3" cx="300" cy="200" r="45" fill="none" stroke="rgba(116,17,2,0.10)" stroke-width="1.2" />
        <circle class="ring-3" cx="300" cy="200" r="35" fill="none" stroke="rgba(255,255,255,0.02)" stroke-width="0.5" />

        <circle cx="300" cy="200" r="4" fill="rgba(116,17,2,0.15)" />

        <g class="rect-1">
          <rect x="-30" y="-30" width="60" height="60" rx="3" fill="rgba(116,17,2,0.04)" stroke="rgba(116,17,2,0.12)" stroke-width="1" transform="rotate(18)" />
        </g>

        <g class="rect-2">
          <rect x="-20" y="-40" width="40" height="80" rx="3" fill="rgba(255,255,255,0.01)" stroke="rgba(255,255,255,0.05)" stroke-width="0.8" transform="rotate(-24)" />
        </g>

        <g class="rect-3">
          <rect x="-25" y="-15" width="50" height="30" rx="2" fill="rgba(116,17,2,0.03)" stroke="rgba(116,17,2,0.08)" stroke-width="0.8" transform="rotate(42)" />
        </g>

        <line class="sweep-line" x1="0" y1="340" x2="120" y2="340" stroke="rgba(255,255,255,0.05)" stroke-width="1" stroke-linecap="round" />

        <circle class="particle-1" cx="80" cy="120" r="1.5" fill="rgba(255,255,255,0.12)" />
        <circle class="particle-2" cx="480" cy="80" r="1" fill="rgba(255,255,255,0.10)" />
        <circle class="particle-3" cx="150" cy="320" r="1.5" fill="rgba(255,255,255,0.08)" />
        <circle class="particle-4" cx="520" cy="300" r="1" fill="rgba(255,255,255,0.12)" />
        <circle class="particle-5" cx="400" cy="140" r="1.5" fill="rgba(255,255,255,0.06)" />
        <circle class="particle-6" cx="200" cy="80" r="1" fill="rgba(255,255,255,0.10)" />
      </svg>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  background: #0D0D0D;
}

/* ── Left Panel ── */
.login-panel {
  flex: 0 0 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #FFFFFF;
  position: relative;
}

.login-panel::after {
  content: '';
  position: absolute;
  right: 0;
  top: 12%;
  height: 76%;
  width: 1px;
  background: linear-gradient(180deg, transparent, rgba(4, 45, 41, 0.15), transparent);
}

.login-card {
  width: 100%;
  max-width: 320px;
}

.login-logo {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.login-logo-icon {
  width: 44px;
  height: 44px;
}

.login-title {
  font-size: 26px;
  font-weight: 700;
  color: #042D29;
  letter-spacing: -0.5px;
  text-align: center;
  margin-bottom: 4px;
}

.login-subtitle {
  font-size: 14px;
  color: #929079;
  font-weight: 400;
  text-align: center;
  margin-bottom: 36px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-group label {
  font-size: 12px;
  font-weight: 600;
  color: #929079;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.field-group input {
  width: 100%;
  padding: 12px 0;
  border: none;
  border-bottom: 1.5px solid #E5E7EB;
  border-radius: 0;
  font-size: 15px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s ease;
  background: transparent;
}

.field-group input::placeholder {
  color: #D1D5DB;
  font-size: 15px;
}

.field-group input:focus {
  border-color: #042D29;
}

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrapper input {
  padding-right: 36px;
}

.btn-toggle-password {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: none;
  border: none;
  color: #929079;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s ease;
}

.btn-toggle-password:hover {
  color: #042D29;
}

.eye-icon {
  width: 18px;
  height: 18px;
}

.mensaje-error {
  font-size: 13px;
  color: #741102;
  padding: 0;
  margin: 0;
  animation: errorFade 0.3s ease-out;
}

@keyframes errorFade {
  from { opacity: 0; transform: translateY(-3px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-ingresar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  margin-top: 8px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 0;
  font-size: 14px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-ingresar:hover {
  background: #052E2A;
}

.btn-ingresar:active {
  background: #741102;
}

.btn-ingresar:disabled {
  background: #D1D5DB;
  color: #929079;
  cursor: not-allowed;
}

.btn-seguimiento {
  width: 100%;
  padding: 12px 14px;
  background: #FFFFFF;
  color: #042D29;
  border: 1px solid #D1D5DB;
  border-radius: 0;
  font-size: 14px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  transition: border-color 0.2s ease, color 0.2s ease;
}

.btn-seguimiento:hover {
  border-color: #042D29;
  color: #741102;
}

.btn-arrow {
  font-size: 18px;
  line-height: 1;
  transition: transform 0.2s ease;
}

.btn-ingresar:hover .btn-arrow {
  transform: translateX(3px);
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ── Right Panel ── */
.scene-panel {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0D0D0D 0%, #1A1A2E 50%, #0D0D0D 100%);
  background-size: 200% 200%;
  animation: breathe 10s ease-in-out infinite;
}

@keyframes breathe {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.scene-overlay {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 40% 30%, rgba(116, 17, 2, 0.06) 0%, transparent 60%),
    radial-gradient(ellipse at 70% 70%, rgba(116, 17, 2, 0.04) 0%, transparent 50%);
  pointer-events: none;
  z-index: 1;
}

.scene-svg {
  position: relative;
  z-index: 2;
  width: 100%;
  height: 100%;
  max-height: 100vh;
}

@media (max-width: 900px) {
  .login-page {
    flex-direction: column;
  }

  .login-panel {
    flex: none;
    min-height: 100vh;
  }

  .login-panel::after {
    right: auto;
    bottom: 0;
    top: auto;
    height: 1px;
    width: 76%;
    left: 12%;
    background: linear-gradient(90deg, transparent, rgba(4, 45, 41, 0.15), transparent);
  }

  .scene-panel {
    display: none;
  }
}
</style>

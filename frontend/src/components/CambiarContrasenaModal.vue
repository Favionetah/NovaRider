<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'

const emit = defineEmits(['close'])
const auth = useAuthStore()

const passwordActual = ref('')
const nuevaPassword = ref('')
const confirmarPassword = ref('')
const error = ref('')
const loading = ref(false)
const success = ref(false)

onMounted(async () => {
  await nextTick()
  gsap.from('.modal-overlay', { opacity: 0, duration: 0.2 })
  gsap.from('.modal-card', { y: 30, opacity: 0, duration: 0.3, ease: 'power3.out' })
})

function cerrar() {
  emit('close')
}

async function guardar() {
  error.value = ''
  success.value = false

  if (!passwordActual.value || !nuevaPassword.value || !confirmarPassword.value) {
    error.value = 'Todos los campos son obligatorios'
    return
  }

  if (nuevaPassword.value.length < 6) {
    error.value = 'La nueva contraseña debe tener al menos 6 caracteres'
    return
  }

  if (nuevaPassword.value !== confirmarPassword.value) {
    error.value = 'Las contraseñas nuevas no coinciden'
    return
  }

  loading.value = true
  try {
    await auth.cambiarContrasena(passwordActual.value, nuevaPassword.value)
    success.value = true
    passwordActual.value = ''
    nuevaPassword.value = ''
    confirmarPassword.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cambiar la contraseña'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="cerrar">
    <div class="modal-card">
      <div class="modal-header">
        <h2 class="modal-title">Cambiar Contrase&ntilde;a</h2>
        <button class="modal-close" @click="cerrar">&times;</button>
      </div>

      <form class="modal-body" @submit.prevent="guardar">
        <div v-if="error" class="form-error">{{ error }}</div>
        <div v-if="success" class="form-success">
          <svg viewBox="0 0 24 24" fill="none" class="success-icon" width="24" height="24">
            <circle cx="12" cy="12" r="10" stroke="#2F855A" stroke-width="2"/>
            <path d="M8 12l3 3 5-5" stroke="#2F855A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Contrase&ntilde;a actualizada exitosamente</span>
        </div>

        <div class="form-group">
          <label class="form-label">Contrase&ntilde;a actual</label>
          <input
            v-model="passwordActual"
            type="password"
            class="form-input"
            placeholder="Ingresa tu contraseña actual"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Nueva contrase&ntilde;a</label>
          <input
            v-model="nuevaPassword"
            type="password"
            class="form-input"
            placeholder="Mínimo 6 caracteres"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Confirmar nueva contrase&ntilde;a</label>
          <input
            v-model="confirmarPassword"
            type="password"
            class="form-input"
            placeholder="Repite la nueva contraseña"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancelar" @click="cerrar">{{ success ? 'Cerrar' : 'Cancelar' }}</button>
          <button v-if="!success" type="submit" class="btn-guardar" :disabled="loading">
            {{ loading ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 32px;
}

.modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 0;
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #929079;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: color 0.2s;
}

.modal-close:hover {
  color: #741102;
}

.modal-body {
  padding: 20px 24px 24px;
}

.form-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 16px;
  animation: slideDown 0.25s ease;
}

.form-success {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F0FFF4;
  border: 2px solid #2F855A;
  color: #2F855A;
  padding: 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 16px;
  animation: slideDown 0.25s ease;
}

.success-icon {
  flex-shrink: 0;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-cancelar {
  padding: 10px 20px;
  background: transparent;
  color: #929079;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancelar:hover {
  border-color: #929079;
  color: #042D29;
}

.btn-guardar {
  padding: 10px 20px;
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

.btn-guardar:hover {
  background: #052E2A;
}

.btn-guardar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
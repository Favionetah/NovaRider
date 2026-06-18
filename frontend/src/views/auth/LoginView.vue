<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const password = ref('')
const mensajeError = ref('')
const enviando = ref(false)

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
  <div class="login-container">
    <div class="login-card">
      <h1 class="login-title">NovaRider</h1>
      <p class="login-subtitle">Taller de Motocicletas</p>

      <form @submit.prevent="iniciarSesion" class="login-form">
        <div class="campo">
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

        <div class="campo">
          <label for="password">Contraseña</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Ingrese su contraseña"
            required
            autocomplete="current-password"
          />
        </div>

        <p v-if="mensajeError" class="mensaje-error">{{ mensajeError }}</p>

        <button type="submit" class="btn-ingresar" :disabled="enviando">
          {{ enviando ? 'Ingresando...' : 'Ingresar' }}
        </button>
      </form>
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
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f0f2f5;
}

.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.login-card {
  background: #fff;
  border-radius: 12px;
  padding: 40px 36px;
  width: 100%;
  max-width: 380px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
}

.login-title {
  text-align: center;
  font-size: 28px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.login-subtitle {
  text-align: center;
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 32px;
}

.login-form {
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
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.campo input {
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 15px;
  outline: none;
  transition: border-color 0.2s;
}

.campo input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.mensaje-error {
  color: #dc2626;
  font-size: 14px;
  text-align: center;
  background: #fef2f2;
  padding: 8px 12px;
  border-radius: 6px;
}

.btn-ingresar {
  padding: 12px;
  background: #1a1a2e;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-ingresar:hover {
  background: #16213e;
}

.btn-ingresar:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}
</style>

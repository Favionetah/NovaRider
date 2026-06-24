import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

  const modulosPermitidos = computed(() => {
    return user.value?.modulos || []
  })

  function tieneAcceso(ruta) {
    if (!user.value) return false
    return modulosPermitidos.value.some(m => m.ruta === ruta)
  }

  function tieneRol(...roles) {
    if (!user.value || !user.value.roles) return false
    return user.value.roles.some(r => roles.includes(r.id_rol))
  }

  async function login(username, password) {
    loading.value = true
    error.value = null

    try {
      await api.get('/sanctum/csrf-cookie')

      const response = await api.post('/login', { username, password })

      user.value = response.data.user
      return response.data
    } catch (err) {
      const message =
        err.response?.data?.message || 'Error al iniciar sesión'
      error.value = message
      throw new Error(message, { cause: err })
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
      user.value = null
    } catch {
      user.value = null
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/me')
      user.value = response.data.user
    } catch {
      user.value = null
    }
  }

  async function cambiarContrasena(passwordActual, nuevaPassword) {
    await api.put('/cambiar-contrasena', {
      password_actual: passwordActual,
      password: nuevaPassword,
      password_confirmation: nuevaPassword,
    })
  }

  return {
    user, loading, error, isAuthenticated,
    modulosPermitidos, tieneAcceso, tieneRol,
    login, logout, fetchUser, cambiarContrasena,
  }
})

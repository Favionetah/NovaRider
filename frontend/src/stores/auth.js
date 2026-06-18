import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

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

  return { user, loading, error, isAuthenticated, login, logout, fetchUser }
})

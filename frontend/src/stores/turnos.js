import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useTurnosStore = defineStore('turnos', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar(params = {}) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/turnos', { params })
      items.value = res.data.turnos
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar turnos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/turnos', data)
    items.value.unshift(res.data.turno)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/turnos/${id}`, data)
    const idx = items.value.findIndex((t) => t.id_turno === id)
    if (idx !== -1) {
      items.value[idx] = res.data.turno
    }
    return res.data
  }

  return { items, loading, error, listar, crear, actualizar }
})

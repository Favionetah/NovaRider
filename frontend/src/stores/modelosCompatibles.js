import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useModelosCompatiblesStore = defineStore('modelosCompatibles', () => {
  const items = ref([])
  const itemsInactivos = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/modelos-compatibles')
      items.value = res.data.modelos
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar modelos'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/modelos-compatibles?inactivos=1')
      itemsInactivos.value = res.data.modelos
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar modelos inactivos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/modelos-compatibles', data)
    items.value.push(res.data.modelo)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/modelos-compatibles/${id}`, data)
    const idx = items.value.findIndex((m) => m.id_modelo === id)
    if (idx !== -1) {
      items.value[idx] = res.data.modelo
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/modelos-compatibles/${id}`)
    const m = items.value.find((i) => i.id_modelo === id)
    if (m) {
      items.value = items.value.filter((i) => i.id_modelo !== id)
      itemsInactivos.value.unshift({ ...m, estadoA: false })
    }
  }

  async function reactivar(id) {
    const res = await api.put(`/modelos-compatibles/${id}/reactivar`)
    itemsInactivos.value = itemsInactivos.value.filter((m) => m.id_modelo !== id)
    items.value.unshift(res.data.modelo)
    return res.data
  }

  return {
    items, itemsInactivos, loading, error,
    listar, listarInactivos, crear, actualizar, eliminar, reactivar,
  }
})

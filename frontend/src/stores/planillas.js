import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const usePlanillasStore = defineStore('planillas', () => {
  const items = ref([])
  const resumen = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function listar(params = {}) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/planillas', { params })
      items.value = res.data.planillas
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar planillas'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/planillas', data)
    items.value.unshift(res.data.planilla)
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/planillas/${id}`)
    items.value = items.value.filter((p) => p.id_planilla !== id)
  }

  return { items, resumen, loading, error, listar, crear, eliminar }
})

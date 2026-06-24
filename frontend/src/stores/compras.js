import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useComprasStore = defineStore('compras', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/compras')
      items.value = res.data.compras
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar compras'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/compras', data)
    items.value.unshift(res.data.compra)
    return res.data
  }

  return { items, loading, error, listar, crear }
})

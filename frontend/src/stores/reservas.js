import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useReservasStore = defineStore('reservas', () => {
  const items = ref([])
  const itemsCanceladas = ref([])
  const loading = ref(false)
  const loadingCanceladas = ref(false)
  const error = ref(null)

  async function listar(params = {}) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/reservas', { params })
      items.value = res.data.reservas
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar reservas'
    } finally {
      loading.value = false
    }
  }

  async function listarCanceladas(params = {}) {
    loadingCanceladas.value = true
    error.value = null
    try {
      const res = await api.get('/reservas', { params: { canceladas: 1, ...params } })
      itemsCanceladas.value = res.data.reservas
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar reservas canceladas'
    } finally {
      loadingCanceladas.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/reservas', data)
    items.value.unshift(res.data.reserva)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/reservas/${id}`, data)
    const idx = items.value.findIndex(i => i.id_reserva === id)
    if (idx !== -1) items.value[idx] = res.data.reserva
    return res.data
  }

  async function cancelar(id) {
    await api.delete(`/reservas/${id}`)
    items.value = items.value.filter(i => i.id_reserva !== id)
  }

  async function convertirVenta(id, data) {
    const res = await api.post(`/reservas/${id}/convertir-venta`, data)
    items.value = items.value.filter(i => i.id_reserva !== id)
    return res.data
  }

  async function registrarEnvio(id, data) {
    const res = await api.post(`/reservas/${id}/registrar-envio`, data)
    const idx = items.value.findIndex(i => i.id_reserva === id)
    if (idx !== -1) {
      items.value[idx] = { ...items.value[idx], ...res.data.envio, estado: 'enviado' }
    }
    return res.data
  }

  return { items, itemsCanceladas, loading, loadingCanceladas, error, listar, listarCanceladas, crear, actualizar, cancelar, convertirVenta, registrarEnvio }
})

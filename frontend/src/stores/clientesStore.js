import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useClientesStore = defineStore('clientes', () => {
  const clientes = ref([])
  const clientesInactivos = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/clientes')
      clientes.value = res.data.clientes
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar clientes'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/clientes?inactivos=1')
      clientesInactivos.value = res.data.clientes
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar clientes inactivos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/clientes', data)
    clientes.value.push(res.data.cliente)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/clientes/${id}`, data)
    const idx = clientes.value.findIndex((c) => c.id_cliente === id)
    if (idx !== -1) {
      clientes.value[idx] = res.data.cliente
    }
    return res.data
  }

  async function eliminar(id) {
    try {
      await api.delete(`/clientes/${id}`)
      const cliente = clientes.value.find((c) => c.id_cliente === id)
      if (cliente) {
        clientes.value = clientes.value.filter((c) => c.id_cliente !== id)
        clientesInactivos.value.unshift({ ...cliente, estadoA: false })
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar cliente'
      throw err
    }
  }

  async function reactivar(id) {
    try {
      await api.put(`/clientes/${id}/reactivar`)
      const cliente = clientesInactivos.value.find((c) => c.id_cliente === id)
      if (cliente) {
        clientesInactivos.value = clientesInactivos.value.filter((c) => c.id_cliente !== id)
        clientes.value.push({ ...cliente, estadoA: true })
        // Mantener orden ascendente por ID
        clientes.value.sort((a, b) => a.id_cliente - b.id_cliente)
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al reactivar cliente'
      throw err
    }
  }

  return {
    clientes,
    clientesInactivos,
    loading,
    error,
    listar,
    listarInactivos,
    crear,
    actualizar,
    eliminar,
    reactivar,
  }
})

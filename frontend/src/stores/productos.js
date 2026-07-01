import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useProductosStore = defineStore('productos', () => {
  const items = ref([])
  const itemsInactivos = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/productos')
      items.value = res.data.productos ?? []
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar productos'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/productos?inactivos=1')
      itemsInactivos.value = res.data.productos
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar productos inactivos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/productos', data)
    items.value.push(res.data.producto)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/productos/${id}`, data)
    const idx = items.value.findIndex((p) => p.id_producto === id)
    if (idx !== -1) {
      items.value[idx] = res.data.producto
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/productos/${id}`)
    const p = items.value.find((e) => e.id_producto === id)
    if (p) {
      items.value = items.value.filter((e) => e.id_producto !== id)
      itemsInactivos.value.unshift({ ...p, estadoA: false })
    }
  }

  async function reactivar(id) {
    const res = await api.put(`/productos/${id}/reactivar`)
    itemsInactivos.value = itemsInactivos.value.filter((e) => e.id_producto !== id)
    items.value.unshift(res.data.producto)
    return res.data
  }

  async function listarModelos(id) {
    const res = await api.get(`/productos/${id}/modelos`)
    return res.data.modelos
  }

  async function guardarModelos(id, modelos) {
    const res = await api.post(`/productos/${id}/modelos`, { modelos })
    return res.data
  }

  return {
    items, itemsInactivos, loading, error,
    listar, listarInactivos, crear, actualizar, eliminar, reactivar,
    listarModelos, guardarModelos,
  }
})

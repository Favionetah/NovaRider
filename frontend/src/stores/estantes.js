import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useEstantesStore = defineStore('estantes', () => {
  const items = ref([])
  const itemsInactivos = ref([])
  const arbolUbicaciones = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/estantes')
      items.value = res.data.estantes
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar estantes'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/estantes?inactivos=1')
      itemsInactivos.value = res.data.estantes
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar estantes inactivos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/estantes', data)
    items.value.push(res.data.estante)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/estantes/${id}`, data)
    const idx = items.value.findIndex((e) => e.id_estante === id)
    if (idx !== -1) {
      items.value[idx] = res.data.estante
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/estantes/${id}`)
    const e = items.value.find((i) => i.id_estante === id)
    if (e) {
      items.value = items.value.filter((i) => i.id_estante !== id)
      itemsInactivos.value.unshift({ ...e, estadoA: false })
    }
  }

  async function reactivar(id) {
    const res = await api.put(`/estantes/${id}/reactivar`)
    itemsInactivos.value = itemsInactivos.value.filter((e) => e.id_estante !== id)
    items.value.unshift(res.data.estante)
    return res.data
  }

  async function obtenerArbol() {
    try {
      const res = await api.get('/ubicaciones/arbol')
      arbolUbicaciones.value = res.data.estantes
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar ubicaciones'
    }
  }

  async function obtenerProductos(id) {
    const res = await api.get(`/estantes/${id}/productos`)
    return res.data.productos
  }

  return {
    items, itemsInactivos, arbolUbicaciones, loading, error,
    listar, listarInactivos, crear, actualizar, eliminar, reactivar,
    obtenerArbol, obtenerProductos,
  }
})

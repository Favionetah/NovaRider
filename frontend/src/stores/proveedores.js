import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useProveedoresStore = defineStore('proveedores', () => {
  const items = ref([])
  const itemsInactivos = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/proveedores')
      items.value = res.data.proveedores
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar proveedores'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/proveedores?inactivos=1')
      itemsInactivos.value = res.data.proveedores
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar proveedores inactivos'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    const res = await api.post('/proveedores', data)
    items.value.push(res.data.proveedor)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/proveedores/${id}`, data)
    const idx = items.value.findIndex((p) => p.id_proveedor === id)
    if (idx !== -1) {
      items.value[idx] = res.data.proveedor
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/proveedores/${id}`)
    const p = items.value.find((e) => e.id_proveedor === id)
    if (p) {
      items.value = items.value.filter((e) => e.id_proveedor !== id)
      itemsInactivos.value.unshift({ ...p, estadoA: false })
    }
  }

  return {
    items, itemsInactivos, loading, error,
    listar, listarInactivos, crear, actualizar, eliminar,
  }
})

import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useMotocicletasStore = defineStore('motocicletas', () => {
  const motocicletas = ref([])
  const motocicletasInactivas = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar(id_cliente = null) {
    loading.value = true
    error.value = null
    try {
      const url = id_cliente ? `/motocicletas?id_cliente=${id_cliente}` : '/motocicletas'
      const res = await api.get(url)
      // 🚀 CORREGIDO: Laravel ahora devuelve el arreglo directo, no un objeto envuelto
      motocicletas.value = res.data || []
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar motocicletas'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivas() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/motocicletas?inactivos=1')
      // 🚀 CORREGIDO: Mapeo directo del arreglo de inactivas
      motocicletasInactivas.value = res.data || []
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar motocicletas inactivas'
    } finally {
      loading.value = false
    }
  }

  async function crear(data) {
    loading.value = true
    error.value = null
    try {
      const res = await api.post('/motocicletas', data)
      
      // Volvemos a listar para refrescar el almacén de forma limpia y actualizada
      await listar() 
      
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al guardar la motocicleta'
      throw err // Re-lanzamos el error para que el modal sepa que no debe cerrarse
    } finally {
      loading.value = false
    }
  }

  async function actualizar(id, data) {
    const res = await api.put(`/motocicletas/${id}`, data)
    const idx = motocicletas.value.findIndex((m) => m.id_motocicleta === id)
    if (idx !== -1) {
      // Nota: Si tu backend también devuelve la moto directa en update sin envolver, 
      // podrías requerir cambiar res.data.motocicleta por res.data.v_motocicleta o res.data directamente.
      motocicletas.value[idx] = res.data.motocicleta || res.data
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/motocicletas/${id}`)
    const moto = motocicletas.value.find((m) => m.id_motocicleta === id)
    if (moto) {
      motocicletas.value = motocicletas.value.filter((m) => m.id_motocicleta !== id)
      motocicletasInactivas.value.unshift({ ...moto, estadoA: false })
    }
  }

  async function reactivar(id) {
    await api.put(`/motocicletas/${id}/reactivar`)
    const moto = motocicletasInactivas.value.find((m) => m.id_motocicleta === id)
    if (moto) {
      motocicletasInactivas.value = motocicletasInactivas.value.filter((m) => m.id_motocicleta !== id)
      motocicletas.value.unshift({ ...moto, estadoA: true })
    }
  }

  async function obtenerHistorial(id) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get(`/motocicletas/${id}/historial`)
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar historial'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    motocicletas,
    motocicletasInactivas,
    loading,
    error,
    listar,
    listarInactivas,
    crear,
    actualizar,
    eliminar,
    reactivar,
    obtenerHistorial,
  }
})
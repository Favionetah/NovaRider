import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useProgramacionesStore = defineStore('programaciones', () => {
  const items = ref([])
  const globalData = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function obtener(idEmpleado) {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/programaciones', { params: { id_empleado: idEmpleado } })
      items.value = res.data.programaciones
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar horario'
    } finally {
      loading.value = false
    }
  }

  async function obtenerGlobal() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/programaciones/global')
      globalData.value = res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar horarios'
    } finally {
      loading.value = false
    }
  }

  async function guardar(idEmpleado, horario) {
    const res = await api.post('/programaciones', { id_empleado: idEmpleado, horario })
    items.value = res.data.programaciones
    return res.data
  }

  return { items, globalData, loading, error, obtener, obtenerGlobal, guardar }
})

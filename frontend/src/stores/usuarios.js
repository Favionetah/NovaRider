import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useUsuariosStore = defineStore('usuarios', () => {
  const usuarios = ref([])
  const usuariosInactivos = ref([])
  const roles = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/usuarios')
      usuarios.value = res.data.usuarios
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar usuarios'
    } finally {
      loading.value = false
    }
  }

  async function listarInactivos() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/usuarios?inactivos=1')
      usuariosInactivos.value = res.data.usuarios
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar usuarios inactivos'
    } finally {
      loading.value = false
    }
  }

  async function obtenerRoles() {
    try {
      const res = await api.get('/roles')
      roles.value = res.data.roles
    } catch {
      roles.value = []
    }
  }

  async function crear(data) {
    const res = await api.post('/usuarios', data)
    usuarios.value.push(res.data.usuario)
    return res.data
  }

  async function actualizar(id, data) {
    const res = await api.put(`/usuarios/${id}`, data)
    const idx = usuarios.value.findIndex((u) => u.id_usuario === id)
    if (idx !== -1) {
      usuarios.value[idx] = res.data.usuario
    }
    return res.data
  }

  async function eliminar(id) {
    await api.delete(`/usuarios/${id}`)
    usuarios.value = usuarios.value.filter((u) => u.id_usuario !== id)
  }

  async function reactivar(id) {
    await api.put(`/usuarios/${id}/reactivar`)
    const user = usuariosInactivos.value.find((u) => u.id_usuario === id)
    if (user) {
      usuariosInactivos.value = usuariosInactivos.value.filter((u) => u.id_usuario !== id)
      usuarios.value.unshift({ ...user, estadoA: true })
    }
  }

  return {
    usuarios, usuariosInactivos, roles, loading, error,
    listar, listarInactivos, obtenerRoles,
    crear, actualizar, eliminar, reactivar,
  }
})

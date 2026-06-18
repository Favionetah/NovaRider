<script setup>
import { onMounted, ref } from 'vue'
import { useUsuariosStore } from '@/stores/usuarios'
import UsuarioFormModal from './UsuarioFormModal.vue'
import ConfirmarEliminacion from './ConfirmarEliminacion.vue'

const store = useUsuariosStore()

onMounted(() => {
  store.listar()
})

const usuarioEditando = ref(null)
const mostrarForm = ref(false)

function abrirCrear() {
  store.obtenerRoles()
  usuarioEditando.value = null
  mostrarForm.value = true
}

function editarUsuario(usuario) {
  store.obtenerRoles()
  usuarioEditando.value = usuario
  mostrarForm.value = true
}

function cerrarFormulario() {
  mostrarForm.value = false
  usuarioEditando.value = null
}

const usuarioEliminar = ref(null)
const mostrarConfirmacion = ref(false)

function confirmarEliminar(usuario) {
  usuarioEliminar.value = usuario
  mostrarConfirmacion.value = true
}

async function eliminarUsuario() {
  await store.eliminar(usuarioEliminar.value.id_usuario)
  mostrarConfirmacion.value = false
  usuarioEliminar.value = null
}

function cancelarEliminar() {
  mostrarConfirmacion.value = false
  usuarioEliminar.value = null
}
</script>

<template>
  <div class="usuarios-page">
    <header class="page-header">
      <h1>Gesti&oacute;n de Usuarios</h1>
      <button class="btn-nuevo" @click="abrirCrear">Nuevo Usuario</button>
    </header>

    <p v-if="store.error" class="mensaje-error">{{ store.error }}</p>

    <div v-if="store.loading" class="cargando">Cargando usuarios...</div>

    <div v-else class="tabla-wrapper">
      <table class="tabla-usuarios">
        <thead>
          <tr>
            <th>#</th>
            <th>Empleado</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Tel&eacute;fono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in store.usuarios" :key="u.id_usuario">
            <td>{{ u.id_usuario }}</td>
            <td>{{ u.nombre_completo }}</td>
            <td>{{ u.username }}</td>
            <td>
              <span class="badge-rol" :class="'rol-' + (u.rol || '').toLowerCase()">
                {{ u.rol }}
              </span>
            </td>
            <td>{{ u.telefono || '—' }}</td>
            <td class="acciones">
              <button class="btn-editar" @click="editarUsuario(u)">Editar</button>
              <button class="btn-eliminar" @click="confirmarEliminar(u)">Desactivar</button>
            </td>
          </tr>
          <tr v-if="store.usuarios.length === 0">
            <td colspan="6" class="sin-datos">No hay usuarios registrados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <UsuarioFormModal
      v-if="mostrarForm"
      :usuario="usuarioEditando"
      :roles="store.roles"
      @cerrar="cerrarFormulario"
    />

    <ConfirmarEliminacion
      v-if="mostrarConfirmacion"
      :usuario="usuarioEliminar"
      @confirmar="eliminarUsuario"
      @cancelar="cancelarEliminar"
    />
  </div>
</template>

<style scoped>
.usuarios-page {
  padding: 32px;
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 24px;
  color: #1a1a2e;
}

.btn-nuevo {
  padding: 10px 20px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.btn-nuevo:hover {
  background: #1d4ed8;
}

.cargando {
  text-align: center;
  color: #6b7280;
  padding: 40px;
}

.mensaje-error {
  color: #dc2626;
  background: #fef2f2;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.tabla-wrapper {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.tabla-usuarios {
  width: 100%;
  border-collapse: collapse;
}

.tabla-usuarios th {
  background: #f8fafc;
  padding: 12px 16px;
  text-align: left;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e5e7eb;
}

.tabla-usuarios td {
  padding: 14px 16px;
  font-size: 14px;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
}

.tabla-usuarios tr:last-child td {
  border-bottom: none;
}

.badge-rol {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.rol-administrador { background: #fef3c7; color: #92400e; }
.rol-cajero { background: #dbeafe; color: #1e40af; }
.rol-mecanico { background: #d1fae5; color: #065f46; }
.rol-recepcionista { background: #f3e8ff; color: #6b21a8; }

.acciones {
  display: flex;
  gap: 8px;
}

.btn-editar {
  padding: 6px 14px;
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.btn-editar:hover {
  background: #e5e7eb;
}

.btn-eliminar {
  padding: 6px 14px;
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fca5a5;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.btn-eliminar:hover {
  background: #fee2e2;
}

.sin-datos {
  text-align: center;
  color: #9ca3af;
  padding: 40px;
}
</style>

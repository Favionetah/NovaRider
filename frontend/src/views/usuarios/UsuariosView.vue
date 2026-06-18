<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useUsuariosStore } from '@/stores/usuarios'
import UsuarioFormModal from './UsuarioFormModal.vue'
import ConfirmarEliminacion from './ConfirmarEliminacion.vue'

const store = useUsuariosStore()

const tabActivo = ref('activos')
const busqueda = ref('')
const filtroRol = ref('')

onMounted(async () => {
  await Promise.all([store.listar(), store.listarInactivos()])
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async () => {
  busqueda.value = ''
  filtroRol.value = ''
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.tabla-wrapper', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power3.out' })
  gsap.fromTo('.tabla-usuarios tbody tr', { y: 12, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, stagger: 0.04, ease: 'power2.out', delay: 0.15 })
}

const usuariosFiltrados = computed(() => {
  let lista = store.usuarios
  if (filtroRol.value) {
    lista = lista.filter(u => u.id_rol === Number(filtroRol.value))
  }
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    lista = lista.filter(u =>
      u.nombre_completo?.toLowerCase().includes(q) ||
      u.username?.toLowerCase().includes(q) ||
      u.ci?.toLowerCase().includes(q)
    )
  }
  return lista
})

const inactivosFiltrados = computed(() => {
  if (!busqueda.value) return store.usuariosInactivos
  const q = busqueda.value.toLowerCase()
  return store.usuariosInactivos.filter(u =>
    u.nombre_completo?.toLowerCase().includes(q) ||
    u.username?.toLowerCase().includes(q) ||
    u.ci?.toLowerCase().includes(q)
  )
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

async function reactivarUsuario(id) {
  await store.reactivar(id)
}
</script>

<template>
  <div class="usuarios-page">
    <header class="page-header">
      <h1>Gesti&oacute;n de Usuarios</h1>
      <button class="btn-nuevo" @click="abrirCrear">
        <svg viewBox="0 0 24 24" fill="none" class="icon-plus">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        Nuevo Usuario
      </button>
    </header>

    <p v-if="store.error" class="mensaje-error">{{ store.error }}</p>

    <div class="content-card">
      <div class="tabs">
        <button
          class="tab"
          :class="{ active: tabActivo === 'activos' }"
          @click="tabActivo = 'activos'"
        >
          <span class="tab-label">Activos</span>
          <span class="tab-badge" :class="tabActivo === 'activos' ? 'badge-active' : 'badge-inactive'">
            {{ store.usuarios.length }}
          </span>
        </button>
        <button
          class="tab"
          :class="{ active: tabActivo === 'inactivos' }"
          @click="tabActivo = 'inactivos'"
        >
          <span class="tab-label">Inactivos</span>
          <span class="tab-badge" :class="tabActivo === 'inactivos' ? 'badge-active' : 'badge-inactive'">
            {{ store.usuariosInactivos.length }}
          </span>
        </button>
      </div>

      <div class="toolbar" v-if="tabActivo === 'activos'">
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="busqueda"
            type="text"
            class="search-input"
            placeholder="Buscar por nombre, usuario o CI..."
          />
        </div>
        <div class="filter-wrapper">
          <select v-model="filtroRol" class="filter-select">
            <option value="">Todos los roles</option>
            <option v-for="r in store.roles" :key="r.id_rol" :value="r.id_rol">
              {{ r.nombre }}
            </option>
          </select>
        </div>
      </div>

      <div class="toolbar" v-else>
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="busqueda"
            type="text"
            class="search-input"
            placeholder="Buscar por nombre, usuario o CI..."
          />
        </div>
      </div>

      <div v-if="store.loading" class="cargando">Cargando usuarios...</div>

      <div v-else class="tabla-wrapper">
        <table class="tabla-usuarios">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-emp">Empleado</th>
              <th class="col-user">Usuario</th>
              <th class="col-ci">CI</th>
              <th class="col-rol">Rol</th>
              <th class="col-acc">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="tabActivo === 'activos'">
            <tr v-for="u in usuariosFiltrados" :key="u.id_usuario">
              <td class="col-id">{{ u.id_usuario }}</td>
              <td class="col-emp">
                <span class="emp-nombre">{{ u.nombre_completo }}</span>
                <span class="emp-cargo">{{ u.cargo }}</span>
              </td>
              <td class="col-user">{{ u.username }}</td>
              <td class="col-ci">{{ u.ci || '—' }}</td>
              <td class="col-rol">
                <span class="badge-rol" :class="'rol-' + (u.rol || '').toLowerCase()">
                  {{ u.rol }}
                </span>
              </td>
              <td class="col-acc">
                <button class="btn-accion btn-editar" @click="editarUsuario(u)" title="Editar">
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                  </svg>
                </button>
                <button class="btn-accion btn-eliminar" @click="confirmarEliminar(u)" title="Desactivar">
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </td>
            </tr>
            <tr v-if="usuariosFiltrados.length === 0">
              <td colspan="6" class="sin-datos">
                {{ busqueda || filtroRol ? 'No se encontraron usuarios con esos filtros' : 'No hay usuarios activos registrados' }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="u in inactivosFiltrados" :key="u.id_usuario">
              <td class="col-id">{{ u.id_usuario }}</td>
              <td class="col-emp">
                <span class="emp-nombre">{{ u.nombre_completo }}</span>
                <span class="emp-cargo">{{ u.cargo }}</span>
              </td>
              <td class="col-user">{{ u.username }}</td>
              <td class="col-ci">{{ u.ci || '—' }}</td>
              <td class="col-rol">
                <span class="badge-rol inactivo">Inactivo</span>
              </td>
              <td class="col-acc">
                <button class="btn-accion btn-reactivar" @click="reactivarUsuario(u.id_usuario)" title="Reactivar">
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M1 12s2.5-7 11-7 11 7 11 7-2.5 7-11 7-11-7-11-7z" stroke="currentColor" stroke-width="1.5" />
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" />
                  </svg>
                  Reactivar
                </button>
              </td>
            </tr>
            <tr v-if="inactivosFiltrados.length === 0">
              <td colspan="6" class="sin-datos">
                {{ busqueda ? 'No se encontraron usuarios inactivos con ese criterio' : 'No hay usuarios inactivos' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #042D29;
}

.btn-nuevo {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-nuevo:hover {
  background: #052E2A;
}

.icon-plus {
  width: 18px;
  height: 18px;
}

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 16px;
}

/* ── Card ── */
.content-card {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29, #741102) 1;
}

/* ── Tabs ── */
.tabs {
  display: flex;
  border-bottom: 1px solid #E5E7EB;
  padding: 0 24px;
}

.tab {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 20px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: -1px;
}

.tab:hover {
  color: #042D29;
}

.tab.active {
  color: #042D29;
  font-weight: 600;
  border-bottom-color: #042D29;
}

.tab-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 11px;
  font-size: 12px;
  font-weight: 600;
}

.badge-active {
  background: rgba(4, 45, 41, 0.1);
  color: #042D29;
}

.badge-inactive {
  background: #F3F4F6;
  color: #929079;
}

/* ── Toolbar ── */
.toolbar {
  display: flex;
  gap: 12px;
  padding: 16px 24px;
  border-bottom: 1px solid #F3F4F6;
}

.search-wrapper {
  flex: 1;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #929079;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.search-input::placeholder {
  color: #929079;
}

.filter-wrapper {
  min-width: 180px;
}

.filter-select {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  background: #FFFFFF;
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

/* ── Loading ── */
.cargando {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}

/* ── Table ── */
.tabla-wrapper {
  overflow-x: auto;
}

.tabla-usuarios {
  width: 100%;
  border-collapse: collapse;
}

.tabla-usuarios th {
  padding: 12px 16px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-usuarios td {
  padding: 14px 16px;
  font-size: 14px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
  vertical-align: middle;
}

.tabla-usuarios tr:last-child td {
  border-bottom: none;
}

.tabla-usuarios tbody tr {
  transition: background 0.15s ease;
}

.tabla-usuarios tbody tr:hover {
  background: #F9FAFB;
}

.col-id { width: 50px; }
.col-emp { min-width: 180px; }
.col-user { min-width: 120px; }
.col-ci { min-width: 130px; }
.col-rol { width: 120px; }
.col-acc { width: 100px; text-align: right; }

.emp-nombre {
  display: block;
  font-weight: 500;
  color: #042D29;
}

.emp-cargo {
  display: block;
  font-size: 12px;
  color: #929079;
  margin-top: 2px;
}

.badge-rol {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.rol-administrador { background: rgba(116, 17, 2, 0.1); color: #741102; }
.rol-cajero { background: rgba(146, 144, 121, 0.15); color: #5C5B4E; }
.rol-mecanico { background: rgba(4, 45, 41, 0.12); color: #042D29; }
.rol-recepcionista { background: rgba(146, 144, 121, 0.1); color: #5C5B4E; }
.badge-rol.inactivo { background: #F3F4F6; color: #9CA3AF; }

/* ── Action buttons ── */
.col-acc {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
}

.btn-accion {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #FFFFFF;
}

.icon-accion {
  width: 16px;
  height: 16px;
}

.btn-editar {
  color: #042D29;
}

.btn-editar:hover {
  border-color: #042D29;
  background: rgba(4, 45, 41, 0.05);
}

.btn-eliminar {
  color: #741102;
}

.btn-eliminar:hover {
  border-color: #741102;
  background: rgba(116, 17, 2, 0.05);
}

.btn-reactivar {
  color: #042D29;
  border-color: #042D29;
  background: rgba(4, 45, 41, 0.05);
}

.btn-reactivar:hover {
  background: rgba(4, 45, 41, 0.1);
}

.sin-datos {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}
</style>

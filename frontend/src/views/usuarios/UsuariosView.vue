<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUsuariosStore } from '@/stores/usuarios'
import { useToastStore } from '@/stores/toast'
import UsuarioFormModal from './UsuarioFormModal.vue'
import ConfirmarEliminacion from './ConfirmarEliminacion.vue'
import VistaPreviaPdfModal from './VistaPreviaPdfModal.vue'

const router = useRouter()
const store = useUsuariosStore()
const toast = useToastStore()

const tabActivo = ref('activos')
const busqueda = ref('')
const filtroRol = ref('')
const paginaActual = ref(1)
const POR_PAGINA = 12
const stats = ref({ total: 0, activos: 0, turnos_hoy: 0 })
const verPagos = ref(false)

async function cargarStats() {
  try {
    const { default: api } = await import('@/services/api')
    const res = await api.get('/turnos', { params: { fecha: new Date().toISOString().split('T')[0] } })
    stats.value.turnos_hoy = res.data.turnos.length
  } catch { /* ignore */ }
  stats.value.activos = store.usuarios.length
  stats.value.total = store.usuarios.length + store.usuariosInactivos.length
}

onMounted(async () => {
  await Promise.all([store.listar(), store.listarInactivos(), store.obtenerRoles()])
  await cargarStats()
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async () => {
  busqueda.value = ''
  filtroRol.value = ''
  paginaActual.value = 1
  await nextTick()
  animarEntrada()
})

watch([busqueda, filtroRol], () => {
  paginaActual.value = 1
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.stats-grid', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.fromTo('.tabla-usuarios tbody tr', { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
}

function irADetalle(id) {
  router.push(`/usuarios/${id}`)
}

const usuariosFiltrados = computed(() => {
  let lista = store.usuarios
  if (filtroRol.value) {
    lista = lista.filter(u => u.roles?.some(r => r.id_rol === Number(filtroRol.value)))
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

const totalPaginas = computed(() => {
  const lista = tabActivo.value === 'activos' ? usuariosFiltrados.value : inactivosFiltrados.value
  return Math.max(1, Math.ceil(lista.length / POR_PAGINA))
})

const usuariosPagina = computed(() => {
  const start = (paginaActual.value - 1) * POR_PAGINA
  return usuariosFiltrados.value.slice(start, start + POR_PAGINA)
})

const inactivosPagina = computed(() => {
  const start = (paginaActual.value - 1) * POR_PAGINA
  return inactivosFiltrados.value.slice(start, start + POR_PAGINA)
})

function irAPagina(p) {
  if (p >= 1 && p <= totalPaginas.value) {
    paginaActual.value = p
  }
}

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

function guardadoFormulario() {
  const eraEdicion = !!usuarioEditando.value
  mostrarForm.value = false
  usuarioEditando.value = null
  toast.success(eraEdicion ? 'Usuario editado correctamente' : 'Usuario creado correctamente')
}

function cerrarFormulario() {
  mostrarForm.value = false
  usuarioEditando.value = null
}

const usuarioEliminar = ref(null)
const mostrarConfirmacion = ref(false)
const tieneOrdenesActivas = ref(null)
const ordenesActivas = ref([])

async function confirmarEliminar(usuario) {
  usuarioEliminar.value = usuario
  const esMecanico = usuario.roles?.some(r => r.id_rol === 3)

  if (esMecanico) {
    const data = await store.verificarOrdenesActivas(usuario.id_usuario)
    tieneOrdenesActivas.value = data.tiene_ordenes
    ordenesActivas.value = data.ordenes
  } else {
    tieneOrdenesActivas.value = null
    ordenesActivas.value = []
  }

  mostrarConfirmacion.value = true
}

async function eliminarUsuario() {
  try {
    await store.eliminar(usuarioEliminar.value.id_usuario)
    mostrarConfirmacion.value = false
    usuarioEliminar.value = null
    toast.success('Usuario desactivado correctamente')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Error al desactivar usuario')
  }
}

function cancelarEliminar() {
  mostrarConfirmacion.value = false
  usuarioEliminar.value = null
}

async function reactivarUsuario(id) {
  try {
    await store.reactivar(id)
    toast.success('Usuario reactivado correctamente')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Error al reactivar usuario')
  }
}

const mostrarPreviewPdf = ref(false)
const pdfBlobUrl = ref('')
const pdfCargando = ref(false)

async function exportarPdf() {
  pdfCargando.value = true
  mostrarPreviewPdf.value = true
  try {
    const { default: api } = await import('@/services/api')
    const params = { preview: true }
    if (busqueda.value) params.busqueda = busqueda.value
    if (filtroRol.value) params.rol = filtroRol.value
    if (tabActivo.value === 'inactivos') params.inactivos = true
    if (verPagos.value) params.tipo = 'pagos'
    const res = await api.get('/usuarios/reporte/pdf', { params, responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    pdfBlobUrl.value = URL.createObjectURL(blob)
  } catch {
    pdfBlobUrl.value = ''
  } finally {
    pdfCargando.value = false
  }
}

async function descargarPdf() {
  try {
    const { default: api } = await import('@/services/api')
    const params = {}
    if (busqueda.value) params.busqueda = busqueda.value
    if (filtroRol.value) params.rol = filtroRol.value
    if (tabActivo.value === 'inactivos') params.inactivos = true
    if (verPagos.value) params.tipo = 'pagos'
    const res = await api.get('/usuarios/reporte/pdf', { params, responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `reporte_usuarios_${new Date().toISOString().slice(0, 10)}.pdf`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    setTimeout(() => URL.revokeObjectURL(url), 1000)
  } catch { /* ignore */ }
}

function cerrarPreviewPdf() {
  if (pdfBlobUrl.value) URL.revokeObjectURL(pdfBlobUrl.value)
  pdfBlobUrl.value = ''
  mostrarPreviewPdf.value = false
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

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon-users">
          <svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total Empleados</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-active">
          <svg viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.activos }}</span>
          <span class="stat-label">Activos</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-clock">
          <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.turnos_hoy }}</span>
          <span class="stat-label">Turnos Hoy</span>
        </div>
      </div>
    </div>

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
        <button class="btn-toggle-pagos" :class="{ active: verPagos }" @click="verPagos = !verPagos">
          <svg viewBox="0 0 24 24" fill="none" style="width:16px;height:16px">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Ver Pagos
        </button>
        <button class="btn-export-pdf" @click="exportarPdf">
          <svg viewBox="0 0 24 24" fill="none" class="icon-download">
            <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Exportar PDF
        </button>
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

      <div v-show="!store.loading" class="tabla-wrapper">
        <table class="tabla-usuarios">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-emp">Empleado</th>
              <th class="col-user">Usuario</th>
              <th class="col-ci">CI</th>
              <th class="col-rol">Rol</th>
              <th class="col-horario">
                <span v-if="!verPagos">Estado Horario</span>
                <span v-else>Último Pago</span>
              </th>
              <th class="col-acc">Acciones</th>
            </tr>
          </thead>
          <tbody v-if="tabActivo === 'activos'">
            <tr v-for="u in usuariosPagina" :key="u.id_usuario">
              <td class="col-id">{{ u.id_usuario }}</td>
              <td class="col-emp">
                <span class="emp-nombre enlace" @click="irADetalle(u.id_usuario)">{{ u.nombre_completo }}</span>
                <span class="emp-cargo">{{ u.cargo }}</span>
              </td>
              <td class="col-user">{{ u.username }}</td>
              <td class="col-ci">{{ u.ci || '—' }}</td>
              <td class="col-rol">
                <div class="roles-wrap">
                  <span
                    v-for="r in (u.roles || [])"
                    :key="r.id_rol"
                    class="badge-rol"
                    :class="'rol-' + (r.nombre || '').toLowerCase()"
                  >
                    {{ r.nombre }}
                  </span>
                </div>
              </td>
              <td class="col-horario">
                <template v-if="!verPagos">
                  <span v-if="u.has_horario" class="badge-horario badge-asignado">Horario asignado</span>
                  <span v-else class="badge-horario badge-sin-asignar">Sin asignar</span>
                </template>
                <template v-else>
                  <span v-if="u.ultimo_pago" class="badge-horario badge-pago">
                    Bs {{ Number(u.ultimo_pago.sueldo_neto).toFixed(2) }}
                  </span>
                  <span v-else class="badge-horario badge-sin-pago">Sin pagos</span>
                </template>
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
            <tr v-if="usuariosPagina.length === 0">
              <td colspan="7" class="sin-datos">
                {{ busqueda || filtroRol ? 'No se encontraron usuarios con esos filtros' : 'No hay usuarios activos registrados' }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="u in inactivosPagina" :key="u.id_usuario">
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
              <td class="col-horario">—</td>
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
            <tr v-if="inactivosPagina.length === 0">
              <td colspan="7" class="sin-datos">
                {{ busqueda ? 'No se encontraron usuarios inactivos con ese criterio' : 'No hay usuarios inactivos' }}
              </td>
            </tr>
          </tbody>
        </table>

        <div class="paginacion">
          <button class="btn-pag" :disabled="paginaActual === 1" @click="irAPagina(1)">««</button>
          <button class="btn-pag" :disabled="paginaActual === 1" @click="irAPagina(paginaActual - 1)">«</button>
          <span class="pag-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
          <button class="btn-pag" :disabled="paginaActual === totalPaginas" @click="irAPagina(paginaActual + 1)">»</button>
          <button class="btn-pag" :disabled="paginaActual === totalPaginas" @click="irAPagina(totalPaginas)">»»</button>
        </div>
      </div>
    </div>

    <UsuarioFormModal
      v-if="mostrarForm"
      :usuario="usuarioEditando"
      :roles="store.roles"
      @guardado="guardadoFormulario"
      @cerrar="cerrarFormulario"
    />

    <ConfirmarEliminacion
      v-if="mostrarConfirmacion"
      :usuario="usuarioEliminar"
      :tiene-ordenes-activas="tieneOrdenesActivas"
      :ordenes="ordenesActivas"
      @confirmar="eliminarUsuario"
      @cancelar="cancelarEliminar"
    />

    <VistaPreviaPdfModal
      v-if="mostrarPreviewPdf"
      :pdf-blob-url="pdfBlobUrl"
      :cargando="pdfCargando"
      titulo="Vista Previa — Reporte de Usuarios"
      @descargar="descargarPdf"
      @cerrar="cerrarPreviewPdf"
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

.btn-export-pdf {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 16px;
  background: #FFFFFF;
  color: #1F2937;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-toggle-pagos {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 14px;
  background: #FFFFFF;
  color: #929079;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-toggle-pagos:hover {
  border-color: #042D29;
  color: #042D29;
}

.btn-toggle-pagos.active {
  background: rgba(4, 45, 41, 0.08);
  border-color: #042D29;
  color: #042D29;
  font-weight: 600;
}

.btn-export-pdf:hover {
  border-color: #042D29;
  background: #F9FAFB;
}

.icon-download {
  width: 18px;
  height: 18px;
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
  padding: 0 16px 20px 16px;
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
.col-rol { min-width: 100px; }
.col-horario { min-width: 140px; white-space: nowrap; }
.tabla-usuarios th.col-horario { text-align: center; }
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

.roles-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.badge-horario {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.badge-asignado {
  background: rgba(4, 45, 41, 0.1);
  color: #042D29;
}

.badge-sin-asignar {
  background: rgba(116, 17, 2, 0.1);
  color: #741102;
}

.badge-pago {
  background: rgba(4, 45, 41, 0.1);
  color: #042D29;
  font-weight: 700;
}

.badge-sin-pago {
  background: rgba(146, 144, 121, 0.1);
  color: #5C5B4E;
}

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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}

.stat-card {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  border-left: 4px solid #042D29;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 22px;
  height: 22px;
}

.stat-icon-users { background: rgba(4, 45, 41, 0.1); color: #042D29; }
.stat-icon-active { background: rgba(4, 45, 41, 0.08); color: #042D29; }
.stat-icon-clock { background: rgba(146, 144, 121, 0.15); color: #5C5B4E; }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #042D29;
  line-height: 1.2;
}

.stat-label {
  font-size: 13px;
  color: #929079;
  font-weight: 500;
}

.enlace {
  cursor: pointer;
  transition: color 0.15s ease;
}

.enlace:hover {
  color: #741102;
}

.paginacion {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #E5E7EB;
}

.btn-pag {
  padding: 6px 12px;
  background: #FFFFFF;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  color: #042D29;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pag:hover:not(:disabled) {
  border-color: #042D29;
  background: #F0F0EC;
}

.btn-pag:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pag-info {
  font-size: 13px;
  color: #929079;
  font-weight: 600;
  min-width: 120px;
  text-align: center;
}


.sin-datos {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}
</style>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useClientesStore } from '@/stores/clientesStore'
import { useToastStore } from '@/stores/toast'
import ClienteFormModal from './ClienteFormModal.vue'
import ConfirmarEliminarCliente from './ConfirmarEliminarCliente.vue'

const router = useRouter()
const store = useClientesStore()
const toast = useToastStore()

const tabActivo = ref('activos')
const busqueda = ref('')
const clienteEditando = ref(null)
const mostrarForm = ref(false)
const clienteEliminar = ref(null)
const mostrarConfirmacion = ref(false)

onMounted(async () => {
  await Promise.all([store.listar(), store.listarInactivos()])
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.tabla-wrapper', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power3.out' })
  gsap.fromTo('.tabla-clientes tbody tr', { y: 12, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, stagger: 0.04, ease: 'power2.out', delay: 0.15 })
}

function nombreCompleto(cliente) {
  return [
    cliente.primer_nombre,
    cliente.segundo_nombre,
    cliente.apellido_paterno,
    cliente.apellido_materno,
  ]
    .filter(Boolean)
    .join(' ')
}

const clientesFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  return store.clientes.filter((c) => {
    if (!q) return true

    // Buscar en datos del cliente
    const coincideCliente = [
      nombreCompleto(c),
      c.ci,
      c.telefono,
      c.nit,
      c.direccion,
    ]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(q))

    if (coincideCliente) return true

    // Buscar en sus motocicletas
    return (c.motocicletas || []).some((m) => {
      return [
        m.placa,
        m.marca,
        m.modelo,
      ]
        .filter(Boolean)
        .some((v) => v.toLowerCase().includes(q))
    })
  })
})

const inactivosFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  return store.clientesInactivos.filter((c) => {
    if (!q) return true

    const coincideCliente = [
      nombreCompleto(c),
      c.ci,
      c.telefono,
      c.nit,
      c.direccion,
    ]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(q))

    if (coincideCliente) return true

    return (c.motocicletas || []).some((m) => {
      return [
        m.placa,
        m.marca,
        m.modelo,
      ]
        .filter(Boolean)
        .some((v) => v.toLowerCase().includes(q))
    })
  })
})

function quitarFiltroCliente() {
  idClienteFiltro.value = null
  router.replace({ query: { ...route.query, clienteId: undefined } })
}

function abrirCrear() {
  clienteEditando.value = null
  mostrarForm.value = true
}

function editarCliente(cliente) {
  clienteEditando.value = cliente
  mostrarForm.value = true
}

function cerrarFormulario() {
  mostrarForm.value = false
  clienteEditando.value = null
}

function confirmarEliminar(cliente) {
  clienteEliminar.value = cliente
  mostrarConfirmacion.value = true
}

async function eliminarCliente() {
  if (!clienteEliminar.value) return
  try {
    await store.eliminar(clienteEliminar.value.id_cliente)
    clienteEliminar.value = null
    mostrarConfirmacion.value = false
    toast.show('Cliente desactivado correctamente', 'success')
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al desactivar el cliente'
    toast.show(msg, 'error')
  }
}

function cancelarEliminar() {
  clienteEliminar.value = null
  mostrarConfirmacion.value = false
}

async function reactivarCliente(id) {
  try {
    await store.reactivar(id)
    toast.show('Cliente reactivado correctamente', 'success')
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al reactivar el cliente'
    toast.show(msg, 'error')
  }
}

function irAMotocicletas() {
  router.push({ name: 'motocicletas' })
}

function exportarPdf() {
  window.open(`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/reportes/pdf?tipo=clientes`, '_blank')
}
</script>

<template>
  <div class="clientes-page">
    <header class="page-header">
      <div>
        <h1>Clientes</h1>
        <p class="page-subtitle">Registra y administra clientes vinculados a sus motocicletas.</p>
      </div>
      <div class="actions-header">
        <button class="btn-secundario" @click="irAMotocicletas">Ver Motocicletas</button>
        <button class="btn-primario" @click="abrirCrear">
          <span>Nuevo Cliente</span>
        </button>
      </div>
    </header>

    <div class="content-card">
      <div class="tabs">
        <button
          class="tab"
          :class="{ active: tabActivo === 'activos' }"
          @click="tabActivo = 'activos'"
        >
          Activos
          <span class="tab-badge" :class="tabActivo === 'activos' ? 'badge-active' : 'badge-inactive'">
            {{ store.clientes.length }}
          </span>
        </button>
        <button
          class="tab"
          :class="{ active: tabActivo === 'inactivos' }"
          @click="tabActivo = 'inactivos'"
        >
          Inactivos
          <span class="tab-badge" :class="tabActivo === 'inactivos' ? 'badge-active' : 'badge-inactive'">
            {{ store.clientesInactivos.length }}
          </span>
        </button>
      </div>

      <div class="toolbar">
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="busqueda"
            type="text"
            class="search-input"
            placeholder="Buscar por cliente o datos de su motocicleta (placa, marca)..."
          />
        </div>
        <button class="btn-export-pdf" @click="exportarPdf">
          <svg viewBox="0 0 24 24" fill="none" class="icon-download">
            <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Exportar PDF
        </button>
      </div>

      <div v-if="store.loading" class="cargando">Cargando clientes...</div>

      <div v-else class="tabla-wrapper">
        <table class="tabla-clientes">
          <thead>
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>CI</th>
              <th>Teléfono</th>
              <th>NIT</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody v-if="tabActivo === 'activos'">
            <tr v-for="cliente in clientesFiltrados" :key="cliente.id_cliente">
              <td>{{ cliente.id_cliente }}</td>
              <td>
                <strong>{{ nombreCompleto(cliente) }}</strong>
                <span class="dato-secundario">{{ cliente.direccion || 'Sin dirección' }}</span>
              </td>
              <td>{{ cliente.ci || '—' }}</td>
              <td>{{ cliente.telefono || '—' }}</td>
              <td>{{ cliente.nit || '—' }}</td>
              <td>
                <div class="col-acciones">
                  <button class="btn-accion btn-editar" @click="editarCliente(cliente)">Editar</button>
                  <button class="btn-accion btn-eliminar" @click="confirmarEliminar(cliente)">Desactivar</button>
                </div>
              </td>
            </tr>
            <tr v-if="clientesFiltrados.length === 0">
              <td colspan="6" class="sin-datos">
                {{ busqueda ? 'No se encontraron clientes con ese criterio' : 'No hay clientes activos registrados' }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="cliente in inactivosFiltrados" :key="cliente.id_cliente">
              <td>{{ cliente.id_cliente }}</td>
              <td>
                <strong>{{ nombreCompleto(cliente) }}</strong>
                <span class="dato-secundario">{{ cliente.direccion || 'Sin dirección' }}</span>
              </td>
              <td>{{ cliente.ci || '—' }}</td>
              <td>{{ cliente.telefono || '—' }}</td>
              <td>{{ cliente.nit || '—' }}</td>
              <td>
                <button class="btn-accion btn-reactivar" @click="reactivarCliente(cliente.id_cliente)">Reactivar</button>
              </td>
            </tr>
            <tr v-if="inactivosFiltrados.length === 0">
              <td colspan="6" class="sin-datos">
                {{ busqueda ? 'No se encontraron clientes inactivos con ese criterio' : 'No hay clientes inactivos' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ClienteFormModal
      v-if="mostrarForm"
      :cliente="clienteEditando"
      @cerrar="cerrarFormulario"
    />

    <ConfirmarEliminarCliente
      v-if="mostrarConfirmacion"
      :cliente="clienteEliminar"
      @confirmar="eliminarCliente"
      @cancelar="cancelarEliminar"
    />
  </div>
</template>

<style scoped>
.clientes-page {
  padding: 32px;
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 28px;
}

.page-header h1 {
  font-size: 26px;
  font-weight: 700;
  color: #042D29;
  margin-bottom: 6px;
}

.page-subtitle {
  color: #929079;
  font-size: 14px;
}

.actions-header {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-primario,
.btn-secundario {
  background: #042D29;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 12px 18px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, background 0.2s ease;
}

.btn-secundario {
  background: #fff;
  color: #042D29;
  border: 1.5px solid #042D29;
}

.btn-primario:hover,
.btn-secundario:hover {
  transform: translateY(-1px);
}

.content-card {
  background: #fff;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 18px;
}

.tab {
  background: #f5f4f0;
  border: none;
  border-radius: 12px;
  padding: 10px 16px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #042d29;
  font-weight: 600;
}

.tab.active {
  background: #042d29;
  color: #fff;
}

.tab-badge {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 12px;
}

.badge-active {
  background: #fff;
  color: #042d29;
}

.badge-inactive {
  background: #E5E7EB;
  color: #6B7280;
}

.toolbar {
  display: flex;
  justify-content: flex-start;
  gap: 16px;
  margin-bottom: 20px;
}

.search-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F5F4F0;
  border-radius: 12px;
  padding: 10px 14px;
}

.search-icon {
  width: 18px;
  height: 18px;
  color: #929079;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  color: #042D29;
  font-size: 14px;
  outline: none;
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

.btn-export-pdf:hover {
  border-color: #042D29;
  background: #F9FAFB;
}

.icon-download {
  width: 18px;
  height: 18px;
}

.tabla-wrapper {
  overflow-x: auto;
}

.tabla-clientes {
  width: 100%;
  border-collapse: collapse;
}

.tabla-clientes th,
.tabla-clientes td {
  padding: 14px 12px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-clientes th {
  color: #6B7280;
  font-size: 13px;
  font-weight: 700;
}

.tabla-clientes td {
  vertical-align: middle;
  font-size: 14px;
  color: #1F2937;
}

.col-acciones {
  display: flex;
  gap: 8px;
  flex-wrap: nowrap;
}

.nombre-link {
  color: #042D29;
  cursor: pointer;
  display: block;
}

.nombre-link:hover {
  text-decoration: underline;
  color: #741102;
}

.dato-secundario {
  display: block;
  color: #929079;
  margin-top: 4px;
  font-size: 13px;
}

.btn-accion {
  border: none;
  border-radius: 10px;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
}

.btn-editar {
  background: #F5F4F0;
  color: #042D29;
}

.btn-eliminar {
  background: #741102;
  color: #fff;
}

.btn-reactivar {
  background: #042D29;
  color: #fff;
}

.cargando,
.sin-datos {
  padding: 24px;
  color: #6B7280;
  text-align: center;
}

.mensaje-error {
  margin-bottom: 16px;
  padding: 14px 16px;
  background: #FFF5F5;
  border: 1px solid #F1A8A8;
  color: #741102;
  border-radius: 12px;
}
</style>
<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useClientesStore } from '@/stores/clientesStore'
import { useMotocicletasStore } from '@/stores/motocicletasStore'
import { useToastStore } from '@/stores/toast'
import { useAuthStore } from '@/stores/auth'
import MotocicletaFormModal from './MotocicletaFormModal.vue'
import ConfirmarEliminarMotocicleta from './ConfirmarEliminarMotocicleta.vue'
import HistorialVehiculoModal from './HistorialVehiculoModal.vue'

const router = useRouter()
const clienteStore = useClientesStore()
const store = useMotocicletasStore()
const toast = useToastStore()
const auth = useAuthStore()

const tabActivo = ref('activos')
const busqueda = ref('')
const motocicletaEditando = ref(null)
const mostrarForm = ref(false)
const motoEliminar = ref(null)
const mostrarConfirmacion = ref(false)
const motoHistorial = ref(null)
const mostrarHistorial = ref(false)

onMounted(async () => {
  await Promise.all([
    clienteStore.listar(),
    store.listar(),
    store.listarInactivas()
  ])
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.tabla-wrapper', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power3.out' })
  gsap.fromTo('.tabla-motos tbody tr', { y: 12, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, stagger: 0.04, ease: 'power2.out', delay: 0.15 })
}

function nombreCliente(moto) {
  return moto.cliente ? [
    moto.cliente.primer_nombre,
    moto.cliente.segundo_nombre,
    moto.cliente.apellido_paterno,
    moto.cliente.apellido_materno,
  ].filter(Boolean).join(' ') : 'Sin cliente'
}

const motosFiltradas = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  // Seguridad: Asegurar que siempre sea un arreglo iterable
  const listaMotos = store.motocicletas || []
  
  return listaMotos.filter((m) => {
    if (!q) return true

    return [
      m.placa,
      m.marca,
      m.modelo,
      m.color,
      m.cilindrada,
      nombreCliente(m),
      m.cliente?.ci,
      m.cliente?.nit,
    ]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(q))
  })
})

const motosInactivasFiltradas = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  // Seguridad: Asegurar que siempre sea un arreglo iterable
  const listaInactivas = store.motocicletasInactivas || []
  
  return listaInactivas.filter((m) => {
    if (!q) return true

    return [
      m.placa,
      m.marca,
      m.modelo,
      m.color,
      m.cilindrada,
      nombreCliente(m),
      m.cliente?.ci,
      m.cliente?.nit,
    ]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(q))
  })
})

function irAClientes() {
  router.push({ name: 'clientes' })
}

function abrirCrear() {
  motocicletaEditando.value = null
  mostrarForm.value = true
}

function editarMotocicleta(moto) {
  motocicletaEditando.value = moto
  mostrarForm.value = true
}

function cerrarFormulario() {
  mostrarForm.value = false
  motocicletaEditando.value = null
}

function confirmarEliminar(moto) {
  motoEliminar.value = moto
  mostrarConfirmacion.value = true
}

async function eliminarMotocicleta() {
  if (!motoEliminar.value) return
  try {
    await store.eliminar(motoEliminar.value.id_motocicleta)
    motoEliminar.value = null
    mostrarConfirmacion.value = false
    toast.show('Motocicleta desactivada correctamente', 'success')
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al desactivar la motocicleta'
    toast.show(msg, 'error')
  }
}

function cancelarEliminar() {
  motoEliminar.value = null
  mostrarConfirmacion.value = false
}

async function reactivarMotocicleta(id) {
  try {
    await store.reactivar(id)
    toast.show('Motocicleta reactivada correctamente', 'success')
  } catch (error) {
    const msg = error.response?.data?.message || 'Error al reactivar la motocicleta'
    toast.show(msg, 'error')
  }
}

function exportarPdf() {
  window.open(`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/reportes/pdf?tipo=motos`, '_blank')
}

function verHistorial(moto) {
  motoHistorial.value = moto
  mostrarHistorial.value = true
}

function cerrarHistorial() {
  mostrarHistorial.value = false
  motoHistorial.value = null
}
</script>

<template>
  <div class="motocicletas-page">
    <header class="page-header">
      <div>
        <h1>Motocicletas</h1>
        <p class="page-subtitle">Administra las motocicletas registradas y su propietario.</p>
      </div>
      <div class="actions-header">
        <button class="btn-secundario" @click="irAClientes">Ver Clientes</button>
        <button class="btn-primario" @click="abrirCrear">Nueva Motocicleta</button>
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
            {{ (store.motocicletas || []).length }}
          </span>
        </button>
        <button
          class="tab"
          :class="{ active: tabActivo === 'inactivos' }"
          @click="tabActivo = 'inactivos'"
        >
          Inactivas
          <span class="tab-badge" :class="tabActivo === 'inactivos' ? 'badge-active' : 'badge-inactive'">
            {{ (store.motocicletasInactivas || []).length }}
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
            placeholder="Buscar por placa, modelo o datos del propietario..."
          />
        </div>
        <button class="btn-export-pdf" @click="exportarPdf">
          <svg viewBox="0 0 24 24" fill="none" class="icon-download">
            <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Exportar PDF
        </button>
      </div>

      <div v-if="store.loading" class="cargando">Cargando motocicletas...</div>

      <div v-else class="tabla-wrapper">
        <table class="tabla-motos">
          <thead>
            <tr>
              <th>#</th>
              <th>Placa</th>
              <th>Cliente</th>
              <th>Modelo</th>
              <th>Año</th>
              <th>Color</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody v-if="tabActivo === 'activos'">
            <tr v-for="moto in motosFiltradas" :key="moto.id_motocicleta">
              <td>{{ moto.id_motocicleta }}</td>
              <td>
                <strong>{{ moto.placa }}</strong>
              </td>
              <td>{{ nombreCliente(moto) }}</td>
              <td>{{ moto.marca }} {{ moto.modelo }}</td>
              <td>{{ moto.anio || '—' }}</td>
              <td>{{ moto.color || '—' }}</td>
              <td>
                <div class="col-acciones">
                  <button class="btn-accion btn-historial" @click="verHistorial(moto)">Historial</button>
                  <button class="btn-accion btn-editar" @click="editarMotocicleta(moto)">Editar</button>
                  <button class="btn-accion btn-eliminar" @click="confirmarEliminar(moto)">Desactivar</button>
                </div>
              </td>
            </tr>
            <tr v-if="motosFiltradas.length === 0">
              <td colspan="7" class="sin-datos">
                {{ busqueda ? 'No se encontraron motocicletas con ese criterio' : 'No hay motocicletas activas registradas' }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="moto in motosInactivasFiltradas" :key="moto.id_motocicleta">
              <td>{{ moto.id_motocicleta }}</td>
              <td>{{ moto.placa }}</td>
              <td>{{ nombreCliente(moto) }}</td>
              <td>{{ moto.marca }} {{ moto.modelo }}</td>
              <td>{{ moto.anio || '—' }}</td>
              <td>{{ moto.color || '—' }}</td>
              <td>
                <button class="btn-accion btn-reactivar" @click="reactivarMotocicleta(moto.id_motocicleta)">Reactivar</button>
              </td>
            </tr>
            <tr v-if="motosInactivasFiltradas.length === 0">
              <td colspan="7" class="sin-datos">
                {{ busqueda ? 'No se encontraron motocicletas inactivas con ese criterio' : 'No hay motocicletas inactivas' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <MotocicletaFormModal
      v-if="mostrarForm"
      :motocicleta="motocicletaEditando"
      :clientes="clienteStore.clientes"
      @cerrar="cerrarFormulario"
    />

    <ConfirmarEliminarMotocicleta
      v-if="mostrarConfirmacion"
      :motocicleta="motoEliminar"
      @confirmar="eliminarMotocicleta"
      @cancelar="cancelarEliminar"
    />

    <HistorialVehiculoModal
      v-if="mostrarHistorial"
      :motocicleta="motoHistorial"
      @cerrar="cerrarHistorial"
    />
  </div>
</template>

<style scoped>
.motocicletas-page {
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

.tabla-motos {
  width: 100%;
  border-collapse: collapse;
}

.tabla-motos th,
.tabla-motos td {
  padding: 14px 12px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-motos th {
  color: #6B7280;
  font-size: 13px;
  font-weight: 700;
}

.tabla-motos td {
  vertical-align: middle;
  font-size: 14px;
  color: #1F2937;
}

.col-acciones {
  display: flex;
  gap: 8px;
  flex-wrap: nowrap;
}

.btn-accion {
  border: none;
  border-radius: 10px;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
}

.btn-historial {
  background: #042D29;
  color: #fff;
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
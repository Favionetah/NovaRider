<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useComprasStore } from '@/stores/compras'
import { useProveedoresStore } from '@/stores/proveedores'
import api from '@/services/api'
import CompraFormModal from './CompraFormModal.vue'
import ProveedorFormModal from './ProveedorFormModal.vue'
import VistaPreviaPdfModal from '@/views/usuarios/VistaPreviaPdfModal.vue'

const comprasStore = useComprasStore()
const provStore = useProveedoresStore()

const tabActivo = ref('compras')
const busqueda = ref('')
const busquedaProv = ref('')
const filtroProveedor = ref('')
const filtroFechaDesde = ref('')
const filtroFechaHasta = ref('')
const incluirInactivos = ref(false)
const keyTabla = ref(0)
const mostrarFormCompra = ref(false)
const mostrarFormProveedor = ref(false)
const proveedorEditando = ref(null)

onMounted(async () => {
  await Promise.all([comprasStore.listar(), provStore.listar(), provStore.listarInactivos()])
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async () => {
  busqueda.value = ''
  busquedaProv.value = ''
  filtroProveedor.value = ''
  filtroFechaDesde.value = ''
  filtroFechaHasta.value = ''
  incluirInactivos.value = false
  await nextTick()
  animarEntrada()
})

watch([busqueda, filtroProveedor, filtroFechaDesde, filtroFechaHasta], () => {
  keyTabla.value++
  nextTick(() => animarFilas())
})

watch([busquedaProv, incluirInactivos], () => {
  nextTick(() => animarFilas())
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.toolbar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.fromTo('.tabla-contenido tbody tr', { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
}

function animarFilas() {
  gsap.fromTo(
    '.tabla-contenido tbody tr',
    { y: 10, opacity: 0 },
    { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out' }
  )
}

const tieneFiltrosCompras = computed(() => {
  return busqueda.value || filtroProveedor.value || filtroFechaDesde.value || filtroFechaHasta.value
})

const tieneFiltrosProveedores = computed(() => {
  return busquedaProv.value || incluirInactivos.value
})

function limpiarFiltros() {
  busqueda.value = ''
  busquedaProv.value = ''
  filtroProveedor.value = ''
  filtroFechaDesde.value = ''
  filtroFechaHasta.value = ''
  incluirInactivos.value = false
}

const generandoPdf = ref(false)
const mostrarVistaPrevia = ref(false)
const pdfBlobUrl = ref('')

function construirParamsPdf() {
  const params = {}
  if (busqueda.value) params.busqueda = busqueda.value
  if (filtroProveedor.value) params.proveedor = filtroProveedor.value
  if (filtroFechaDesde.value) params.fecha_desde = filtroFechaDesde.value
  if (filtroFechaHasta.value) params.fecha_hasta = filtroFechaHasta.value
  return params
}

async function exportarPDF() {
  generandoPdf.value = true
  mostrarVistaPrevia.value = true
  pdfBlobUrl.value = ''
  try {
    const res = await api.get('/compras/reporte/pdf', {
      params: { ...construirParamsPdf(), preview: '1' },
      responseType: 'blob'
    })
    if (pdfBlobUrl.value) URL.revokeObjectURL(pdfBlobUrl.value)
    pdfBlobUrl.value = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
  } catch {
    mostrarVistaPrevia.value = false
    alert('Error al generar el reporte PDF')
  } finally {
    generandoPdf.value = false
  }
}

function descargarPdf() {
  if (!pdfBlobUrl.value) return
  const link = document.createElement('a')
  link.href = pdfBlobUrl.value
  link.download = 'reporte_compras.pdf'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function cerrarVistaPrevia() {
  mostrarVistaPrevia.value = false
  if (pdfBlobUrl.value) {
    URL.revokeObjectURL(pdfBlobUrl.value)
    pdfBlobUrl.value = ''
  }
}

const comprasFiltradas = computed(() => {
  let lista = comprasStore.items
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    lista = lista.filter(c =>
      c.proveedor?.nombre?.toLowerCase().includes(q) ||
      c.nro_factura_proveedor?.toLowerCase().includes(q)
    )
  }
  if (filtroProveedor.value) {
    lista = lista.filter(c => {
      const id = c.id_proveedor || c.proveedor?.id_proveedor
      return id == filtroProveedor.value
    })
  }
  if (filtroFechaDesde.value) {
    lista = lista.filter(c => c.fecha >= filtroFechaDesde.value)
  }
  if (filtroFechaHasta.value) {
    lista = lista.filter(c => c.fecha <= filtroFechaHasta.value)
  }
  return lista
})

const proveedoresUnicos = computed(() => {
  const vistos = []
  comprasStore.items.forEach(c => {
    const id = c.id_proveedor || c.proveedor?.id_proveedor
    if (id && !vistos.some(v => v.id_proveedor === id)) {
      vistos.push(c.proveedor || { id_proveedor: id, nombre: '—' })
    }
  })
  return vistos
})

const proveedoresFiltrados = computed(() => {
  let lista = incluirInactivos.value
    ? [...provStore.items, ...provStore.itemsInactivos]
    : provStore.items
  if (busquedaProv.value) {
    const q = busquedaProv.value.toLowerCase()
    lista = lista.filter(p =>
      p.nombre?.toLowerCase().includes(q) ||
      p.telefono?.toLowerCase().includes(q) ||
      p.direccion?.toLowerCase().includes(q)
    )
  }
  return lista
})

function abrirCompra() {
  mostrarFormCompra.value = true
}

function abrirProveedor(proveedor = null) {
  proveedorEditando.value = proveedor
  mostrarFormProveedor.value = true
}

function cerrarCompra() {
  mostrarFormCompra.value = false
}

async function onCompraGuardada() {
  mostrarFormCompra.value = false
  await comprasStore.listar()
  await nextTick()
  animarFilas()
}

function cerrarProveedor() {
  mostrarFormProveedor.value = false
  proveedorEditando.value = null
}

async function onProveedorGuardado() {
  mostrarFormProveedor.value = false
  proveedorEditando.value = null
  await provStore.listar()
  await nextTick()
  animarFilas()
}

async function eliminarProveedor(id) {
  await provStore.eliminar(id)
}
</script>

<template>
  <div class="compras-page">
    <header class="page-header">
      <h1>Compras</h1>
      <button v-if="tabActivo === 'compras'" class="btn-nuevo" @click="abrirCompra">
        <svg viewBox="0 0 24 24" fill="none" class="icon-plus">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        Nueva Compra
      </button>
      <button v-else class="btn-nuevo" @click="abrirProveedor()">
        <svg viewBox="0 0 24 24" fill="none" class="icon-plus">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        Nuevo Proveedor
      </button>
    </header>

    <p v-if="comprasStore.error" class="mensaje-error">{{ comprasStore.error }}</p>
    <p v-if="provStore.error" class="mensaje-error">{{ provStore.error }}</p>

    <div class="content-card">
      <div class="tabs">
        <button
          class="tab"
          :class="{ active: tabActivo === 'compras' }"
          @click="tabActivo = 'compras'"
        >
          <span class="tab-label">Compras</span>
          <span class="tab-badge" :class="tabActivo === 'compras' ? 'badge-active' : 'badge-inactive'">
            {{ comprasStore.items.length }}
          </span>
        </button>
        <button
          class="tab"
          :class="{ active: tabActivo === 'proveedores' }"
          @click="tabActivo = 'proveedores'"
        >
          <span class="tab-label">Proveedores</span>
          <span class="tab-badge" :class="tabActivo === 'proveedores' ? 'badge-active' : 'badge-inactive'">
            {{ provStore.items.length }}
          </span>
        </button>
      </div>

      <div v-if="tabActivo === 'compras'" class="toolbar">
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="busqueda"
            type="text"
            class="search-input"
            placeholder="Buscar por proveedor o factura..."
          />
        </div>
        <select v-model="filtroProveedor" class="filtro-select">
          <option value="">Todos los proveedores</option>
          <option v-for="p in proveedoresUnicos" :key="p.id_proveedor" :value="p.id_proveedor">
            {{ p.nombre }}
          </option>
        </select>
        <input v-model="filtroFechaDesde" type="date" class="filtro-input" placeholder="Desde" title="Fecha desde" />
        <input v-model="filtroFechaHasta" type="date" class="filtro-input" placeholder="Hasta" title="Fecha hasta" />
        <button v-if="tieneFiltrosCompras" class="btn-limpiar" @click="limpiarFiltros">Limpiar filtros</button>
        <button class="btn-exportar" @click="exportarPDF" :disabled="generandoPdf">
          <svg viewBox="0 0 24 24" fill="none" class="icon-export">
            <path d="M12 3v12M12 15l-4-4M12 15l4-4M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          {{ generandoPdf ? 'Generando...' : 'Exportar PDF' }}
        </button>
      </div>

      <div v-else class="toolbar">
        <div class="search-wrapper">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
            <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            v-model="busquedaProv"
            type="text"
            class="search-input"
            placeholder="Buscar proveedor..."
          />
        </div>
        <label class="toggle-label">
          <input type="checkbox" v-model="incluirInactivos" />
          Incluir desactivados
        </label>
        <button v-if="tieneFiltrosProveedores" class="btn-limpiar" @click="limpiarFiltros">Limpiar filtros</button>
      </div>

      <div v-if="tabActivo === 'compras'" class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-prov">Proveedor</th>
              <th class="col-fecha">Fecha</th>
              <th class="col-factura">Factura</th>
              <th class="col-prods">Productos</th>
              <th class="col-total">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in comprasFiltradas" :key="keyTabla + '-' + c.id_compra">
              <td class="col-id">{{ c.id_compra }}</td>
              <td class="col-prov">{{ c.proveedor?.nombre || '—' }}</td>
              <td class="col-fecha">{{ c.fecha }}</td>
              <td class="col-factura">{{ c.nro_factura_proveedor || '—' }}</td>
              <td class="col-prods">{{ c.detalles?.length || 0 }}</td>
              <td class="col-total">${{ Number(c.total_compra).toFixed(2) }}</td>
            </tr>
            <tr v-if="comprasFiltradas.length === 0">
              <td colspan="6" class="sin-datos">
                {{ busqueda ? 'No se encontraron compras' : 'No hay compras registradas' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-prov">Nombre</th>
              <th class="col-tel">Tel&eacute;fono</th>
              <th class="col-dir">Direcci&oacute;n</th>
              <th class="col-acc">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in proveedoresFiltrados" :key="p.id_proveedor">
              <td class="col-id">{{ p.id_proveedor }}</td>
              <td class="col-prov">{{ p.nombre }}</td>
              <td class="col-tel">{{ p.telefono || '—' }}</td>
              <td class="col-dir">{{ p.direccion || '—' }}</td>
              <td class="col-acc">
                <button class="btn-accion btn-editar" @click="abrirProveedor(p)" title="Editar">
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                  </svg>
                </button>
                <button class="btn-accion btn-eliminar" @click="eliminarProveedor(p.id_proveedor)" title="Desactivar">
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </td>
            </tr>
            <tr v-if="proveedoresFiltrados.length === 0">
              <td colspan="5" class="sin-datos">
                {{ busquedaProv ? 'No se encontraron proveedores' : 'No hay proveedores registrados' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <CompraFormModal v-if="mostrarFormCompra" @cerrar="cerrarCompra" @guardado="onCompraGuardada" />

    <ProveedorFormModal
      v-if="mostrarFormProveedor"
      :proveedor="proveedorEditando"
      @cerrar="cerrarProveedor"
      @guardado="onProveedorGuardado"
    />

    <VistaPreviaPdfModal
      v-if="mostrarVistaPrevia"
      :pdf-blob-url="pdfBlobUrl"
      :cargando="generandoPdf"
      @cerrar="cerrarVistaPrevia"
      @descargar="descargarPdf"
    />
  </div>
</template>

<style scoped>
.compras-page {
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

.content-card {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29, #741102) 1;
}

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

.filtro-select,
.filtro-input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  background: #FFFFFF;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.filtro-select:focus,
.filtro-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  cursor: pointer;
  white-space: nowrap;
}

.toggle-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #042D29;
  cursor: pointer;
}

.btn-limpiar {
  padding: 10px 16px;
  background: #FFFFFF;
  color: #741102;
  border: 1.5px solid #741102;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.btn-limpiar:hover {
  background: rgba(116, 17, 2, 0.05);
}

.btn-exportar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #FFFFFF;
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-exportar:hover:not(:disabled) {
  background: rgba(4, 45, 41, 0.06);
  border-color: #052E2A;
}

.btn-exportar:active:not(:disabled) {
  background: rgba(4, 45, 41, 0.1);
  border-color: #741102;
  color: #741102;
}

.btn-exportar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.icon-export {
  width: 18px;
  height: 18px;
}

.tabla-wrapper {
  overflow-x: auto;
}

.tabla-contenido {
  width: 100%;
  border-collapse: collapse;
}

.tabla-contenido th {
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

.tabla-contenido td {
  padding: 14px 16px;
  font-size: 14px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
  vertical-align: middle;
}

.tabla-contenido tr:last-child td {
  border-bottom: none;
}

.tabla-contenido tbody tr {
  transition: background 0.15s ease;
}

.tabla-contenido tbody tr:hover {
  background: #F9FAFB;
}

.col-id { width: 50px; }
.col-prov { min-width: 160px; }
.col-fecha { min-width: 110px; }
.col-factura { min-width: 130px; }
.col-prods { width: 100px; text-align: center; }
.col-total { min-width: 100px; font-weight: 600; }
.col-tel { min-width: 120px; }
.col-dir { min-width: 180px; }
.col-acc { width: 100px; text-align: right; display: flex; gap: 6px; justify-content: flex-end; }

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

.btn-editar { color: #042D29; }
.btn-editar:hover { border-color: #042D29; background: rgba(4, 45, 41, 0.05); }
.btn-eliminar { color: #741102; }
.btn-eliminar:hover { border-color: #741102; background: rgba(116, 17, 2, 0.05); }

.page-header, .toolbar, .tabla-wrapper,
.tabla-contenido tbody tr {
  opacity: 0;
}

.sin-datos {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}
</style>

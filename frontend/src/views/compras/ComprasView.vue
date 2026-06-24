<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useComprasStore } from '@/stores/compras'
import { useProveedoresStore } from '@/stores/proveedores'
import CompraFormModal from './CompraFormModal.vue'
import ProveedorFormModal from './ProveedorFormModal.vue'

const comprasStore = useComprasStore()
const provStore = useProveedoresStore()

const tabActivo = ref('compras')
const busqueda = ref('')
const busquedaProv = ref('')
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
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.toolbar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.fromTo('.tabla-contenido tbody tr', { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
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
  return lista
})

const proveedoresFiltrados = computed(() => {
  let lista = provStore.items
  if (busquedaProv.value) {
    const q = busquedaProv.value.toLowerCase()
    lista = lista.filter(p =>
      p.nombre?.toLowerCase().includes(q) ||
      p.telefono?.toLowerCase().includes(q)
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

function cerrarProveedor() {
  mostrarFormProveedor.value = false
  proveedorEditando.value = null
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
            <tr v-for="c in comprasFiltradas" :key="c.id_compra">
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

    <CompraFormModal v-if="mostrarFormCompra" @cerrar="cerrarCompra" />

    <ProveedorFormModal
      v-if="mostrarFormProveedor"
      :proveedor="proveedorEditando"
      @cerrar="cerrarProveedor"
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

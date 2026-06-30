<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import AperturaCierreCard from './AperturaCierreCard.vue'
import RegistroVentasCard from './RegistroVentasCard.vue'
import CarritoSidebar from './CarritoSidebar.vue'
import TicketModal from './TicketModal.vue'

const cajaAbierta = ref(false)
const idCajaActiva = ref(null)
const saldoDigital = ref(0)
const montoInicialLocal = ref(200)

const carrito = ref([])
const carritoAbierto = ref(false)
const mostrarTicket = ref(false)
const ticketData = ref(null)
const logsServidor = ref('Listo')

const tabActiva = ref('filtros')
const criterioBusqueda = ref('')
const filtroFecha = ref('')

const historialVentas = ref([])

onMounted(async () => {
  await cargarHistorialDesdeBD()
})

async function cargarHistorialDesdeBD() {
  try {
    const res = await api.get('/caja/ventas')
    historialVentas.value = (res.data.ventas || []).map((v) => ({
      id_venta: v.id_venta,
      nroRecibo: v.nro_factura || `V${v.id_venta}`,
      fecha: v.fecha_hora ? v.fecha_hora.split(' ')[0] : '',
      cliente: v.cliente_nombre || 'Cliente General',
      placa: '',
      concepto: 'Venta de Caja',
      productosConcat: v.metodo_pago || 'Efectivo',
      itemsCount: 1,
      total: parseFloat(v.total || 0),
    }))
  } catch {
    logsServidor.value = 'Error al cargar historial'
  }
}

const totalVentasCalculado = computed(() => {
  return historialVentas.value.reduce((acc, v) => acc + (v.total || 0), 0)
})

const totalItemsVendidos = computed(() => {
  return historialVentas.value.reduce((acc, v) => acc + (v.itemsCount || 0), 0)
})

const ventasFiltradas = computed(() => {
  return historialVentas.value.filter((venta) => {
    const cumpleFecha = filtroFecha.value ? venta.fecha === filtroFecha.value : true
    const txt = criterioBusqueda.value.toLowerCase().trim()
    const cumpleTexto = !txt ? true : (
      (venta.cliente || '').toLowerCase().includes(txt) ||
      (venta.placa || '').toLowerCase().includes(txt) ||
      (venta.concepto || '').toLowerCase().includes(txt)
    )
    return cumpleFecha && cumpleTexto
  })
})

const reporteAgregado = computed(() => {
  let mesMonto = 0, mesCantidad = 0
  let anioMonto = 0, anioCantidad = 0
  const hoy = new Date()
  const mesActual = String(hoy.getMonth() + 1).padStart(2, '0')
  const anioActual = String(hoy.getFullYear())

  historialVentas.value.forEach((venta) => {
    if (venta.fecha) {
      const [vAnio, vMes] = venta.fecha.split('-')
      if (vAnio === anioActual) {
        anioMonto += venta.total || 0
        anioCantidad++
        if (vMes === mesActual) {
          mesMonto += venta.total || 0
          mesCantidad++
        }
      }
    }
  })

  return { mesMonto, mesCantidad, anioMonto, anioCantidad }
})

function vFechaFormato(fStr) {
  if (!fStr) return ''
  const parts = fStr.split('-')
  if (parts.length !== 3) return fStr
  return `${parts[2]}/${parts[1]}/${parts[0]}`
}

function toggleCarrito() {
  if (cajaAbierta.value) carritoAbierto.value = !carritoAbierto.value
}

async function ejecutarApertura() {
  if (montoInicialLocal.value < 0) return
  logsServidor.value = 'Registrando apertura...'
  try {
    const res = await api.post('/caja/abrir', { monto_inicial: montoInicialLocal.value })
    idCajaActiva.value = res.data.id_caja
    cajaAbierta.value = true
    saldoDigital.value = montoInicialLocal.value
    logsServidor.value = 'Jornada iniciada con éxito.'
    await cargarHistorialDesdeBD()
  } catch {
    cajaAbierta.value = true
    saldoDigital.value = montoInicialLocal.value
    logsServidor.value = 'Apertura local'
  }
}

async function handleCerrarCaja(datosCierre) {
  logsServidor.value = 'Procesando arqueo...'
  try {
    await api.post('/caja/cerrar', {
      id_caja: idCajaActiva.value,
      monto_cierre: datosCierre.fisico,
      observacion: datosCierre.observaciones,
    })
    logsServidor.value = 'Arqueo consolidado'
  } catch {
    logsServidor.value = 'Error en arqueo'
  }
  cajaAbierta.value = false
  idCajaActiva.value = null
  saldoDigital.value = 0
  carrito.value = []
  historialVentas.value = []
}

function handleAgregarItem(item) {
  carrito.value.push({ id: Date.now(), ...item })
}

function handleRemoverItem(id) {
  carrito.value = carrito.value.filter((i) => i.id !== id)
}

async function handleProcesarVenta() {
  const totalSeguro = carrito.value.reduce((acc, item) => acc + parseFloat(item.precio || 0), 0)
  const itemsParaTicket = carrito.value.map((item, index) => ({
    id: item.id || index,
    nombreItem: item.concepto || 'Producto',
    precioItem: parseFloat(item.precio || 0),
  }))
  const conceptoReal = itemsParaTicket.map((i) => i.nombreItem).join(', ')
  let idTicketFinal = `REC-${Math.floor(Math.random() * 90000) + 10000}`

  try {
    const res = await api.post('/caja/ventas', {
      id_caja: idCajaActiva.value,
      subtotal: totalSeguro,
      descuento: 0,
      total: totalSeguro,
      metodo_pago: 'Efectivo',
    })
    if (res.data.id_venta) {
      idTicketFinal = `REC-${res.data.id_venta}`
    }
    await cargarHistorialDesdeBD()
  } catch {
    // continua con id local
  }

  ticketData.value = {
    nroRecibo: idTicketFinal,
    items: itemsParaTicket,
    total: totalSeguro,
    metodo_pago: 'Efectivo',
    cliente: 'Cliente General',
    placa: 'S/P',
    concepto: conceptoReal,
  }

  mostrarTicket.value = true
  carrito.value = []
  carritoAbierto.value = false
}
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1>Ventas y Caja</h1>
      <p class="page-subtitle">Gesti&oacute;n interna, arqueo diario e historial de facturaci&oacute;n</p>
    </div>

    <!-- Modal Apertura -->
    <div v-if="!cajaAbierta" class="modal-overlay">
      <div class="modal-card" style="max-width: 400px;">
        <div class="modal-header">
          <h2>Apertura de Jornada</h2>
        </div>
        <div class="modal-body">
          <p class="texto-ayuda">Ingrese el saldo inicial en efectivo para habilitar el m&oacute;dulo de ventas.</p>
          <div class="form-group">
            <label for="monto-inicial">Monto Inicial en Caja (S/)</label>
            <input
              id="monto-inicial"
              v-model.number="montoInicialLocal"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
            />
          </div>
          <div class="modal-footer" style="border: none; margin-top: 16px;">
            <button class="btn-primario w-100" @click="ejecutarApertura">
              Inicializar Caja
            </button>
          </div>
          <p class="status-text">Status: {{ logsServidor }}</p>
        </div>
      </div>
    </div>

    <div :class="{ 'blur-content': !cajaAbierta }">
      <!-- Barra superior -->
      <div class="toolbar">
        <div class="toolbar-left">
          <span class="total-badge">Total turno: S/ {{ totalVentasCalculado.toFixed(2) }}</span>
          <span class="total-badge sec">{{ historialVentas.length }} comprobante(s)</span>
        </div>
        <button class="btn-primario" :disabled="!cajaAbierta" @click="toggleCarrito">
          <svg viewBox="0 0 24 24" fill="none" class="btn-icon" width="18" height="18">
            <circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2"/>
            <circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Carrito
          <span v-if="carrito.length > 0" class="carrito-count">{{ carrito.length }}</span>
        </button>
      </div>

      <div class="kpi-row">
        <div class="kpi-card">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
              <rect x="2" y="4" width="20" height="16" rx="2" stroke="#042D29" stroke-width="2"/>
              <path d="M12 10v4M10 12h4" stroke="#042D29" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">S/ {{ totalVentasCalculado.toFixed(2) }}</span>
            <span class="kpi-label">Total Recaudado (Turno)</span>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="#042D29" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M14 2v6h6" stroke="#042D29" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ historialVentas.length }}</span>
            <span class="kpi-label">Comprobantes Emitidos</span>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon">
            <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="#042D29" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="7" r="4" stroke="#042D29" stroke-width="2"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ totalItemsVendidos }}</span>
            <span class="kpi-label">Servicios / Productos</span>
          </div>
        </div>
      </div>

      <div class="caja-grid">
        <div class="caja-sidebar-col">
          <AperturaCierreCard
            :saldoSistema="saldoDigital"
            :cajaAbierta="cajaAbierta"
            :logsServidor="logsServidor"
            @onCerrarCaja="handleCerrarCaja"
          />
        </div>

        <div class="caja-main-col">
          <RegistroVentasCard
            :cajaAbierta="cajaAbierta"
            @onAgregarItem="handleAgregarItem"
          />

          <div class="card-seccion">
            <div class="card-seccion-header">
              <div>
                <h3>Consola de Transacciones</h3>
                <p class="texto-ayuda">Filtros din&aacute;micos y estad&iacute;sticas por periodos</p>
              </div>
            </div>

            <div class="tabs" style="margin: 0 24px;">
              <button class="tab-btn" :class="{ active: tabActiva === 'filtros' }" @click="tabActiva = 'filtros'">
                B&uacute;squeda y Filtros
              </button>
              <button class="tab-btn" :class="{ active: tabActiva === 'reportes' }" @click="tabActiva = 'reportes'">
                Reportes por Mes / A&ntilde;o
              </button>
            </div>

            <div class="card-seccion-body">
              <!-- TAB FILTROS -->
              <div v-if="tabActiva === 'filtros'">
                <div class="filtros-row">
                  <div class="search-box" style="flex: 1;">
                    <svg viewBox="0 0 24 24" fill="none" class="search-icon">
                      <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                      <path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input v-model="criterioBusqueda" type="text" placeholder="Buscar por cliente, placa o producto..." class="search-input" />
                  </div>
                  <input v-model="filtroFecha" type="date" class="date-input" />
                </div>

                <div v-if="ventasFiltradas.length === 0" class="empty-state">
                  No se encontraron ventas que coincidan con los filtros aplicados.
                </div>

                <div v-else class="tabla-wrapper" style="margin-top: 16px;">
                  <table class="tabla-contenido">
                    <thead>
                      <tr>
                        <th>Recibo / Fecha</th>
                        <th>Cliente</th>
                        <th>Concepto</th>
                        <th class="text-right">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(venta, idx) in ventasFiltradas" :key="idx">
                        <td>
                          <span class="recibo-nro">#{{ venta.nroRecibo }}</span>
                          <span class="recibo-fecha">{{ vFechaFormato(venta.fecha) }}</span>
                        </td>
                        <td>
                          <span class="cliente-nombre">{{ venta.cliente }}</span>
                          <span v-if="venta.placa" class="cliente-placa">{{ venta.placa }}</span>
                        </td>
                        <td class="td-concepto">{{ venta.concepto }}</td>
                        <td class="td-monto">S/ {{ venta.total.toFixed(2) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- TAB REPORTES -->
              <div v-if="tabActiva === 'reportes'">
                <div class="reportes-grid">
                  <div class="reporte-card mes">
                    <span class="reporte-label">Acumulado Mes Actual</span>
                    <span class="reporte-monto">S/ {{ reporteAgregado.mesMonto.toFixed(2) }}</span>
                    <span class="reporte-count">{{ reporteAgregado.mesCantidad }} &Oacute;rdenes liquidadas</span>
                  </div>
                  <div class="reporte-card anio">
                    <span class="reporte-label">Acumulado A&ntilde;o Actual</span>
                    <span class="reporte-monto">S/ {{ reporteAgregado.anioMonto.toFixed(2) }}</span>
                    <span class="reporte-count">{{ reporteAgregado.anioCantidad }} &Oacute;rdenes totales</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CarritoSidebar
      :isOpen="carritoAbierto"
      :items="carrito"
      @onClose="toggleCarrito"
      @onRemoveItem="handleRemoverItem"
      @onProcesarVenta="handleProcesarVenta"
    />

    <TicketModal
      :show="mostrarTicket"
      :datosTicket="ticketData"
      @onClose="mostrarTicket = false"
    />
  </div>
</template>

<style scoped>
.page-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px;
}

.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 700; color: #042D29; margin: 0 0 4px; }
.page-subtitle { font-size: 14px; color: #929079; margin: 0; }

.texto-ayuda {
  font-size: 13px;
  color: #929079;
  margin: 0 0 16px;
}

.status-text {
  font-size: 10px;
  color: #929079;
  font-family: 'Inter', monospace;
  margin-top: 12px;
  text-align: center;
}

/* Modal override */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}

.modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.modal-body { padding: 24px; }

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.form-group input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.btn-primario {
  display: inline-flex;
  align-items: center;
  justify-content: center;
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

.btn-primario:hover { background: #052E2A; }
.btn-primario:disabled { opacity: 0.5; cursor: not-allowed; }
.w-100 { width: 100%; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Toolbar */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 16px;
}

.toolbar-left {
  display: flex;
  gap: 12px;
  align-items: center;
}

.total-badge {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  background: #F0F4F3;
  padding: 6px 14px;
  border-radius: 20px;
}

.total-badge.sec { color: #929079; background: #F9F9F7; }

.btn-icon { width: 18px; height: 18px; }

.carrito-count {
  background: #741102;
  color: #FFFFFF;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 50%;
  line-height: 1.4;
}

/* KPIs */
.kpi-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.kpi-card {
  background: #FFFFFF;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  background: #F0F4F3;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kpi-valor {
  font-size: 20px;
  font-weight: 700;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.kpi-label {
  font-size: 12px;
  color: #929079;
}

/* Grid layout */
.caja-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
  align-items: start;
}

.caja-main-col {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Card section */
.card-seccion {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
}

.card-seccion-header {
  padding: 20px 24px 12px;
}

.card-seccion-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
  margin: 0 0 4px;
}

.card-seccion-body {
  padding: 20px 24px 24px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  padding: 0 24px;
}

.tab-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  border-radius: 8px 8px 0 0;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover { color: #042D29; background: #F5F4F0; }
.tab-btn.active { color: #042D29; background: #F5F4F0; font-weight: 600; }

/* Filters */
.filtros-row {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box {
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
  background: #FFFFFF;
}

.search-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.date-input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Inter', monospace;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s;
}

.date-input:focus { border-color: #042D29; }

.empty-state {
  text-align: center;
  color: #929079;
  padding: 40px 0;
  font-style: italic;
  font-size: 13px;
}

/* Table */
.tabla-wrapper {
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  overflow: hidden;
}

.tabla-contenido {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.tabla-contenido th {
  background: #F9F9F7;
  color: #929079;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.5px;
  padding: 10px 12px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-contenido td {
  padding: 10px 12px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
}

.tabla-contenido tbody tr:hover { background: #F9F9F7; }
.tabla-contenido tbody tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }

.recibo-nro {
  display: block;
  font-weight: 600;
  color: #042D29;
  font-family: 'Inter', monospace;
  font-size: 12px;
}

.recibo-fecha {
  font-size: 11px;
  color: #929079;
  font-family: 'Inter', monospace;
}

.cliente-nombre {
  display: block;
  font-weight: 500;
}

.cliente-placa {
  font-size: 11px;
  color: #929079;
  text-transform: uppercase;
  font-family: 'Inter', monospace;
}

.td-concepto {
  color: #929079;
  font-size: 12px;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.td-monto {
  text-align: right;
  font-weight: 600;
  font-family: 'Inter', monospace;
  color: #042D29;
}

/* Reportes */
.reportes-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.reporte-card {
  padding: 20px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.reporte-card.mes { background: #F0F4F3; border-left: 4px solid #042D29; }
.reporte-card.anio { background: #F9F9F7; border-left: 4px solid #929079; }

.reporte-label {
  font-size: 12px;
  color: #929079;
  font-weight: 500;
}

.reporte-monto {
  font-size: 22px;
  font-weight: 700;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.reporte-count {
  font-size: 12px;
  color: #1F2937;
}

.blur-content { opacity: 0.5; pointer-events: none; filter: blur(2px); }
</style>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import AperturaCierreCard from './AperturaCierreCard.vue'
import RegistroVentasCard from './RegistroVentasCard.vue'
import CarritoSidebar from './CarritoSidebar.vue'
import TicketModal from './TicketModal.vue'

// Estados de la Jornada de Caja
const cajaAbierta = ref(false)
const idCajaActiva = ref(null)
const saldoDigital = ref(0)
const montoInicialLocal = ref(200) 
const mostrarModalApertura = ref(false)

// Estados de Transacción e Interfaz
const carrito = ref([])
const carritoAbierto = ref(false)
const mostrarTicket = ref(false)
const ticketData = ref(null)
const logsServidor = ref('Listo')

// Origen unificado de la verdad del Cliente Seleccionado
const clienteSeleccionadoPrincipal = ref('Cliente General')

// Lista de clientes traídos de la Base de Datos
const listaClientes = ref([
  { id: 1, nombre: 'Cliente General' }
])

// Filtros e Historial
const criterioBusqueda = ref('')
const filtroFecha = ref('')
const historialVentas = ref([])

onMounted(async () => {
  await cargarClientesDesdeBD()
  await cargarHistorialDesdeBD()
  if (!cajaAbierta.value) {
    mostrarModalApertura.value = true
  }
})

async function cargarClientesDesdeBD() {
  try {
    const res = await api.get('/clientes-lista')
    if (res.data && res.data.clientes) {
      listaClientes.value = res.data.clientes.map(c => ({
        id: c.id_cliente,
        nombre: `${c.primer_nombre || ''} ${c.apellido_paterno || ''}`.trim() || 'Cliente Sin Nombre'
      }))
    }
  } catch (error) {
    console.error(error)
    logsServidor.value = 'No se pudo sincronizar los clientes'
  }
}

async function cargarHistorialDesdeBD() {
  try {
    const res = await api.get('/caja/ventas')
    historialVentas.value = (res.data.ventas || []).map((v) => ({
      id_venta: v.id_venta,
      nroRecibo: v.nro_factura || `VTA-${String(v.id_venta).padStart(3, '0')}`,
      fecha: v.fecha_hora ? v.fecha_hora.split(' ')[0] : '',
      cliente: v.cliente_nombre || 'Cliente General',
      placa: v.placa || 'S/P',
      metodo_pago: v.metodo_pago || 'Efectivo',
      total: parseFloat(v.total || 0)
    }))
    saldoDigital.value = historialVentas.value.reduce((acc, v) => acc + v.total, 0)
  } catch (error) {
    console.error(error)
    logsServidor.value = 'Error al obtener historial'
  }
}

async function handleAbrirCaja() {
  try {
    logsServidor.value = 'Abriendo caja...'
    const res = await api.post('/caja/abrir', { monto_inicial: montoInicialLocal.value })
    if (res.data.status === 'success') {
      idCajaActiva.value = res.data.id_caja
      saldoDigital.value = parseFloat(res.data.monto)
      cajaAbierta.value = true
      mostrarModalApertura.value = false 
      logsServidor.value = 'Caja Abierta Exitosamente'
    }
  } catch (error) {
    console.error(error)
    logsServidor.value = 'Fallo al abrir caja'
  }
}

function handleAgregarItem(nuevoItem) {
  carrito.value.push(nuevoItem)
  logsServidor.value = `Agregado: ${nuevoItem.concepto}`
}

function handleRemoveItem(idItem) {
  carrito.value = carrito.value.filter(item => item.id !== idItem)
}

function handleCambiarCliente(nuevoNombreCliente) {
  clienteSeleccionadoPrincipal.value = nuevoNombreCliente
}

async function handleProcesarVenta(datosVentaForm) {
  if (!idCajaActiva.value) return
  try {
    logsServidor.value = 'Procesando venta...'
    
    const clienteEncontrado = listaClientes.value.find(c => c.nombre === clienteSeleccionadoPrincipal.value)
    const idClienteFinal = clienteEncontrado ? clienteEncontrado.id : 1 

    const res = await api.post('/caja/ventas', {
      id_caja: idCajaActiva.value,
      id_cliente: idClienteFinal,
      total: datosVentaForm.total,
      subtotal: datosVentaForm.total,
      descuento: 0,
      metodo_pago: datosVentaForm.metodo_pago
    })

    if (res.data.status === 'success') {
      ticketData.value = {
        nroRecibo: res.data.id_venta || `VTA-${String(res.data.id_venta).padStart(3, '0')}`,
        cliente: clienteSeleccionadoPrincipal.value, 
        placa: datosVentaForm.placa,
        metodo_pago: datosVentaForm.metodo_pago,
        items: datosVentaForm.items,
        total: datosVentaForm.total
      }
      
      carrito.value = []
      carritoAbierto.value = false
      mostrarTicket.value = true
      await cargarHistorialDesdeBD()
      logsServidor.value = 'Venta registrada con éxito'
    }
  } catch (error) {
    console.error(error)
    logsServidor.value = 'Error al procesar la venta'
  }
}

async function handleCerrarCaja(datosCierre) {
  try {
    const res = await api.post('/caja/cerrar', {
      id_caja: idCajaActiva.value,
      monto_cierre: datosCierre.fisico,
      observacion: datosCierre.observaciones
    })
    if (res.data.status === 'success') {
      cajaAbierta.value = false
      idCajaActiva.value = null
      saldoDigital.value = 0
      carrito.value = []
      mostrarModalApertura.value = true 
      logsServidor.value = `Jornada Cerrada.`
      await cargarHistorialDesdeBD()
    }
  } catch (error) {
    console.error(error)
    logsServidor.value = 'Error en el cierre'
  }
}

const ventasFiltradas = computed(() => {
  return historialVentas.value.filter((v) => {
    const coincideCriterio =
      v.nroRecibo.toLowerCase().includes(criterioBusqueda.value.toLowerCase()) ||
      v.cliente.toLowerCase().includes(criterioBusqueda.value.toLowerCase()) ||
      v.metodo_pago.toLowerCase().includes(criterioBusqueda.value.toLowerCase())
    const coincideFecha = filtroFecha.value ? v.fecha === filtroFecha.value : true
    return coincideCriterio && coincideFecha
  })
})
</script>

<template>
  <div class="caja-dashboard-container">
    
    <Teleport to="body">
      <div v-if="mostrarModalApertura && !cajaAbierta" class="modal-apertura-overlay">
        <div class="modal-apertura-content">
          <div class="modal-apertura-header">
            <svg viewBox="0 0 24 24" fill="none" width="28" height="28" class="icon-lock">
              <rect x="3" y="11" width="18" height="11" rx="2" stroke="#042D29" stroke-width="2"/>
              <path d="M7 11V7a5 5 0 0110 0v4" stroke="#042D29" stroke-width="2"/>
            </svg>
            <h3>Apertura de Jornada Requerida</h3>
            <p>Ingrese el monto base en efectivo existente en caja física para iniciar operaciones.</p>
          </div>
          
          <div class="modal-apertura-body">
            <label>Monto Inicial de Apertura</label>
            <div class="input-bs-wrapper">
              <span class="currency-label">Bs.</span>
              <input v-model.number="montoInicialLocal" type="number" min="0" />
            </div>
            <button class="btn-confirmar-apertura" @click="handleAbrirCaja">
              Habilitar e Iniciar Caja General
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <div class="caja-header-grid" :class="{ 'blur-layout': !cajaAbierta }">
      <div class="panel-control-jornada">
        <AperturaCierreCard
          :cajaAbierta="cajaAbierta"
          :saldoSistema="saldoDigital"
          :logsServidor="logsServidor"
          @onCerrarCaja="handleCerrarCaja"
        />
      </div>

      <div class="panel-registro-ventas-custom">
        <RegistroVentasCard 
          :cajaAbierta="cajaAbierta" 
          :clientes="listaClientes"
          @onAgregarItem="handleAgregarItem"
          @onCambiarCliente="handleCambiarCliente"
        />
      </div>
    </div>

    <div 
      class="floating-cart-trigger-verificado" 
      :class="{ 'blur-layout': !cajaAbierta, 'disabled-trigger': !cajaAbierta }" 
      @click="cajaAbierta && (carritoAbierto = true)"
    >
      <div class="trigger-left">
        <span class="badge-contador">{{ carrito.length }}</span>
        <span class="trigger-title">Artículos listos en Carrito</span>
      </div>
      <div class="trigger-right">
        <span class="trigger-label">Ver Detalle</span>
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" class="arrow-icon">
          <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>

    <div class="historial-seccion-card" :class="{ 'blur-layout': !cajaAbierta }">
      <div class="tabs-header">
        <button class="active">Historial de Operaciones</button>
      </div>

      <div class="tabs-content">
        <div class="tab-filtros-pane">
          <div class="busqueda-grid">
            <div class="search-input-group">
              <label>Buscador Inteligente</label>
              <input v-model="criterioBusqueda" type="text" placeholder="Buscar por N° Recibo, Cliente o Método..." />
            </div>
            <div class="search-input-group">
              <label>Filtrar por Fecha exacta</label>
              <input v-model="filtroFecha" type="date" />
            </div>
          </div>

          <div class="tabla-contenedor">
            <table class="tabla-contenido">
              <thead>
                <tr>
                  <th>N° Recibo / Fecha</th>
                  <th>Cliente Beneficiario</th>
                  <th>Método</th>
                  <th class="text-right">Total Cobrado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="ventasFiltradas.length === 0">
                  <td colspan="4" class="text-center-empty">No se encontraron registros en el historial.</td>
                </tr>
                <tr v-for="venta in ventasFiltradas" :key="venta.id_venta">
                  <td>
                    <span class="recibo-nro">{{ venta.nroRecibo }}</span>
                    <span class="recibo-fecha">{{ venta.fecha }}</span>
                  </td>
                  <td>
                    <span class="cliente-nombre">{{ venta.cliente }}</span>
                    <span class="cliente-placa" style="font-size: 11px; color:#929079; display:block;">Placa: {{ venta.placa }}</span>
                  </td>
                  <td><span class="metodo-badge">{{ venta.metodo_pago }}</span></td>
                  <td class="td-monto">Bs. {{ venta.total.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <CarritoSidebar
      :isOpen="carritoAbierto"
      :items="carrito"
      :clienteActual="clienteSeleccionadoPrincipal"
      @onClose="carritoAbierto = false"
      @onRemoveItem="handleRemoveItem"
      @onProcesarVenta="handleProcesarVenta"
    />

    <TicketModal :show="mostrarTicket" :datosTicket="ticketData" @onClose="mostrarTicket = false" />
  </div>
</template>

<style scoped>
.caja-dashboard-container { 
  padding: 24px; 
  display: flex; 
  flex-direction: column; 
  gap: 24px; 
  font-family: 'Inter', sans-serif; 
  background-color: #FAFAFA; 
  min-height: 100vh; 
}

/* Modificado para nivelar perfectamente las dos tarjetas con align-items: stretch */
.caja-header-grid { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 24px; 
  align-items: stretch; 
  transition: filter 0.3s ease; 
}

.blur-layout { 
  filter: blur(4px); 
  pointer-events: none; 
  opacity: 0.6; 
}

/* Modal Apertura */
.modal-apertura-overlay { position: fixed; inset: 0; background: rgba(4, 45, 41, 0.4); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal-apertura-content { background: #FFFFFF; border-radius: 20px; padding: 32px; width: 100%; max-width: 460px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-top: 5px solid #042D29; }
.modal-apertura-header { text-align: center; margin-bottom: 24px; }
.icon-lock { margin-bottom: 12px; }
.modal-apertura-header h3 { margin: 0; font-size: 18px; color: #042D29; font-weight: 700; }
.modal-apertura-header p { margin: 8px 0 0; font-size: 13px; color: #6B7280; line-height: 1.4; }
.modal-apertura-body { display: flex; flex-direction: column; gap: 12px; }
.modal-apertura-body label { font-size: 12px; font-weight: 600; color: #4B5563; text-transform: uppercase; }
.input-bs-wrapper { position: relative; }
.currency-label { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; font-weight: 600; color: #042D29; }
.input-bs-wrapper input { width: 100%; padding: 12px 14px 12px 46px; border: 1.5px solid #D1D5DB; border-radius: 10px; font-size: 16px; font-weight: 700; color: #042D29; outline: none; box-sizing: border-box; }
.btn-confirmar-apertura { margin-top: 8px; padding: 14px; background: #042D29; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; }
.btn-confirmar-apertura:hover { background: #0b4640; }

/* NUEVO DISEÑO: BANNER DEL CARRITO ESTILO VERDE CLARO VERIFICADO */
.floating-cart-trigger-verificado { 
  background-color: #E8F5E9; 
  border: 1.5px solid #A5D6A7; 
  color: #1B5E20; 
  padding: 14px 24px; 
  border-radius: 12px; 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  cursor: pointer; 
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.08); 
  transition: all 0.25s ease;
}

.floating-cart-trigger-verificado:hover:not(.disabled-trigger) {
  background-color: #C8E6C9;
  box-shadow: 0 6px 16px rgba(76, 175, 80, 0.15);
}

.trigger-left { display: flex; align-items: center; gap: 12px; }

.badge-contador { 
  background: #2E7D32; 
  color: white; 
  font-size: 13px; 
  font-weight: 700; 
  padding: 2px 10px; 
  border-radius: 20px; 
  font-family: 'Inter', monospace;
}

.trigger-title { font-size: 14px; font-weight: 700; letter-spacing: 0.2px; }
.trigger-right { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; }

.arrow-icon { transition: transform 0.2s ease; }
.floating-cart-trigger-verificado:hover .arrow-icon { transform: translateX(3px); }

/* Historial */
.historial-seccion-card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden; }
.tabs-header { border-bottom: 1px solid #E5E7EB; background: #FAFAFA; padding: 0 24px; }
.tabs-header button { background: none; border: none; padding: 16px 4px; font-size: 14px; font-weight: 600; color: #042D29; cursor: pointer; border-bottom: 2px solid #042D29; }
.tabs-content { padding: 24px; }
.busqueda-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px; }
.search-input-group { display: flex; flex-direction: column; gap: 4px; }
.search-input-group label { font-size: 12px; font-weight: 600; color: #4B5563; text-transform: uppercase; }
.search-input-group input { padding: 10px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 13px; outline: none; }
.tabla-contenedor { overflow-x: auto; }
.tabla-contenido { width: 100%; border-collapse: collapse; text-align: left; }
.tabla-contenido th { padding: 12px 16px; font-size: 12px; font-weight: 600; color: #4B5563; background: #F9F9F7; text-transform: uppercase; }
.tabla-contenido td { padding: 14px 16px; border-bottom: 1px solid #F3F4F6; font-size: 13px; }
.recibo-nro { display: block; font-weight: 600; color: #042D29; }
.recibo-fecha { font-size: 11px; color: #929079; }
.cliente-nombre { font-weight: 500; color: #1F2937; }
.metodo-badge { background: #E5E7EB; color: #1F2937; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.td-monto { text-align: right; font-weight: 600; color: #042D29; font-family: 'Inter', monospace; }
.text-center-empty { text-align: center; color: #929079; padding: 30px 0; font-style: italic; }
.text-right { text-align: right; }
</style>
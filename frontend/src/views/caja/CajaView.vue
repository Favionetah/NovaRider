<template>
  <div class="container-fluid py-3 bg-neutral min-vh-100 font-sans position-relative">
    
    <div v-if="!cajaAbierta" class="modal-apertura-overlay d-flex align-items-center justify-content-center">
      <div class="card border-0 shadow-lg bg-white p-4 rounded-3 text-center" style="width: 360px; animation: modalSlideUp 0.25s ease;">
        <div class="bg-neutral rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-dark" style="width: 52px; height: 52px;">
          <i class="bi bi-door-open fs-4"></i>
        </div>
        <h5 class="fw-semibold text-dark mb-1 tracking-tight">Apertura de Jornada</h5>
        <p class="text-muted small mb-4">Ingrese el saldo inicial en efectivo para habilitar el módulo de ventas.</p>
        
        <div class="mb-4 text-start">
          <label class="form-label text-secondary small fw-medium mb-1.5">Monto Inicial en Caja (Bs.)</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-0 text-muted font-monospace px-2.5">Bs.</span>
            <input type="number" class="form-control py-2.5 bg-light border-0 fw-semibold font-monospace text-center" v-model.number="montoInicialLocal" placeholder="0.00" />
          </div>
        </div>

        <button class="btn btn-dark w-100 py-2.5 fw-medium rounded-2 transition-all d-flex align-items-center justify-content-center gap-2" @click="ejecutarApertura">
          <i class="bi bi-play-fill"></i> Inicializar Caja
        </button>
        
        <div class="mt-3 text-muted font-monospace" style="font-size: 0.65rem;">
          Status: {{ logsServidor }}
        </div>
      </div>
    </div>

    <div :class="{ 'blur-content': !cajaAbierta }">
      <div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom border-light-subtle">
        <div>
          <h5 class="fw-semibold text-dark mb-0 tracking-tight">Módulo de Ventas y Caja</h5>
          <p class="text-muted small mb-0" style="font-size: 0.75rem;">Gestión interna, arqueo diario e historial de facturación</p>
        </div>
        <button class="btn btn-outline-dark btn-sm d-flex align-items-center gap-2 rounded-2 px-3 transition-all" :disabled="!cajaAbierta" @click="toggleCarrito">
          <i class="bi bi-cart3"></i> 
          <span class="fw-medium">Ver Carrito</span>
          <span v-if="carrito && carrito.length > 0" class="badge bg-dark rounded-pill font-monospace" style="font-size: 0.7rem;">{{ carrito.length }}</span>
        </button>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-white rounded-3 kpi-card">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="bg-neutral rounded-3 p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-cash-coin fs-5 text-dark"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.05rem;">{{ totalVentasCalculado.toFixed(1) }} Bs.</h6>
                <small class="text-muted" style="font-size: 0.72rem;">Total Recaudado (Turno)</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-white rounded-3 kpi-card">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="bg-neutral rounded-3 p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-receipt-cutoff fs-5 text-dark"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.05rem;">{{ historialVentas.length }}</h6>
                <small class="text-muted" style="font-size: 0.72rem;">Comprobantes Emitidos</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-white rounded-3 kpi-card">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="bg-neutral rounded-3 p-2 text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-box-seam fs-5 text-dark"></i>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.05rem;">{{ totalItemsVendidos }}</h6>
                <small class="text-muted" style="font-size: 0.72rem;">Servicios / Productos</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-4 col-xl-3">
          <AperturaCierreCard :saldoSistema="saldoDigital" :cajaAbierta="cajaAbierta" :logsServidor="logsServidor" @onCerrarCaja="handleCerrarCaja" />
        </div>

        <div class="col-12 col-lg-8 col-xl-9">
          <RegistroVentasCard :cajaAbierta="cajaAbierta" @onAgregarItem="handleAgregarItem" />

          <div class="card border-0 shadow-sm bg-white rounded-3 mt-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h6 class="fw-semibold text-dark mb-0 tracking-tight">Consola de Transacciones e Historial Histórico</h6>
                  <small class="text-muted" style="font-size: 0.72rem;">Filtros dinámicos en tiempo real y estadísticas agregadas por periodos</small>
                </div>
              </div>
              
              <ul class="nav nav-tabs border-bottom-0 gap-2" style="font-size: 0.8rem;">
                <li class="nav-item">
                  <button class="nav-link border-0 rounded-2 py-2 px-3 transition-all d-flex align-items-center gap-2" :class="{ 'bg-dark text-white fw-medium': tabActiva === 'filtros', 'text-secondary bg-light': tabActiva !== 'filtros' }" @click="tabActiva = 'filtros'">
                    <i class="bi bi-search"></i> Búsqueda y Filtros
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link border-0 rounded-2 py-2 px-3 transition-all d-flex align-items-center gap-2" :class="{ 'bg-dark text-white fw-medium': tabActiva === 'reportes', 'text-secondary bg-light': tabActiva !== 'reportes' }" @click="tabActiva = 'reportes'">
                    <i class="bi bi-bar-chart-line"></i> Reportes por Mes / Año
                  </button>
                </li>
              </ul>
            </div>

            <div class="card-body p-4 pt-3">
              <div v-if="tabActiva === 'filtros'">
                <div class="row g-2 mb-3">
                  <div class="col-12 col-md-8">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-funnel"></i></span>
                      <input type="text" class="form-control bg-light border-0 py-2" v-model="criterioBusqueda" placeholder="Buscar por placa, cliente o producto..." style="font-size: 0.78rem;" />
                    </div>
                  </div>
                  <div class="col-12 col-md-4">
                    <input type="date" class="form-control form-control-sm bg-light border-0 py-2 font-monospace" v-model="filtroFecha" style="font-size: 0.78rem;" />
                  </div>
                </div>

                <div v-if="ventasFiltradas.length === 0" class="text-center py-4 text-muted border border-dashed rounded-3">
                  <i class="bi bi-filter-square fs-4 d-block mb-1 text-secondary"></i>
                  <span class="small">No se encontraron ventas que coincidan con los filtros aplicados.</span>
                </div>

                <div v-else class="table-responsive">
                  <table class="table table-sm align-middle mb-0" style="font-size: 0.8rem;">
                    <thead>
                      <tr class="text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;">
                        <th class="py-2">Recibo / Fecha</th>
                        <th class="py-2">Cliente / Motocicleta</th>
                        <th class="py-2">Concepto / Ítems</th>
                        <th class="py-2 text-end">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(venta, idx) in ventasFiltradas" :key="idx" class="border-bottom border-light-subtle">
                        <td>
                          <span class="font-monospace text-secondary fw-semibold d-block">#{{ venta.nroRecibo }}</span>
                          <small class="text-muted font-monospace" style="font-size: 0.68rem;">{{ vFechaFormato(venta.fecha) }}</small>
                        </td>
                        <td>
                          <span class="fw-medium text-dark d-block">{{ venta.cliente }}</span>
                          <small class="text-muted font-monospace text-uppercase" style="font-size: 0.7rem;">{{ venta.placa }}</small>
                        </td>
                        <td class="text-muted small">
                          <span class="d-block text-dark fw-medium text-truncate" style="max-width: 250px;">{{ venta.concepto }}</span>
                          <span class="text-secondary" style="font-size: 0.7rem;">Prod: {{ venta.productosConcat }}</span>
                        </td>
                        <td class="text-end fw-bold font-monospace text-dark">{{ venta.total.toFixed(1) }} Bs.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div v-if="tabActiva === 'reportes'">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded-3 border-start border-3 border-primary">
                      <span class="text-secondary small fw-medium d-block mb-1">Acumulado Mes Actual</span>
                      <h5 class="fw-bold text-dark font-monospace mb-1">{{ reporteAgregado.mesMonto.toFixed(1) }} Bs.</h5>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">
                        <i class="bi bi-cart-check"></i> {{ reporteAgregado.mesCantidad }} Órdenes liquidadas este mes
                      </small>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded-3 border-start border-3 border-success">
                      <span class="text-secondary small fw-medium d-block mb-1">Acumulado Año Actual</span>
                      <h5 class="fw-bold text-dark font-monospace mb-1">{{ reporteAgregado.anioMonto.toFixed(1) }} Bs.</h5>
                      <small class="text-muted d-block" style="font-size: 0.72rem;">
                        <i class="bi bi-graph-up-arrow"></i> {{ reporteAgregado.anioCantidad }} Órdenes totales anuales
                      </small>
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <span class="text-dark small fw-semibold d-block mb-2">Resumen de Rendimiento Mensual</span>
                  <div class="table-responsive">
                    <table class="table table-sm align-middle bg-light rounded-2 overflow-hidden mb-0" style="font-size: 0.78rem;">
                      <thead>
                        <tr class="text-muted text-uppercase bg-light-subtle" style="font-size: 0.65rem;">
                          <th class="p-2 ps-3">Periodo / Gestión</th>
                          <th class="p-2 text-center">Volumen de Ventas</th>
                          <th class="p-2 text-end pe-3">Total Neto Recaudado</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-bottom border-white">
                          <td class="ps-3 text-dark fw-medium">Junio 2026</td>
                          <td class="text-center font-monospace">{{ reporteAgregado.mesCantidad }}</td>
                          <td class="text-end pe-3 font-monospace fw-bold text-dark">{{ reporteAgregado.mesMonto.toFixed(1) }} Bs.</td>
                        </tr>
                        <tr>
                          <td class="ps-3 text-muted">Meses Previos (Histórico)</td>
                          <td class="text-center font-monospace text-muted">0</td>
                          <td class="text-end pe-3 font-monospace text-muted">0.0 Bs.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <CarritoSidebar :isOpen="carritoAbierto" :items="carrito" @onClose="toggleCarrito" @onRemoveItem="handleRemoverItem" @onProcesarVenta="handleProcesarVenta" />
<TicketModal 
  :show="mostrarTicket" 
  :datosTicket="ticketData" 
  @onClose="mostrarTicket = false" 
/>/>  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AperturaCierreCard from './AperturaCierreCard.vue'
import RegistroVentasCard from './RegistroVentasCard.vue'
import CarritoSidebar from './CarritoSidebar.vue'
import TicketModal from './TicketModal.vue'

// Controles de estado básicos
const cajaAbierta = ref(false)
const saldoDigital = ref(0)
const montoInicialLocal = ref(200)

// DECLARACIÓN DE VARIABLES DE RESPALDO (Evitan errores de compilación)
const montoInicial = ref(200)
const saldoEfectivo = ref(200)
const carrito = ref([])
const carritoAbierto = ref(false)
const mostrarTicket = ref(false)
const ticketData = ref(null)
const logsServidor = ref('Sincronizado con el servidor')

// Controles de Navegación del Panel de Auditoría
const tabActiva = ref('filtros')
const criterioBusqueda = ref('')
const filtroFecha = ref('')

// HISTORIAL PERSISTENTE CONECTADO DIRECTAMENTE A LARAVEL
const historialVentas = ref([])

onMounted(async () => {
  // Inicialización y carga de datos inicial de la BD
  await cargarHistorialDesdeBD()
})

const cargarHistorialDesdeBD = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/taller/caja/ventas')
    if (!res.ok) throw new Error('Error al conectar con el servidor')
    
    const data = await res.json()
    
    if (Array.isArray(data)) {
      historialVentas.value = data.map(v => {
        let fechaFormateada = new Date().toISOString().split('T')[0];
        if (v.fecha_hora && typeof v.fecha_hora === 'string') {
          fechaFormateada = v.fecha_hora.split(' ')[0];
        }

        return {
          nroRecibo: v.id_venta,
          fecha: fechaFormateada,
          cliente: id_cliente,
          placa:"",
          concepto: 'Venta de Caja',
          productosConcat: v.metodo_pago || 'Efectivo',
          itemsCount: 1,
          total: parseFloat(v.total || 0)
        };
      })
    }
  } catch (err) {
    logsServidor.value = `Historial: ${err.message}`
  }
}

// KPIs del Turno Actual
const totalVentasCalculado = computed(() => {
  const list = Array.isArray(historialVentas.value) ? historialVentas.value : []
  return list.reduce((acc, v) => acc + (parseFloat(v.total) || 0), 0)
})

const totalItemsVendidos = computed(() => {
  const list = Array.isArray(historialVentas.value) ? historialVentas.value : []
  return list.reduce((acc, v) => acc + (parseInt(v.itemsCount) || 0), 0)
})

// MOTOR DE BÚSQUEDA MULTI-CRITERIO
const ventasFiltradas = computed(() => {
  const list = Array.isArray(historialVentas.value) ? historialVentas.value : []
  return list.filter(venta => {
    const cumpleFecha = filtroFecha.value ? venta.fecha === filtroFecha.value : true
    const txt = criterioBusqueda.value.toLowerCase().trim()
    const cumpleTexto = !txt ? true : (
      (venta.cliente || '').toLowerCase().includes(txt) ||
      (venta.placa || '').toLowerCase().includes(txt) ||
      (venta.concepto || '').toLowerCase().includes(txt) ||
      (venta.productosConcat || '').toLowerCase().includes(txt)
    )
    return cumpleFecha && cumpleTexto
  })
})

// ACUMULADOR INTELIGENTE POR PERIODOS (MES / AÑO)
const reporteAgregado = computed(() => {
  let mesMonto = 0, mesCantidad = 0
  let anioMonto = 0, anioCantidad = 0
  
  const hoy = new Date()
  const mesActual = String(hoy.getMonth() + 1).padStart(2, '0')
  const anioActual = String(hoy.getFullYear())

  const list = Array.isArray(historialVentas.value) ? historialVentas.value : []
  list.forEach(venta => {
    if (venta.fecha) {
      const [vAnio, vMes] = venta.fecha.split('-')
      if (vAnio === anioActual) {
        anioMonto += (venta.total || 0)
        anioCantidad++
        if (vMes === mesActual) {
          mesMonto += (venta.total || 0)
          mesCantidad++
        }
      }
    }
  })

  return { mesMonto, mesCantidad, anioMonto, anioCantidad }
})

const vFechaFormato = (fStr) => {
  if (!fStr) return ''
  const parts = fStr.split('-')
  if (parts.length !== 3) return fStr
  return `${parts[2]}/${parts[1]}/${parts[0]}`
}

const toggleCarrito = () => { if (cajaAbierta.value) carritoAbierto.value = !carritoAbierto.value }

const ejecutarApertura = async () => {
  if (montoInicialLocal.value < 0) return
  logsServidor.value = 'Registrando apertura en servidor...'
  try {
    await fetch('http://127.0.0.1:8000/taller/caja/apertura', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ monto_inicial: montoInicialLocal.value })
    })
    
    cajaAbierta.value = true
    montoInicial.value = montoInicialLocal.value
    saldoEfectivo.value = montoInicialLocal.value
    
    localStorage.setItem('caja_abierta', 'true')
    localStorage.setItem('monto_inicial', montoInicialLocal.value.toString())
    logsServidor.value = 'Jornada iniciada con éxito.'
    await cargarHistorialDesdeBD()
  } catch (err) {
    cajaAbierta.value = true
    montoInicial.value = montoInicialLocal.value
    saldoEfectivo.value = montoInicialLocal.value
    logsServidor.value = 'Apertura forzada localmente.'
  }
}

const handleCerrarCaja = async (datosCierre) => {
  logsServidor.value = 'Procesando arqueo...'
  try {
    await fetch('http://127.0.0.1:8000/taller/caja/cierre', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        monto_final_fisico: datosCierre.fisico,
        monto_digital_sistema: saldoDigital.value,
        observaciones: datosCierre.observaciones
      })
    })
    logsServidor.value = 'Arqueo consolidado'
    cajaAbierta.value = false
    saldoDigital.value = 0
    carrito.value = []
    historialVentas.value = []
  } catch (err) {
    logsServidor.value = `Error: ${err.message}`
  }
}

const handleAgregarItem = (item) => { 
  if (!Array.isArray(carrito.value)) carrito.value = []
  carrito.value.push({ id: Date.now(), ...item }) 
}

const handleRemoverItem = (id) => { 
  if (Array.isArray(carrito.value)) {
    carrito.value = carrito.value.filter(i => i.id !== id) 
  }
}



// ==========================================
// 🌟 FUNCIÓN CORREGIDA: PROCESAR NUEVA VENTA
// ==========================================
// ==========================================
// 🌟 FUNCIÓN CORREGIDA: PROCESAR NUEVA VENTA
// ==========================================
const handleProcesarVenta = async () => {
  console.log("🚀 Iniciando procesamiento de venta...");

  // 1. CAPTURAR EL TOTAL REAL DEL CARRITO
  let totalSeguro = 0;
  try {
    if (typeof totalCalculadoCarrito !== 'undefined' && totalCalculadoCarrito.value) {
      totalSeguro = parseFloat(totalCalculadoCarrito.value);
    } else if (typeof totalNeto !== 'undefined' && totalNeto.value) {
      totalSeguro = parseFloat(totalNeto.value);
    }
  } catch (e) {
    totalSeguro = 0;
  }

  // 2. EXTRAER LOS PRODUCTOS DEL CARRITO (Usando tus propiedades 'concepto' y 'precio')
  let itemsParaTicket = [];
  try {
    if (typeof carrito !== 'undefined' && carrito.value && carrito.value.length > 0) {
      itemsParaTicket = carrito.value.map((item, index) => {
        return {
          id: item.id || index || Math.random(),
          nombreItem: item.concepto || 'Producto/Servicio', 
          precioItem: parseFloat(item.precio || 0)         
        };
      });
    }
  } catch (e) {
    console.error("Error al procesar artículos:", e);
    itemsParaTicket = [];
  }

  // Capturamos lo que escribiste en el formulario en variables limpias
  const clienteReal = (typeof formularioSeleccion !== 'undefined' && formularioSeleccion.value?.cliente) || 'Cliente General';
  const placaReal = (typeof formularioSeleccion !== 'undefined' && formularioSeleccion.value?.placa) || 'S/P';
  const conceptoReal = itemsParaTicket.length > 0 ? itemsParaTicket.map(i => i.nombreItem).join(', ') : 'Venta de Caja';

  let idTicketFinal = `REC-${Math.floor(Math.random() * 90000) + 10000}`;

  // 3. ENVIAR LOS DATOS DEL FORMULARIO A TU BACKEND LARAVEL
  try {
    const params = new URLSearchParams();
    params.append('id_cliente', '1');  
    params.append('id_empleado', '1'); 
    params.append('id_caja', '1');     
    params.append('subtotal', totalSeguro.toString());
    params.append('descuento', '0');
    params.append('total', totalSeguro.toString());
    params.append('metodo_pago', 'Efectivo');
    
    // 🌟 ENVIAMOS EL CLIENTE, CONCEPTO Y PLACA REALES A LARAVEL PARA QUE SE GUARDEN EN LA BD
    params.append('cliente', clienteReal);
    params.append('placa', placaReal);
    params.append('concepto', conceptoReal);

    const res = await fetch('http://127.0.0.1:8000/taller/caja/recibos', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json'
      },
      body: params
    });

    if (res.ok) {
      const data = await res.json();
      if (data && data.id) {
        idTicketFinal = `REC-${data.id}`;
      }
    }
  } catch (err) {
    console.warn('Conexión local activa.');
  }

  // 4. PASAR LOS DATOS REALES AL COMPROBANTE EN PANTALLA
  try {
    if (ticketData !== null) {
      ticketData.value = {
        nroRecibo: idTicketFinal,
        items: itemsParaTicket, 
        total: totalSeguro,
        metodo_pago: 'Efectivo',
        cliente: clienteReal,  // Dinámico real
        placa: placaReal,      // Dinámico real
        concepto: conceptoReal
      };
    }

    mostrarTicket.value = true;

  } catch (errorRender) {
    console.error('Error al renderizar el ticket modal:', errorRender);
  }

  // 5. LIMPIAR CARRITO Y SOLICITAR REFRESCO AUTOMÁTICO A LARAVEL
  try {
    if (carrito && carrito.value) {
      carrito.value = [];
    }
    if (carritoAbierto !== undefined && carritoAbierto !== null) {
      carritoAbierto.value = false;
    }
    
    // Forzamos a que vuelva a leer la Base de Datos para rellenar la lista de abajo
    if (typeof obtenerVentas === 'function') {
      obtenerVentas();
    }
  } catch (e) {
    console.error("Error al reestablecer estados:", e);
  }
};

// ==========================================
// 🌟 FUNCIÓN PARA VER DETALLES DESDE EL HISTORIAL
// ==========================================
const verRecibo = (venta) => {
  console.log("Abriendo ticket del historial para la fila:", venta);
  
  ticketData.value = {
    nroRecibo: venta.nro_factura || `REC-${venta.id_venta}`,
    items: [
      {
        id: venta.id_venta || 1,
        nombreItem: venta.concepto || `Venta Registrada (#${venta.nro_factura || venta.id_venta})`,
        precioItem: parseFloat(venta.total || 0)
      }
    ],
    total: parseFloat(venta.total || 0),
    metodo_pago: venta.metodo_pago || 'Efectivo',
    cliente: venta.cliente || venta.nombre_cliente || 'Cliente General',
    placa: venta.placa || 'S/P',
    concepto: venta.concepto || 'Venta de Caja'
  };
  
  mostrarTicket.value = true;
};

const cerrarTicket = () => { 
  mostrarTicket.value = false; 
  ticketData.value = null; 
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
.font-sans { font-family: 'Inter', sans-serif; }
.bg-neutral { background-color: #fafafa !important; }
.tracking-tight { letter-spacing: -0.02em; }
.transition-all { transition: all 0.2s ease; }
.kpi-card { border-left: 3px solid #1a1a1a !important; }

.modal-apertura-overlay {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.15); z-index: 3000; backdrop-filter: blur(4px);
}
</style>
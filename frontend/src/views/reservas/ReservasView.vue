<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useReservasStore } from '@/stores/reservas'
import { useToastStore } from '@/stores/toast'
import ReservaFormModal from './ReservaFormModal.vue'
import EnvioFormModal from './EnvioFormModal.vue'

const store = useReservasStore()
const toast = useToastStore()

const busqueda = ref('')
const filtroEstado = ref('')
const mostrarForm = ref(false)
const mostrarEnvio = ref(false)
const reservaEnvio = ref(null)
const convertirModal = ref(false)
const reservaConvertir = ref(null)
const metodoPago = ref('')
const descuento = ref(0)
const detalleExpandido = ref(null)

const tabActivo = ref('activas')
const fechaCancelDesde = ref('')
const fechaCancelHasta = ref('')
const cancelarModal = ref(false)
const reservaCancelar = ref(null)

onMounted(async () => {
  await store.listar()
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async (val) => {
  if (val === 'canceladas') {
    await store.listarCanceladas()
  } else {
    busqueda.value = ''
    filtroEstado.value = ''
    fechaCancelDesde.value = ''
    fechaCancelHasta.value = ''
    await store.listar()
  }
  await nextTick()
  animarEntrada()
})

watch([busqueda, filtroEstado], async () => {
  if (tabActivo.value !== 'activas') return
  const params = {}
  if (filtroEstado.value) params.estado = filtroEstado.value
  if (busqueda.value) params.busqueda = busqueda.value
  await store.listar(params)
  await nextTick()
  animarEntrada()
})

watch([busqueda, fechaCancelDesde, fechaCancelHasta], async () => {
  if (tabActivo.value !== 'canceladas') return
  const params = {}
  if (busqueda.value) params.busqueda = busqueda.value
  if (fechaCancelDesde.value) params.fecha_desde = fechaCancelDesde.value
  if (fechaCancelHasta.value) params.fecha_hasta = fechaCancelHasta.value
  await store.listarCanceladas(params)
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.toolbar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.from('.tabla-contenido tbody tr', { y: 10, opacity: 0, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
}

function animarFilas() {
  gsap.from(
    '.tabla-contenido tbody tr:not(.fila-detalle)',
    { y: 10, opacity: 0, duration: 0.25, stagger: 0.04, ease: 'power2.out' }
  )
}

const estados = ['pendiente', 'completada', 'enviado']

function abrirForm() {
  mostrarForm.value = true
}

function cerrarForm() {
  mostrarForm.value = false
}

function onReservaGuardada() {
  mostrarForm.value = false
  toast.show('Reserva creada exitosamente', 'success')
  nextTick(() => animarFilas())
}

function toggleDetalle(id) {
  detalleExpandido.value = detalleExpandido.value === id ? null : id
}

function abrirEnvio(reserva) {
  reservaEnvio.value = reserva
  mostrarEnvio.value = true
}

function cerrarEnvio() {
  mostrarEnvio.value = false
  reservaEnvio.value = null
}

function onEnvioGuardado() {
  mostrarEnvio.value = false
  reservaEnvio.value = null
  toast.show('Envío registrado exitosamente', 'success')
  nextTick(() => animarFilas())
}

function abrirCancelar(reserva) {
  reservaCancelar.value = reserva
  cancelarModal.value = true
}

function cerrarCancelar() {
  cancelarModal.value = false
  reservaCancelar.value = null
}

async function confirmarCancelar() {
  await store.cancelar(reservaCancelar.value.id_reserva)
  toast.show('Reserva cancelada', 'success')
  cerrarCancelar()
  nextTick(() => animarFilas())
}

function abrirConvertir(reserva) {
  reservaConvertir.value = reserva
  metodoPago.value = 'QR'
  descuento.value = 0
  convertirModal.value = true
}

function cerrarConvertir() {
  convertirModal.value = false
  reservaConvertir.value = null
}

async function confirmarConvertir() {
  if (!metodoPago.value) return
  const data = { metodo_pago: metodoPago.value }
  if (descuento.value > 0) data.descuento = descuento.value
  const res = await store.convertirVenta(reservaConvertir.value.id_reserva, data)
  toast.show(res.message || 'Reserva convertida a venta exitosamente', 'success')
  cerrarConvertir()
  nextTick(() => animarFilas())
}

function estadoClase(estado) {
  const map = { pendiente: 'badge-pendiente', completada: 'badge-completada', enviado: 'badge-enviado', cancelada: 'badge-cancelada' }
  return map[estado] || ''
}
</script>

<template>
  <div class="reservas-page">
    <header class="page-header">
      <h1>Reservas y Envíos</h1>
      <button v-if="tabActivo === 'activas'" class="btn-nuevo" @click="abrirForm">
        <svg viewBox="0 0 24 24" fill="none" class="icon-plus">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        Nueva Reserva
      </button>
    </header>

    <p v-if="store.error" class="mensaje-error">{{ store.error }}</p>

    <div class="content-card">
      <div class="tabs">
        <button
          class="tab"
          :class="{ active: tabActivo === 'activas' }"
          @click="tabActivo = 'activas'"
        >
          <span class="tab-label">Activas</span>
          <span class="tab-badge" :class="tabActivo === 'activas' ? 'badge-active' : 'badge-inactive'">
            {{ store.items.length }}
          </span>
        </button>
        <button
          class="tab"
          :class="{ active: tabActivo === 'canceladas' }"
          @click="tabActivo = 'canceladas'"
        >
          <span class="tab-label">Canceladas</span>
          <span class="tab-badge" :class="tabActivo === 'canceladas' ? 'badge-active' : 'badge-inactive'">
            {{ store.itemsCanceladas.length }}
          </span>
        </button>
      </div>

      <div v-if="tabActivo === 'activas'">
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
            placeholder="Buscar por cliente o # reserva..."
          />
        </div>

        <select v-model="filtroEstado" class="filtro-select">
          <option value="">Todos los estados</option>
          <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
        </select>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-cli">Cliente</th>
              <th class="col-fecha">Solicitud</th>
              <th class="col-prods">Productos</th>
              <th class="col-adelanto">Adelanto</th>
              <th class="col-estado">Estado</th>
              <th class="col-envio">Envío</th>
              <th class="col-acc">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="r in store.items" :key="r.id_reserva">
            <tr>
              <td class="col-id">{{ r.id_reserva }}</td>
              <td class="col-cli">{{ r.cliente?.nombre_completo || '—' }}</td>
              <td class="col-fecha">{{ r.fecha_solicitud }}</td>
              <td class="col-prods">{{ r.detalles?.length || 0 }}</td>
              <td class="col-adelanto">
                <template v-if="r.monto_adelanto > 0">
                  Bs {{ Number(r.monto_adelanto).toFixed(2) }}
                  <span class="metodo-pago">{{ r.adelanto_metodo_pago }}</span>
                </template>
                <span v-else class="sin-adelanto">—</span>
              </td>
              <td class="col-estado">
                <span class="estado-badge" :class="estadoClase(r.estado)">{{ r.estado }}</span>
              </td>
              <td class="col-envio">
                <template v-if="r.envio">
                  <span class="envio-info">{{ r.envio.empresa_transporte }}</span>
                  <span class="envio-guia">Guía: {{ r.envio.nro_guia }}</span>
                </template>
                <span v-else class="sin-envio">—</span>
              </td>
              <td class="col-acc">
                <button
                  class="btn-accion"
                  :class="{ 'btn-detalle-activo': detalleExpandido === r.id_reserva }"
                  @click="toggleDetalle(r.id_reserva)"
                  title="Detalle"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" />
                    <path d="M21 12c0 1.5-4 7-9 7s-9-5.5-9-7 4-7 9-7 9 5.5 9 7z" stroke="currentColor" stroke-width="1.5" />
                  </svg>
                </button>

                <button
                  v-if="r.estado === 'pendiente'"
                  class="btn-accion btn-convertir"
                  @click="abrirConvertir(r)"
                  title="Convertir a venta"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M12 2v4M12 18v4M4 12H2M22 12h-2M4 4l16 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M4 12a8 8 0 1 0 16 0 8 8 0 0 0-16 0z" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                  </svg>
                </button>

                <button
                  v-if="!r.envio && (r.estado === 'completada' || r.estado === 'pendiente')"
                  class="btn-accion btn-enviar"
                  @click="abrirEnvio(r)"
                  title="Registrar envío"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M3 12h18M12 3v18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M5 12a7 7 0 1 0 14 0 7 7 0 0 0-14 0z" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                  </svg>
                </button>

                <button
                  v-if="r.estado === 'pendiente'"
                  class="btn-accion btn-eliminar"
                  @click="abrirCancelar(r)"
                  title="Cancelar"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="detalleExpandido === r.id_reserva" class="fila-detalle">
              <td colspan="8">
                <div class="detalle-contenido">
                  <div class="detalle-productos">
                    <div v-for="d in r.detalles || []" :key="d.id_detalle_reserva" class="detalle-item">
                      <span class="detalle-item-nombre">{{ d.producto?.nombre || '—' }}</span>
                      <span class="detalle-item-cant">x{{ d.cantidad_reservada }}</span>
                      <span class="detalle-item-precio">Bs {{ Number(d.producto?.precio_venta).toFixed(2) }} c/u</span>
                    </div>
                  </div>
                  <div class="detalle-meta">
                    <span v-if="r.fecha_expiracion"><strong>Expira:</strong> {{ r.fecha_expiracion }}</span>
                    <span v-if="r.departamento_origen"><strong>Origen:</strong> {{ r.departamento_origen }}</span>
                    <span v-if="r.monto_adelanto > 0"><strong>Adelanto:</strong> Bs {{ Number(r.monto_adelanto).toFixed(2) }} ({{ r.adelanto_metodo_pago }})</span>
                  </div>
                </div>
              </td>
            </tr>
            </template>

            <tr v-if="store.items.length === 0">
              <td colspan="8" class="sin-datos">
                {{ busqueda || filtroEstado ? 'No se encontraron reservas' : 'No hay reservas registradas' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      </div>

      <div v-else>
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
              placeholder="Buscar por cliente o # reserva..."
            />
          </div>
          <div class="date-filter">
            <label class="date-filter-label">Desde</label>
            <input
              v-model="fechaCancelDesde"
              type="date"
              class="filtro-input"
            />
          </div>
          <div class="date-filter">
            <label class="date-filter-label">Hasta</label>
            <input
              v-model="fechaCancelHasta"
              type="date"
              class="filtro-input"
            />
          </div>
        </div>

        <div v-if="store.loadingCanceladas" class="cargando">Cargando reservas canceladas...</div>

        <div v-else class="tabla-wrapper">
          <table class="tabla-contenido">
            <thead>
              <tr>
                <th class="col-id">#</th>
                <th class="col-cli">Cliente</th>
                <th class="col-fecha">Solicitud</th>
                <th class="col-fecha">Cancelación</th>
                <th class="col-prods">Productos</th>
                <th class="col-acc">Acción</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="r in store.itemsCanceladas" :key="r.id_reserva">
              <tr>
                <td class="col-id">{{ r.id_reserva }}</td>
                <td class="col-cli">{{ r.cliente?.nombre_completo || '—' }}</td>
                <td class="col-fecha">{{ r.fecha_solicitud }}</td>
                <td class="col-fecha">{{ r.fechahoraA ? r.fechahoraA.substring(0, 10) : '—' }}</td>
                <td class="col-prods">{{ r.detalles?.length || 0 }}</td>
                <td class="col-acc">
                  <button
                    class="btn-accion"
                    :class="{ 'btn-detalle-activo': detalleExpandido === r.id_reserva }"
                    @click="toggleDetalle(r.id_reserva)"
                    title="Detalle"
                  >
                    <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                      <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" />
                      <path d="M21 12c0 1.5-4 7-9 7s-9-5.5-9-7 4-7 9-7 9 5.5 9 7z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                  </button>
                </td>
              </tr>

              <tr v-if="detalleExpandido === r.id_reserva" class="fila-detalle">
                <td colspan="6">
                  <div class="detalle-contenido">
                    <div class="detalle-productos">
                      <div v-for="d in r.detalles || []" :key="d.id_detalle_reserva" class="detalle-item">
                        <span class="detalle-item-nombre">{{ d.producto?.nombre || '—' }}</span>
                        <span class="detalle-item-cant">x{{ d.cantidad_reservada }}</span>
                        <span class="detalle-item-precio">Bs {{ Number(d.producto?.precio_venta).toFixed(2) }} c/u</span>
                      </div>
                    </div>
                    <div class="detalle-meta">
                      <span v-if="r.fecha_expiracion"><strong>Expira:</strong> {{ r.fecha_expiracion }}</span>
                      <span v-if="r.departamento_origen"><strong>Origen:</strong> {{ r.departamento_origen }}</span>
                      <span v-if="r.monto_adelanto > 0"><strong>Adelanto:</strong> Bs {{ Number(r.monto_adelanto).toFixed(2) }} ({{ r.adelanto_metodo_pago }})</span>
                    </div>
                  </div>
                </td>
              </tr>
              </template>

              <tr v-if="store.itemsCanceladas.length === 0">
                <td colspan="6" class="sin-datos">
                  {{ busqueda || fechaCancelDesde || fechaCancelHasta ? 'No se encontraron reservas canceladas con ese criterio' : 'No hay reservas canceladas' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <ReservaFormModal
      v-if="mostrarForm"
      :key="'reserva-' + mostrarForm"
      @cerrar="cerrarForm"
      @guardado="onReservaGuardada"
    />

    <EnvioFormModal
      v-if="mostrarEnvio"
      :key="'envio-' + mostrarEnvio"
      :reserva="reservaEnvio"
      @cerrar="cerrarEnvio"
      @guardado="onEnvioGuardado"
    />

    <div v-if="cancelarModal" class="modal-overlay">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h2>Cancelar Reserva</h2>
          <button class="btn-cerrar" @click="cerrarCancelar">&times;</button>
        </div>

        <div class="modal-body">
          <p class="convertir-info">
            &iquest;Est&aacute;s seguro de cancelar la reserva #{{ reservaCancelar?.id_reserva }} de
            <strong>{{ reservaCancelar?.cliente?.nombre_completo }}</strong>?
          </p>
          <p class="nota">Los productos reservados volver&aacute;n a estar disponibles en stock.</p>
        </div>

        <div class="modal-footer">
          <button class="btn-cancelar" @click="cerrarCancelar">Volver</button>
          <button class="btn-confirmar-eliminar" @click="confirmarCancelar">S&iacute;, cancelar</button>
        </div>
      </div>
    </div>

    <div v-if="convertirModal" class="modal-overlay">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h2>Convertir a Venta</h2>
          <button class="btn-cerrar" @click="cerrarConvertir">&times;</button>
        </div>

        <div class="modal-body">
          <p class="convertir-info">
            Reserva #{{ reservaConvertir?.id_reserva }} — {{ reservaConvertir?.cliente?.nombre_completo }}
          </p>

          <div class="form-group">
            <label>Método de Pago <span class="required">*</span></label>
            <select v-model="metodoPago">
              <option value="QR">QR</option>
              <option value="Efectivo">Efectivo</option>
            </select>
          </div>

          <div class="form-group">
            <label>Descuento</label>
            <input v-model.number="descuento" type="number" min="0" step="0.01" placeholder="0" />
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancelar" @click="cerrarConvertir">Cancelar</button>
          <button class="btn-guardar" @click="confirmarConvertir">Confirmar Venta</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reservas-page {
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

.btn-nuevo:hover { background: #052E2A; }

.icon-plus { width: 18px; height: 18px; }

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 16px;
}

.mensaje-exito {
  background: #F0FFF4;
  border-left: 3px solid #042D29;
  color: #042D29;
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

.tab:hover { color: #042D29; }

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
  align-items: center;
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

.search-input::placeholder { color: #929079; }

.filtro-select {
  padding: 10px 32px 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  background: #FFFFFF;
  cursor: pointer;
  min-width: 160px;
}

.filtro-select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.filtro-input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  background: #FFFFFF;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.filtro-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.date-filter {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.date-filter-label {
  font-size: 11px;
  font-weight: 500;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cargando {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}

.tabla-wrapper { overflow-x: auto; }

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

.tabla-contenido tr:last-child td { border-bottom: none; }

.tabla-contenido tbody tr { transition: background 0.15s ease; }
.tabla-contenido tbody tr:hover { background: #F9FAFB; }

.col-id { width: 50px; }
.col-cli { min-width: 160px; }
.col-fecha { min-width: 100px; }
.col-prods { width: 80px; text-align: center; }
.col-adelanto { min-width: 120px; }
.col-estado { min-width: 100px; }
.col-envio { min-width: 140px; }
.col-acc { min-width: 140px; white-space: nowrap; }

.estado-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-pendiente { background: #FFF8E1; color: #B8860B; }
.badge-completada { background: #E8F5E9; color: #2E7D32; }
.badge-enviado { background: #E3F2FD; color: #1565C0; }
.badge-cancelada { background: #FFEBEE; color: #C62828; }

.metodo-pago {
  display: inline-block;
  margin-left: 4px;
  padding: 2px 6px;
  background: rgba(4, 45, 41, 0.08);
  border-radius: 4px;
  font-size: 11px;
  font-weight: 500;
  color: #042D29;
}

.sin-adelanto, .sin-envio { color: #929079; }

.envio-info {
  display: block;
  font-weight: 500;
  font-size: 13px;
}

.envio-guia {
  display: block;
  font-size: 11px;
  color: #929079;
}

.btn-accion {
  display: inline-flex;
  align-items: center;
  padding: 6px 8px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #FFFFFF;
  color: #042D29;
  margin: 0 2px;
}

.icon-accion { width: 16px; height: 16px; }

.btn-accion:hover { border-color: #042D29; background: rgba(4, 45, 41, 0.05); }
.btn-convertir { color: #2E7D32; }
.btn-convertir:hover { border-color: #2E7D32; background: rgba(46, 125, 50, 0.05); }
.btn-enviar { color: #1565C0; }
.btn-enviar:hover { border-color: #1565C0; background: rgba(21, 101, 192, 0.05); }
.btn-eliminar { color: #741102; }
.btn-eliminar:hover { border-color: #741102; background: rgba(116, 17, 2, 0.05); }

.btn-detalle-activo {
  border-color: #042D29;
  background: rgba(4, 45, 41, 0.1);
  color: #042D29;
}

.page-header, .toolbar, .tabla-wrapper { opacity: 0; }

.fila-detalle td {
  padding: 0;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.detalle-contenido {
  padding: 16px 24px 16px 56px;
}

.detalle-productos {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

.detalle-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
}

.detalle-item-nombre {
  flex: 1;
  font-weight: 500;
  color: #1F2937;
}

.detalle-item-cant {
  color: #929079;
  min-width: 40px;
}

.detalle-item-precio {
  color: #042D29;
  font-weight: 600;
  min-width: 90px;
  text-align: right;
}

.detalle-meta {
  display: flex;
  gap: 20px;
  font-size: 12px;
  color: #929079;
}

.detalle-meta strong {
  color: #1F2937;
}

.sin-datos {
  text-align: center;
  color: #929079;
  padding: 40px;
  font-size: 14px;
}

/* Convertir modal */
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
  max-width: 720px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-sm { max-width: 440px; }

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h2 { font-size: 18px; font-weight: 600; color: #042D29; }

.btn-cerrar {
  background: none;
  border: none;
  font-size: 24px;
  color: #929079;
  cursor: pointer;
  line-height: 1;
}

.btn-cerrar:hover { color: #741102; }

.modal-body { padding: 24px; }

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 16px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.required { color: #741102; }

.form-group input,
.form-group select {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.convertir-info {
  font-size: 14px;
  color: #1F2937;
  margin-bottom: 16px;
  padding: 10px 14px;
  background: #F9FAFB;
  border-radius: 8px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #E5E7EB;
}

.btn-cancelar {
  padding: 10px 20px;
  background: #FFFFFF;
  color: #929079;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancelar:hover { border-color: #929079; color: #1F2937; }

.btn-guardar {
  padding: 10px 24px;
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

.btn-guardar:hover { background: #052E2A; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-confirmar-eliminar {
  padding: 10px 24px;
  background: #741102;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-confirmar-eliminar:hover { background: #8C1503; }

.nota {
  font-size: 13px;
  color: #929079;
  font-style: italic;
  margin-top: 8px;
}
</style>

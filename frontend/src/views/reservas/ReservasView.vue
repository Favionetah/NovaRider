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
const mostrarConfirmarCancelar = ref(false)
const reservaCancelar = ref(null)
const detalleExpandido = ref(null)
const cerrandoId = ref(null)

onMounted(async () => {
  await store.listar()
  await nextTick()
  animarEntrada()
})

watch([busqueda, filtroEstado], async () => {
  const params = {}
  if (filtroEstado.value) params.estado = filtroEstado.value
  if (busqueda.value) params.busqueda = busqueda.value
  await store.listar(params)
  await nextTick()
  animarEntrada()
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.toolbar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.fromTo('.tabla-contenido tbody tr', { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
}

const estados = ['pendiente', 'completada', 'enviado', 'cancelada']

function abrirForm() {
  mostrarForm.value = true
}

function cerrarForm() {
  mostrarForm.value = false
}

function toggleDetalle(id) {
  const fila = document.querySelector(`tr[data-detalle="${id}"]`)

  if (detalleExpandido.value === id) {
    cerrandoId.value = id
    if (fila) {
      gsap.to(fila, {
        height: 0, opacity: 0, duration: 0.25, ease: 'power2.in',
        onComplete: () => {
          cerrandoId.value = null
          detalleExpandido.value = null
        }
      })
    } else {
      cerrandoId.value = null
      detalleExpandido.value = null
    }
  } else {
    detalleExpandido.value = id
    nextTick(() => {
      const nuevaFila = document.querySelector(`tr[data-detalle="${id}"]`)
      if (nuevaFila) {
        gsap.fromTo(nuevaFila,
          { height: 0, opacity: 0 },
          { height: 'auto', opacity: 1, duration: 0.3, ease: 'power3.out' }
        )
        gsap.fromTo(nuevaFila.querySelectorAll('tbody tr'),
          { x: -10, opacity: 0 },
          { x: 0, opacity: 1, duration: 0.2, stagger: 0.05, ease: 'power2.out', delay: 0.1 }
        )
      }
    })
  }
}

function abrirEnvio(reserva) {
  reservaEnvio.value = reserva
  mostrarEnvio.value = true
}

function cerrarEnvio() {
  mostrarEnvio.value = false
  reservaEnvio.value = null
}

async function ejecutarCancelar() {
  await store.cancelar(reservaCancelar.value.id_reserva)
  toast.show('Reserva cancelada correctamente')
  cerrarConfirmarCancelar()
}

function confirmarCancelar(reserva) {
  reservaCancelar.value = reserva
  mostrarConfirmarCancelar.value = true
}

function cerrarConfirmarCancelar() {
  mostrarConfirmarCancelar.value = false
  reservaCancelar.value = null
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
  await store.convertirVenta(reservaConvertir.value.id_reserva, data)
  toast.show('Reserva convertida a venta correctamente')
  cerrarConvertir()
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
      <button class="btn-nuevo" @click="abrirForm">
        <svg viewBox="0 0 24 24" fill="none" class="icon-plus">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        Nueva Reserva
      </button>
    </header>

    <p v-if="store.error" class="mensaje-error">{{ store.error }}</p>

    <div class="content-card">
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
                  ${{ Number(r.monto_adelanto).toFixed(2) }}
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
                <button class="btn-accion" :class="{ 'btn-detalle-active': detalleExpandido === r.id_reserva }" @click="toggleDetalle(r.id_reserva)" title="Detalle">
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
                  @click="confirmarCancelar(r)"
                  title="Cancelar"
                >
                  <svg viewBox="0 0 24 24" fill="none" class="icon-accion">
                    <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="detalleExpandido === r.id_reserva || cerrandoId === r.id_reserva" :data-detalle="r.id_reserva" class="detalle-fila">
              <td colspan="8">
                  <div class="detalle-contenido">
                  <div class="detalle-resumen">
                    <h4>Reserva #{{ r.id_reserva }}</h4>
                    <div class="detalle-resumen-campos">
                      <span><strong>Adelanto:</strong> Bs {{ Number(r.monto_adelanto || 0).toFixed(2) }}</span>
                      <span><strong>Método de pago:</strong> {{ r.adelanto_metodo_pago || '—' }}</span>
                      <span class="saldo-pendiente">
                        <strong>Saldo pendiente:</strong>
                        Bs {{ (r.detalles.reduce((s, d) => s + (d.producto?.precio_venta || 0) * d.cantidad_reservada, 0) - (r.monto_adelanto || 0)).toFixed(2) }}
                      </span>
                    </div>
                  </div>
                  <table class="tabla-detalle">
                    <thead>
                      <tr>
                        <th>Producto</th>
                        <th>Precio Unit.</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="d in r.detalles" :key="d.id_detalle_reserva">
                        <td>{{ d.producto?.nombre || '—' }}</td>
                        <td>Bs {{ Number(d.producto?.precio_venta || 0).toFixed(2) }}</td>
                        <td>{{ d.cantidad_reservada }}</td>
                        <td>Bs {{ (Number(d.producto?.precio_venta || 0) * d.cantidad_reservada).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                  </table>
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

    <ReservaFormModal v-if="mostrarForm" @cerrar="cerrarForm" />

    <EnvioFormModal
      v-if="mostrarEnvio"
      :reserva="reservaEnvio"
      @cerrar="cerrarEnvio"
    />

    <div v-if="convertirModal" class="modal-overlay" @click.self="cerrarConvertir">
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

    <div v-if="mostrarConfirmarCancelar" class="modal-overlay" @click.self="cerrarConfirmarCancelar">
      <div class="modal-confirmar">
        <h3>Cancelar Reserva</h3>
        <p>¿Estás seguro de cancelar la reserva <strong>#{{ reservaCancelar?.id_reserva }}</strong> del cliente <strong>{{ reservaCancelar?.cliente?.nombre_completo }}</strong>?</p>
        <p class="nota-cancelar">El stock reservado será devuelto y la reserva quedará como cancelada.</p>
        <div class="acciones-confirmar">
          <button class="btn-cancelar-modal" @click="cerrarConfirmarCancelar">Volver</button>
          <button class="btn-confirmar-cancelar" @click="ejecutarCancelar">Sí, cancelar</button>
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

.btn-detalle-active {
  color: #FFFFFF;
  background: #042D29;
  border-color: #042D29;
}

.detalle-fila {
  overflow: hidden;
}

.detalle-fila td {
  padding: 0 !important;
  border-top: 1px solid #E5E7EB !important;
}

.detalle-contenido {
  padding: 20px 24px;
  background: #F9FAFB;
}

.detalle-contenido h4 {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #E5E7EB;
}

.detalle-resumen {
  margin-bottom: 16px;
}

.detalle-resumen-campos {
  display: flex;
  gap: 24px;
  font-size: 13px;
  color: #1F2937;
  margin-top: 4px;
}

.saldo-pendiente {
  background: #FEF3C7;
  color: #92400E;
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 600;
  margin-left: auto;
}

.tabla-detalle {
  width: 100%;
  border-collapse: collapse;
}

.tabla-detalle th {
  font-size: 11px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  padding: 6px 12px;
  text-align: left;
}

.tabla-detalle td {
  font-size: 13px;
  padding: 8px 12px;
  color: #1F2937;
}

.tabla-detalle tbody tr:last-child td {
  border-bottom: 1px solid #E5E7EB;
}

.modal-confirmar {
  background: #FFFFFF;
  border-radius: 14px;
  max-width: 440px;
  width: 100%;
  padding: 28px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-confirmar h3 {
  font-size: 18px;
  font-weight: 600;
  color: #042D29;
  margin-bottom: 12px;
}

.modal-confirmar p {
  font-size: 14px;
  color: #1F2937;
  line-height: 1.5;
  margin-bottom: 8px;
}

.nota-cancelar {
  font-size: 12px;
  color: #929079;
  margin-bottom: 20px;
}

.acciones-confirmar {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancelar-modal {
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

.btn-cancelar-modal:hover { border-color: #929079; color: #1F2937; }

.btn-confirmar-cancelar {
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

.btn-confirmar-cancelar:hover { background: #5A0D01; }
</style>

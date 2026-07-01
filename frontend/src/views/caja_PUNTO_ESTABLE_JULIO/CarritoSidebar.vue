<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  items: { type: Array, default: () => [] },
  clienteActual: { type: String, default: 'Cliente General' }
})

const emit = defineEmits(['onClose', 'onRemoveItem', 'onProcesarVenta'])

const metodoPago = ref('Efectivo')
const placaVehiculo = ref('')
const fechaHoraTicket = ref('')
let intervalId = null

const obtenerFechaHoraActual = () => {
  const ahora = new Date()
  const opciones = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }
  fechaHoraTicket.value = ahora.toLocaleDateString('es-BO', opciones)
}

onMounted(() => {
  obtenerFechaHoraActual()
  intervalId = setInterval(obtenerFechaHoraActual, 1000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})

const total = computed(() =>
  props.items.reduce((acc, item) => acc + parseFloat(item.precio || 0), 0)
)

function enviarVenta() {
  if (props.items.length === 0) return

  emit('onProcesarVenta', {
    metodo_pago: metodoPago.value,
    placa: placaVehiculo.value.trim().toUpperCase() || 'S/P',
    items: props.items,
    total: total.value
  })

  placaVehiculo.value = ''
  metodoPago.value = 'Efectivo'
}
</script>

<template>
  <Teleport to="body">
    <div v-if="isOpen" class="sidebar-overlay" @click="emit('onClose')"></div> 
    <div class="carrito-sidebar" :class="{ abierto: isOpen }">
      
      <div class="sidebar-header">
        <div class="header-main-title">
          <h3>Detalle de Orden</h3>
          <p class="header-subtitle">Pre-comprobante y revisión final</p>
        </div>
        <button class="close-btn" @click="emit('onClose')">
          <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <div v-if="items.length > 0" class="pre-comprobante-box">
        <div class="box-header-title">📋 Resumen de Facturación</div>
        <div class="comprobante-grid">
          <div class="comprobante-row">
            <span class="c-label">Cliente:</span>
            <span class="c-value highlight-client">{{ clienteActual }}</span>
          </div>
          <div class="comprobante-row">
            <span class="c-label">Fecha/Hora:</span>
            <span class="c-value font-mono">{{ fechaHoraTicket }}</span>
          </div>
          <div class="comprobante-row">
            <span class="c-label">Método:</span>
            <span class="c-value badge-preview">{{ metodoPago }}</span>
          </div>
          <div class="comprobante-row">
            <span class="c-label">Vehículo / Placa:</span>
            <span class="c-value font-mono">{{ placaVehiculo.trim().toUpperCase() || 'SIN ASIGNAR' }}</span>
          </div>
        </div>
      </div>

      <div v-if="items.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" width="40" height="40" class="empty-icon">
          <circle cx="9" cy="21" r="1" stroke="#929079" stroke-width="2"/>
          <circle cx="20" cy="21" r="1" stroke="#929079" stroke-width="2"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke="#929079" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>El carrito está vacío</p>
      </div>

      <div v-else class="sidebar-items">
        <div class="items-section-title">Servicios Agregados</div>
        <div v-for="item in items" :key="item.id" class="cart-item-card">
          <div class="cart-item-info">
            <span class="cart-item-concepto">{{ item.concepto }}</span>
            <span class="cart-item-precio">Bs. {{ parseFloat(item.precio).toFixed(2) }}</span>
          </div>
          <button class="remove-btn" @click="emit('onRemoveItem', item.id)">
            <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
              <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="sidebar-footer">
        <div v-if="items.length > 0" class="form-facturacion">
          <div class="form-row-sidebar">
            <div class="form-group-sidebar" style="flex: 1;">
              <label>Método de Pago</label>
              <select v-model="metodoPago" class="select-sidebar">
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </div>
            <div class="form-group-sidebar" style="flex: 1;">
              <label>Placa (Opcional)</label>
              <input 
                v-model="placaVehiculo" 
                type="text" 
                placeholder="1234-ABC" 
                class="input-sidebar"
              />
            </div>
          </div>
          <hr class="divisor-footer"/>
        </div>

        <div class="total-row">
          <span class="total-label">Total Neto a Cobrar</span>
          <span class="total-valor">Bs. {{ total.toFixed(2) }}</span>
        </div>
        <button
          class="btn-primario w-100"
          :disabled="items.length === 0"
          @click="enviarVenta"
        >
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Procesar e Imprimir Venta
        </button>
      </div>

    </div>
  </Teleport>
</template>

<style scoped>
.sidebar-overlay { position: fixed; inset: 0; background: rgba(4, 45, 41, 0.3); backdrop-filter: blur(2px); z-index: 200; }
.carrito-sidebar { position: fixed; top: 0; right: 0; width: 400px; height: 100vh; background: #FFFFFF; z-index: 201; display: flex; flex-direction: column; transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: -6px 0 25px rgba(0,0,0,0.12); }
.carrito-sidebar.abierto { transform: translateX(0); }
.sidebar-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #E5E7EB; background: #FAFAFA; }
.sidebar-header h3 { font-size: 16px; font-weight: 700; color: #042D29; margin: 0; }
.header-subtitle { font-size: 11px; color: #6B7280; margin: 3px 0 0 0; font-weight: 500; }
.close-btn { background: none; border: none; color: #929079; cursor: pointer; padding: 6px; border-radius: 6px; transition: background 0.2s ease; display: flex; align-items: center; justify-content: center; }
.close-btn:hover { background: #F3F4F6; color: #1F2937; }
.pre-comprobante-box { background: #F9FAFB; border: 1.5px dashed #042D29; border-radius: 12px; margin: 20px 24px 8px 24px; padding: 14px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02); }
.box-header-title { font-size: 12px; font-weight: 700; color: #042D29; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
.comprobante-grid { display: flex; flex-direction: column; gap: 7px; }
.comprobante-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
.c-label { font-weight: 600; color: #6B7280; }
.c-value { font-weight: 700; color: #1F2937; text-align: right; }
.highlight-client { color: #741102; text-transform: uppercase; font-size: 12px; }
.font-mono { font-family: 'Inter', monospace; font-size: 12px; color: #374151; }
.badge-preview { background: #E5E7EB; padding: 1px 8px; border-radius: 6px; font-size: 11px; color: #042D29; }
.sidebar-items { flex: 1; overflow-y: auto; padding: 12px 24px; display: flex; flex-direction: column; gap: 8px; }
.items-section-title { font-size: 11px; font-weight: 700; color: #929079; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; margin-bottom: 2px; }
.cart-item-card { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 10px; transition: all 0.2s; }
.cart-item-card:hover { border-color: #929079; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
.cart-item-info { display: flex; flex-direction: column; gap: 3px; }
.cart-item-concepto { font-size: 13.5px; font-weight: 600; color: #1F2937; }
.cart-item-precio { font-size: 13px; font-weight: 700; color: #042D29; font-family: 'Inter', monospace; }
.remove-btn { background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 6px; border-radius: 8px; transition: all 0.2s ease; }
.remove-btn:hover { background: #FEF2F2; color: #741102; }
.sidebar-footer { padding: 16px 24px 24px; border-top: 1px solid #E5E7EB; display: flex; flex-direction: column; gap: 14px; background: #FAFAFA; }
.form-facturacion { display: flex; flex-direction: column; gap: 10px; }
.form-group-sidebar { display: flex; flex-direction: column; gap: 5px; }
.form-group-sidebar label { font-size: 11px; font-weight: 700; color: #4B5563; text-transform: uppercase; letter-spacing: 0.3px; }
.select-sidebar, .input-sidebar { padding: 9px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 13px; font-weight: 600; outline: none; background: #FFFFFF; color: #1F2937; transition: border-color 0.2s; }
.select-sidebar:focus, .input-sidebar:focus { border-color: #042D29; }
.form-row-sidebar { display: flex; gap: 12px; }
.divisor-footer { border: 0; border-top: 1px solid #E5E7EB; margin: 4px 0; }
.total-row { display: flex; justify-content: space-between; align-items: center; }
.total-label { font-size: 14px; font-weight: 600; color: #4B5563; }
.total-valor { font-size: 24px; font-weight: 800; color: #042D29; font-family: 'Inter', monospace; }
.btn-primario { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 20px; background: #042D29; color: #FFFFFF; border: none; border-radius: 12px; font-size: 14px; font-family: 'Inter', sans-serif; font-weight: 700; cursor: pointer; transition: background 0.2s ease; box-shadow: 0 4px 12px rgba(4, 45, 41, 0.1); }
.btn-primario:hover { background: #052E2A; }
.btn-primario:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
.w-100 { width: 100%; }
</style>
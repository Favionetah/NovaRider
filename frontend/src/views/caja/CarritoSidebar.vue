<template>
  <div>
    <div v-if="isOpen" class="sidebar-backdrop" @click="cerrarSidebar"></div>

    <div :class="['carrito-sidebar bg-white shadow-lg border-start border-light-subtle', { 'open': isOpen }]">
      <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
        <h6 class="fw-semibold text-dark mb-0 d-flex align-items-center gap-2 tracking-tight">
          <i class="bi bi-bag text-muted"></i> Orden Actual
        </h6>
        <button class="btn-close" style="font-size: 0.75rem;" @click="cerrarSidebar"></button>
      </div>

      <div class="p-3 sidebar-body">
        <div v-if="items.length > 0" class="bg-light rounded-3 p-2.5 mb-3 small border-0">
          <div class="text-dark fw-medium mb-0.5" style="font-size: 0.8rem;">{{ items[0].cliente }}</div>
          <div class="text-muted font-monospace" style="font-size: 0.72rem;">Placa: {{ items[0].placa }}</div>
        </div>

        <div v-if="items.length === 0" class="text-center py-5 text-muted font-sans my-auto">
          <i class="bi bi-mailbox display-6 d-block mb-2 text-light-emphasis"></i>
          <span class="small d-block text-secondary">No hay ítems en preventa</span>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-borderless align-middle table-sm" style="font-size: 0.8rem;">
            <thead>
<tr class="text-muted border-bottom border-light-subtle text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">                 <th class="pb-2">Ítem</th>
                <th class="text-end pb-2" style="width: 80px;">Subtotal</th>
                <th class="text-center pb-2" style="width: 40px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id" class="border-bottom border-light-subtle">
                <td class="py-2.5">
                  <span class="fw-medium text-dark d-block">{{ item.nombreItem }}</span>
                  <small class="text-muted text-truncate d-inline-block" style="max-width: 160px; font-size: 0.72rem;">{{ item.concepto }}</small>
                </td>
                <td class="text-end fw-semibold font-monospace py-2.5 text-dark">{{ item.precioItem }}</td>
                <td class="text-center py-2.5">
                  <button class="btn btn-sm text-muted p-0 border-0 hover-danger" @click="eliminarItem(item.id)">
                    <i class="bi bi-x-lg" style="font-size: 0.75rem;"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="items.length > 0" class="mt-4 pt-3 border-top border-light-subtle">
          <div class="mb-2.5">
            <label class="form-label text-muted small fw-medium mb-1">Cupón Corporativo</label>
            <div class="input-group input-group-sm">
              <input type="text" class="form-control bg-light border-0 font-monospace text-uppercase" v-model="cuponInput" placeholder="Ej. NOVARIDER2026" :disabled="cuponAplicado" />
              <button class="btn fw-medium btn-dark" @click="validarCupon" :disabled="cuponAplicado">
                {{ cuponAplicado ? '✓' : 'Aplicar' }}
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-muted small fw-medium mb-1">Descuento Manual Fijo (Bs.)</label>
            <input type="number" class="form-control form-control-sm bg-light border-0 font-monospace" v-model.number="descuentoFijo" placeholder="0.00" />
          </div>

          <div class="mb-3.5">
            <label class="form-label text-muted small fw-medium mb-1">Método de Cobro</label>
            <select class="form-select form-select-sm bg-light border-0 fw-medium" v-model="metodoPago">
              <option value="Efectivo">Efectivo Moneda Nacional</option>
              <option value="QR / Transferencia">Código QR Interbancario</option>
              <option value="Tarjeta">Terminal de Punto de Venta (Tarjeta)</option>
            </select>
          </div>

          <div class="bg-light p-3 rounded-3 mb-4 border-0">
            <div class="d-flex justify-content-between text-muted small mb-1.5">
              <span>Subtotal bruto:</span>
              <span class="font-monospace fw-medium">{{ subtotal }} Bs.</span>
            </div>
            <div v-if="descuentoPorCupon > 0" class="d-flex justify-content-between text-success small mb-1.5">
              <span>Beneficio Cupón (15%):</span>
              <span class="font-monospace">-{{ descuentoPorCupon.toFixed(1) }} Bs.</span>
            </div>
            <div v-if="descuentoFijo > 0" class="d-flex justify-content-between text-success small mb-1.5">
              <span>Bonificación Fija:</span>
              <span class="font-monospace">-{{ descuentoFijo.toFixed(1) }} Bs.</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle">
              <span class="fw-semibold text-dark" style="font-size: 0.85rem;">Total Neto:</span>
              <span class="fw-bold text-dark font-monospace fs-5">{{ totalNeto.toFixed(1) }} Bs.</span>
            </div>
          </div>

          <button class="btn btn-dark btn-sm w-100 fw-medium py-2 rounded-2 transition-all shadow-sm" @click="procesarVenta">
            Cerrar Venta y Generar Recibo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  items: { type: Array, default: () => [] }
})

const emit = defineEmits(['onClose', 'onRemoveItem', 'onProcesarVenta'])

const cuponInput = ref('')
const cuponAplicado = ref(false)
const descuentoFijo = ref(0)
const metodoPago = ref('Efectivo')

const subtotal = computed(() => props.items.reduce((acc, item) => acc + item.precioItem, 0))
const descuentoPorCupon = computed(() => cuponAplicado.value ? (subtotal.value * 0.15) : 0)
const totalNeto = computed(() => {
  const total = subtotal.value - descuentoPorCupon.value - (descuentoFijo.value || 0)
  return total < 0 ? 0 : total
})

const cerrarSidebar = () => emit('onClose')
const eliminarItem = (id) => emit('onRemoveItem', id)

const validarCupon = () => {
  if (cuponInput.value.trim().toUpperCase() === 'NOVARIDER2026') {
    cuponAplicado.value = true
  }
}

const procesarVenta = () => {
  if (props.items.length === 0) return
  emit('onProcesarVenta', {
    cliente: props.items[0].cliente,
    placa: props.items[0].placa,
    concepto: props.items[0].concepto,
    subtotal: subtotal.value,
    descuentoTotal: descuentoPorCupon.value + (descuentoFijo.value || 0),
    totalNeto: totalNeto.value,
    metodoPago: metodoPago.value
  })
  cuponInput.value = ''
  cuponAplicado.value = false
  descuentoFijo.value = 0
  metodoPago.value = 'Efectivo'
}
</script>

<style scoped>
.sidebar-backdrop {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.15); z-index: 1040; backdrop-filter: blur(2px);
}
.carrito-sidebar {
  position: fixed; top: 0; right: -380px; width: 380px; height: 100vh;
  z-index: 1050; transition: right 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex; flex-direction: column;
}
.carrito-sidebar.open { right: 0; }
.sidebar-body { flex: 1; overflow-y: auto; }
.hover-danger:hover { color: #dc3545 !important; }
</style>
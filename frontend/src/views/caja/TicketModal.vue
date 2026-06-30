<template>
  <div v-if="show" class="modal-backdrop-custom d-flex align-items-center justify-content-center">
    <div class="ticket-container bg-white p-4 shadow-lg rounded-3 border border-light-subtle">
      <!-- Icono de Estado Exitoso -->
      <div class="text-center mb-4">
        <div class="success-icon-circle mx-auto mb-2 bg-neutral text-dark d-flex align-items-center justify-content-center rounded-circle">
          <i class="bi bi-check-circle-fill fs-4"></i>
        </div>
        <h6 class="fw-semibold text-dark mb-0 tracking-tight">Transacción Exitosa</h6>
        <p class="text-muted small mb-0" style="font-size: 0.72rem;">El comprobante ha sido indexado en el servidor</p>
      </div>

      <!-- Tarjeta de Datos Estilizada -->
      <div class="bg-neutral rounded-3 p-3 mb-4 border border-light-subtle">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-light-subtle">
          <div>
            <span class="fw-bold text-dark font-monospace tracking-wider block" style="font-size: 0.75rem;">NOVARIDER TALLER</span>
            <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">Recibo ID: #{{ datosTicket?.nroRecibo }}</span>
          </div>
          <i class="bi bi-qr-code-scan fs-4 text-secondary"></i>
        </div>

        <div class="row g-2 mb-3 text-secondary" style="font-size: 0.75rem;">
          <div class="col-4 fw-medium text-muted">Cliente:</div>
          <div class="col-8 text-dark fw-medium text-end">{{ datosTicket?.cliente }}</div>
          
          <div class="col-4 fw-medium text-muted">Vehículo:</div>
          <div class="col-8 text-dark font-monospace text-end text-uppercase">{{ datosTicket?.placa }}</div>
          
          <div class="col-4 fw-medium text-muted">Trabajo:</div>
          <div class="col-8 text-dark text-end text-truncate" :title="datosTicket?.concepto">{{ datosTicket?.concepto }}</div>
        </div>

        <!-- Tabla Limpia de Productos -->
        <div class="table-responsive">
          <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.75rem;">
<thead>
    <tr>
        <th colspan="2" class="text-center pb-2">
            <span class="fw-bold text-dark font-monospace tracking-wider block" style="font-size: 0.75rem;">NOVARIDER TALLER</span>
        </th>
    </tr>
    <tr>
        <th class="ps-0 pb-1" data-v-inspector="src/views/caja/TicketModal.vue:38:136">Detalle del ítem</th>
        <th class="text-end pe-0 pb-1" style="width: 70px;">Total</th>
    </tr>
</thead>
            <tbody>
              <tr v-for="item in datosTicket?.items" :key="item.id" class="border-bottom-dashed">
                <td class="ps-0 py-2 text-dark fw-medium">{{ item.nombreItem }}</td>
                <td class="text-end pe-0 font-monospace text-secondary">{{ item.precioItem.toFixed(1) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Fila de Importe Neto -->
        <div class="pt-3 mt-1 d-flex justify-content-between align-items-center fw-semibold text-dark">
          <span style="font-size: 0.8rem;">Total Liquidado</span>
          <span class="font-monospace fs-5 fw-bold text-dark">{{ datosTicket?.total?.toFixed(1) }} Bs.</span>
        </div>
        <div class="text-muted mt-1 text-end font-monospace" style="font-size: 0.62rem;">
          Vía: {{ datosTicket?.metodo_pago }}
        </div>
      </div>

      <!-- Acciones Inferiores Modernas -->
      <div class="row g-2">
        <div class="col-6">
          <button class="btn btn-outline-dark btn-sm w-100 fw-medium py-2 rounded-2 d-flex align-items-center justify-content-center gap-1.5" @click="imprimirRecibo">
            <i class="bi bi-printer"></i> Imprimir
          </button>
        </div>
        <div class="col-6">
          <button class="btn btn-dark btn-sm w-100 fw-medium py-2 rounded-2" @click="cerrarModal">Terminar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  datosTicket: { type: Object, default: null }
})
const emit = defineEmits(['onClose'])
const cerrarModal = () => emit('onClose')
const imprimirRecibo = () => { window.print() }
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.12); z-index: 2000; backdrop-filter: blur(4px);
}
.ticket-container {
  width: 360px;
  animation: modalSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.success-icon-circle {
  width: 48px; height: 48px;
}
.border-bottom-dashed {
  border-bottom: 1px dashed rgba(0,0,0,0.06);
}
@keyframes modalSlideIn {
  from { transform: translateY(8px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
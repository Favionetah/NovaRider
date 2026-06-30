<script setup>
defineProps({
  show: { type: Boolean, default: false },
  datosTicket: { type: Object, default: null },
})

const emit = defineEmits(['onClose'])
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="modal-overlay" @click.self="emit('onClose')">
      <div class="modal-card">
        <div class="modal-header">
          <div class="header-left">
            <svg viewBox="0 0 24 24" fill="none" width="22" height="22">
              <path d="M20 12V6H4v6M20 12v6H4v-6M20 12h2M2 12h2" stroke="#042D29" stroke-width="2"/>
              <rect x="6" y="10" width="12" height="6" rx="1" fill="#042D29" fill-opacity="0.1" stroke="#042D29" stroke-width="1"/>
            </svg>
            <h2>Comprobante de Venta</h2>
          </div>
          <button class="close-btn" @click="emit('onClose')">
            <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
              <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <div v-if="datosTicket" class="modal-body">
          <div class="ticket-header-info">
            <div class="ticket-field">
              <span class="ticket-label">N&deg; Recibo</span>
              <span class="ticket-value">#{{ datosTicket.nroRecibo }}</span>
            </div>
            <div class="ticket-field">
              <span class="ticket-label">Cliente</span>
              <span class="ticket-value">{{ datosTicket.cliente || 'Cliente General' }}</span>
            </div>
            <div class="ticket-field">
              <span class="ticket-label">Placa</span>
              <span class="ticket-value">{{ datosTicket.placa || 'S/P' }}</span>
            </div>
            <div class="ticket-field">
              <span class="ticket-label">M&eacute;todo de Pago</span>
              <span class="ticket-value">{{ datosTicket.metodo_pago || 'Efectivo' }}</span>
            </div>
          </div>

          <div class="ticket-items-header">Detalle</div>
          <div class="ticket-items">
            <div v-for="(item, idx) in datosTicket.items" :key="idx" class="ticket-item">
              <span class="item-nombre">{{ item.nombreItem }}</span>
              <span class="item-precio">S/ {{ parseFloat(item.precioItem).toFixed(2) }}</span>
            </div>
          </div>

          <div class="ticket-total-row">
            <span class="ticket-total-label">Total</span>
            <span class="ticket-total-valor">S/ {{ parseFloat(datosTicket.total).toFixed(2) }}</span>
          </div>

          <p class="ticket-footer-text">Gracias por su preferencia</p>
        </div>

        <div class="modal-footer">
          <button class="btn-primario w-100" @click="emit('onClose')">Cerrar</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 300;
  padding: 20px;
}

.modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #929079;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.close-btn:hover { background: #F5F4F0; color: #1F2937; }

.modal-body {
  padding: 24px;
}

.ticket-header-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.ticket-field {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.ticket-label {
  font-size: 11px;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  font-weight: 500;
}

.ticket-value {
  font-size: 14px;
  font-weight: 600;
  color: #1F2937;
}

.ticket-items-header {
  font-size: 12px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding-bottom: 8px;
  border-bottom: 1px solid #E5E7EB;
  margin-bottom: 8px;
}

.ticket-items {
  display: flex;
  flex-direction: column;
}

.ticket-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #F3F4F6;
}

.ticket-item:last-child { border-bottom: none; }

.item-nombre {
  font-size: 13px;
  color: #1F2937;
}

.item-precio {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.ticket-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  margin-top: 8px;
  border-top: 2px solid #042D29;
}

.ticket-total-label {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
}

.ticket-total-valor {
  font-size: 22px;
  font-weight: 700;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.ticket-footer-text {
  text-align: center;
  font-size: 12px;
  color: #929079;
  font-style: italic;
  margin: 16px 0 0;
}

.modal-footer {
  padding: 0 24px 20px;
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
.w-100 { width: 100%; }
</style>

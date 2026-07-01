<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  datosTicket: {
    type: Object,
    default: () => ({
      nroRecibo: '#000',
      cliente: 'Cliente General',
      placa: 'S/P',
      metodo_pago: 'Efectivo',
      items: [],
      total: 0
    })
  }
})

const emit = defineEmits(['onClose'])

function exportarPDF() {
  window.print()
}

function descargarTicket() {
  window.print()
}
</script>

<template>
  <div v-if="show" class="modal-ticket-overlay" @click.self="emit('onClose')">
    <div class="modal-ticket-content printable-area">
      
      <div class="ticket-header no-print">
        <div class="ticket-header-title">
          <svg viewBox="0 0 24 24" fill="none" width="22" height="22" class="icon-ticket">
            <path d="M2 7a2 2 0 012-2h16a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 000-4V7z" stroke="#042D29" stroke-width="2"/>
            <path d="M9 9h6m-6 3h3" stroke="#042D29" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <h3>Comprobante de Venta</h3>
        </div>
        <button class="close-x-btn" @click="emit('onClose')">✕</button>
      </div>

      <div class="ticket-body">
        <div class="meta-grid">
          <div class="meta-block">
            <span class="meta-label">N° RECIBO</span>
            <span class="meta-value">{{ datosTicket?.nroRecibo }}</span>
          </div>
          <div class="meta-block">
            <span class="meta-label">CLIENTE</span>
            <span class="meta-value text-capitalize">{{ datosTicket?.cliente }}</span>
          </div>
          <div class="meta-block">
            <span class="meta-label">PLACAS</span>
            <span class="meta-value font-mono">{{ datosTicket?.placa }}</span>
          </div>
          <div class="meta-block">
            <span class="meta-label">MÉTODO DE PAGO</span>
            <span class="meta-value">{{ datosTicket?.metodo_pago }}</span>
          </div>
        </div>

        <hr class="ticket-divider"/>

        <div class="detalle-seccion">
          <span class="section-lbl">DETALLE</span>
          <div class="items-list-print">
            <div v-if="!datosTicket?.items || datosTicket.items.length === 0" class="item-print-row">
              <span class="item-desc">Venta de Servicios Generales</span>
              <span class="item-val">Bs. {{ datosTicket?.total?.toFixed(2) }}</span>
            </div>
            <div v-else v-for="item in datosTicket.items" :key="item.id" class="item-print-row">
              <span class="item-desc">{{ item.concepto }}</span>
              <span class="item-val">Bs. {{ parseFloat(item.precio).toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <hr class="ticket-divider"/>

        <div class="total-print-row">
          <span class="total-lbl">Total</span>
          <span class="total-val">Bs. {{ datosTicket?.total?.toFixed(2) }}</span>
        </div>

        <p class="gracias-footer">Gracias por su preferencia</p>
      </div>

      <div class="ticket-actions-footer no-print">
        <div class="action-buttons-row">
          <button class="btn-action-outline btn-descargar" @click="descargarTicket">
            <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
              <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Imprimir
          </button>

          <button class="btn-action-outline btn-pdf" @click="exportarPDF">
            <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
              <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke="currentColor" stroke-width="2"/>
              <path d="M12 3v6h6" stroke="currentColor" stroke-width="2"/>
            </svg>
            Exportar PDF
          </button>
        </div>

        <button class="btn-cerrar-principal" @click="emit('onClose')">
          Cerrar Comprobante
        </button>
      </div>

    </div>
  </div>
</template>

<style scoped>
.modal-ticket-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
.modal-ticket-content { background: #FFFFFF; border-radius: 18px; width: 100%; max-width: 440px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden; display: flex; flex-direction: column; }
.ticket-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid #F3F4F6; }
.ticket-header-title { display: flex; align-items: center; gap: 10px; }
.ticket-header-title h3 { margin: 0; font-size: 16px; font-weight: 700; color: #042D29; }
.icon-ticket { color: #042D29; }
.close-x-btn { background: none; border: none; font-size: 16px; color: #9CA3AF; cursor: pointer; padding: 4px; border-radius: 4px; transition: background 0.2s; }
.close-x-btn:hover { background: #F3F4F6; color: #1F2937; }
.ticket-body { padding: 24px; display: flex; flex-direction: column; }
.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; margin-bottom: 6px; }
.meta-block { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 11px; font-weight: 700; color: #929079; letter-spacing: 0.5px; }
.meta-value { font-size: 14px; font-weight: 700; color: #042D29; }
.text-capitalize { text-transform: capitalize; }
.font-mono { font-family: 'Inter', monospace; }
.ticket-divider { border: 0; border-top: 1.5px dashed #E5E7EB; margin: 16px 0; width: 100%; }
.detalle-seccion { display: flex; flex-direction: column; gap: 8px; }
.section-lbl { font-size: 11px; font-weight: 700; color: #929079; letter-spacing: 0.5px; text-transform: uppercase; }
.items-list-print { display: flex; flex-direction: column; gap: 6px; }
.item-print-row { display: flex; justify-content: space-between; align-items: center; font-size: 13.5px; color: #1F2937; }
.item-desc { font-weight: 500; }
.item-val { font-weight: 600; font-family: 'Inter', monospace; }
.total-print-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.total-lbl { font-size: 15px; font-weight: 700; color: #042D29; }
.total-val { font-size: 22px; font-weight: 800; color: #042D29; font-family: 'Inter', monospace; }
.gracias-footer { text-align: center; font-size: 13px; font-style: italic; color: #929079; margin: 24px 0 0 0; }
.ticket-actions-footer { padding: 0 24px 24px 24px; display: flex; flex-direction: column; gap: 10px; background: #FFFFFF; }
.action-buttons-row { display: flex; gap: 10px; width: 100%; }
.btn-action-outline { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; background: #FFFFFF; border: 1.5px solid #D1D5DB; border-radius: 10px; font-size: 13px; font-weight: 600; color: #4B5563; cursor: pointer; transition: all 0.2s ease; }
.btn-action-outline:hover { border-color: #042D29; color: #042D29; background: #F4F6F6; }
.btn-cerrar-principal { width: 100%; padding: 13px; background: #042D29; color: #FFFFFF; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; text-align: center; }
.btn-cerrar-principal:hover { background: #0b4640; }

@media print {
  body * { visibility: hidden; }
  .printable-area, .printable-area * { visibility: visible; }
  .printable-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border-radius: 0 !important; }
  .no-print { display: none !important; }
}
</style>
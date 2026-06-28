<script setup>
import { onMounted, nextTick } from 'vue'

defineProps({
  pdfBlobUrl: { type: String, default: '' },
  cargando: { type: Boolean, default: false },
})

const emit = defineEmits(['cerrar', 'descargar'])

onMounted(async () => {
  await nextTick()
  if (typeof gsap !== 'undefined') {
    gsap.fromTo('.vp-modal-card', { scale: 0.95, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.25, ease: 'power3.out' })
  }
})
</script>

<template>
  <div class="vp-modal-overlay" @click.self="emit('cerrar')">
    <div class="vp-modal-card">
      <div class="vp-modal-header">
        <div class="vp-header-left">
          <svg viewBox="0 0 24 24" fill="none" class="vp-icon-pdf">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h2>Vista Previa — Reporte de Usuarios</h2>
        </div>
        <button class="vp-btn-cerrar" @click="emit('cerrar')">&times;</button>
      </div>

      <div class="vp-modal-body">
        <div v-if="cargando" class="vp-loading">
          <div class="vp-spinner"></div>
          <span>Generando reporte...</span>
        </div>
        <iframe
          v-else-if="pdfBlobUrl"
          :src="pdfBlobUrl"
          class="vp-pdf-frame"
          frameborder="0"
        ></iframe>
        <div v-else class="vp-loading">
          <span>No se pudo generar el PDF</span>
        </div>
      </div>

      <div class="vp-modal-footer">
        <button class="vp-btn-cancelar" @click="emit('cerrar')">Cerrar</button>
        <button
          class="vp-btn-descargar"
          :disabled="cargando || !pdfBlobUrl"
          @click="emit('descargar')"
        >
          <svg viewBox="0 0 24 24" fill="none" class="vp-icon-download">
            <path d="M12 3v12M12 15l-4-4M12 15l4-4M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Descargar PDF
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.vp-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}

.vp-modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.vp-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.vp-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.vp-icon-pdf {
  width: 22px;
  height: 22px;
  color: #741102;
}

.vp-modal-header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
}

.vp-btn-cerrar {
  background: none;
  border: none;
  font-size: 28px;
  color: #929079;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
  padding: 0;
}

.vp-btn-cerrar:hover {
  color: #741102;
}

.vp-modal-body {
  flex: 1;
  overflow: hidden;
  min-height: 0;
}

.vp-pdf-frame {
  width: 100%;
  height: 70vh;
  border: none;
  display: block;
}

.vp-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 60px 20px;
  color: #929079;
  font-size: 14px;
}

.vp-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #E5E7EB;
  border-top-color: #042D29;
  border-radius: 50%;
  animation: vp-spin 0.7s linear infinite;
}

@keyframes vp-spin {
  to { transform: rotate(360deg); }
}

.vp-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 14px 24px;
  border-top: 1px solid #E5E7EB;
}

.vp-btn-cancelar {
  padding: 10px 18px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  background: #FFFFFF;
  color: #1F2937;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.vp-btn-cancelar:hover {
  background: #F9FAFB;
  border-color: #929079;
}

.vp-btn-descargar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.vp-btn-descargar:hover:not(:disabled) {
  background: #052E2A;
}

.vp-btn-descargar:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.vp-icon-download {
  width: 16px;
  height: 16px;
}
</style>

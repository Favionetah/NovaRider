<template>
  <div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm bg-white rounded-3">
      <div class="card-body p-3">
        <h6 class="fw-semibold text-dark mb-3 tracking-tight">Flujo de Caja</h6>
        
        <div v-if="!cajaAbierta">
          <div class="mb-3">
            <label class="form-label text-muted small fw-medium mb-1">Monto Inicial (Bs.)</label>
            <input type="number" class="form-control form-control-sm text-center fw-semibold py-2 bg-light border-0" v-model.number="montoInicial" placeholder="0.00" />
          </div>
          <button class="btn btn-dark btn-sm w-100 fw-medium py-2 rounded-2" @click="emitirApertura">Abrir Jornada</button>
        </div>

        <div v-else>
          <div class="bg-light p-2 rounded-2 mb-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small fw-medium">En Sistema:</span>
            <span class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">{{ saldoSistema }} Bs.</span>
          </div>
          
          <div class="mb-2">
            <label class="form-label text-muted small fw-medium mb-1">Físico Real (Bs.)</label>
            <input type="number" class="form-control form-control-sm py-2 bg-light border-0" v-model.number="cierreDatos.fisico" placeholder="0.00" />
          </div>

          <div class="mb-3">
            <label class="form-label text-muted small fw-medium mb-1">Notas de Arqueo</label>
            <textarea class="form-control form-control-sm py-1 bg-light border-0 small" rows="2" v-model="cierreDatos.observaciones" placeholder="Novedades o justificaciones..."></textarea>
          </div>

          <button class="btn btn-outline-danger btn-sm w-100 fw-medium py-2 rounded-2" @click="emitirCierre">Cerrar Caja e Informar</button>
        </div>
      </div>
    </div>

    <div class="card border-0 bg-dark text-white rounded-3" style="opacity: 0.9;">
      <div class="card-body p-3" style="font-size: 0.72rem;">
        <div class="d-flex align-items-center gap-1.5 text-white-50 fw-semibold text-uppercase tracking-wider mb-1.5" style="font-size: 0.65rem;">
          <span class="d-inline-block rounded-circle bg-success" style="width: 5px; height: 5px;"></span> Terminal Status
        </div>
        <p class="font-monospace text-light mb-0 lh-base">> {{ logsServidor }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  saldoSistema: { type: Number, default: 0 },
  cajaAbierta: { type: Boolean, default: false },
  logsServidor: { type: String, default: '' }
})

const emit = defineEmits(['onAbrirCaja', 'onCerrarCaja'])
const montoInicial = ref(200)
const cierreDatos = ref({ fisico: 0, observaciones: '' })

const emitirApertura = () => {
  if (montoInicial.value < 0) return
  emit('onAbrirCaja', montoInicial.value)
}

const emitirCierre = () => {
  if (cierreDatos.value.fisico < 0) return
  emit('onCerrarCaja', { ...cierreDatos.value })
  cierreDatos.value = { fisico: 0, observaciones: '' }
}
</script>
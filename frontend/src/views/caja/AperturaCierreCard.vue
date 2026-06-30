<script setup>
import { ref } from 'vue'

const props = defineProps({
  saldoSistema: { type: Number, default: 0 },
  cajaAbierta: { type: Boolean, default: false },
  logsServidor: { type: String, default: '' },
})

const emit = defineEmits(['onCerrarCaja'])

const mostrarFormCierre = ref(false)
const montoFisico = ref(0)
const observaciones = ref('')

function abrirFormCierre() {
  montoFisico.value = props.saldoSistema
  observaciones.value = ''
  mostrarFormCierre.value = true
}

function confirmarCierre() {
  emit('onCerrarCaja', {
    fisico: montoFisico.value,
    observaciones: observaciones.value,
  })
  mostrarFormCierre.value = false
}
</script>

<template>
  <div class="card-seccion">
    <div class="card-seccion-header">
      <div class="header-row">
        <svg viewBox="0 0 24 24" fill="none" width="20" height="20" class="header-icon">
          <rect x="2" y="4" width="20" height="16" rx="2" stroke="#042D29" stroke-width="2"/>
          <path d="M12 10v4M10 12h4" stroke="#042D29" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <h3>Jornada</h3>
        <span class="status-dot" :class="{ activa: cajaAbierta }"></span>
      </div>
      <p class="texto-ayuda">{{ cajaAbierta ? 'Jornada en curso' : 'Caja cerrada' }}</p>
    </div>

    <div class="card-seccion-body">
      <div class="saldo-row">
        <span class="saldo-label">Saldo en sistema</span>
        <span class="saldo-valor">S/ {{ saldoSistema.toFixed(2) }}</span>
      </div>

      <div v-if="!cajaAbierta" class="empty-state">
        Inicie la jornada desde el panel principal.
      </div>

      <div v-if="cajaAbierta && !mostrarFormCierre" class="cierre-actions">
        <button class="btn-secundario w-100" @click="abrirFormCierre">
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
            <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
            <path d="M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Cerrar Jornada
        </button>
      </div>

      <div v-if="cajaAbierta && mostrarFormCierre" class="cierre-form">
        <div class="form-group">
          <label>Monto f&iacute;sico contado (S/)</label>
          <input v-model.number="montoFisico" type="number" step="0.01" min="0" />
        </div>
        <div class="form-group">
          <label>Observaciones</label>
          <textarea v-model="observaciones" rows="2" placeholder="Notas del arqueo..."></textarea>
        </div>
        <div class="form-actions">
          <button class="btn-ghost" @click="mostrarFormCierre = false">Cancelar</button>
          <button class="btn-primario" @click="confirmarCierre">
            Confirmar Cierre
          </button>
        </div>
      </div>

      <p v-if="logsServidor" class="status-text">{{ logsServidor }}</p>
    </div>
  </div>
</template>

<style scoped>
.card-seccion {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
}

.card-seccion-header {
  padding: 20px 24px 12px;
}

.header-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-icon { flex-shrink: 0; }

.card-seccion-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #929079;
  transition: background 0.3s;
}

.status-dot.activa { background: #22C55E; }

.texto-ayuda {
  font-size: 13px;
  color: #929079;
  margin: 4px 0 0;
}

.card-seccion-body {
  padding: 12px 24px 24px;
}

.saldo-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: #F0F4F3;
  border-radius: 10px;
  margin-bottom: 16px;
}

.saldo-label {
  font-size: 13px;
  color: #1F2937;
  font-weight: 500;
}

.saldo-valor {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.empty-state {
  text-align: center;
  color: #929079;
  padding: 20px 0;
  font-size: 13px;
  font-style: italic;
}

.cierre-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn-secundario {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
  background: transparent;
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-secundario:hover { background: #F0F4F3; }
.w-100 { width: 100%; }

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 12px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.form-group input,
.form-group textarea {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #FFFFFF;
  resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.form-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 16px;
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

.btn-ghost {
  padding: 10px 20px;
  background: transparent;
  color: #929079;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-ghost:hover { background: #F5F4F0; }

.status-text {
  font-size: 10px;
  color: #929079;
  font-family: 'Inter', monospace;
  margin-top: 12px;
  text-align: center;
}
</style>

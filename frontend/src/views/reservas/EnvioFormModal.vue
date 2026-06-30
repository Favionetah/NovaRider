<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useReservasStore } from '@/stores/reservas'

const props = defineProps({
  reserva: { type: Object, required: true },
})

const emit = defineEmits(['cerrar', 'guardado'])

const store = useReservasStore()

const guardando = ref(false)
const errorGeneral = ref('')
const errores = ref({})

const form = ref({
  empresa_transporte: '',
  nro_guia: '',
  fecha_despacho: new Date().toISOString().split('T')[0],
  estado_envio: 'en_transito',
})

onMounted(async () => {
  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function cerrar() {
  emit('cerrar')
}

function validar() {
  errores.value = {}
  let ok = true
  if (!form.value.empresa_transporte.trim()) {
    errores.value.empresa_transporte = ['La empresa de transporte es requerida']
    ok = false
  }
  if (!form.value.nro_guia.trim()) {
    errores.value.nro_guia = ['El número de guía es requerido']
    ok = false
  }
  return ok
}

async function guardar() {
  guardando.value = true
  errorGeneral.value = ''

  if (!validar()) {
    guardando.value = false
    return
  }

  try {
    await store.registrarEnvio(props.reserva.id_reserva, form.value)
    emit('guardado')
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al registrar envio'
    }
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay">
    <div class="modal-card modal-sm">
      <div class="modal-header">
        <h2>Registrar Envío</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <p class="envio-info">
          Reserva #{{ reserva.id_reserva }} — {{ reserva.cliente?.nombre_completo }}
        </p>

        <div class="form-group">
          <label for="empresa">Empresa transporte <span class="required">*</span></label>
          <input
            id="empresa"
            v-model="form.empresa_transporte"
            type="text"
            placeholder="Ej: Boliviana de Envíos"
            :class="{ 'input-error': errores.empresa_transporte }"
          />
          <span v-if="errores.empresa_transporte" class="error-text">{{ errores.empresa_transporte[0] }}</span>
        </div>

        <div class="form-group">
          <label for="guia">Nro. Guía <span class="required">*</span></label>
          <input
            id="guia"
            v-model="form.nro_guia"
            type="text"
            placeholder="Número de guía"
            :class="{ 'input-error': errores.nro_guia }"
          />
          <span v-if="errores.nro_guia" class="error-text">{{ errores.nro_guia[0] }}</span>
        </div>

        <div class="form-group">
          <label for="fecha">Fecha despacho <span class="required">*</span></label>
          <input
            id="fecha"
            v-model="form.fecha_despacho"
            type="date"
            :class="{ 'input-error': errores.fecha_despacho }"
          />
          <span v-if="errores.fecha_despacho" class="error-text">{{ errores.fecha_despacho[0] }}</span>
        </div>

        <div class="form-group">
          <label for="est_envio">Estado envío</label>
          <select id="est_envio" v-model="form.estado_envio">
            <option value="en_transito">En tránsito</option>
            <option value="entregado">Entregado</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Registrar Envío' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
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

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  font-size: 13px;
  margin: 16px 24px 0;
  border-radius: 8px;
}

.modal-body { padding: 24px; }

.envio-info {
  font-size: 14px;
  color: #1F2937;
  margin-bottom: 16px;
  padding: 10px 14px;
  background: #F9FAFB;
  border-radius: 8px;
}

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

.form-group input.input-error,
.form-group select.input-error { border-color: #741102; }

.error-text {
  font-size: 12px;
  color: #741102;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 8px;
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
</style>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useTurnosStore } from '@/stores/turnos'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  turno: { type: Object, default: null },
  idEmpleado: { type: Number, required: true },
})

const emit = defineEmits(['cerrar'])

const store = useTurnosStore()
const toast = useToastStore()
const esEdicion = !!props.turno

const form = ref({
  id_empleado: props.idEmpleado,
  fecha: props.turno?.fecha || '',
  hora_entrada: props.turno?.hora_entrada || '',
  hora_salida: props.turno?.hora_salida || '',
  observacion: props.turno?.observacion || '',
})

const errores = ref({})
const guardando = ref(false)
const errorGeneral = ref('')

onMounted(async () => {
  await nextTick()
  gsap.fromTo('.modal-card', { y: 20, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.25, ease: 'power3.out' })
})

function cerrar() { emit('cerrar') }

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  try {
    if (esEdicion) {
      await store.actualizar(props.turno.id_turno, {
        observacion: form.value.observacion,
      })
    } else {
      await store.crear(form.value)
    }
    toast.show('Asistencia guardada correctamente')
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al guardar turno'
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
        <h2>{{ esEdicion ? 'Editar Asistencia' : 'Asistencia Manual' }}</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error-sm">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Fecha</label>
            <input v-model="form.fecha" type="date" disabled />
          </div>
          <div class="form-group">
            <label>Hora Entrada</label>
            <input v-model="form.hora_entrada" type="time" disabled />
          </div>
          <div class="form-group">
            <label>Hora Salida</label>
            <input v-model="form.hora_salida" type="time" disabled />
          </div>
          <div class="form-group form-group-full">
            <label>Observaci&oacute;n</label>
            <input v-model="form.observacion" type="text" placeholder="Ej: Llegó tarde por tráfico" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Guardar' }}
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
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 110;
  padding: 20px;
}

.modal-sm { max-width: 480px; }

.modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

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

.mensaje-error-sm {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 8px 12px;
  font-size: 13px;
  margin: 16px 24px 0;
  border-radius: 6px;
}

.modal-body { padding: 24px; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group-full { grid-column: 1 / -1; }

.form-group label { font-size: 13px; font-weight: 500; color: #1F2937; }

.form-group input {
  padding: 9px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus { border-color: #042D29; }
.form-group input:disabled { background: #F9FAFB; color: #929079; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #E5E7EB;
}

.btn-cancelar {
  padding: 9px 18px;
  background: #FFFFFF;
  color: #929079;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancelar:hover { border-color: #929079; color: #1F2937; }

.btn-guardar {
  padding: 9px 22px;
  background: #042D29;
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-guardar:hover { background: #052E2A; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

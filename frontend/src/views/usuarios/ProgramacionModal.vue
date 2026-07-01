<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useProgramacionesStore } from '@/stores/programaciones'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  idEmpleado: { type: Number, required: true },
  horarioActual: { type: Array, default: () => [] },
})

const emit = defineEmits(['cerrar', 'guardado'])

const store = useProgramacionesStore()
const toast = useToastStore()

const nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
const tipoJornada = ref('completa')
const guardando = ref(false)
const errorGeneral = ref('')

const horario = ref([])

function initHorario() {
  if (props.horarioActual.length === 6) {
    horario.value = props.horarioActual.map(d => ({
      dia_semana: d.dia_semana,
      activo: d.activo,
      hora_entrada: d.activo && d.hora_entrada ? d.hora_entrada : '08:00',
      hora_salida: d.activo && d.hora_salida ? d.hora_salida : (d.dia_semana === 6 ? '14:00' : '16:00'),
    }))
  } else {
    horario.value = Array.from({ length: 6 }, (_, i) => ({
      dia_semana: i + 1,
      activo: true,
      hora_entrada: '08:00',
      hora_salida: i === 5 ? '14:00' : '16:00',
    }))
  }
}

const totalHoras = computed(() => {
  return horario.value.reduce((sum, d) => {
    if (!d.activo || !d.hora_entrada || !d.hora_salida) return sum
    const [h1, m1] = d.hora_entrada.split(':').map(Number)
    const [h2, m2] = d.hora_salida.split(':').map(Number)
    return sum + ((h2 * 60 + m2) - (h1 * 60 + m1)) / 60
  }, 0)
})

const horasDisponibles = ['08:00', '09:00', '10:00', '11:00', '12:00']

function onCambiarInicio(dia) {
  if (!dia.activo || !dia.hora_entrada) return
  const [h] = dia.hora_entrada.split(':').map(Number)
  const horasJornada = tipoJornada.value === 'completa' ? 8 : 6
  const hFin = h + horasJornada
  dia.hora_salida = `${String(hFin).padStart(2, '0')}:00`
}

function activarDia(dia) {
  dia.activo = !dia.activo
  if (dia.activo) {
    if (!dia.hora_entrada) dia.hora_entrada = '08:00'
    onCambiarInicio(dia)
  }
}

function aplicarATodos() {
  const activos = horario.value.filter(d => d.activo)
  if (activos.length === 0) return
  const ref = activos[0]
  activos.forEach(d => {
    d.hora_entrada = ref.hora_entrada
    onCambiarInicio(d)
  })
}

onMounted(async () => {
  initHorario()
  await nextTick()
  gsap.fromTo('.modal-card', { y: 20, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errorGeneral.value = ''
  try {
    await store.guardar(props.idEmpleado, horario.value)
    toast.show('Horario guardado correctamente')
    emit('guardado', horario.value)
    cerrar()
  } catch (err) {
    errorGeneral.value = err.response?.data?.message || 'Error al guardar horario'
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Configurar Horario Semanal</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error-sm">{{ errorGeneral }}</p>

      <div class="modal-body">
        <div class="jornada-selector">
          <label>Tipo de jornada:</label>
          <select v-model="tipoJornada" class="jornada-select">
            <option value="completa">Completa (8h)</option>
            <option value="media">Media Jornada (6h)</option>
          </select>
          <button class="btn-aplicar-todo" @click="aplicarATodos">Aplicar a todos</button>
        </div>

        <div class="planilla-grid">
          <div class="grid-header">
            <span class="col-dia">D&iacute;a</span>
            <span class="col-toggle">Activo</span>
            <span class="col-hora">Entrada</span>
            <span class="col-hora">Salida</span>
            <span class="col-horas">Horas</span>
          </div>

            <div v-for="d in horario" :key="d.dia_semana" class="grid-fila" :class="{ inactivo: !d.activo }">
              <span class="col-dia">{{ nombresDias[d.dia_semana - 1] }}</span>
              <span class="col-toggle">
                <button class="toggle-btn" :class="{ active: d.activo }" @click="activarDia(d)">
                  {{ d.activo ? 'S&iacute;' : 'No' }}
                </button>
              </span>
              <span class="col-hora">
                <select v-model="d.hora_entrada" :disabled="!d.activo" @change="onCambiarInicio(d)" class="hora-select">
                  <option v-for="h in horasDisponibles" :key="h" :value="h">{{ h }}</option>
                </select>
              </span>
              <span class="col-hora">
                <span class="hora-fija">{{ d.hora_salida || '—' }}</span>
              </span>
              <span class="col-horas">
                <span v-if="d.activo && d.hora_entrada && d.hora_salida" class="horas-badge">
                  {{ (() => { const [h1,m1]=d.hora_entrada.split(':').map(Number); const [h2,m2]=(d.hora_salida||'0:00').split(':').map(Number); return Math.max(0, ((h2*60+m2)-(h1*60+m1))/60); })() }}h
                </span>
                <span v-else class="horas-badge inactivo">—</span>
              </span>
            </div>
        </div>

        <div class="total-semanal">
          Total semanal: <strong>{{ totalHoras }}h</strong>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-cancelar" @click="cerrar">Cancelar</button>
        <button class="btn-guardar" :disabled="guardando" @click="guardar">
          {{ guardando ? 'Guardando...' : 'Guardar Horario' }}
        </button>
      </div>
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

.modal-card {
  background: #FFFFFF;
  border-radius: 14px;
  max-width: 600px;
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

.modal-body {
  padding: 24px;
}

.jornada-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.jornada-selector label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.jornada-select {
  padding: 7px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
}

.btn-aplicar-todo {
  padding: 6px 14px;
  background: rgba(4,45,41,0.08);
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 8px;
  font-size: 12px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-aplicar-todo:hover { background: rgba(4,45,41,0.15); }

.planilla-grid {
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  overflow: hidden;
}

.grid-header {
  display: grid;
  grid-template-columns: 120px 70px 1fr 1fr 70px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.grid-header span {
  padding: 10px 12px;
  font-size: 11px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.grid-fila {
  display: grid;
  grid-template-columns: 120px 70px 1fr 1fr 70px;
  align-items: center;
  border-bottom: 1px solid #F3F4F6;
  transition: background 0.15s;
}

.grid-fila:last-child { border-bottom: none; }
.grid-fila:hover { background: #F9FAFB; }
.grid-fila.inactivo { opacity: 0.5; }

.grid-fila span {
  padding: 10px 12px;
  font-size: 13px;
  color: #1F2937;
}

.col-dia { font-weight: 500; color: #042D29; }

.toggle-btn {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  border: 1.5px solid #D1D5DB;
  background: #FFFFFF;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn.active {
  background: rgba(4,45,41,0.1);
  color: #042D29;
  border-color: #042D29;
}

.hora-select {
  width: 100%;
  padding: 6px 8px;
  border: 1.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
}

.hora-select:disabled { background: #F9FAFB; color: #9CA3AF; }

.hora-fija {
  display: block;
  padding: 6px 8px;
  font-weight: 500;
  color: #042D29;
}

.horas-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(4,45,41,0.1);
  color: #042D29;
}

.horas-badge.inactivo { background: #F3F4F6; color: #9CA3AF; }

.total-semanal {
  margin-top: 16px;
  text-align: right;
  font-size: 14px;
  color: #1F2937;
}

.total-semanal strong {
  font-size: 18px;
  color: #042D29;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
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

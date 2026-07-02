<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useTurnosStore } from '@/stores/turnos'
import { useProgramacionesStore } from '@/stores/programaciones'
import TurnoFormModal from './TurnoFormModal.vue'
import api from '@/services/api'

const props = defineProps({
  idEmpleado: { type: Number, default: null },
})

const store = useTurnosStore()
const programacionesStore = useProgramacionesStore()
const mostrarForm = ref(false)
const turnoEditando = ref(null)
const registrando = ref(false)
const mensajeRegistro = ref('')
const tipoRegistro = ref('') // 'entrada' o 'salida'

onMounted(async () => {
  if (props.idEmpleado) {
    await Promise.all([
      store.listar({ id_empleado: props.idEmpleado }),
      programacionesStore.obtener(props.idEmpleado),
    ])
    verificarEstadoHoy()
  }
  await nextTick()
  animar()
})

watch(() => props.idEmpleado, async () => {
  if (props.idEmpleado) {
    await Promise.all([
      store.listar({ id_empleado: props.idEmpleado }),
      programacionesStore.obtener(props.idEmpleado),
    ])
    verificarEstadoHoy()
  }
})

function animar() {
  gsap.fromTo('.turnos-content', { opacity: 0 }, { opacity: 1, duration: 0.3 })
}

const turnoHoy = computed(() => {
  const hoy = new Date().toISOString().split('T')[0]
  return store.items.find(t => t.fecha === hoy) || null
})

const estadoHoy = computed(() => {
  if (!turnoHoy.value) return 'sin_entrada'
  if (!turnoHoy.value.hora_salida) return 'sin_salida'
  return 'completo'
})

function verificarEstadoHoy() {
  if (estadoHoy.value === 'completo') {
    tipoRegistro.value = 'completo'
  } else if (estadoHoy.value === 'sin_salida') {
    tipoRegistro.value = 'salida'
  } else {
    tipoRegistro.value = 'entrada'
  }
}

async function registrarAsistencia() {
  if (!props.idEmpleado) return
  registrando.value = true
  mensajeRegistro.value = ''
  try {
    const ahora = new Date()
    const hora = String(ahora.getHours()).padStart(2, '0') + ':' + String(ahora.getMinutes()).padStart(2, '0')
    const res = await api.post('/turnos/registrar', { id_empleado: props.idEmpleado, hora })
    tipoRegistro.value = res.data.tipo === 'entrada' ? 'salida' : 'completo'
    await store.listar({ id_empleado: props.idEmpleado })
  } catch (err) {
    mensajeRegistro.value = err.response?.data?.message || 'Error al registrar'
  } finally {
    registrando.value = false
  }
}

function abrirForm(turno) {
  turnoEditando.value = turno
  mostrarForm.value = true
}

function cerrarForm() {
  mostrarForm.value = false
  turnoEditando.value = null
}

function formatearFecha(fecha) {
  if (!fecha) return '—'
  const [a, m, d] = fecha.split('-')
  return `${d}/${m}/${a}`
}

function formatearDiff(minutos) {
  if (minutos === null || minutos === undefined) return '—'
  if (minutos === 0) return 'A tiempo'
  if (minutos > 0) return `+${minutos} min`
  return `${minutos} min`
}
</script>

<template>
  <div class="turnos-content">
    <div class="registro-hoy">
      <div class="hoy-info">
        <span class="hoy-label">Hoy</span>
        <span class="hoy-fecha">{{ new Date().toLocaleDateString('es-BO') }}</span>
      </div>
      <div class="hoy-accion">
        <p v-if="mensajeRegistro" class="mensaje-registro" :class="{ error: mensajeRegistro.includes('Error') }">
          {{ mensajeRegistro }}
        </p>
        <button
          v-if="tipoRegistro === 'entrada'"
          class="btn-registro"
          :disabled="registrando"
          @click="registrarAsistencia"
        >
          {{ registrando ? 'Registrando...' : 'Registrar Entrada' }}
        </button>
        <button
          v-else-if="tipoRegistro === 'salida'"
          class="btn-registro btn-salida"
          :disabled="registrando"
          @click="registrarAsistencia"
        >
          {{ registrando ? 'Registrando...' : 'Registrar Salida' }}
        </button>
        <div v-else class="completo-badge">
          Jornada completa
        </div>
      </div>
    </div>

    <p v-if="store.error" class="mensaje-error-sm">{{ store.error }}</p>

    <table class="tabla-turnos">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Esperado</th>
          <th>Entrada</th>
          <th>Salida</th>
          <th>Retrazo (min)</th>
          <th>Estado del Turno actual</th>
          <th>Obs.</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in store.items" :key="t.id_turno">
          <td>{{ formatearFecha(t.fecha) }}</td>
          <td class="esperada-cell">{{ t.hora_entrada_esperada || '—' }}</td>
          <td>{{ t.hora_entrada || '—' }}</td>
          <td>{{ t.hora_salida || '—' }}</td>
          <td>
            <span class="diff-badge" :class="{
              'a-tiempo': t.minutos_tarde === null || t.minutos_tarde === 0,
              'tarde': t.minutos_tarde > 0,
              'temprano': t.minutos_tarde !== null && t.minutos_tarde < 0,
            }">
              {{ t.minutos_tarde !== null ? formatearDiff(t.minutos_tarde) : '—' }}
            </span>
          </td>
          <td>
            <span v-if="t.hora_entrada && t.hora_salida" class="badge-asistencia completo">Completo</span>
            <span v-else-if="t.hora_entrada" class="badge-asistencia parcial">En curso</span>
            <span v-else class="badge-asistencia ausente">Pendiente</span>
          </td>
          <td class="obs-cell">{{ t.observacion || '—' }}</td>
          <td>
            <button class="btn-accion-sm" @click="abrirForm(t)" title="Editar">
              <svg viewBox="0 0 24 24" fill="none" style="width:14px;height:14px">
                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
              </svg>
            </button>
          </td>
        </tr>
        <tr v-if="store.items.length === 0">
          <td colspan="8" class="sin-datos-sm">No hay asistencias registradas</td>
        </tr>
      </tbody>
    </table>

    <Teleport to="body">
      <TurnoFormModal
        v-if="mostrarForm"
        :turno="turnoEditando"
        :id-empleado="idEmpleado"
        @cerrar="cerrarForm"
      />
    </Teleport>
  </div>
</template>

<style scoped>
.turnos-content { padding: 4px 0; }

.registro-hoy {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, #042D29 0%, #052E2A 100%);
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 20px;
  color: #FFFFFF;
}

.hoy-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.hoy-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.7;
}

.hoy-fecha {
  font-size: 16px;
  font-weight: 600;
}

.hoy-accion {
  display: flex;
  align-items: center;
  gap: 12px;
}

.mensaje-registro {
  margin: 0;
  font-size: 12px;
  color: rgba(255,255,255,0.9);
}

.mensaje-registro.error { color: #FCA5A5; }

.btn-registro {
  padding: 10px 22px;
  background: #FFFFFF;
  color: #042D29;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-registro:hover { background: #F5F4F0; transform: scale(1.02); }
.btn-registro:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.btn-salida {
  background: rgba(255,255,255,0.15);
  color: #FFFFFF;
  border: 1.5px solid rgba(255,255,255,0.3);
}

.btn-salida:hover { background: rgba(255,255,255,0.25); }

.completo-badge {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  margin-bottom: 12px;
}

.tabla-turnos {
  width: 100%;
  border-collapse: collapse;
}

.tabla-turnos th {
  padding: 10px 12px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-turnos td {
  padding: 12px;
  font-size: 13px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
}

.tabla-turnos tr:last-child td { border-bottom: none; }
.tabla-turnos tbody tr:hover { background: #F9FAFB; }

.esperada-cell { color: #929079; font-size: 12px; }

.diff-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.a-tiempo { background: rgba(4, 45, 41, 0.1); color: #042D29; }
.tarde { background: #FFF5F5; color: #741102; }
.temprano { background: rgba(146, 144, 121, 0.12); color: #5C5B4E; }

.badge-asistencia {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.completo { background: rgba(4, 45, 41, 0.1); color: #042D29; }
.parcial { background: rgba(146, 144, 121, 0.15); color: #5C5B4E; }
.ausente { background: #FFF5F5; color: #741102; }

.obs-cell { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.btn-accion-sm {
  padding: 4px 8px;
  border: 1.5px solid #D1D5DB;
  border-radius: 6px;
  background: #FFFFFF;
  color: #042D29;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-accion-sm:hover { border-color: #042D29; }

.sin-datos-sm {
  text-align: center;
  color: #929079;
  padding: 30px;
  font-size: 13px;
}
</style>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProgramacionesStore } from '@/stores/programaciones'
import TurnosTab from './TurnosTab.vue'
import PlanillaTab from './PlanillaTab.vue'
import ProgramacionModal from './ProgramacionModal.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const usuario = ref(null)
const loading = ref(true)
const error = ref('')
const tabActivo = ref('info')
const guardandoSueldo = ref(false)
const sueldoEditando = ref(false)
const sueldoForm = ref(0)

const programacionesStore = useProgramacionesStore()
const mostrarModalHorario = ref(false)
const horarioListo = ref(false)

const programaciones = computed(() => programacionesStore.items || [])

const esAdmin = computed(() => auth.tieneRol(1))

onMounted(async () => {
  try {
    const { default: api } = await import('@/services/api')
    const res = await api.get(`/usuarios/${route.params.id}`)
    usuario.value = res.data.usuario
    sueldoForm.value = usuario.value.sueldo_base || 0
    await programacionesStore.obtener(route.params.id)
    horarioListo.value = programaciones.value.length > 0
  } catch {
    error.value = 'Error al cargar datos del empleado'
  } finally {
    loading.value = false
  }
  await nextTick()
  gsap.fromTo('.detalle-header', { y: -20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power3.out' })
  gsap.fromTo('.detalle-body', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
})

function volver() {
  router.push('/usuarios')
}

async function guardarSueldo() {
  guardandoSueldo.value = true
  error.value = ''
  try {
    const { default: api } = await import('@/services/api')
    await api.put(`/usuarios/${route.params.id}`, { sueldo_base: sueldoForm.value })
    usuario.value.sueldo_base = sueldoForm.value
    sueldoEditando.value = false
  } catch (err) {
    const msg = err.response?.data?.errors?.sueldo_base?.[0]
    error.value = msg ? 'Su sueldo no puede ser negativo' : 'Error al guardar sueldo base'
  } finally {
    guardandoSueldo.value = false
  }
}
</script>

<template>
  <div class="detalle-page">
    <div v-if="loading" class="cargando">Cargando...</div>

    <template v-else-if="usuario">
      <div class="detalle-header">
        <button class="btn-volver" @click="volver">
          <svg viewBox="0 0 24 24" fill="none" style="width:18px;height:18px">
            <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Volver
        </button>

        <div class="header-info">
          <div class="avatar">{{ (usuario.nombre_completo || '?')[0] }}</div>
          <div class="header-text">
            <h1>{{ usuario.nombre_completo || 'Sin nombre' }}</h1>
            <div class="header-meta">
              <span class="meta-badge">{{ usuario.rol }}</span>
              <span class="meta-sep">|</span>
              <span>{{ usuario.cargo }}</span>
              <span class="meta-sep">|</span>
              <span>CI: {{ usuario.ci || '—' }}</span>
            </div>
          </div>
        </div>

        <div v-if="esAdmin" class="sueldo-section">
          <label>Sueldo Base</label>
          <div v-if="!sueldoEditando" class="sueldo-display" @click="sueldoEditando = true">
            <span class="sueldo-valor">${{ Number(usuario.sueldo_base || 0).toFixed(2) }}</span>
            <svg viewBox="0 0 24 24" fill="none" class="icon-edit-sueldo">
              <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
          </div>
          <div v-else class="sueldo-edit">
            <input v-model.number="sueldoForm" type="number" min="0" step="0.01" class="sueldo-input" @input="error = ''" />
            <button class="btn-sm btn-save" @click="guardarSueldo" :disabled="guardandoSueldo">Guardar</button>
            <button class="btn-sm btn-cancel" @click="sueldoEditando = false; error = ''">Cancelar</button>
          </div>
        </div>
      </div>

      <p v-if="error" class="mensaje-error">{{ error }}</p>

      <div class="detalle-body">
        <div class="tabs">
          <button class="tab" :class="{ active: tabActivo === 'info' }" @click="tabActivo = 'info'">
            Informaci&oacute;n
          </button>
          <button class="tab" :class="{ active: tabActivo === 'turnos' }" @click="tabActivo = 'turnos'">
            Turnos
          </button>
          <button class="tab" :class="{ active: tabActivo === 'planilla' }" @click="tabActivo = 'planilla'">
            Planilla
          </button>
          <button class="tab" :class="{ active: tabActivo === 'horario' }" @click="tabActivo = 'horario'">
            Horario
          </button>
        </div>

        <div v-if="tabActivo === 'info'" class="tab-content">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Usuario</span>
              <span class="info-value">{{ usuario.username }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">CI</span>
              <span class="info-value">{{ usuario.ci || '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tel&eacute;fono</span>
              <span class="info-value">{{ usuario.telefono || '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Fecha Nacimiento</span>
              <span class="info-value">{{ usuario.fecha_nacimiento || '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Cargo</span>
              <span class="info-value">{{ usuario.cargo || '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Fecha Ingreso</span>
              <span class="info-value">{{ usuario.fecha_ingreso || '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Roles</span>
              <span class="info-value">
                <span v-for="r in (usuario.roles || [])" :key="r.id_rol" class="rol-badge">
                  {{ r.nombre }}
                </span>
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Estado</span>
              <span class="info-value" :class="usuario.estadoA ? 'text-active' : 'text-inactive'">
                {{ usuario.estadoA ? 'Activo' : 'Inactivo' }}
              </span>
            </div>
          </div>
        </div>

        <TurnosTab v-if="tabActivo === 'turnos'" :id-empleado="usuario.id_empleado" />

        <PlanillaTab v-if="tabActivo === 'planilla'" :id-empleado="usuario.id_empleado" :sueldo-base="usuario.sueldo_base" />

        <div v-if="tabActivo === 'horario'" class="tab-content">
          <div class="horario-header-tab">
            <h3>Horario Semanal</h3>
            <button class="btn-horario" @click="mostrarModalHorario = true">
              {{ programaciones.length > 0 ? 'Editar Horario' : 'Configurar Horario' }}
            </button>
          </div>

          <div v-if="programaciones.length > 0" class="horario-grid-tab">
            <div class="horario-grid-header">
              <span>D&iacute;a</span>
              <span>Entrada</span>
              <span>Salida</span>
              <span>Horas</span>
            </div>
            <div v-for="p in programaciones" :key="p.dia_semana" class="horario-grid-fila" :class="{ inactivo: !p.activo }">
              <span class="dia-nombre">{{ ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][p.dia_semana - 1] }}</span>
              <span>{{ p.activo ? (p.hora_entrada || '—') : '—' }}</span>
              <span>{{ p.activo ? (p.hora_salida || '—') : '—' }}</span>
              <span v-if="p.activo && p.hora_entrada && p.hora_salida" class="horas-celda">
                {{ (() => { const [h1,m1]=p.hora_entrada.split(':').map(Number); const [h2,m2]=p.hora_salida.split(':').map(Number); return ((h2*60+m2)-(h1*60+m1))/60; })() }}h
              </span>
              <span v-else class="horas-celda">—</span>
            </div>
          </div>
          <p v-else class="sin-horario">No se ha configurado horario semanal.</p>
        </div>

        <Teleport to="body">
          <ProgramacionModal
            v-if="mostrarModalHorario"
            :id-empleado="Number(route.params.id)"
            :horario-actual="programaciones"
            @cerrar="mostrarModalHorario = false"
            @guardado="() => { mostrarModalHorario = false; horarioListo = true }"
          />
        </Teleport>
      </div>
    </template>

    <div v-else class="cargando">Empleado no encontrado</div>
  </div>
</template>

<style scoped>
.detalle-page {
  padding: 32px;
  max-width: 1100px;
  margin: 0 auto;
}

.cargando {
  text-align: center;
  color: #929079;
  padding: 60px;
  font-size: 14px;
}

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 20px;
}

.detalle-header {
  background: #FFFFFF;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  margin-bottom: 24px;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29, #741102) 1;
}

.btn-volver {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: none;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #929079;
  cursor: pointer;
  margin-bottom: 16px;
  transition: all 0.2s ease;
}

.btn-volver:hover {
  border-color: #042D29;
  color: #042D29;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #042D29, #052E2A);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  flex-shrink: 0;
}

.header-text h1 {
  font-size: 22px;
  font-weight: 700;
  color: #042D29;
  margin: 0;
}

.header-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
  font-size: 13px;
  color: #929079;
}

.meta-badge {
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  background: rgba(116, 17, 2, 0.1);
  color: #741102;
}

.meta-sep { color: #D1D5DB; }

.sueldo-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  gap: 12px;
}

.sueldo-section label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.sueldo-display {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 12px 4px 0;
  border-radius: 8px;
  transition: background 0.15s;
}

.sueldo-display:hover { background: #F9FAFB; }

.sueldo-valor {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
}

.icon-edit-sueldo {
  width: 16px;
  height: 16px;
  color: #929079;
}

.sueldo-edit {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sueldo-input {
  width: 120px;
  padding: 6px 10px;
  border: 1.5px solid #042D29;
  border-radius: 8px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
}

.btn-sm {
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-save { background: #042D29; color: #FFFFFF; }
.btn-save:hover { background: #052E2A; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancel { background: #FFFFFF; color: #929079; border: 1.5px solid #D1D5DB; }
.btn-cancel:hover { border-color: #929079; color: #1F2937; }

.detalle-body {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29, #741102) 1;
}

.tabs {
  display: flex;
  border-bottom: 1px solid #E5E7EB;
  padding: 0 24px;
}

.tab {
  padding: 14px 24px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: -1px;
}

.tab:hover { color: #042D29; }
.tab.active { color: #042D29; font-weight: 600; border-bottom-color: #042D29; }

.tab-content {
  padding: 24px;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-label {
  font-size: 12px;
  font-weight: 500;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 14px;
  font-weight: 500;
  color: #1F2937;
}

.rol-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(4, 45, 41, 0.1);
  color: #042D29;
  margin-right: 4px;
}

.text-active { color: #042D29; }
.text-inactive { color: #9CA3AF; }

.horario-header-tab {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.horario-header-tab h3 {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.btn-horario {
  padding: 8px 16px;
  background: rgba(4,45,41,0.08);
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-horario:hover { background: rgba(4,45,41,0.15); }

.horario-grid-tab {
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  overflow: hidden;
}

.horario-grid-header {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
  padding: 10px 16px;
  font-size: 11px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.horario-grid-fila {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  padding: 10px 16px;
  border-bottom: 1px solid #F3F4F6;
  font-size: 13px;
  color: #1F2937;
}

.horario-grid-fila:last-child { border-bottom: none; }
.horario-grid-fila.inactivo { opacity: 0.5; }

.dia-nombre { font-weight: 600; color: #042D29; }

.horas-celda {
  font-weight: 600;
  color: #042D29;
}

.sin-horario {
  color: #929079;
  font-size: 14px;
  text-align: center;
  padding: 32px;
}
</style>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProgramacionesStore } from '@/stores/programaciones'
import { useToastStore } from '@/stores/toast'

const router = useRouter()
const store = useProgramacionesStore()
const toast = useToastStore()

const nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
const busqueda = ref('')
const keyTabla = ref(0)

const empleadosFiltrados = computed(() => {
  if (!store.globalData) return []
  if (!busqueda.value) return store.globalData.empleados
  const q = busqueda.value.toLowerCase()
  return store.globalData.empleados.filter(emp => {
    const nombre = (emp.empleado?.nombre_completo || '').toLowerCase()
    const ci = (emp.empleado?.ci || '').toLowerCase()
    return nombre.includes(q) || ci.includes(q)
  })
})

function animarFilas() {
  gsap.fromTo('.empleado-row',
    { y: 15, opacity: 0 },
    { y: 0, opacity: 1, duration: 0.3, stagger: 0.08, ease: 'power2.out' }
  )
  gsap.fromTo('.dia-card',
    { scale: 0.95, opacity: 0 },
    { scale: 1, opacity: 1, duration: 0.2, stagger: 0.03, ease: 'power2.out', delay: 0.15 }
  )
}

onMounted(async () => {
  await store.obtenerGlobal()
  if (store.error) {
    toast.error(store.error)
  }
  await nextTick()
  gsap.fromTo('.page-header', { y: -20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power3.out' })
  gsap.fromTo('.search-bar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.08 })
  gsap.fromTo('.empleado-row', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, stagger: 0.08, ease: 'power2.out', delay: 0.15 })
  gsap.fromTo('.dia-card', { scale: 0.95, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.2, stagger: 0.03, ease: 'power2.out', delay: 0.3 })
})

watch(busqueda, async () => {
  keyTabla.value++
  await nextTick()
  animarFilas()
})

function irADetalle(idUsuario) {
  router.push(`/usuarios/${idUsuario}`)
}
</script>

<template>
  <div class="horarios-page">
    <div class="page-header">
      <h1>Horarios Semanales</h1>
      <p class="subtitle">Planilla global de horarios de todos los empleados</p>
    </div>

    <div v-if="store.globalData" class="search-bar">
      <svg viewBox="0 0 24 24" fill="none" class="search-icon">
        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
      </svg>
      <input
        v-model="busqueda"
        type="text"
        class="search-input"
        placeholder="Buscar por nombre o CI..."
      />
    </div>

    <div v-if="store.loading" class="cargando">Cargando horarios...</div>

    <div v-else-if="store.globalData" class="global-card">
      <div v-if="empleadosFiltrados.length === 0" class="sin-datos">
        {{ busqueda ? 'No se encontraron empleados con ese criterio' : 'No hay horarios configurados.' }}
      </div>

      <div v-for="emp in empleadosFiltrados" :key="keyTabla + '-' + emp.id_empleado" class="empleado-row">
        <div class="emp-header" @click="irADetalle(emp.empleado?.id_usuario)">
          <div class="emp-avatar">{{ (emp.empleado?.nombre_completo || '?')[0] }}</div>
          <div class="emp-info">
            <span class="emp-nombre">{{ emp.empleado?.nombre_completo }}</span>
            <span class="emp-cargo">{{ emp.empleado?.cargo || '—' }}</span>
          </div>
          <div class="emp-total">
            <span class="total-label">Total</span>
            <span class="total-valor">{{ emp.total_horas_semana }}h</span>
          </div>
        </div>

        <div class="dias-grid">
          <div
            v-for="dia in emp.dias"
            :key="dia.dia_semana"
            class="dia-card"
            :class="{ libre: !dia.activo }"
          >
            <span class="dia-nombre">{{ nombresDias[dia.dia_semana - 1] }}</span>
            <template v-if="dia.activo">
              <span class="dia-hora">{{ dia.hora_entrada }} — {{ dia.hora_salida }}</span>
              <span class="dia-horas">
                {{ (() => { const [h1,m1]=dia.hora_entrada.split(':').map(Number); const [h2,m2]=dia.hora_salida.split(':').map(Number); return ((h2*60+m2)-(h1*60+m1))/60; })() }}h
              </span>
            </template>
            <span v-else class="dia-libre">Libre</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.horarios-page {
  padding: 32px;
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #042D29;
  margin: 0;
}

.subtitle {
  font-size: 13px;
  color: #929079;
  margin-top: 4px;
}

.cargando, .page-header, .search-bar, .dia-card {
  opacity: 0;
}

.sin-datos {
  text-align: center;
  color: #929079;
  padding: 60px;
  font-size: 14px;
}

.search-bar {
  position: relative;
  margin-bottom: 20px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #929079;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 11px 14px 11px 42px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #FFFFFF;
}

.search-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.search-input::placeholder {
  color: #929079;
}

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
}

.global-card {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29, #741102) 1;
  overflow: hidden;
}

.empleado-row {
  border-bottom: 1px solid #E5E7EB;
  padding: 20px 24px;
}

.empleado-row:last-child { border-bottom: none; }

.emp-header {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 4px 0;
  border-radius: 8px;
  transition: background 0.15s;
  margin-bottom: 14px;
}

.emp-header:hover { background: #F9FAFB; }

.emp-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #042D29, #052E2A);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  flex-shrink: 0;
}

.emp-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.emp-nombre {
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
}

.emp-cargo {
  font-size: 12px;
  color: #929079;
}

.emp-total {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.total-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #929079;
}

.total-valor {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
}

.dias-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 8px;
}

.dia-card {
  background: #F9FAFB;
  border-radius: 10px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  border: 1.5px solid transparent;
  transition: all 0.15s;
}

.dia-card:hover { border-color: #042D29; }

.dia-card.libre {
  opacity: 0.5;
}

.dia-nombre {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #042D29;
}

.dia-hora {
  font-size: 12px;
  color: #1F2937;
  font-weight: 500;
}

.dia-horas {
  font-size: 14px;
  font-weight: 700;
  color: #042D29;
}

.dia-libre {
  font-size: 12px;
  color: #929079;
  font-style: italic;
}
</style>

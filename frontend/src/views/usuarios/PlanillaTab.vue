<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { usePlanillasStore } from '@/stores/planillas'
import PlanillaFormModal from './PlanillaFormModal.vue'

const props = defineProps({
  idEmpleado: { type: Number, default: null },
  sueldoBase: { type: Number, default: 0 },
})

const store = usePlanillasStore()
const mostrarForm = ref(false)

const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']

onMounted(async () => {
  if (props.idEmpleado) {
    await store.listar({ id_empleado: props.idEmpleado })
  }
  await nextTick()
  animar()
})

watch(() => props.idEmpleado, async () => {
  if (props.idEmpleado) {
    await store.listar({ id_empleado: props.idEmpleado })
  }
})

function animar() {
  gsap.fromTo('.planilla-content', { opacity: 0 }, { opacity: 1, duration: 0.3 })
}

const totalNeto = computed(() => {
  return store.items.reduce((sum, p) => sum + (p.sueldo_neto || 0), 0)
})

function abrirForm() {
  mostrarForm.value = true
}

function cerrarForm() {
  mostrarForm.value = false
}

async function eliminarPlanilla(id) {
  if (confirm('¿Eliminar esta planilla?')) {
    await store.eliminar(id)
  }
}
</script>

<template>
  <div class="planilla-content">
    <div class="tab-toolbar">
      <div class="total-acumulado">
        Total pagado: <strong>${{ totalNeto.toFixed(2) }}</strong>
      </div>
      <button class="btn-nuevo-sm" @click="abrirForm">
        + Nueva Liquidaci&oacute;n
      </button>
    </div>

    <p v-if="store.error" class="mensaje-error-sm">{{ store.error }}</p>

    <table class="tabla-planillas">
      <thead>
        <tr>
          <th>Periodo</th>
          <th>Sueldo Bruto</th>
          <th>Bonos</th>
          <th>Deducciones</th>
          <th>Sueldo Neto</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in store.items" :key="p.id_planilla">
          <td><strong>{{ meses[p.mes - 1] }}</strong> {{ p.anio }}</td>
          <td>${{ Number(p.sueldo_bruto).toFixed(2) }}</td>
          <td class="text-bonos">+${{ Number(p.bonos).toFixed(2) }}</td>
          <td class="text-deducciones">-${{ Number(p.deducciones).toFixed(2) }}</td>
          <td class="text-neto">${{ Number(p.sueldo_neto).toFixed(2) }}</td>
          <td>
            <button class="btn-accion-sm btn-eliminar-sm" @click="eliminarPlanilla(p.id_planilla)" title="Eliminar">
              <svg viewBox="0 0 24 24" fill="none" style="width:14px;height:14px">
                <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </td>
        </tr>
        <tr v-if="store.items.length === 0">
          <td colspan="6" class="sin-datos-sm">No hay planillas registradas</td>
        </tr>
      </tbody>
    </table>

    <Teleport to="body">
      <PlanillaFormModal
        v-if="mostrarForm"
        :id-empleado="idEmpleado"
        :sueldo-base="sueldoBase"
        @cerrar="cerrarForm"
      />
    </Teleport>
  </div>
</template>

<style scoped>
.planilla-content { padding: 4px 0; }

.tab-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.total-acumulado {
  font-size: 14px;
  color: #1F2937;
}

.total-acumulado strong {
  color: #042D29;
  font-size: 16px;
}

.btn-nuevo-sm {
  padding: 8px 16px;
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

.btn-nuevo-sm:hover { background: #052E2A; }

.mensaje-error-sm {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  margin-bottom: 12px;
}

.tabla-planillas {
  width: 100%;
  border-collapse: collapse;
}

.tabla-planillas th {
  padding: 10px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: #929079;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-planillas td {
  padding: 12px 14px;
  font-size: 13px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
}

.tabla-planillas tr:last-child td { border-bottom: none; }
.tabla-planillas tbody tr:hover { background: #F9FAFB; }

.text-bonos { color: #042D29; font-weight: 500; }
.text-deducciones { color: #741102; font-weight: 500; }
.text-neto { font-weight: 700; color: #042D29; }

.btn-accion-sm {
  padding: 4px 8px;
  border: 1.5px solid #D1D5DB;
  border-radius: 6px;
  background: #FFFFFF;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-eliminar-sm:hover { border-color: #741102; color: #741102; }

.sin-datos-sm {
  text-align: center;
  color: #929079;
  padding: 30px;
  font-size: 13px;
}
</style>

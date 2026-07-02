<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { usePlanillasStore } from '@/stores/planillas'
import { useToastStore } from '@/stores/toast'
import PlanillaFormModal from './PlanillaFormModal.vue'

const props = defineProps({
  idEmpleado: { type: Number, default: null },
  sueldoBase: { type: Number, default: 0 },
})

const store = usePlanillasStore()
const toast = useToastStore()
const mostrarForm = ref(false)
const confirmarEliminar = ref(false)
const planillaEliminar = ref(null)

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

async function ejecutarEliminar() {
  await store.eliminar(planillaEliminar.value)
  toast.show('Planilla eliminada correctamente')
  confirmarEliminar.value = false
  planillaEliminar.value = null
}

function confirmarEliminarPlanilla(id) {
  planillaEliminar.value = id
  confirmarEliminar.value = true
}

function cerrarConfirmar() {
  confirmarEliminar.value = false
  planillaEliminar.value = null
}
</script>

<template>
  <div class="planilla-content">
    <div class="tab-toolbar">
      <div class="total-acumulado">
        Total pagado: <strong>Bs {{ totalNeto.toFixed(2) }}</strong>
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
          <td>Bs {{ Number(p.sueldo_bruto).toFixed(2) }}</td>
          <td class="text-bonos">+Bs {{ Number(p.bonos).toFixed(2) }}</td>
          <td class="text-deducciones">-Bs {{ Number(p.deducciones).toFixed(2) }}</td>
          <td class="text-neto">Bs {{ Number(p.sueldo_neto).toFixed(2) }}</td>
          <td>
            <button class="btn-accion-sm btn-eliminar-sm" @click="confirmarEliminarPlanilla(p.id_planilla)" title="Eliminar">
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

    <Teleport to="body">
      <div v-if="confirmarEliminar" class="modal-overlay" @click.self="cerrarConfirmar">
        <div class="modal-confirmar">
          <h3>Eliminar Planilla</h3>
          <p>¿Estás seguro de eliminar esta planilla?</p>
          <p class="nota-eliminar">Una vez eliminada no se podrá recuperar.</p>
          <div class="acciones-confirmar">
            <button class="btn-cancelar-sm" @click="cerrarConfirmar">Volver</button>
            <button class="btn-confirmar-eliminar" @click="ejecutarEliminar">Sí, eliminar</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.planilla-content { padding: 4px 16px; }

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

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 110;
  padding: 20px;
}

.modal-confirmar {
  background: #FFFFFF;
  border-radius: 14px;
  max-width: 440px;
  width: 100%;
  padding: 28px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-confirmar h3 {
  font-size: 18px;
  font-weight: 600;
  color: #042D29;
  margin-bottom: 12px;
}

.modal-confirmar p {
  font-size: 14px;
  color: #1F2937;
  line-height: 1.5;
  margin-bottom: 8px;
}

.nota-eliminar {
  font-size: 12px;
  color: #929079;
  margin-bottom: 20px;
}

.acciones-confirmar {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancelar-sm {
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

.btn-cancelar-sm:hover { border-color: #929079; color: #1F2937; }

.btn-confirmar-eliminar {
  padding: 10px 24px;
  background: #741102;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-confirmar-eliminar:hover { background: #5A0D01; }
</style>

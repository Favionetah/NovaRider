<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { usePlanillasStore } from '@/stores/planillas'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  idEmpleado: { type: Number, required: true },
  sueldoBase: { type: Number, default: 0 },
})

const emit = defineEmits(['cerrar'])

const store = usePlanillasStore()
const toast = useToastStore()

const now = new Date()
const form = ref({
  id_empleado: props.idEmpleado,
  mes: now.getMonth() + 1,
  anio: now.getFullYear(),
  sueldo_bruto: 0,
  bonos: 0,
  deducciones: 0,
  sueldo_neto: 0,
})

const errores = ref({})
const guardando = ref(false)
const errorGeneral = ref('')

const sueldoNeto = computed(() => {
  return (Number(form.value.sueldo_bruto) || 0) + (Number(form.value.bonos) || 0) - (Number(form.value.deducciones) || 0)
})

onMounted(async () => {
  form.value.sueldo_bruto = Number(props.sueldoBase || 0)

  await nextTick()
  gsap.fromTo('.modal-card', { y: 20, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.25, ease: 'power3.out' })
})

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  try {
    await store.crear({
      ...form.value,
      sueldo_neto: sueldoNeto.value,
    })
    toast.show('Liquidación guardada correctamente')
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al guardar planilla'
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
        <h2>Nueva Liquidaci&oacute;n</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error-sm">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Mes <span class="required">*</span></label>
            <select v-model.number="form.mes" :class="{ 'input-error': errores.mes }">
              <option v-for="m in 12" :key="m" :value="m">{{ ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][m-1] }}</option>
            </select>
            <span v-if="errores.mes" class="error-text">{{ errores.mes[0] }}</span>
          </div>
          <div class="form-group">
            <label>A&ntilde;o <span class="required">*</span></label>
            <input v-model.number="form.anio" type="number" min="2020" max="2100" />
          </div>
          <div class="form-group">
            <label>Sueldo Bruto <span class="required">*</span></label>
            <input v-model.number="form.sueldo_bruto" type="number" min="0" step="0.01" />
          </div>
          <div class="form-group">
            <label>Bonos</label>
            <input v-model.number="form.bonos" type="number" min="0" step="0.01" placeholder="0" />
          </div>
          <div class="form-group">
            <label>Deducciones</label>
            <input v-model.number="form.deducciones" type="number" min="0" step="0.01" placeholder="0" />
          </div>
          <div class="form-group">
            <label>Sueldo Neto</label>
            <div class="neto-calculado">
              Bs {{ sueldoNeto.toFixed(2) }}
            </div>
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
  min-width: 0;
}

.form-group label { font-size: 13px; font-weight: 500; color: #1F2937; }
.required { color: #741102; }

.form-group input,
.form-group select {
  padding: 9px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus { border-color: #042D29; }

.form-group input.input-error,
.form-group select.input-error { border-color: #741102; }

.error-text { font-size: 12px; color: #741102; }

.neto-calculado {
  padding: 9px 12px;
  background: #F9FAFB;
  border: 1.5px solid #E5E7EB;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 700;
  color: #042D29;
}

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

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useProveedoresStore } from '@/stores/proveedores'

const props = defineProps({
  proveedor: { type: Object, default: null },
})

const emit = defineEmits(['cerrar'])

const store = useProveedoresStore()

const esEdicion = !!props.proveedor

const form = ref({
  nombre: '',
  telefono: '',
  direccion: '',
})

const errores = ref({})
const guardando = ref(false)
const errorGeneral = ref('')

onMounted(async () => {
  if (esEdicion) {
    form.value.nombre = props.proveedor.nombre || ''
    form.value.telefono = props.proveedor.telefono || ''
    form.value.direccion = props.proveedor.direccion || ''
  }

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  try {
    if (esEdicion) {
      await store.actualizar(props.proveedor.id_proveedor, form.value)
    } else {
      await store.crear(form.value)
    }
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al guardar proveedor'
    }
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="cerrar">
    <div class="modal-card">
      <div class="modal-header">
        <h2>{{ esEdicion ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group form-group-full">
            <label for="nombre">Nombre <span class="required">*</span></label>
            <input
              id="nombre"
              v-model="form.nombre"
              type="text"
              placeholder="Nombre del proveedor"
              :class="{ 'input-error': errores.nombre }"
            />
            <span v-if="errores.nombre" class="error-text">{{ errores.nombre[0] }}</span>
          </div>

          <div class="form-group">
            <label for="telefono">Tel&eacute;fono</label>
            <input
              id="telefono"
              v-model="form.telefono"
              type="text"
              placeholder="N&uacute;mero de tel&eacute;fono"
            />
          </div>

          <div class="form-group">
            <label for="direccion">Direcci&oacute;n</label>
            <input
              id="direccion"
              v-model="form.direccion"
              type="text"
              placeholder="Direcci&oacute;n"
            />
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
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: #042D29;
}

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

.modal-body {
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group-full {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.required { color: #741102; }

.form-group input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.form-group input.input-error {
  border-color: #741102;
}

.error-text {
  font-size: 12px;
  color: #741102;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #E5E7EB;
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

.btn-cancelar:hover {
  border-color: #929079;
  color: #1F2937;
}

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

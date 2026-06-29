<script setup>
import { ref, watch, computed } from 'vue'
import { useMotocicletasStore } from '@/stores/motocicletasStore'

const props = defineProps({
  motocicleta: { type: Object, default: null },
  clientes: { type: Array, default: () => [] },
})
const emit = defineEmits(['cerrar'])

const store = useMotocicletasStore()
const enviando = ref(false)
const mensajeError = ref('')

const maxYear = new Date().getFullYear() + 1

const form = ref({
  id_cliente: props.motocicleta?.id_cliente || '',
  placa: props.motocicleta?.placa || '',
  marca: props.motocicleta?.marca || '',
  modelo: props.motocicleta?.modelo || '',
  anio: props.motocicleta?.anio || '',
  nro_chasis: props.motocicleta?.nro_chasis || '',
  nro_motor: props.motocicleta?.nro_motor || '',
  color: props.motocicleta?.color || '',
  cilindrada: props.motocicleta?.cilindrada || '',
})

const errores = ref({
  id_cliente: '',
  placa: '',
  marca: '',
  modelo: '',
  anio: '',
})

const esEdicion = computed(() => !!props.motocicleta)

watch(
  () => props.motocicleta,
  (nuevo) => {
    form.value = {
      id_cliente: nuevo?.id_cliente || '',
      placa: nuevo?.placa || '',
      marca: nuevo?.marca || '',
      modelo: nuevo?.modelo || '',
      anio: nuevo?.anio || '',
      nro_chasis: nuevo?.nro_chasis || '',
      nro_motor: nuevo?.nro_motor || '',
      color: nuevo?.color || '',
      cilindrada: nuevo?.cilindrada || '',
    }
    limpiarErrores()
    mensajeError.value = ''
  },
  { immediate: true }
)

function limpiarErrores() {
  Object.keys(errores.value).forEach((key) => {
    errores.value[key] = ''
  })
}

function validar() {
  let valido = true
  limpiarErrores()

  if (!form.value.id_cliente) {
    errores.value.id_cliente = 'El cliente es obligatorio'
    valido = false
  }

  if (!form.value.placa) {
    errores.value.placa = 'Obligatoria'
    valido = false
  }

  if (!form.value.marca) {
    errores.value.marca = 'Obligatoria'
    valido = false
  } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.-]+$/.test(form.value.marca)) {
    errores.value.marca = 'Solo letras'
    valido = false
  }

  if (!form.value.modelo) {
    errores.value.modelo = 'Obligatorio'
    valido = false
  } else if (!/^[a-zA-Z0-9\s.-]+$/.test(form.value.modelo)) {
    errores.value.modelo = 'Caracteres no válidos'
    valido = false
  }

  if (!form.value.anio) {
    errores.value.anio = 'Obligatorio'
    valido = false
  } else if (!/^\d{4}$/.test(String(form.value.anio))) {
    errores.value.anio = '4 números'
    valido = false
  }

  if (form.value.cilindrada && !/^\d+$/.test(String(form.value.cilindrada))) {
    mensajeError.value = 'Cilindrada solo números'
    valido = false
  }

  return valido
}

async function guardar() {
  mensajeError.value = ''
  if (!validar()) return

  enviando.value = true

  try {
    if (esEdicion.value) {
      await store.actualizar(props.motocicleta.id_motocicleta, form.value)
    } else {
      await store.crear(form.value)
    }
    emit('cerrar')
  } catch (error) {
    mensajeError.value = error.response?.data?.message || 'Error al guardar motocicleta'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('cerrar')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>{{ esEdicion ? 'Editar Motocicleta' : 'Nueva Motocicleta' }}</h2>
        <button class="btn-cerrar" @click="emit('cerrar')">×</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <p v-if="mensajeError" class="mensaje-error">{{ mensajeError }}</p>

        <div class="form-grid">
          <div class="campo" :class="{ 'has-error': errores.id_cliente }">
            <label for="id_cliente">Cliente</label>
            <select id="id_cliente" v-model="form.id_cliente">
              <option value="">Selecciona un cliente</option>
              <option v-for="cliente in props.clientes" :key="cliente.id_cliente" :value="cliente.id_cliente">
                {{ cliente.primer_nombre }} {{ cliente.apellido_paterno }} {{ cliente.apellido_materno || '' }}
              </option>
            </select>
            <p v-if="errores.id_cliente" class="field-error">{{ errores.id_cliente }}</p>
          </div>
          <div class="campo" :class="{ 'has-error': errores.placa }">
            <label for="placa">Placa</label>
            <input id="placa" v-model="form.placa" type="text" />
            <p v-if="errores.placa" class="field-error">{{ errores.placa }}</p>
          </div>
          <div class="campo" :class="{ 'has-error': errores.marca }">
            <label for="marca">Marca</label>
            <input id="marca" v-model="form.marca" type="text" />
            <p v-if="errores.marca" class="field-error">{{ errores.marca }}</p>
          </div>
          <div class="campo" :class="{ 'has-error': errores.modelo }">
            <label for="modelo">Modelo</label>
            <input id="modelo" v-model="form.modelo" type="text" />
            <p v-if="errores.modelo" class="field-error">{{ errores.modelo }}</p>
          </div>
          <div class="campo" :class="{ 'has-error': errores.anio }">
            <label for="anio">Año</label>
            <input id="anio" v-model="form.anio" type="number" min="1900" :max="maxYear" />
            <p v-if="errores.anio" class="field-error">{{ errores.anio }}</p>
          </div>
          <div class="campo">
            <label for="nro_chasis">Nro. Chasis</label>
            <input id="nro_chasis" v-model="form.nro_chasis" type="text" />
          </div>
          <div class="campo">
            <label for="nro_motor">Nro. Motor</label>
            <input id="nro_motor" v-model="form.nro_motor" type="text" />
          </div>
          <div class="campo">
            <label for="color">Color</label>
            <input id="color" v-model="form.color" type="text" />
          </div>
          <div class="campo">
            <label for="cilindrada">Cilindrada</label>
            <input id="cilindrada" v-model="form.cilindrada" type="text" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="emit('cerrar')">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="enviando">
            {{ enviando ? 'Guardando...' : 'Guardar' }}
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
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 110;
  padding: 16px;
}

.modal-card {
  width: 100%;
  max-width: 640px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.12);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 22px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #042D29;
}

.btn-cerrar {
  background: transparent;
  border: none;
  font-size: 26px;
  color: #6B7280;
  cursor: pointer;
}

.modal-form {
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.campo {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.campo label {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
}

.campo input,
.campo select {
  border: 1.5px solid #D1D5DB;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.campo input:focus,
.campo select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 4px rgba(4, 45, 41, 0.08);
}

.field-error {
  color: #741102;
  font-size: 13px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 0 24px 24px;
  margin-top: 28px;
}

.btn-cancelar,
.btn-guardar {
  border: none;
  border-radius: 10px;
  padding: 12px 18px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancelar {
  background: #F5F4F0;
  color: #042D29;
}

.btn-guardar {
  background: #042D29;
  color: #FFFFFF;
}

.btn-guardar:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.mensaje-error {
  margin-bottom: 16px;
  padding: 14px 16px;
  background: #FFF5F5;
  border: 1px solid #F1A8A8;
  color: #741102;
  border-radius: 12px;
}
</style>

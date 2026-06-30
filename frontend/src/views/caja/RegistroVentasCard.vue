<script setup>
import { ref } from 'vue'

const props = defineProps({
  cajaAbierta: { type: Boolean, default: false },
  clientes: { type: Array, default: () => [] }
})

const emit = defineEmits(['onAgregarItem', 'onCambiarCliente'])

const concepto = ref('')
const precio = ref('')
const clienteSeleccionadoLocal = ref('Cliente General')

function agregarAlCarrito() {
  if (!concepto.value || !precio.value) return

  emit('onAgregarItem', {
    id: Date.now(), 
    concepto: concepto.value,
    precio: parseFloat(precio.value)
  })

  concepto.value = ''
  precio.value = ''
}

function actualizarClientePadre() {
  emit('onCambiarCliente', clienteSeleccionadoLocal.value)
}
</script>

<template>
  <div class="registro-ventas-card">
    <div class="card-header">
      <svg viewBox="0 0 24 24" fill="none" width="20" height="20" class="icon-header">
        <path d="M12 4v16m8-8H4" stroke="#042D29" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <h3>Registrar Venta</h3>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label>Concepto / Servicio</label>
          <input 
            v-model="concepto" 
            type="text" 
            placeholder="Ej. Lavado de Motocicleta" 
            :disabled="!cajaAbierta"
          />
        </div>

        <div class="form-group">
          <label>Precio Unitario</label>
          <div class="input-prefix-wrapper">
            <span class="prefix">Bs.</span>
            <input 
              v-model.number="precio" 
              type="number" 
              placeholder="0.00" 
              min="0" 
              :disabled="!cajaAbierta"
            />
          </div>
        </div>

        <div class="form-group full-width-group">
          <label class="label-resaltado">Cliente Beneficiario</label>
          <select 
            v-model="clienteSeleccionadoLocal" 
            @change="actualizarClientePadre"
            :disabled="!cajaAbierta"
            class="select-formulario-central"
          >
            <option v-for="c in clientes" :key="c.id" :value="c.nombre">
              {{ c.nombre }}
            </option>
          </select>
        </div>
      </div>

      <button 
        class="btn-agregar-carrito" 
        :disabled="!cajaAbierta || !concepto || !precio"
        @click="agregarAlCarrito"
      >
        <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Agregar al Carrito
      </button>
    </div>
  </div>
</template>

<style scoped>
.registro-ventas-card { 
  background: #FFFFFF; 
  border-radius: 16px; 
  box-shadow: 0 4px 16px rgba(0,0,0,0.05); 
  overflow: hidden; 
  height: 100%; 
  display: flex; 
  flex-direction: column; 
}
.card-header { display: flex; align-items: center; gap: 10px; padding: 18px 24px; border-bottom: 1px solid #E5E7EB; background: #FAFAFA; }
.card-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #042D29; }
.icon-header { color: #042D29; }
.card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; flex: 1; justify-content: space-between; }
.form-grid { display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 600; color: #4B5563; text-transform: uppercase; }
.form-group input { padding: 10px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 14px; outline: none; background: #FFFFFF; color: #1F2937; transition: border-color 0.2s; }
.form-group input:focus { border-color: #042D29; }
.input-prefix-wrapper { position: relative; display: flex; align-items: center; }
.prefix { position: absolute; left: 12px; font-size: 14px; font-weight: 600; color: #4B5563; }
.input-prefix-wrapper input { padding-left: 40px; width: 100%; box-sizing: border-box; }
.full-width-group { margin-top: 4px; }
.label-resaltado { color: #042D29 !important; font-weight: 700 !important; }
.select-formulario-central { width: 100%; padding: 11px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 14px; font-weight: 600; background-color: #FFFFFF; color: #1F2937; outline: none; transition: border-color 0.2s; cursor: pointer; }
.select-formulario-central:focus { border-color: #042D29; }
.select-formulario-central:disabled { background: #F3F4F6; cursor: not-allowed; color: #9CA3AF; }
.btn-agregar-carrito { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: #042D29; color: #FFFFFF; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: auto; }
.btn-agregar-carrito:hover:not(:disabled) { background: #0b4640; }
.btn-agregar-carrito:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
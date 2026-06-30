<script setup>
import { ref } from 'vue'

defineProps({
  cajaAbierta: { type: Boolean, default: false },
})

const emit = defineEmits(['onAgregarItem'])

const concepto = ref('')
const precio = ref(0)

function agregar() {
  if (!concepto.value.trim() || precio.value <= 0) return
  emit('onAgregarItem', {
    concepto: concepto.value.trim(),
    precio: parseFloat(precio.value),
  })
  concepto.value = ''
  precio.value = 0
}
</script>

<template>
  <div class="card-seccion">
    <div class="card-seccion-header">
      <div class="header-row">
        <svg viewBox="0 0 24 24" fill="none" width="20" height="20" class="header-icon">
          <path d="M12 5v14M5 12h14" stroke="#042D29" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <h3>Registrar Venta</h3>
      </div>
      <p class="texto-ayuda">Agregue productos o servicios al carrito de venta</p>
    </div>

    <div class="card-seccion-body">
      <div v-if="!cajaAbierta" class="empty-state">
        Debe abrir la jornada para registrar ventas.
      </div>

      <div v-else class="form-vertical">
        <div class="form-group">
          <label>Concepto / Producto / Servicio</label>
          <input
            v-model="concepto"
            type="text"
            placeholder="Ej: Cambio de aceite, Filtro de aire..."
            @keyup.enter="agregar"
          />
        </div>
        <div class="form-row">
          <div class="form-group" style="flex: 1;">
            <label>Precio (S/)</label>
            <input
              v-model.number="precio"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              @keyup.enter="agregar"
            />
          </div>
          <div class="form-action-btn">
            <button class="btn-primario" @click="agregar">
              <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Agregar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-seccion {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
}

.card-seccion-header {
  padding: 20px 24px 12px;
}

.header-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-icon { flex-shrink: 0; }

.card-seccion-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.texto-ayuda {
  font-size: 13px;
  color: #929079;
  margin: 4px 0 0;
}

.card-seccion-body {
  padding: 12px 24px 24px;
}

.empty-state {
  text-align: center;
  color: #929079;
  padding: 20px 0;
  font-size: 13px;
  font-style: italic;
}

.form-vertical {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-row {
  display: flex;
  gap: 12px;
  align-items: flex-end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.form-group input {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.form-group input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.form-action-btn {
  padding-bottom: 1px;
}

.btn-primario {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
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

.btn-primario:hover { background: #052E2A; }
</style>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useComprasStore } from '@/stores/compras'
import api from '@/services/api'

const emit = defineEmits(['cerrar'])

const comprasStore = useComprasStore()

const proveedores = ref([])
const productos = ref([])
const guardando = ref(false)
const errorGeneral = ref('')
const errores = ref({})

const form = ref({
  id_proveedor: '',
  fecha: new Date().toISOString().split('T')[0],
  nro_factura_proveedor: '',
  detalles: [],
})

function agregarFila() {
  form.value.detalles.push({ id_producto: '', cantidad: 1, precio_unitario_compra: 0 })
}

function quitarFila(index) {
  form.value.detalles.splice(index, 1)
}

const total = computed(() => {
  return form.value.detalles.reduce((sum, d) => {
    return sum + (Number(d.cantidad) || 0) * (Number(d.precio_unitario_compra) || 0)
  }, 0)
})

onMounted(async () => {
  try {
    const [provRes, prodRes] = await Promise.all([
      api.get('/proveedores'),
      api.get('/productos'),
    ])
    proveedores.value = provRes.data.proveedores
    productos.value = prodRes.data.productos
  } catch {
    errorGeneral.value = 'Error al cargar datos iniciales'
  }

  agregarFila()

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
    await comprasStore.crear(form.value)
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al registrar compra'
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
        <h2>Nueva Compra</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="proveedor">Proveedor <span class="required">*</span></label>
            <select
              id="proveedor"
              v-model="form.id_proveedor"
              :class="{ 'input-error': errores.id_proveedor }"
            >
              <option value="">Seleccionar proveedor...</option>
              <option v-for="p in proveedores" :key="p.id_proveedor" :value="p.id_proveedor">
                {{ p.nombre }}
              </option>
            </select>
            <span v-if="errores.id_proveedor" class="error-text">{{ errores.id_proveedor[0] }}</span>
          </div>

          <div class="form-group">
            <label for="fecha">Fecha <span class="required">*</span></label>
            <input
              id="fecha"
              v-model="form.fecha"
              type="date"
              :class="{ 'input-error': errores.fecha }"
            />
            <span v-if="errores.fecha" class="error-text">{{ errores.fecha[0] }}</span>
          </div>

          <div class="form-group form-group-full">
            <label for="factura">Nro. Factura Proveedor</label>
            <input
              id="factura"
              v-model="form.nro_factura_proveedor"
              type="text"
              placeholder="N&uacute;mero de factura"
            />
          </div>
        </div>

        <div class="detalles-section">
          <div class="detalles-header">
            <h3>Productos</h3>
            <button type="button" class="btn-agregar" @click="agregarFila">
              + Agregar producto
            </button>
          </div>

          <p v-if="errores.detalles" class="error-text">{{ errores.detalles[0] }}</p>

          <div class="detalle-filas">
            <div v-for="(det, i) in form.detalles" :key="i" class="detalle-fila">
              <div class="detalle-campo">
                <label>Producto <span class="required">*</span></label>
                <select
                  v-model="det.id_producto"
                  :class="{ 'input-error': errores[`detalles.${i}.id_producto`] }"
                >
                  <option value="">Seleccionar...</option>
                  <option v-for="p in productos" :key="p.id_producto" :value="p.id_producto">
                    {{ p.nombre }}
                  </option>
                </select>
              </div>
              <div class="detalle-campo detalle-cant">
                <label>Cantidad <span class="required">*</span></label>
                <input
                  v-model.number="det.cantidad"
                  type="number"
                  min="1"
                  :class="{ 'input-error': errores[`detalles.${i}.cantidad`] }"
                />
              </div>
              <div class="detalle-campo detalle-precio">
                <label>Precio Unit. <span class="required">*</span></label>
                <input
                  v-model.number="det.precio_unitario_compra"
                  type="number"
                  min="0"
                  step="0.01"
                  :class="{ 'input-error': errores[`detalles.${i}.precio_unitario_compra`] }"
                />
              </div>
              <div class="detalle-campo detalle-subtotal">
                <label>Subtotal</label>
                <span class="subtotal-valor">${{ ((det.cantidad || 0) * (det.precio_unitario_compra || 0)).toFixed(2) }}</span>
              </div>
              <button type="button" class="btn-quitar" @click="quitarFila(i)" title="Quitar">
                &times;
              </button>
            </div>
          </div>

          <div class="total-section">
            <span class="total-label">Total:</span>
            <span class="total-valor">${{ total.toFixed(2) }}</span>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Registrar Compra' }}
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
  max-width: 720px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
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

.form-group input,
.form-group select {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.form-group input.input-error,
.form-group select.input-error {
  border-color: #741102;
}

.error-text {
  font-size: 12px;
  color: #741102;
}

.detalles-section {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #E5E7EB;
}

.detalles-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.detalles-header h3 {
  font-size: 15px;
  font-weight: 600;
  color: #042D29;
}

.btn-agregar {
  padding: 6px 14px;
  background: rgba(4, 45, 41, 0.08);
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-agregar:hover {
  background: rgba(4, 45, 41, 0.15);
}

.detalle-filas {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.detalle-fila {
  display: flex;
  gap: 10px;
  align-items: flex-end;
  padding: 10px;
  background: #F9FAFB;
  border-radius: 10px;
}

.detalle-campo {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.detalle-campo label {
  font-size: 11px;
  font-weight: 500;
  color: #929079;
}

.detalle-campo select,
.detalle-campo input {
  padding: 8px 10px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s;
}

.detalle-campo select:focus,
.detalle-campo input:focus {
  border-color: #042D29;
}

.detalle-campo input.input-error,
.detalle-campo select.input-error {
  border-color: #741102;
}

.detalle-cant { max-width: 90px; }
.detalle-precio { max-width: 120px; }
.detalle-subtotal { max-width: 110px; }

.subtotal-valor {
  display: flex;
  align-items: center;
  height: 100%;
  padding: 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
}

.btn-quitar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #FFFFFF;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  color: #929079;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-quitar:hover {
  border-color: #741102;
  color: #741102;
}

.total-section {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #E5E7EB;
}

.total-label {
  font-size: 15px;
  font-weight: 600;
  color: #1F2937;
}

.total-valor {
  font-size: 20px;
  font-weight: 700;
  color: #042D29;
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

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useReservasStore } from '@/stores/reservas'
import { useToastStore } from '@/stores/toast'
import api from '@/services/api'

const emit = defineEmits(['cerrar'])

const store = useReservasStore()
const toast = useToastStore()

const clientes = ref([])
const productos = ref([])
const guardando = ref(false)
const errorGeneral = ref('')
const errores = ref({})

const hoy = new Date().toISOString().split('T')[0]

const form = ref({
  id_cliente: '',
  monto_adelanto: null,
  adelanto_metodo_pago: '',
  fecha_expiracion: hoy,
  departamento_origen: '',
  detalles: [],
})

const minFechaExp = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

function agregarFila() {
  form.value.detalles.push({ id_producto: '', cantidad: 1 })
}

function limpiarMonto() {
  if (!form.value.monto_adelanto) form.value.monto_adelanto = null
}

function quitarFila(index) {
  form.value.detalles.splice(index, 1)
}

const totalEstimado = computed(() => {
  return form.value.detalles.reduce((sum, d) => {
    const prod = productos.value.find(p => p.id_producto === d.id_producto)
    return sum + (d.cantidad || 0) * (prod?.precio_venta || 0)
  }, 0)
})

onMounted(async () => {
  agregarFila()

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })

  try {
    const [cliRes, prodRes] = await Promise.all([
      api.get('/clientes-lista'),
      api.get('/productos'),
    ])
    clientes.value = cliRes.data.clientes
    productos.value = prodRes.data.productos.map(p => ({
      ...p,
      precio_venta: Number(p.precio_venta) || 0,
      stock_disponible: Number(p.stock_disponible) || 0,
    }))
  } catch {
    errorGeneral.value = 'Error al cargar datos iniciales'
  }
})

function cerrar() {
  emit('cerrar')
}

const departamentos = [
  'Beni', 'Chuquisaca', 'Cochabamba', 'La Paz',
  'Oruro', 'Pando', 'Potosí', 'Santa Cruz', 'Tarija'
]

const busquedaCliente = ref('')
const mostrarSugerencias = ref(false)

const clientesFiltrados = computed(() => {
  if (!busquedaCliente.value) return []
  const q = busquedaCliente.value.toLowerCase()
  return clientes.value.filter(c => {
    const nombre = nombreCliente(c).toLowerCase()
    const ci = String(c.ci || '')
    return nombre.includes(q) || ci.includes(q)
  }).slice(0, 8)
})

function seleccionarCliente(c) {
  form.value.id_cliente = c.id_cliente
  busquedaCliente.value = nombreCliente(c) + (c.ci ? ` (${c.ci})` : '')
  mostrarSugerencias.value = false
}

function nombreCliente(c) {
  return [c.primer_nombre, c.segundo_nombre, c.apellido_paterno, c.apellido_materno]
    .filter(Boolean).join(' ')
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  const errs = {}
  if (!form.value.id_cliente) errs.id_cliente = ['Seleccioná un cliente']
  if (!form.value.monto_adelanto || form.value.monto_adelanto < 1) errs.monto_adelanto = ['El monto del adelanto es requerido']
  if (!form.value.adelanto_metodo_pago) errs.adelanto_metodo_pago = ['Seleccioná un método de pago']
  if (form.value.fecha_expiracion && form.value.fecha_expiracion < minFechaExp.value) errs.fecha_expiracion = ['La expiración debe ser al menos un día después de hoy']
  if (form.value.detalles.length === 0) errs.detalles = ['Agregá al menos un producto']

  if (Object.keys(errs).length) {
    errores.value = errs
    guardando.value = false
    return
  }

  try {
    await store.crear(form.value)
    toast.show('Reserva creada correctamente')
    cerrar()
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al crear reserva'
    }
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Nueva Reserva</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Cliente <span class="required">*</span></label>
            <div class="autocomplete-wrapper">
              <input
                v-model="busquedaCliente"
                type="text"
                placeholder="Buscar por nombre o CI..."
                @focus="mostrarSugerencias = true"
                @input="form.id_cliente = ''"
                :class="{ 'input-error': errores.id_cliente }"
              />
              <ul v-if="mostrarSugerencias && clientesFiltrados.length" class="sugerencias-list">
                <li v-for="c in clientesFiltrados" :key="c.id_cliente" @mousedown.prevent="seleccionarCliente(c)">
                  {{ nombreCliente(c) }} {{ c.ci ? `(${c.ci})` : '' }}
                </li>
              </ul>
            </div>
            <span v-if="errores.id_cliente" class="error-text">{{ errores.id_cliente[0] }}</span>
          </div>

          <div class="form-group">
            <label for="fecha_exp">Fecha expiración</label>
            <input
              id="fecha_exp"
              v-model="form.fecha_expiracion"
              type="date"
              :min="minFechaExp"
              :class="{ 'input-error': errores.fecha_expiracion }"
            />
            <span v-if="errores.fecha_expiracion" class="error-text">{{ errores.fecha_expiracion[0] }}</span>
          </div>

          <div class="form-group">
            <label for="dep_origen">Departamento destino</label>
            <select id="dep_origen" v-model="form.departamento_origen">
              <option value="">Seleccionar departamento...</option>
              <option v-for="d in departamentos" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>

          <div class="form-group">
            <label for="monto_adelanto">Monto adelanto <span class="required">*</span></label>
            <input
              id="monto_adelanto"
              v-model.number="form.monto_adelanto"
              type="number"
              step="0.01"
              placeholder="ej: 50"
              @blur="limpiarMonto"
              :class="{ 'input-error': errores.monto_adelanto }"
            />
            <span v-if="errores.monto_adelanto" class="error-text">{{ errores.monto_adelanto[0] }}</span>
          </div>

          <div class="form-group">
            <label for="met_pago">Método pago adelanto <span class="required">*</span></label>
            <select
              id="met_pago"
              v-model="form.adelanto_metodo_pago"
              :class="{ 'input-error': errores.adelanto_metodo_pago }"
            >
              <option value="">Seleccionar...</option>
              <option value="QR">QR</option>
              <option value="Efectivo">Efectivo</option>
            </select>
            <span v-if="errores.adelanto_metodo_pago" class="error-text">{{ errores.adelanto_metodo_pago[0] }}</span>
          </div>
        </div>

        <div class="detalles-section">
          <div class="detalles-header">
            <h3>Productos a reservar</h3>
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
                  <option
                    v-for="p in productos"
                    :key="p.id_producto"
                    :value="p.id_producto"
                    :disabled="p.stock_disponible < 1"
                  >
                    {{ p.nombre }} (stock: {{ p.stock_disponible }})
                  </option>
                </select>
              </div>
              <div class="detalle-campo detalle-cant">
                <label>Cantidad <span class="required">*</span></label>
                <input
                  v-model.number="det.cantidad"
                  type="number"
                  min="1"
                  :max="productos.find(p => p.id_producto === det.id_producto)?.stock_disponible || 1"
                  :class="{ 'input-error': errores[`detalles.${i}.cantidad`] }"
                />
              </div>
              <div class="detalle-campo detalle-precio-info">
                <label>Precio U.</label>
                <span class="precio-info">
                  Bs {{ (productos.find(p => p.id_producto === det.id_producto)?.precio_venta || 0).toFixed(2) }}
                </span>
              </div>
              <button type="button" class="btn-quitar" @click="quitarFila(i)" title="Quitar">&times;</button>
            </div>
          </div>

          <div class="total-section">
            <span class="total-label">Total estimado:</span>
            <span class="total-valor">Bs {{ totalEstimado.toFixed(2) }}</span>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="guardando">
            {{ guardando ? 'Guardando...' : 'Crear Reserva' }}
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

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  font-size: 13px;
  margin: 16px 24px 0;
  border-radius: 8px;
}

.modal-body { padding: 24px; }

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
.form-group select.input-error { border-color: #741102; }

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

.btn-agregar:hover { background: rgba(4, 45, 41, 0.15); }

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
.detalle-campo input:focus { border-color: #042D29; }

.detalle-campo input.input-error,
.detalle-campo select.input-error { border-color: #741102; }

.detalle-cant { max-width: 90px; }
.detalle-precio-info { max-width: 110px; }

.precio-info {
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

.btn-quitar:hover { border-color: #741102; color: #741102; }

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

.btn-cancelar:hover { border-color: #929079; color: #1F2937; }

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

.autocomplete-wrapper {
  position: relative;
}

.sugerencias-list {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #FFFFFF;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 10;
  list-style: none;
  padding: 0;
  margin: 4px 0 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.sugerencias-list li {
  padding: 10px 12px;
  font-size: 13px;
  cursor: pointer;
  color: #1F2937;
}

.sugerencias-list li:hover {
  background: #F3F4F6;
}
</style>

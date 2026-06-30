<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useReservasStore } from '@/stores/reservas'
import api from '@/services/api'

const emit = defineEmits(['cerrar', 'guardado'])

const store = useReservasStore()

const clientes = ref([])
const productos = ref([])
const guardando = ref(false)
const errorGeneral = ref('')
const errores = ref({})

const busquedaCliente = ref('')
const mostrarDropdown = ref(false)
const clienteSeleccionado = ref(null)

const departamentos = ['Beni', 'Chuquisaca', 'Cochabamba', 'La Paz', 'Oruro', 'Pando', 'Potosí', 'Santa Cruz', 'Tarija']

const clientesFiltrados = computed(() => {
  if (!busquedaCliente.value) return []
  const q = busquedaCliente.value.toLowerCase()
  const filtrados = clientes.value.filter(c => {
    const nombre = nombreCliente(c).toLowerCase()
    return nombre.includes(q) || (c.ci && c.ci.includes(q))
  })
  return filtrados.slice(0, 30)
})

function seleccionarCliente(cliente) {
  clienteSeleccionado.value = cliente
  form.value.id_cliente = cliente.id_cliente
  busquedaCliente.value = nombreCliente(cliente) + (cliente.ci ? ` (${cliente.ci})` : '')
  mostrarDropdown.value = false
}

function limpiarCliente() {
  clienteSeleccionado.value = null
  form.value.id_cliente = ''
  busquedaCliente.value = ''
  mostrarDropdown.value = false
}

const form = ref({
  id_cliente: '',
  monto_adelanto: 0,
  adelanto_metodo_pago: '',
  fecha_expiracion: '',
  departamento_origen: '',
  detalles: [],
})

function agregarFila() {
  form.value.detalles.push({ id_producto: '', cantidad: 1 })
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

  agregarFila()

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function cerrar() {
  emit('cerrar')
}

function nombreCliente(c) {
  return [c.primer_nombre, c.segundo_nombre, c.apellido_paterno, c.apellido_materno]
    .filter(Boolean).join(' ')
}

function validar() {
  const errs = {}
  if (!form.value.id_cliente) errs.id_cliente = ['Debe seleccionar un cliente']
  if (!form.value.fecha_expiracion) errs.fecha_expiracion = ['La fecha de expiración es obligatoria']
  if (!form.value.departamento_origen) errs.departamento_origen = ['Debe seleccionar un departamento de origen']
  if (!form.value.monto_adelanto || form.value.monto_adelanto <= 0) errs.monto_adelanto = ['El adelanto debe ser mayor a 0']
  if (form.value.monto_adelanto > 0 && !form.value.adelanto_metodo_pago) errs.adelanto_metodo_pago = ['Debe seleccionar un método de pago']
  if (form.value.detalles.length === 0) {
    errs.detalles = ['Debe agregar al menos un producto']
  } else {
    form.value.detalles.forEach((d, i) => {
      if (!d.id_producto) errs[`detalles.${i}.id_producto`] = ['Seleccione un producto']
      if (!d.cantidad || d.cantidad < 1) errs[`detalles.${i}.cantidad`] = ['Ingrese una cantidad válida']
    })
  }
  return errs
}

async function guardar() {
  errores.value = {}
  errorGeneral.value = ''

  const errs = validar()
  if (Object.keys(errs).length > 0) {
    errores.value = errs
    return
  }

  guardando.value = true

  try {
    await store.crear(form.value)
    emit('guardado')
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
          <div class="form-group form-group-full">
            <label for="buscar_cliente">Cliente <span class="required">*</span></label>
            <div class="cliente-buscador">
              <input
                id="buscar_cliente"
                v-model="busquedaCliente"
                type="text"
                placeholder="Buscar cliente por nombre o CI..."
                :class="{ 'input-error': errores.id_cliente }"
                @input="mostrarDropdown = true"
                @focus="mostrarDropdown = true"
                @blur="setTimeout(() => mostrarDropdown = false, 200)"
              />
              <button v-if="form.id_cliente" type="button" class="btn-limpiar-cliente" @click="limpiarCliente">&times;</button>
              <div v-if="mostrarDropdown && clientesFiltrados.length > 0" class="cliente-dropdown">
                <div
                  v-for="c in clientesFiltrados"
                  :key="c.id_cliente"
                  class="cliente-option"
                  @mousedown.prevent="seleccionarCliente(c)"
                >
                  {{ nombreCliente(c) }}
                  <span v-if="c.ci" class="cliente-ci">{{ c.ci }}</span>
                </div>
              </div>
              <div v-if="mostrarDropdown && busquedaCliente && clientesFiltrados.length === 0" class="cliente-dropdown">
                <div class="cliente-option sin-resultados">Sin resultados</div>
              </div>
            </div>
            <span v-if="errores.id_cliente" class="error-text">{{ errores.id_cliente[0] }}</span>
          </div>

          <div class="form-group">
            <label for="fecha_exp">Fecha expiración <span class="required">*</span></label>
            <input
              id="fecha_exp"
              v-model="form.fecha_expiracion"
              type="date"
              :class="{ 'input-error': errores.fecha_expiracion }"
            />
            <span v-if="errores.fecha_expiracion" class="error-text">{{ errores.fecha_expiracion[0] }}</span>
          </div>

          <div class="form-group">
            <label for="dep_origen">Departamento origen <span class="required">*</span></label>
            <select
              id="dep_origen"
              v-model="form.departamento_origen"
              :class="{ 'input-error': errores.departamento_origen }"
            >
              <option value="">Seleccionar...</option>
              <option v-for="dep in departamentos" :key="dep" :value="dep">{{ dep }}</option>
            </select>
            <span v-if="errores.departamento_origen" class="error-text">{{ errores.departamento_origen[0] }}</span>
          </div>

          <div class="form-group">
            <label for="monto_adelanto">Adelanto (Bs) <span class="required">*</span></label>
            <input
              id="monto_adelanto"
              v-model.number="form.monto_adelanto"
              type="number"
              min="0.01"
              step="0.01"
              placeholder="0.01"
              :class="{ 'input-error': errores.monto_adelanto }"
            />
            <span v-if="errores.monto_adelanto" class="error-text">{{ errores.monto_adelanto[0] }}</span>
          </div>

          <div v-if="form.monto_adelanto > 0" class="form-group">
            <label for="met_pago">Método de pago <span class="required">*</span></label>
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
                <span v-if="errores[`detalles.${i}.id_producto`]" class="error-text">
                  {{ errores[`detalles.${i}.id_producto`][0] }}
                </span>
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
                <span v-if="errores[`detalles.${i}.cantidad`]" class="error-text">
                  {{ errores[`detalles.${i}.cantidad`][0] }}
                </span>
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

.cliente-buscador {
  position: relative;
}

.cliente-buscador input {
  padding-right: 36px;
}

.btn-limpiar-cliente {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  font-size: 20px;
  color: #929079;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
}

.btn-limpiar-cliente:hover { color: #741102; }

.cliente-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 10;
  background: #FFFFFF;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  margin-top: 4px;
  max-height: 240px;
  overflow-y: auto;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.cliente-option {
  padding: 10px 12px;
  font-size: 14px;
  color: #1F2937;
  cursor: pointer;
  transition: background 0.15s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cliente-option:hover { background: rgba(4, 45, 41, 0.06); }

.cliente-ci {
  font-size: 12px;
  color: #929079;
}

.sin-resultados {
  color: #929079;
  cursor: default;
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
</style>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useProductosStore } from '@/stores/productos'
import api from '@/services/api'

const props = defineProps({
  producto: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardado'])

const store = useProductosStore()

const esEdicion = !!props.producto

const form = ref({
  id_ubicacion: '',
  nombre: '',
  descripcion: '',
  precio_venta: 0,
  costo: 0,
  stock_fisico: 0,
  stock_disponible: 0,
  stock_minimo: 0,
})

const margenNeto = computed(() => {
  const venta = Number(form.value.precio_venta) || 0
  const costo = Number(form.value.costo) || 0
  return venta - costo
})

const margenPorcentaje = computed(() => {
  const costo = Number(form.value.costo) || 0
  if (costo === 0) return null
  return ((margenNeto.value / costo) * 100)
})

const errores = ref({})
const guardando = ref(false)
const errorGeneral = ref('')

const arbolEstantes = ref([])
const estanteSeleccionado = ref('')
const seccionSeleccionada = ref('')
const cargandoUbicaciones = ref(false)

const seccionesDisponibles = ref([])
const ubicacionesDisponibles = ref([])

onMounted(async () => {
  cargandoUbicaciones.value = true
  try {
    const res = await api.get('/ubicaciones/arbol')
    arbolEstantes.value = res.data.estantes
  } catch {
    errorGeneral.value = 'Error al cargar ubicaciones'
  } finally {
    cargandoUbicaciones.value = false
  }

  if (esEdicion && props.producto.ubicacion) {
    estanteSeleccionado.value = props.producto.ubicacion.id_estante || ''
    cargarSecciones()
    seccionSeleccionada.value = props.producto.ubicacion.id_seccion || ''
    cargarUbicaciones()
    form.value.id_ubicacion = props.producto.ubicacion.id_ubicacion || ''
    form.value.nombre = props.producto.nombre || ''
    form.value.descripcion = props.producto.descripcion || ''
    form.value.precio_venta = props.producto.precio_venta || 0
    form.value.costo = props.producto.costo || 0
    form.value.stock_fisico = props.producto.stock_fisico || 0
    form.value.stock_disponible = props.producto.stock_disponible || 0
    form.value.stock_minimo = props.producto.stock_minimo || 0
  } else if (!esEdicion) {
    form.value.stock_disponible = 0
  }

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function cargarSecciones() {
  const estante = arbolEstantes.value.find((e) => e.id_estante === Number(estanteSeleccionado.value))
  seccionesDisponibles.value = estante ? estante.secciones : []
  seccionSeleccionada.value = ''
  ubicacionesDisponibles.value = []
  form.value.id_ubicacion = ''
}

function cargarUbicaciones() {
  const seccion = seccionesDisponibles.value.find((s) => s.id_seccion === Number(seccionSeleccionada.value))
  ubicacionesDisponibles.value = seccion ? seccion.ubicaciones : []
  form.value.id_ubicacion = ''
}

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  const payload = {
    id_ubicacion: Number(form.value.id_ubicacion) || null,
    nombre: form.value.nombre,
    descripcion: form.value.descripcion || '',
    precio_venta: Number(form.value.precio_venta) || 0,
    costo: Number(form.value.costo) || 0,
    stock_fisico: Number(form.value.stock_fisico) || 0,
    stock_disponible: Number(form.value.stock_disponible) || 0,
    stock_minimo: Number(form.value.stock_minimo) || 0,
  }

  try {
    if (esEdicion) {
      await store.actualizar(props.producto.id_producto, payload)
    } else {
      await store.crear(payload)
    }
    emit('guardado')
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al guardar producto'
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
        <h2>{{ esEdicion ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
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
              maxlength="50"
              placeholder="Nombre del producto/repuesto"
              :class="{ 'input-error': errores.nombre }"
            />
            <span class="char-counter">{{ form.nombre.length }}/50</span>
            <span v-if="errores.nombre" class="error-text">{{ errores.nombre[0] }}</span>
          </div>

          <div class="form-group form-group-full">
            <label for="descripcion">Descripci&oacute;n</label>
            <textarea
              id="descripcion"
              v-model="form.descripcion"
              maxlength="100"
              placeholder="Descripci&oacute;n del producto"
              rows="2"
            ></textarea>
            <span class="char-counter">{{ form.descripcion.length }}/100</span>
          </div>

          <div class="form-group">
            <label>Estante <span class="required">*</span></label>
            <select v-model="estanteSeleccionado" @change="cargarSecciones" :disabled="cargandoUbicaciones">
              <option value="">Seleccionar estante</option>
              <option v-for="e in arbolEstantes" :key="e.id_estante" :value="e.id_estante">
                Estante {{ e.numero_estante }} {{ e.pasillo ? '- ' + e.pasillo : '' }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Secci&oacute;n <span class="required">*</span></label>
            <select v-model="seccionSeleccionada" @change="cargarUbicaciones" :disabled="!estanteSeleccionado">
              <option value="">Seleccionar secci&oacute;n</option>
              <option v-for="s in seccionesDisponibles" :key="s.id_seccion" :value="s.id_seccion">
                Secci&oacute;n {{ s.codigo_seccion }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Nivel <span class="required">*</span></label>
            <select v-model="form.id_ubicacion" :disabled="!seccionSeleccionada">
              <option value="">Seleccionar nivel</option>
              <option v-for="u in ubicacionesDisponibles" :key="u.id_ubicacion" :value="u.id_ubicacion">
                {{ u.nivel }}
              </option>
            </select>
            <span v-if="errores.id_ubicacion" class="error-text">{{ errores.id_ubicacion[0] }}</span>
          </div>

          <div class="form-group form-group-full">
            <div class="precios-header">
              <span class="precios-titulo">Precios</span>
            </div>
            <div class="precios-grid">
              <div class="form-group">
                <label for="precio_venta">Precio de Venta (Bs)</label>
                <input
                  id="precio_venta"
                  v-model="form.precio_venta"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  @input="() => {}"
                />
              </div>

              <div class="form-group">
                <label for="costo">Costo (Bs)</label>
                <input
                  id="costo"
                  v-model="form.costo"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div v-if="esEdicion && props.producto.ultimo_costo_compra" class="precio-referencia">
              <svg viewBox="0 0 24 24" fill="none" class="ref-icon" width="16" height="16">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>&Uacute;ltimo costo de compra registrado: <strong>Bs {{ Number(props.producto.ultimo_costo_compra).toFixed(2) }}</strong></span>
            </div>

            <div class="margen-info">
              <span class="margen-label">Margen de ganancia:</span>
              <span class="margen-valor" :class="{ positivo: margenNeto > 0, negativo: margenNeto < 0, cero: margenNeto === 0 }">
                Bs {{ margenNeto.toFixed(2) }}
                <span v-if="margenPorcentaje !== null" class="margen-porcentaje">
                  ({{ margenPorcentaje >= 0 ? '+' : '' }}{{ margenPorcentaje.toFixed(1) }}%)
                </span>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label for="stock_fisico">Stock F&iacute;sico</label>
            <input
              id="stock_fisico"
              v-model="form.stock_fisico"
              type="number"
              min="0"
              placeholder="0"
            />
          </div>

          <div class="form-group">
            <label for="stock_disponible">Stock Disponible</label>
            <input
              id="stock_disponible"
              v-model="form.stock_disponible"
              type="number"
              min="0"
              placeholder="0"
            />
            <span class="help-text">Descontando reservas activas</span>
          </div>

          <div class="form-group">
            <label for="stock_minimo">Stock M&iacute;nimo</label>
            <input
              id="stock_minimo"
              v-model="form.stock_minimo"
              type="number"
              min="0"
              placeholder="0"
            />
            <span class="help-text">Alerta cuando el stock disponible est&eacute; por debajo</span>
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
  max-width: 620px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  max-height: 90vh;
  overflow-y: auto;
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

.form-group-full { grid-column: 1 / -1; }

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.required { color: #741102; }

.form-group input,
.form-group textarea,
.form-group select {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #FFFFFF;
}

.form-group textarea {
  resize: vertical;
  min-height: 60px;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.form-group select {
  cursor: pointer;
}

.form-group input.input-error {
  border-color: #741102;
}

.error-text {
  font-size: 12px;
  color: #741102;
}

.char-counter {
  font-size: 11px;
  color: #929079;
  text-align: right;
  margin-top: 2px;
}

.help-text {
  font-size: 11px;
  color: #929079;
  font-style: italic;
}

.precios-header { margin-bottom: 8px; }

.precios-titulo {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.precios-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.precio-referencia {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding: 8px 12px;
  background: #F0F4F3;
  border-radius: 8px;
  font-size: 12px;
  color: #1F2937;
}

.precio-referencia strong { color: #042D29; }

.ref-icon { color: #042D29; flex-shrink: 0; }

.margen-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 6px;
  padding: 8px 12px;
  background: #F9F9F7;
  border-radius: 8px;
  font-size: 13px;
}

.margen-label {
  color: #929079;
  font-weight: 500;
}

.margen-valor {
  font-weight: 600;
  font-family: 'Inter', monospace;
}

.margen-valor.positivo { color: #2E7D32; }
.margen-valor.negativo { color: #741102; }
.margen-valor.cero { color: #929079; }

.margen-porcentaje {
  font-weight: 400;
  font-size: 12px;
  opacity: 0.8;
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

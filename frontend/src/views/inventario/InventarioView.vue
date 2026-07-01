<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useProductosStore } from '@/stores/productos'
import { useEstantesStore } from '@/stores/estantes'
import { useModelosCompatiblesStore } from '@/stores/modelosCompatibles'
import ProductoFormModal from './ProductoFormModal.vue'
import EstanteFormModal from './EstanteFormModal.vue'
import ModeloCompatibilidadModal from './ModeloCompatibilidadModal.vue'
import MotocicletasCompatiblesModal from './MotocicletasCompatiblesModal.vue'

const productosStore = useProductosStore()
const estantesStore = useEstantesStore()
const modelosStore = useModelosCompatiblesStore()

const tabActivo = ref('productos')
const busqueda = ref('')
const busquedaEstante = ref('')
const keyTabla = ref(0)

const mostrarFormProducto = ref(false)
const productoEditando = ref(null)

const mostrarFormEstante = ref(false)
const estanteEditando = ref(null)

const mostrarCompatibilidad = ref(false)
const productoCompatibilidad = ref(null)

const mostrarMotosCompatibles = ref(false)
const productoMotosCompatibles = ref(null)

const compatProductoSeleccionado = ref('')
const compatModelosIds = ref([])
const compatGuardando = ref(false)
const compatMensaje = ref('')

onMounted(async () => {
  await Promise.all([
    productosStore.listar(),
    estantesStore.listar(),
    modelosStore.listar(),
  ])
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async () => {
  busqueda.value = ''
  busquedaEstante.value = ''
  compatProductoSeleccionado.value = ''
  compatModelosIds.value = []
  compatMensaje.value = ''
  await nextTick()
  animarEntrada()
})

watch(busqueda, () => {
  keyTabla.value++
  nextTick(() => animarFilas())
})

watch(busquedaEstante, () => {
  nextTick(() => animarFilas())
})

function animarEntrada() {
  gsap.fromTo('.page-header', { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out' })
  gsap.fromTo('.toolbar', { y: -10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, ease: 'power2.out', delay: 0.1 })
  gsap.fromTo('.tabla-wrapper', { y: 15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.3, ease: 'power3.out', delay: 0.15 })
  gsap.fromTo('.tabla-contenido tbody tr', { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out', delay: 0.2 })
}

function animarFilas() {
  gsap.fromTo(
    '.tabla-contenido tbody tr',
    { y: 10, opacity: 0 },
    { y: 0, opacity: 1, duration: 0.25, stagger: 0.04, ease: 'power2.out' }
  )
}

function abrirNuevoProducto() {
  productoEditando.value = null
  mostrarFormProducto.value = true
}

function abrirEditarProducto(producto) {
  productoEditando.value = productoSinProxys(producto)
  mostrarFormProducto.value = true
}

function abrirCompatibilidad(producto) {
  productoCompatibilidad.value = productoSinProxys(producto)
  mostrarCompatibilidad.value = true
}

function abrirMotosCompatibles(producto) {
  productoMotosCompatibles.value = productoSinProxys(producto)
  mostrarMotosCompatibles.value = true
}

function productoSinProxys(p) {
  return JSON.parse(JSON.stringify(p))
}

async function cargarCompatibilidadProducto() {
  if (!compatProductoSeleccionado.value) {
    compatModelosIds.value = []
    return
  }
  try {
    const modelos = await productosStore.listarModelos(compatProductoSeleccionado.value)
    compatModelosIds.value = modelos.map((m) => m.id_modelo)
  } catch {
    compatModelosIds.value = []
  }
}

function toggleCompatModelo(idModelo) {
  const idx = compatModelosIds.value.indexOf(idModelo)
  if (idx !== -1) {
    compatModelosIds.value.splice(idx, 1)
  } else {
    compatModelosIds.value.push(idModelo)
  }
}

async function guardarCompatibilidad() {
  if (!compatProductoSeleccionado.value) return
  compatGuardando.value = true
  compatMensaje.value = ''
  try {
    await productosStore.guardarModelos(compatProductoSeleccionado.value, compatModelosIds.value)
    compatMensaje.value = 'Compatibilidad guardada exitosamente'
    setTimeout(() => { compatMensaje.value = '' }, 3000)
  } catch {
    compatMensaje.value = 'Error al guardar compatibilidad'
  } finally {
    compatGuardando.value = false
  }
}

function agruparModelosPorMarca() {
  const grupos = {}
  modelosStore.items.filter((m) => m.estadoA).forEach((m) => {
    if (!grupos[m.marca_moto]) grupos[m.marca_moto] = []
    grupos[m.marca_moto].push(m)
  })
  return grupos
}

const productosFiltrados = computed(() => {
  if (!busqueda.value) return productosStore.items
  const q = busqueda.value.toLowerCase()
  return productosStore.items.filter((p) =>
    p.nombre.toLowerCase().includes(q) ||
    (p.ubicacion?.numero_estante?.toString() || '').includes(q)
  )
})

const estantesFiltrados = computed(() => {
  if (!busquedaEstante.value) return estantesStore.items
  const q = busquedaEstante.value.toLowerCase()
  return estantesStore.items.filter((e) =>
    e.numero_estante?.toString().includes(q) ||
    (e.pasillo || '').toLowerCase().includes(q) ||
    (e.descripcion || '').toLowerCase().includes(q)
  )
})


</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1>Inventario</h1>
      <p class="page-subtitle">Productos, repuestos y gesti&oacute;n de stock</p>
    </div>

    <div class="tabs">
      <button
        class="tab-btn"
        :class="{ active: tabActivo === 'productos' }"
        @click="tabActivo = 'productos'"
      >
        Productos
      </button>
      <button
        class="tab-btn"
        :class="{ active: tabActivo === 'estantes' }"
        @click="tabActivo = 'estantes'"
      >
        Estantes
      </button>
      <button
        class="tab-btn"
        :class="{ active: tabActivo === 'modelos' }"
        @click="tabActivo = 'modelos'"
      >
        Gestionar Compatibilidad
      </button>
    </div>

    <!-- TAB: PRODUCTOS -->
    <div v-if="tabActivo === 'productos'" class="tab-content">
      <div class="toolbar">
        <div class="search-box">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre o estante..."
            class="search-input"
          />
        </div>
        <button class="btn-primario" @click="abrirNuevoProducto">
          <svg viewBox="0 0 24 24" fill="none" class="btn-icon">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Nuevo Producto
        </button>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Estante</th>
              <th>Precio Venta</th>
              <th>Costo</th>
              <th>Stock F&iacute;sico</th>
              <th>Stock Disponible</th>
              <th>Stock M&iacute;nimo</th>
              <th>Alertas</th>
              <th>Compatibilidad</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in productosFiltrados" :key="p.id_producto" :class="{ 'fila-alerta': p.alerta_stock }">
              <td class="td-nombre">
                <button class="btn-nombre-producto" @click="abrirMotosCompatibles(p)" title="Ver motocicletas compatibles">
                  {{ p.nombre }}
                </button>
              </td>
              <td>
                <span v-if="p.ubicacion" class="ubicacion-badge">
                  Est.{{ p.ubicacion.numero_estante }} - {{ p.ubicacion.codigo_seccion }} - {{ p.ubicacion.nivel }}
                </span>
                <span v-else class="sin-ubicacion">—</span>
              </td>
              <td class="td-monto">S/ {{ (p.precio_venta ?? 0).toFixed(2) }}</td>
              <td class="td-monto">S/ {{ (p.costo ?? 0).toFixed(2) }}</td>
              <td class="td-numero">{{ p.stock_fisico }}</td>
              <td class="td-numero">{{ p.stock_disponible }}</td>
              <td class="td-numero">{{ p.stock_minimo }}</td>
              <td>
                <span v-if="p.alerta_stock" class="alerta-badge" title="Stock por debajo del m&iacute;nimo">
                  &#9888;
                </span>
                <span v-else class="ok-badge">&#10003;</span>
              </td>
              <td>
                <button class="btn-accion btn-compatibilidad" @click="abrirCompatibilidad(p)" title="Asignar modelos compatibles">
                  <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                    <path d="M4 17l4 4 12-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
              </td>
              <td>
                <div class="acciones-btns">
                  <button class="btn-accion btn-editar" @click="abrirEditarProducto(p)" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                      <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 3 21l.5-4.5L17 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="productosFiltrados.length === 0">
              <td colspan="10" class="td-vacio">No se encontraron productos</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB: ESTANTES -->
    <div v-if="tabActivo === 'estantes'" class="tab-content">
      <div class="toolbar">
        <div class="search-box">
          <svg viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input
            v-model="busquedaEstante"
            type="text"
            placeholder="Buscar estante..."
            class="search-input"
          />
        </div>
        <button class="btn-primario" @click="mostrarFormEstante = true; estanteEditando = null">
          <svg viewBox="0 0 24 24" fill="none" class="btn-icon">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Nuevo Estante
        </button>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th>N&deg; Estante</th>
              <th>Pasillo</th>
              <th>Descripci&oacute;n</th>
              <th>Secciones</th>
              <th>Ubicaciones</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in estantesFiltrados" :key="e.id_estante">
              <td class="td-numero">{{ e.numero_estante }}</td>
              <td>{{ e.pasillo || '—' }}</td>
              <td>{{ e.descripcion || '—' }}</td>
              <td class="td-numero">{{ e.secciones?.length || 0 }}</td>
              <td class="td-numero">
                {{ e.secciones?.reduce((acc, s) => acc + (s.ubicaciones?.length || 0), 0) || 0 }}
              </td>
              <td>
                <span class="estado-badge" :class="e.estadoA ? 'activo' : 'inactivo'">
                  {{ e.estadoA ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td>
                <div class="acciones-btns">
                  <button class="btn-accion btn-editar" @click="estanteEditando = e; mostrarFormEstante = true" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                      <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 3 21l.5-4.5L17 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button v-if="e.estadoA" class="btn-accion btn-eliminar" @click="estantesStore.eliminar(e.id_estante)" title="Desactivar">
                    <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="estantesFiltrados.length === 0">
              <td colspan="7" class="td-vacio">No se encontraron estantes</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB: GESTIONAR COMPATIBILIDAD -->
    <div v-if="tabActivo === 'modelos'" class="tab-content">
      <div class="compat-intro">
        <p>Selecciona un producto y asigna los modelos de motocicleta con los que es compatible.</p>
      </div>

      <div class="compat-selector">
        <label for="compat-producto">Producto:</label>
        <select
          id="compat-producto"
          v-model="compatProductoSeleccionado"
          @change="cargarCompatibilidadProducto"
          class="compat-select"
        >
          <option value="">-- Seleccionar producto --</option>
          <option
            v-for="p in productosStore.items"
            :key="p.id_producto"
            :value="p.id_producto"
          >
            {{ p.nombre }}
          </option>
        </select>
      </div>

      <div v-if="compatMensaje" class="compat-mensaje" :class="{ error: compatMensaje.includes('Error') }">
        {{ compatMensaje }}
      </div>

      <div v-if="compatProductoSeleccionado" class="compat-panel">
        <h3 class="compat-titulo">
          Modelos compatibles con:
          <strong>{{ productosStore.items.find(p => p.id_producto === Number(compatProductoSeleccionado))?.nombre }}</strong>
        </h3>

        <div v-if="modelosStore.items.filter(m => m.estadoA).length === 0" class="sin-modelos">
          No hay modelos de motocicleta registrados. Crea modelos desde la base de datos primero.
        </div>

        <div v-else class="compat-grid">
          <div
            v-for="(modelos, marca) in agruparModelosPorMarca()"
            :key="marca"
            class="compat-marca"
          >
            <h4 class="compat-marca-titulo">{{ marca }}</h4>
            <div class="compat-modelos">
              <label
                v-for="m in modelos"
                :key="m.id_modelo"
                class="compat-item"
                :class="{ seleccionado: compatModelosIds.includes(m.id_modelo) }"
              >
                <input
                  type="checkbox"
                  :checked="compatModelosIds.includes(m.id_modelo)"
                  @change="toggleCompatModelo(m.id_modelo)"
                />
                <span class="compat-modelo-nombre">{{ m.modelo_moto }}</span>
                <span v-if="m.anio_inicio || m.anio_fin" class="compat-anios">
                  ({{ m.anio_inicio || '—' }} - {{ m.anio_fin || '—' }})
                </span>
              </label>
            </div>
          </div>
        </div>

        <div class="compat-footer">
          <span class="compat-contador">{{ compatModelosIds.length }} modelo(s) seleccionado(s)</span>
          <button
            class="btn-primario"
            :disabled="compatGuardando"
            @click="guardarCompatibilidad"
          >
            {{ compatGuardando ? 'Guardando...' : 'Guardar Compatibilidad' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODALES -->
    <ProductoFormModal
      v-if="mostrarFormProducto"
      :producto="productoEditando"
      @cerrar="mostrarFormProducto = false"
      @guardado="mostrarFormProducto = false"
    />

    <EstanteFormModal
      v-if="mostrarFormEstante"
      :estante="estanteEditando"
      @cerrar="mostrarFormEstante = false"
      @guardado="mostrarFormEstante = false"
    />

    <ModeloCompatibilidadModal
      v-if="mostrarCompatibilidad"
      :producto="productoCompatibilidad"
      @cerrar="mostrarCompatibilidad = false"
      @guardado="mostrarCompatibilidad = false"
    />

    <MotocicletasCompatiblesModal
      v-if="mostrarMotosCompatibles"
      :producto="productoMotosCompatibles"
      @cerrar="mostrarMotosCompatibles = false"
    />
  </div>
</template>

<style scoped>
.page-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 32px;
}

.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 700; color: #042D29; margin: 0 0 4px; }
.page-subtitle { font-size: 14px; color: #929079; margin: 0; }

.tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 24px;
  background: #FFFFFF;
  border-radius: 12px;
  padding: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.tab-btn {
  flex: 1;
  padding: 10px 20px;
  border: none;
  background: transparent;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  color: #929079;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover { color: #042D29; background: #F5F4F0; }
.tab-btn.active { background: #042D29; color: #FFFFFF; font-weight: 600; }

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  gap: 16px;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #929079;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #FFFFFF;
}

.search-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.btn-primario {
  display: inline-flex;
  align-items: center;
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
  transition: background 0.2s ease, transform 0.2s ease;
  white-space: nowrap;
}

.btn-primario:hover { background: #052E2A; }
.btn-primario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-icon { width: 18px; height: 18px; }

.tabla-wrapper {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
}

.tabla-contenido {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.tabla-contenido th {
  background: #F9F9F7;
  color: #929079;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-contenido td {
  padding: 12px 16px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
}

.tabla-contenido tbody tr:hover { background: #F9F9F7; }
.tabla-contenido tbody tr:last-child td { border-bottom: none; }

.fila-alerta { background: #FFF5F5; }
.fila-alerta:hover { background: #FFE8E8 !important; }

.td-nombre { font-weight: 600; color: #042D29; }
.td-monto { font-family: 'Inter', monospace; text-align: right; }
.td-numero { text-align: center; font-family: 'Inter', monospace; }
.td-vacio { text-align: center; color: #929079; padding: 40px 16px; font-style: italic; }

.ubicacion-badge {
  display: inline-block;
  background: #F0F4F3;
  color: #042D29;
  font-size: 11px;
  font-weight: 500;
  padding: 3px 8px;
  border-radius: 6px;
}

.sin-ubicacion { color: #D1D5DB; }

.alerta-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: #741102;
  color: #FFFFFF;
  border-radius: 50%;
  font-size: 14px;
  cursor: help;
}

.ok-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: #E8F5E9;
  color: #2E7D32;
  border-radius: 50%;
  font-size: 12px;
}

.estado-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  text-transform: uppercase;
}

.estado-badge.activo { background: #E8F5E9; color: #2E7D32; }
.estado-badge.inactivo { background: #FFF5F5; color: #741102; }

.acciones-btns {
  display: flex;
  gap: 4px;
}

.btn-accion {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: transparent;
  color: #929079;
}

.btn-accion:hover { background: #F5F4F0; }
.btn-editar:hover { color: #042D29; }
.btn-eliminar:hover { color: #741102; background: #FFF5F5; }
.btn-compatibilidad:hover { color: #042D29; }

.btn-nombre-producto {
  background: none;
  border: none;
  padding: 0;
  font: inherit;
  font-weight: 600;
  color: #042D29;
  cursor: pointer;
  text-align: left;
  transition: color 0.2s ease;
  text-decoration: underline;
  text-underline-offset: 2px;
  text-decoration-color: transparent;
}

.btn-nombre-producto:hover {
  color: #741102;
  text-decoration-color: #741102;
}

.compat-intro {
  background: #F5F4F0;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 20px;
}

.compat-intro p {
  margin: 0;
  font-size: 14px;
  color: #1F2937;
}

.compat-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  background: #FFFFFF;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.compat-selector label {
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
  white-space: nowrap;
}

.compat-select {
  flex: 1;
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #FFFFFF;
  cursor: pointer;
}

.compat-select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.compat-mensaje {
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 16px;
  background: #E8F5E9;
  color: #2E7D32;
  border-left: 3px solid #2E7D32;
}

.compat-mensaje.error {
  background: #FFF5F5;
  color: #741102;
  border-left-color: #741102;
}

.compat-panel {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  overflow: hidden;
  border-top: 4px solid transparent;
  border-image: linear-gradient(90deg, #042D29 0%, #741102 100%) 1;
  padding: 24px;
}

.compat-titulo {
  font-size: 15px;
  font-weight: 500;
  color: #1F2937;
  margin: 0 0 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #E5E7EB;
}

.compat-titulo strong {
  color: #042D29;
}

.sin-modelos {
  text-align: center;
  color: #929079;
  padding: 40px 0;
  font-style: italic;
}

.compat-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.compat-marca {
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 16px;
}

.compat-marca-titulo {
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
  margin: 0 0 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid #E5E7EB;
}

.compat-modelos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 6px;
}

.compat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 13px;
}

.compat-item:hover { border-color: #929079; }

.compat-item.seleccionado {
  border-color: #042D29;
  background: #F0F4F3;
}

.compat-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #042D29;
}

.compat-modelo-nombre { font-weight: 500; color: #1F2937; }

.compat-anios {
  font-size: 11px;
  color: #929079;
}

.compat-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #E5E7EB;
}

.compat-contador {
  font-size: 13px;
  color: #929079;
}
</style>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useProductosStore } from '@/stores/productos'
import { useEstantesStore } from '@/stores/estantes'
import ProductoFormModal from './ProductoFormModal.vue'
import EstanteFormModal from './EstanteFormModal.vue'
import ModeloCompatibilidadModal from './ModeloCompatibilidadModal.vue'
import MotocicletasCompatiblesModal from './MotocicletasCompatiblesModal.vue'

const productosStore = useProductosStore()
const estantesStore = useEstantesStore()

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
const compatModelosDisponibles = ref([])
const compatModelosSeleccionados = ref([])
const compatGuardando = ref(false)
const compatMensaje = ref('')
const compatBusquedaModelo = ref('')

function keyCompatModelo(m) {
  return `${m.marca}|${m.modelo}`
}

const confirmarEliminarEstante = ref(false)
const estanteAEliminar = ref(null)
const productosDelEstante = ref([])
const cargandoProductosEstante = ref(false)

onMounted(async () => {
  await Promise.all([
    productosStore.listar(),
    estantesStore.listar(),
  ])
  await nextTick()
  animarEntrada()
})

watch(tabActivo, async () => {
  busqueda.value = ''
  busquedaEstante.value = ''
  compatProductoSeleccionado.value = ''
  compatModelosSeleccionados.value = []
  compatModelosDisponibles.value = []
  compatMensaje.value = ''
  compatBusquedaModelo.value = ''
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
    compatModelosDisponibles.value = []
    compatModelosSeleccionados.value = []
    return
  }
  compatBusquedaModelo.value = ''
  try {
    const [modelos, modelosActuales] = await Promise.all([
      productosStore.obtenerModelosDesdeMotocicletas(),
      productosStore.listarModelos(compatProductoSeleccionado.value),
    ])
    compatModelosDisponibles.value = modelos
    compatModelosSeleccionados.value = modelosActuales
      .filter((m) => m.marca_moto && m.modelo_moto)
      .map((m) => keyCompatModelo({ marca: m.marca_moto, modelo: m.modelo_moto }))
  } catch {
    compatModelosDisponibles.value = []
    compatModelosSeleccionados.value = []
  }
}

function toggleCompatModelo(modeloKey) {
  const idx = compatModelosSeleccionados.value.indexOf(modeloKey)
  if (idx !== -1) {
    compatModelosSeleccionados.value.splice(idx, 1)
  } else {
    compatModelosSeleccionados.value.push(modeloKey)
  }
}

async function guardarCompatibilidad() {
  if (!compatProductoSeleccionado.value) return
  compatGuardando.value = true
  compatMensaje.value = ''

  const payload = compatModelosSeleccionados.value.map((k) => {
    const [marca, modelo] = k.split('|')
    return { marca, modelo }
  })

  try {
    await productosStore.guardarModelos(compatProductoSeleccionado.value, payload)
    compatMensaje.value = 'Compatibilidad guardada exitosamente'
    setTimeout(() => { compatMensaje.value = '' }, 3000)
  } catch {
    compatMensaje.value = 'Error al guardar compatibilidad'
  } finally {
    compatGuardando.value = false
  }
}

function agruparModelosPorMarca() {
  const q = compatBusquedaModelo.value.toLowerCase()
  const grupos = {}
  compatModelosDisponibles.value
    .filter((m) => !q || m.marca.toLowerCase().includes(q) || m.modelo.toLowerCase().includes(q))
    .forEach((m) => {
      if (!grupos[m.marca]) grupos[m.marca] = []
      grupos[m.marca].push(m)
    })
  return grupos
}

function clasificarStock(p) {
  const disp = p.stock_disponible ?? 0
  const min = p.stock_minimo ?? 0
  if (disp === 0 || disp < 10 || disp <= min) return { tipo: 'alerta' }
  return { tipo: 'ok' }
}

async function confirmarEliminar(e) {
  estanteAEliminar.value = e
  productosDelEstante.value = []
  confirmarEliminarEstante.value = true
  cargandoProductosEstante.value = true
  try {
    const productos = await estantesStore.obtenerProductos(e.id_estante)
    productosDelEstante.value = productos
  } catch {
    productosDelEstante.value = []
  } finally {
    cargandoProductosEstante.value = false
  }
}

async function ejecutarEliminarEstante() {
  if (!estanteAEliminar.value) return
  try {
    await estantesStore.eliminar(estanteAEliminar.value.id_estante)
    confirmarEliminarEstante.value = false
    estanteAEliminar.value = null
    productosDelEstante.value = []
    productosStore.listar()
  } catch {
    // error handled by store
  }
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

function nivelesUnicos(e) {
  if (!e.secciones?.length) return []
  const niveles = new Set()
  e.secciones.forEach((s) => {
    (s.ubicaciones || []).forEach((u) => {
      if (u.nivel) niveles.add(u.nivel)
    })
  })
  return [...niveles]
}


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

      <div class="stock-alert-banner">
        <svg viewBox="0 0 24 24" fill="none" width="20" height="20" class="banner-icon">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Se alertar&aacute; cuando el stock de un producto sea inferior a 10 unidades.</span>
      </div>

      <div class="tabla-wrapper">
        <table class="tabla-contenido">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Estante</th>
              <th>Precio Venta</th>
              <th>Costo</th>
              <th>Stock</th>
              <th class="td-alertas">Alertas</th>
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
              <td class="td-estante">
                <span v-if="p.ubicacion && estantesStore.items.some(e => e.id_estante === p.ubicacion.id_estante)" class="ubicacion-badge">
                  Est.{{ p.ubicacion.numero_estante }} - {{ p.ubicacion.codigo_seccion }} - {{ p.ubicacion.nivel }}
                </span>
                <span v-else class="sin-asignar">Sin asignar</span>
              </td>
              <td class="td-monto">Bs {{ (p.precio_venta ?? 0).toFixed(2) }}</td>
              <td class="td-monto">Bs {{ (p.costo ?? 0).toFixed(2) }}</td>
              <td class="td-stock">
                <span class="stock-disp">{{ p.stock_disponible }}</span>
                <span class="stock-sep">/</span>
                <span class="stock-fis">{{ p.stock_fisico }}</span>
              </td>
              <td class="td-alertas">
                <span v-if="clasificarStock(p).tipo !== 'ok'" class="alerta-texto">
                  Reabastecer producto
                </span>
                <span v-else class="ok-badge">&#10003;</span>
              </td>
              <td>
                <button class="btn-compat-pill" @click="abrirCompatibilidad(p)" title="Ver motos compatibles">
                  <svg viewBox="0 0 24 24" fill="none" width="14" height="14">
                    <circle cx="10" cy="10" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M22 22l-5-5M10 6V2M10 18v4M18 10h4M2 10h4M6 4l2 2M14 14l2 2M6 16l-2 2M14 6l2-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  Motos
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
              <td colspan="8" class="td-vacio">No se encontraron productos</td>
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
              <th>Niveles</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in estantesFiltrados" :key="e.id_estante">
              <td class="td-numero">{{ e.numero_estante }}</td>
              <td>{{ e.pasillo || '—' }}</td>
              <td class="td-descripcion">{{ e.descripcion || '—' }}</td>
              <td>
                <div class="pills-container">
                  <span v-for="s in (e.secciones || [])" :key="s.codigo_seccion" class="seccion-pill">
                    {{ s.codigo_seccion }}
                  </span>
                  <span v-if="!e.secciones?.length" class="sin-dato">&mdash;</span>
                </div>
              </td>
              <td>
                <div class="pills-container">
                  <span v-for="nivel in nivelesUnicos(e)" :key="nivel" class="nivel-pill">
                    {{ nivel }}
                  </span>
                  <span v-if="!nivelesUnicos(e).length" class="sin-dato">&mdash;</span>
                </div>
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
                  <button v-if="e.estadoA" class="btn-accion btn-eliminar" @click="confirmarEliminar(e)" title="Desactivar">
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

    <!-- MODAL: CONFIRMAR ELIMINAR ESTANTE -->
    <div v-if="confirmarEliminarEstante" class="modal-overlay">
      <div class="modal-card modal-eliminar-estante">
        <div class="modal-header">
          <h2>Desactivar Estante</h2>
          <button class="btn-cerrar" @click="confirmarEliminarEstante = false">&times;</button>
        </div>

        <div class="modal-body">
          <div class="alerta-confirmacion">
            <svg viewBox="0 0 24 24" fill="none" width="24" height="24" class="alerta-icono">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
              <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <div>
              <p class="alerta-titulo">¿Est&aacute;s seguro de desactivar el estante <strong>N&deg; {{ estanteAEliminar?.numero_estante }}</strong>?</p>
              <p class="alerta-subtitulo">Los productos en este estante dejar&aacute;n de tener una ubicaci&oacute;n asignada.</p>
            </div>
          </div>

          <div v-if="cargandoProductosEstante" class="cargando-productos">
            Verificando productos registrados...
          </div>

          <div v-else-if="productosDelEstante.length" class="productos-afectados">
            <div class="productos-header">
              <svg viewBox="0 0 24 24" fill="none" width="16" height="16" class="productos-icono">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Se encontraron <strong>{{ productosDelEstante.length }}</strong> producto(s) registrado(s) en este estante:</span>
            </div>
            <ul class="productos-lista">
              <li v-for="prod in productosDelEstante" :key="prod.id_producto">
                <span class="producto-nombre">{{ prod.nombre }}</span>
                <span class="producto-stock">Stock: {{ prod.stock_disponible }}</span>
              </li>
            </ul>
            <p class="productos-nota">
              Los productos permanecer&aacute;n en el sistema sin ubicaci&oacute;n asignada.
            </p>
          </div>

          <div v-else class="sin-productos">
            No hay productos registrados en este estante.
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancelar" @click="confirmarEliminarEstante = false">Cancelar</button>
          <button class="btn-eliminar-confirm" @click="ejecutarEliminarEstante" :disabled="cargandoProductosEstante">
            {{ cargandoProductosEstante ? 'Verificando...' : 'Desactivar Estante' }}
          </button>
        </div>
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

        <div v-if="compatModelosDisponibles.length > 0" class="compat-search-box">
          <svg viewBox="0 0 24 24" fill="none" class="compat-search-icon">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input
            v-model="compatBusquedaModelo"
            type="text"
            placeholder="Buscar por marca o modelo..."
            class="compat-search-input"
          />
        </div>

        <div v-if="compatModelosDisponibles.length === 0" class="sin-modelos">
          No hay motocicletas registradas para mostrar modelos compatibles.
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
                :key="keyCompatModelo(m)"
                class="compat-item"
                :class="{ seleccionado: compatModelosSeleccionados.includes(keyCompatModelo(m)) }"
              >
                <input
                  type="checkbox"
                  :checked="compatModelosSeleccionados.includes(keyCompatModelo(m))"
                  @change="toggleCompatModelo(keyCompatModelo(m))"
                />
                <span class="compat-modelo-nombre">{{ m.modelo }}</span>
              </label>
            </div>
          </div>
        </div>

        <div class="compat-footer">
          <span class="compat-contador">{{ compatModelosSeleccionados.length }} modelo(s) seleccionado(s)</span>
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

.td-nombre,
.td-estante {
  max-width: 180px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.td-nombre { font-weight: 600; color: #042D29; }
.td-monto { font-family: 'Inter', monospace; text-align: right; }
.td-stock { text-align: center; font-family: 'Inter', monospace; font-size: 13px; }
.tabla-contenido .td-alertas { text-align: center; }
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

.td-descripcion {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pills-container {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.seccion-pill {
  display: inline-block;
  background: #F0F4F3;
  color: #042D29;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 6px;
}

.nivel-pill {
  display: inline-block;
  background: #EFF6FF;
  color: #1E40AF;
  font-size: 11px;
  font-weight: 500;
  padding: 2px 8px;
  border-radius: 6px;
}

.sin-dato { color: #D1D5DB; }

.stock-disp { font-weight: 700; color: #042D29; }
.stock-sep { color: #D1D5DB; margin: 0 2px; }
.stock-fis { color: #929079; }

.stock-alert-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FFF5F5;
  border-left: 4px solid #741102;
  border-radius: 8px;
  padding: 10px 16px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #741102;
}
.banner-icon { flex-shrink: 0; }

.btn-compat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  background: #F0F4F3;
  color: #042D29;
  border: none;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn-compat-pill:hover {
  background: #E0E8E6;
  color: #042D29;
}

/* Modal de confirmación eliminar estante */
.modal-eliminar-estante {
  max-width: 520px;
}

/* Modal base — inline */
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
  max-width: 580px;
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

.modal-body { padding: 24px; }

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

.alerta-confirmacion {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #FFF5F5;
  border-left: 4px solid #741102;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 16px;
}

.alerta-icono {
  flex-shrink: 0;
  color: #741102;
  margin-top: 2px;
}

.alerta-titulo {
  margin: 0 0 2px;
  font-size: 14px;
  font-weight: 600;
  color: #741102;
}

.alerta-subtitulo {
  margin: 0;
  font-size: 12px;
  color: #991B1B;
}

.cargando-productos {
  text-align: center;
  padding: 24px;
  color: #929079;
  font-size: 13px;
}

.productos-afectados {
  margin-top: 4px;
}

.productos-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #1F2937;
  margin-bottom: 8px;
}

.productos-header strong { color: #042D29; }

.productos-icono {
  flex-shrink: 0;
  color: #741102;
}

.productos-lista {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 0;
  margin: 0;
  list-style: none;
}

.productos-lista li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  font-size: 13px;
  border-bottom: 1px solid #F3F4F6;
}

.productos-lista li:last-child { border-bottom: none; }
.productos-lista li:nth-child(odd) { background: #FAFAFA; }

.producto-nombre { font-weight: 500; color: #042D29; }
.producto-stock { color: #929079; font-family: 'Inter', monospace; font-size: 12px; }

.productos-nota {
  font-size: 12px;
  color: #D97706;
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  border-radius: 6px;
  padding: 8px 12px;
  margin: 10px 0 0;
}

.sin-productos {
  text-align: center;
  padding: 16px;
  color: #929079;
  font-size: 13px;
  background: #F9F9F7;
  border-radius: 8px;
}

.sin-asignar {
  color: #929079;
  font-style: italic;
  font-size: 13px;
}

.btn-eliminar-confirm {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #741102;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-eliminar-confirm:hover { background: #8B1A0A; }
.btn-eliminar-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

.compat-search-box {
  position: relative;
  margin-bottom: 16px;
}

.compat-search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #929079;
  pointer-events: none;
}

.compat-search-input {
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
  box-sizing: border-box;
}

.compat-search-input:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.alerta-texto {
  display: inline;
  color: #741102;
  font-size: 13px;
  font-weight: 600;
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

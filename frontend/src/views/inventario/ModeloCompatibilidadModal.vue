<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useProductosStore } from '@/stores/productos'

const props = defineProps({
  producto: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardado'])

const productosStore = useProductosStore()

function keyModelo(m) {
  return `${m.marca}|${m.modelo}`
}

const modelosDisponibles = ref([])
const modeloSeleccionados = ref([])
const cargando = ref(false)
const guardando = ref(false)
const errorGeneral = ref('')
const busquedaModelo = ref('')

onMounted(async () => {
  cargando.value = true
  try {
    const modelos = await productosStore.obtenerModelosDesdeMotocicletas()
    modelosDisponibles.value = modelos

    if (props.producto) {
      const res = await productosStore.listarModelos(props.producto.id_producto)
      modeloSeleccionados.value = res
        .filter((m) => m.marca_moto && m.modelo_moto)
        .map((m) => keyModelo({ marca: m.marca_moto, modelo: m.modelo_moto }))
    }
  } catch {
      errorGeneral.value = 'Error al cargar datos'
  } finally {
    cargando.value = false
  }

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function toggleModelo(m) {
  const k = keyModelo(m)
  const idx = modeloSeleccionados.value.indexOf(k)
  if (idx !== -1) {
    modeloSeleccionados.value.splice(idx, 1)
  } else {
    modeloSeleccionados.value.push(k)
  }
}

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errorGeneral.value = ''

  const payload = modeloSeleccionados.value.map((k) => {
    const [marca, modelo] = k.split('|')
    return { marca, modelo }
  })

  try {
    await productosStore.guardarModelos(props.producto.id_producto, payload)
    emit('guardado')
  } catch (err) {
    errorGeneral.value = err.response?.data?.message || 'Error al guardar compatibilidad'
  } finally {
    guardando.value = false
  }
}

function agruparPorMarca() {
  const q = busquedaModelo.value.toLowerCase()
  const grupos = {}
  modelosDisponibles.value
    .filter((m) => !q || m.marca.toLowerCase().includes(q) || m.modelo.toLowerCase().includes(q))
    .forEach((m) => {
      if (!grupos[m.marca]) grupos[m.marca] = []
      grupos[m.marca].push(m)
    })
  return grupos
}
</script>

<template>
  <div class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Compatibilidad de Modelos</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <div class="modal-body">
        <p class="producto-info">
          <strong>Producto:</strong> {{ props.producto?.nombre }}
        </p>

        <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

        <div v-if="cargando" class="cargando">Cargando modelos...</div>

        <div v-else class="modelos-lista">
          <div v-if="modelosDisponibles.length > 0" class="compat-search-box">
            <svg viewBox="0 0 24 24" fill="none" class="compat-search-icon">
              <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
              <path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input
              v-model="busquedaModelo"
              type="text"
              placeholder="Buscar por marca o modelo..."
              class="compat-search-input"
            />
          </div>

          <div v-if="modelosDisponibles.length > 0">
            <div v-for="(modelos, marca) in agruparPorMarca()" :key="marca" class="marca-grupo">
              <h4 class="marca-titulo">{{ marca }}</h4>
              <div class="modelos-grid">
                <label
                  v-for="m in modelos"
                  :key="keyModelo(m)"
                  class="modelo-item"
                  :class="{ seleccionado: modeloSeleccionados.includes(keyModelo(m)) }"
                >
                  <input
                    type="checkbox"
                    :checked="modeloSeleccionados.includes(keyModelo(m))"
                    @change="toggleModelo(m)"
                  />
                  <span class="modelo-nombre">{{ m.modelo }}</span>
                </label>
              </div>
            </div>
          </div>

          <p v-else class="sin-modelos">
            No hay motocicletas registradas para mostrar modelos compatibles.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cancelar</button>
          <button type="button" class="btn-guardar" :disabled="guardando || cargando" @click="guardar">
            {{ guardando ? 'Guardando...' : 'Guardar Compatibilidad' }}
          </button>
        </div>
      </div>
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

.producto-info {
  font-size: 14px;
  color: #1F2937;
  margin: 0 0 16px;
  padding: 12px;
  background: #F5F4F0;
  border-radius: 8px;
}

.mensaje-error {
  background: #FFF5F5;
  border-left: 3px solid #741102;
  color: #741102;
  padding: 10px 14px;
  font-size: 13px;
  margin-bottom: 16px;
  border-radius: 8px;
}

.cargando, .sin-modelos {
  text-align: center;
  color: #929079;
  padding: 32px 0;
  font-style: italic;
}

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

.marca-grupo { margin-bottom: 20px; }

.marca-titulo {
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
  margin: 0 0 8px;
  padding-bottom: 6px;
  border-bottom: 1px solid #E5E7EB;
}

.modelos-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
}

.modelo-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 13px;
}

.modelo-item:hover { border-color: #929079; }

.modelo-item.seleccionado {
  border-color: #042D29;
  background: #F0F4F3;
}

.modelo-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #042D29;
}

.modelo-nombre { font-weight: 500; color: #1F2937; }

.modelo-anios {
  font-size: 11px;
  color: #929079;
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

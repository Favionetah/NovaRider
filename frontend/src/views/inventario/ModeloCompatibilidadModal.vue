<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useProductosStore } from '@/stores/productos'
import { useModelosCompatiblesStore } from '@/stores/modelosCompatibles'

const props = defineProps({
  producto: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardado'])

const productosStore = useProductosStore()
const modelosStore = useModelosCompatiblesStore()

const modelosDisponibles = ref([])
const modeloSeleccionados = ref([])
const cargando = ref(false)
const guardando = ref(false)
const errorGeneral = ref('')

onMounted(async () => {
  cargando.value = true
  try {
    await modelosStore.listar()
    modelosDisponibles.value = modelosStore.items.filter((m) => m.estadoA)

    if (props.producto) {
      const res = await productosStore.listarModelos(props.producto.id_producto)
      modeloSeleccionados.value = res.map((m) => m.id_modelo)
    }
  } catch {
      errorGeneral.value = 'Error al cargar datos'
  } finally {
    cargando.value = false
  }

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function toggleModelo(idModelo) {
  const idx = modeloSeleccionados.value.indexOf(idModelo)
  if (idx !== -1) {
    modeloSeleccionados.value.splice(idx, 1)
  } else {
    modeloSeleccionados.value.push(idModelo)
  }
}

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errorGeneral.value = ''

  try {
    await productosStore.guardarModelos(props.producto.id_producto, modeloSeleccionados.value)
    emit('guardado')
  } catch (err) {
    errorGeneral.value = err.response?.data?.message || 'Error al guardar compatibilidad'
  } finally {
    guardando.value = false
  }
}

function agruparPorMarca() {
  const grupos = {}
  modelosDisponibles.value.forEach((m) => {
    if (!grupos[m.marca_moto]) grupos[m.marca_moto] = []
    grupos[m.marca_moto].push(m)
  })
  return grupos
}
</script>

<template>
  <div class="modal-overlay" @click.self="cerrar">
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
          <div v-for="(modelos, marca) in agruparPorMarca()" :key="marca" class="marca-grupo">
            <h4 class="marca-titulo">{{ marca }}</h4>
            <div class="modelos-grid">
              <label
                v-for="m in modelos"
                :key="m.id_modelo"
                class="modelo-item"
                :class="{ seleccionado: modeloSeleccionados.includes(m.id_modelo) }"
              >
                <input
                  type="checkbox"
                  :checked="modeloSeleccionados.includes(m.id_modelo)"
                  @change="toggleModelo(m.id_modelo)"
                />
                <span class="modelo-nombre">{{ m.modelo_moto }}</span>
                <span v-if="m.anio_inicio || m.anio_fin" class="modelo-anios">
                  ({{ m.anio_inicio || '—' }} - {{ m.anio_fin || '—' }})
                </span>
              </label>
            </div>
          </div>

          <p v-if="modelosDisponibles.length === 0" class="sin-modelos">
            No hay modelos compatibles registrados. Crea modelos en la pesta&ntilde;a "Compatibilidad" primero.
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

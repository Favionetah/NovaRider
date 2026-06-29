<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useEstantesStore } from '@/stores/estantes'

const props = defineProps({
  estante: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardado'])

const store = useEstantesStore()

const esEdicion = !!props.estante

const form = ref({
  numero_estante: '',
  pasillo: '',
  descripcion: '',
  secciones: [
    { codigo_seccion: 'A', niveles: ['Alto', 'Medio', 'Bajo'] },
    { codigo_seccion: 'B', niveles: ['Alto', 'Medio', 'Bajo'] },
    { codigo_seccion: 'C', niveles: ['Alto', 'Medio', 'Bajo'] },
    { codigo_seccion: 'D', niveles: ['Alto', 'Medio', 'Bajo'] },
  ],
})

const errores = ref({})
const guardando = ref(false)
const errorGeneral = ref('')

const opcionesSecciones = ['A', 'B', 'C', 'D']
const opcionesNiveles = ['Alto', 'Medio', 'Bajo']

onMounted(async () => {
  if (esEdicion && props.estante) {
    form.value.numero_estante = props.estante.numero_estante || ''
    form.value.pasillo = props.estante.pasillo || ''
    form.value.descripcion = props.estante.descripcion || ''

    if (props.estante.secciones && props.estante.secciones.length > 0) {
      form.value.secciones = props.estante.secciones.map((s) => ({
        codigo_seccion: s.codigo_seccion,
        niveles: (s.ubicaciones || []).map((u) => u.nivel).filter(Boolean),
      }))
    }
  }

  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

function toggleSeccion(codigo) {
  const idx = form.value.secciones.findIndex((s) => s.codigo_seccion === codigo)
  if (idx !== -1) {
    form.value.secciones.splice(idx, 1)
  } else {
    form.value.secciones.push({ codigo_seccion: codigo, niveles: [...opcionesNiveles] })
  }
}

function toggleNivel(seccionIdx, nivel) {
  const seccion = form.value.secciones[seccionIdx]
  const nIdx = seccion.niveles.indexOf(nivel)
  if (nIdx !== -1) {
    seccion.niveles.splice(nIdx, 1)
  } else {
    seccion.niveles.push(nivel)
  }
}

function cerrar() {
  emit('cerrar')
}

async function guardar() {
  guardando.value = true
  errores.value = {}
  errorGeneral.value = ''

  const payload = {
    numero_estante: Number(form.value.numero_estante),
    pasillo: form.value.pasillo || '',
    descripcion: form.value.descripcion || '',
    secciones: form.value.secciones
      .filter((s) => s.niveles.length > 0)
      .map((s) => ({
        codigo_seccion: s.codigo_seccion,
        niveles: s.niveles,
      })),
  }

  try {
    if (esEdicion) {
      await store.actualizar(props.estante.id_estante, payload)
    } else {
      await store.crear(payload)
    }
    emit('guardado')
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      errores.value = data.errors
    } else {
      errorGeneral.value = data?.message || 'Error al guardar estante'
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
        <h2>{{ esEdicion ? 'Editar Estante' : 'Nuevo Estante' }}</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <p v-if="errorGeneral" class="mensaje-error">{{ errorGeneral }}</p>

      <form @submit.prevent="guardar" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label for="numero_estante">N&deg; Estante <span class="required">*</span></label>
            <input
              id="numero_estante"
              v-model="form.numero_estante"
              type="number"
              min="1"
              max="50"
              placeholder="1 - 50"
              :class="{ 'input-error': errores.numero_estante }"
            />
            <span v-if="errores.numero_estante" class="error-text">{{ errores.numero_estante[0] }}</span>
          </div>

          <div class="form-group">
            <label for="pasillo">Pasillo</label>
            <input
              id="pasillo"
              v-model="form.pasillo"
              type="text"
              placeholder="Ej: Pasillo A"
            />
          </div>

          <div class="form-group form-group-full">
            <label for="descripcion">Descripci&oacute;n</label>
            <input
              id="descripcion"
              v-model="form.descripcion"
              type="text"
              placeholder="Ubicaci&oacute;n del estante"
            />
          </div>

          <div class="form-group form-group-full">
            <label>Secciones y Niveles</label>
            <div class="secciones-grid">
              <div
                v-for="codigo in opcionesSecciones"
                :key="codigo"
                class="seccion-card"
                :class="{ activa: form.secciones.some((s) => s.codigo_seccion === codigo) }"
              >
                <div class="seccion-header">
                  <label class="checkbox-label">
                    <input
                      type="checkbox"
                      :checked="form.secciones.some((s) => s.codigo_seccion === codigo)"
                      @change="toggleSeccion(codigo)"
                    />
                    <span>Secci&oacute;n {{ codigo }}</span>
                  </label>
                </div>
                <div v-if="form.secciones.some((s) => s.codigo_seccion === codigo)" class="niveles-opciones">
                  <label
                    v-for="nivel in opcionesNiveles"
                    :key="nivel"
                    class="nivel-label"
                    :class="{ activo: form.secciones.find((s) => s.codigo_seccion === codigo)?.niveles.includes(nivel) }"
                  >
                    <input
                      type="checkbox"
                      :checked="form.secciones.find((s) => s.codigo_seccion === codigo)?.niveles.includes(nivel)"
                      @change="toggleNivel(form.secciones.findIndex((s) => s.codigo_seccion === codigo), nivel)"
                    />
                    <span>{{ nivel }}</span>
                  </label>
                </div>
              </div>
            </div>
            <span class="help-text">Selecciona las secciones (A-D) y niveles (Alto, Medio, Bajo) para este estante</span>
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

.form-group input.input-error { border-color: #741102; }

.error-text { font-size: 12px; color: #741102; }
.help-text { font-size: 11px; color: #929079; font-style: italic; }

.secciones-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.seccion-card {
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  padding: 12px;
  transition: all 0.2s ease;
}

.seccion-card.activa {
  border-color: #042D29;
  background: #F0F4F3;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #042D29;
}

.niveles-opciones {
  display: flex;
  gap: 4px;
  margin-top: 8px;
}

.nivel-label {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  cursor: pointer;
  background: #FFFFFF;
  border: 1px solid #D1D5DB;
  transition: all 0.2s ease;
}

.nivel-label.activo {
  background: #042D29;
  color: #FFFFFF;
  border-color: #042D29;
}

.nivel-label input { display: none; }

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

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '@/services/api'

const props = defineProps({
  producto: { type: Object, required: true },
})

const emit = defineEmits(['cerrar'])

const motocicletas = ref([])
const total = ref(0)
const cargando = ref(true)
const error = ref('')
const descargando = ref(false)

onMounted(async () => {
  await cargarMotocicletas()
  await nextTick()
  gsap.fromTo('.modal-card', { y: 30, opacity: 0, scale: 0.97 }, { y: 0, opacity: 1, scale: 1, duration: 0.3, ease: 'power3.out' })
})

async function cargarMotocicletas() {
  cargando.value = true
  error.value = ''
  try {
    const res = await api.get(`/productos/${props.producto.id_producto}/motocicletas-compatibles`)
    motocicletas.value = res.data.motocicletas
    total.value = res.data.total
  } catch {
    error.value = 'Error al cargar motocicletas compatibles'
  } finally {
    cargando.value = false
  }
}

async function descargarPdf() {
  descargando.value = true
  try {
    const res = await api.get(`/productos/${props.producto.id_producto}/motocicletas-compatibles/pdf`, {
      responseType: 'blob',
    })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    const filename = `compatibilidad_${props.producto.nombre.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch {
    error.value = 'Error al descargar PDF'
  } finally {
    descargando.value = false
  }
}

function cerrar() {
  emit('cerrar')
}
</script>

<template>
  <div class="modal-overlay" @click.self="cerrar">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Motocicletas Compatibles</h2>
        <button class="btn-cerrar" @click="cerrar">&times;</button>
      </div>

      <div class="modal-body">
        <div class="producto-badge">
          <strong>Producto:</strong> {{ producto.nombre }}
          <span v-if="producto.ubicacion" class="ubicacion-tag">
            Est.{{ producto.ubicacion.numero_estante }} - {{ producto.ubicacion.codigo_seccion }} - {{ producto.ubicacion.nivel }}
          </span>
        </div>

        <p v-if="error" class="mensaje-error">{{ error }}</p>

        <div v-if="cargando" class="cargando">Buscando motocicletas compatibles...</div>

        <div v-else-if="motocicletas.length === 0" class="sin-resultados">
          No se encontraron motocicletas compatibles con este producto.
          <br>
          <span class="sin-resultados-sub">Aseg&uacute;rate de haber asignado modelos compatibles en "Gestionar Compatibilidad".</span>
        </div>

        <div v-else class="motocicletas-lista">
          <div class="resultados-header">
            <span class="total-badge">{{ total }} motocicleta(s) compatible(s)</span>
            <button class="btn-pdf" :disabled="descargando" @click="descargarPdf">
              <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              {{ descargando ? 'Descargando...' : 'Descargar PDF' }}
            </button>
          </div>

          <div class="tabla-wrapper">
            <table class="tabla-contenido">
              <thead>
                <tr>
                  <th>Placa</th>
                  <th>Marca</th>
                  <th>Modelo</th>
                  <th>A&ntilde;o</th>
                  <th>Color</th>
                  <th>Propietario</th>
                  <th>Tel&eacute;fono</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in motocicletas" :key="m.id_motocicleta">
                  <td class="td-placa">{{ m.placa || '—' }}</td>
                  <td>{{ m.marca }}</td>
                  <td>{{ m.modelo }}</td>
                  <td class="td-numero">{{ m.anio || '—' }}</td>
                  <td>{{ m.color || '—' }}</td>
                  <td>{{ m.cliente?.nombre_completo || '—' }}</td>
                  <td>{{ m.cliente?.telefono || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="cerrar">Cerrar</button>
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
  max-width: 800px;
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

.producto-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #F0F4F3;
  border-radius: 10px;
  font-size: 14px;
  color: #1F2937;
  margin-bottom: 16px;
}

.producto-badge strong { color: #042D29; }

.ubicacion-tag {
  font-size: 11px;
  background: #FFFFFF;
  padding: 3px 8px;
  border-radius: 6px;
  color: #929079;
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

.cargando, .sin-resultados {
  text-align: center;
  color: #929079;
  padding: 40px 0;
  font-style: italic;
}

.sin-resultados-sub {
  font-size: 12px;
  color: #D1D5DB;
}

.resultados-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.total-badge {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  background: #F0F4F3;
  padding: 6px 14px;
  border-radius: 20px;
}

.btn-pdf {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #FFFFFF;
  color: #741102;
  border: 1.5px solid #741102;
  border-radius: 10px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pdf:hover { background: #FFF5F5; }
.btn-pdf:disabled { opacity: 0.6; cursor: not-allowed; }

.tabla-wrapper {
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  overflow: hidden;
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
  font-size: 10px;
  letter-spacing: 0.5px;
  padding: 10px 12px;
  text-align: left;
  border-bottom: 1px solid #E5E7EB;
}

.tabla-contenido td {
  padding: 10px 12px;
  color: #1F2937;
  border-bottom: 1px solid #F3F4F6;
}

.tabla-contenido tbody tr:hover { background: #F9F9F7; }
.tabla-contenido tbody tr:last-child td { border-bottom: none; }

.td-placa { font-weight: 600; color: #042D29; font-family: 'Inter', monospace; text-transform: uppercase; }
.td-numero { text-align: center; }

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
</style>

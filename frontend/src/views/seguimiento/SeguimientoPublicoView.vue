<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import seguimientoService from '@/services/seguimientoService'

const route = useRoute()
const router = useRouter()

const codigo = ref((route.params.codigo || '').toString())
const orden = ref(null)
const cargando = ref(false)
const mensajeError = ref('')

const pasos = ['Pendiente', 'En proceso', 'Esperando repuestos', 'Listo para entrega', 'Entregado']

const estadoActualIndex = computed(() => {
  const index = pasos.indexOf(orden.value?.estado)
  return index === -1 ? 0 : index
})

async function consultar() {
  const codigoLimpio = codigo.value.trim()
  if (!codigoLimpio) {
    mensajeError.value = 'Ingrese el codigo de seguimiento del recibo.'
    orden.value = null
    return
  }

  cargando.value = true
  mensajeError.value = ''

  try {
    const res = await seguimientoService.consultar(codigoLimpio)
    orden.value = res.data.orden
    router.replace({ name: 'seguimiento-publico', params: { codigo: orden.value.codigo_seguimiento } })
  } catch (error) {
    orden.value = null
    mensajeError.value = error.response?.data?.message || 'No se pudo consultar el seguimiento.'
  } finally {
    cargando.value = false
  }
}

function volverLogin() {
  router.push('/login')
}

onMounted(() => {
  if (codigo.value) consultar()
})
</script>

<template>
  <main class="seguimiento-page">
    <section class="consulta-panel">
      <button type="button" class="link-volver" @click="volverLogin">Volver al login</button>
      <h1>Seguimiento de reparacion</h1>
      <p>Ingrese el codigo impreso en su recibo para ver el avance de su motocicleta.</p>

      <form class="consulta-form" @submit.prevent="consultar">
        <input
          v-model.trim="codigo"
          type="text"
          placeholder="Ej. NR-000001"
          autocomplete="off"
          class="codigo-input"
        >
        <button type="submit" :disabled="cargando">
          {{ cargando ? 'Consultando...' : 'Consultar' }}
        </button>
      </form>

      <p v-if="mensajeError" class="mensaje-error">{{ mensajeError }}</p>
    </section>

    <section v-if="orden" class="resultado-panel">
      <div class="resumen-orden">
        <div>
          <span class="label">Seguimiento No.</span>
          <strong>{{ orden.codigo_seguimiento }}</strong>
          <small>Orden {{ orden.nro_orden }}</small>
        </div>
        <span class="estado-actual">{{ orden.estado }}</span>
      </div>

      <div class="datos-grid">
        <div>
          <span class="label">Motocicleta</span>
          <strong>{{ orden.motocicleta?.marca }} {{ orden.motocicleta?.modelo }}</strong>
          <small>{{ orden.motocicleta?.placa }} · {{ orden.motocicleta?.color || 'Color no registrado' }}</small>
        </div>
        <div>
          <span class="label">Cliente</span>
          <strong>{{ orden.cliente?.nombre || 'Cliente' }}</strong>
          <small>Ingreso: {{ orden.fecha_ingreso }}</small>
        </div>
        <div>
          <span class="label">Tecnico</span>
          <strong>{{ orden.mecanico || 'Taller NovaRider' }}</strong>
          <small>{{ orden.condicion_entrada }}</small>
        </div>
      </div>

      <div class="progreso">
        <div
          v-for="(paso, index) in pasos"
          :key="paso"
          class="paso"
          :class="{ completado: index <= estadoActualIndex, activo: index === estadoActualIndex }"
        >
          <span class="punto"></span>
          <span>{{ paso }}</span>
        </div>
      </div>

      <div class="historial">
        <h2>Historial</h2>
        <ol>
          <li v-for="item in orden.historial" :key="`${item.fecha_hora_cambio}-${item.estado}`">
            <time>{{ item.fecha_hora_cambio || 'Fecha pendiente' }}</time>
            <strong>{{ item.estado }}</strong>
            <span>{{ item.observacion || 'Actualizacion de estado' }}</span>
          </li>
        </ol>
      </div>

      <div v-if="orden.repuestos && orden.repuestos.length" class="desglose">
        <h2>Repuestos utilizados</h2>
        <div v-for="repuesto in orden.repuestos" :key="`${repuesto.producto}-${repuesto.descripcion}`" class="desglose-row">
          <span>
            <strong>{{ repuesto.producto || 'Repuesto' }}</strong>
            <small>{{ repuesto.descripcion }}</small>
          </span>
          <b>{{ repuesto.cantidad }} x Bs {{ Number(repuesto.costo_unitario || 0).toFixed(2) }}</b>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.seguimiento-page {
  min-height: 100vh;
  padding: 32px;
  background: #F5F4F0;
  color: #042D29;
}

.consulta-panel,
.resultado-panel {
  max-width: 900px;
  margin: 0 auto 24px;
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
  border-top: 4px solid #741102;
  padding: 28px;
}

.link-volver {
  border: none;
  background: transparent;
  color: #929079;
  font-weight: 700;
  cursor: pointer;
  margin-bottom: 18px;
}

h1 {
  font-size: 28px;
  margin-bottom: 8px;
}

p {
  color: #55574A;
  margin-bottom: 20px;
}

.consulta-form {
  display: flex;
  gap: 12px;
}

.codigo-input {
  flex: 1;
  min-width: 0;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  padding: 12px 14px;
  color: #042D29;
  font-size: 16px;
  text-transform: uppercase;
}

.consulta-form button {
  border: none;
  border-radius: 10px;
  background: #042D29;
  color: #FFFFFF;
  font-weight: 700;
  padding: 0 18px;
  cursor: pointer;
}

.consulta-form button:disabled {
  background: #929079;
  cursor: not-allowed;
}

.mensaje-error {
  color: #741102;
  font-weight: 600;
  margin: 14px 0 0;
}

.resumen-orden {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  padding: 22px;
  border-radius: 12px;
  background: #042D29;
  color: #FFFFFF;
}

.label {
  display: block;
  color: #929079;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 6px;
  text-transform: uppercase;
}

.resumen-orden strong {
  display: block;
  font-size: 30px;
  letter-spacing: 1px;
}

.resumen-orden small {
  color: rgba(255, 255, 255, 0.78);
}

.estado-actual {
  align-self: flex-start;
  border-radius: 999px;
  background: #FFFFFF;
  color: #042D29;
  font-weight: 800;
  padding: 8px 12px;
  white-space: nowrap;
}

.datos-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin: 22px 0;
}

.datos-grid > div {
  border: 1px solid #E2E4E3;
  border-radius: 10px;
  padding: 14px;
}

.datos-grid strong,
.datos-grid small {
  display: block;
}

.datos-grid small {
  color: #55574A;
  margin-top: 4px;
}

.progreso {
  display: grid;
  gap: 12px;
  margin: 26px 0;
}

.paso {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 12px;
  align-items: center;
  color: #929079;
  font-weight: 700;
}

.punto {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #D1D5DB;
  background: #FFFFFF;
}

.paso.completado {
  color: #042D29;
}

.paso.completado .punto {
  border-color: #042D29;
  background: #042D29;
}

.paso.activo {
  color: #741102;
}

.paso.activo .punto {
  border-color: #741102;
  background: #741102;
}

.historial h2 {
  font-size: 18px;
  margin-bottom: 12px;
}

.desglose {
  margin-top: 24px;
  border-top: 1px solid #E2E4E3;
  padding-top: 18px;
}

.desglose h2 {
  font-size: 18px;
  margin-bottom: 12px;
}

.desglose-row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 1px solid #F1F3F2;
}

.desglose-row span,
.desglose-row small {
  display: block;
}

.desglose-row small {
  color: #55574A;
  margin-top: 3px;
}

.desglose-row b {
  color: #042D29;
  white-space: nowrap;
}

.historial ol {
  display: grid;
  gap: 10px;
  list-style: none;
}

.historial li {
  border-left: 3px solid #042D29;
  padding: 4px 0 4px 12px;
}

.historial time,
.historial span {
  display: block;
  color: #55574A;
  font-size: 13px;
}

@media (max-width: 720px) {
  .seguimiento-page {
    padding: 18px;
  }

  .consulta-form,
  .resumen-orden {
    flex-direction: column;
  }

  .consulta-form button {
    height: 44px;
  }

  .datos-grid {
    grid-template-columns: 1fr;
  }
}
</style>

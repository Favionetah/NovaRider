<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import api from '@/services/api'

const tipo = ref('clientes')
const data = ref([])
const loading = ref(false)
const error = ref(null)

async function cargarDatos() {
  loading.value = true
  error.value = null
  try {
    const res = await api.get(`/reportes/data?tipo=${tipo.value}`)
    data.value = res.data.data
    await nextTick()
    animarTabla()
  } catch (err) {
    error.value = 'Error al cargar los datos del reporte'
  } finally {
    loading.value = false
  }
}

function animarTabla() {
  gsap.fromTo('.tabla-wrapper', 
    { opacity: 0, y: 10 }, 
    { opacity: 1, y: 0, duration: 0.4 }
  )
}

function exportarPdf() {
  window.open(`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/reportes/pdf?tipo=${tipo.value}`, '_blank')
}

function formatearMoneda(valor) {
  return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(valor)
}

onMounted(cargarDatos)
watch(tipo, cargarDatos)
</script>

<template>
  <div class="reportes-page">
    <header class="page-header">
      <div>
        <h1>Reportes del Sistema</h1>
        <p class="subtitle">Genera informes detallados de cada módulo de NovaRider.</p>
      </div>
      <button class="btn-export-global" @click="window.open(`${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/reportes/pdf?tipo=sistema`, '_blank')" :disabled="loading">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon">
          <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Descargar Resumen Ejecutivo
      </button>
    </header>

    <div class="selector-container">
      <div class="selector-tipo">
        <button class="btn-tab" :class="{ active: tipo === 'clientes' }" @click="tipo = 'clientes'">Clientes</button>
        <button class="btn-tab" :class="{ active: tipo === 'motos' }" @click="tipo = 'motos'">Motos</button>
        <button class="btn-tab" :class="{ active: tipo === 'usuarios' }" @click="tipo = 'usuarios'">Personal</button>
        <button class="btn-tab" :class="{ active: tipo === 'inventario' }" @click="tipo = 'inventario'">Inventario</button>
        <button class="btn-tab" :class="{ active: tipo === 'ventas' }" @click="tipo = 'ventas'">Ventas</button>
      </div>
    </div>

    <div class="content-container">
      <div class="content-card">
        <div class="header-previa">
          <h3>Vista previa del reporte</h3>
          <button class="btn-export" @click="exportarPdf" :disabled="loading || data.length === 0">
             Exportar lista actual a PDF
          </button>
        </div>
        
        <div v-else class="tabla-wrapper">
          <table class="tabla-reporte">
            <thead>
              <tr v-if="tipo === 'clientes'">
                <th>Cliente</th>
                <th>CI / NIT</th>
                <th class="txt-centro">Motos</th>
              </tr>
              <tr v-else-if="tipo === 'motos'">
                <th>Placa</th>
                <th>Vehículo</th>
                <th>Propietario</th>
              </tr>
              <tr v-else-if="tipo === 'usuarios'">
                <th>Usuario</th>
                <th>Cargo</th>
                <th>Roles</th>
              </tr>
              <tr v-else-if="tipo === 'inventario'">
                <th>Producto</th>
                <th>Stock Disponible</th>
                <th>Precio Venta</th>
              </tr>
              <tr v-else-if="tipo === 'ventas'">
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Método</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in data" :key="item.id_cliente || item.id_motocicleta || item.id_usuario || item.id_producto || item.id_venta">
                <template v-if="tipo === 'clientes'">
                  <td><strong>{{ item.primer_nombre }} {{ item.apellido_paterno }}</strong></td>
                  <td>{{ item.ci }}</td>
                  <td class="txt-centro"><span class="badge">{{ item.motocicletas_count }}</span></td>
                </template>
                <template v-else-if="tipo === 'motos'">
                  <td><span class="placa">{{ item.placa }}</span></td>
                  <td>{{ item.marca }} {{ item.modelo }}</td>
                  <td>{{ item.cliente ? `${item.cliente.primer_nombre} ${item.cliente.apellido_paterno}` : '—' }}</td>
                </template>
                <template v-else-if="tipo === 'usuarios'">
                  <td><strong>{{ item.username }}</strong></td>
                  <td>{{ item.empleado?.cargo || '—' }}</td>
                  <td>{{ item.roles?.map(r => r.nombre).join(', ') }}</td>
                </template>
                <template v-else-if="tipo === 'inventario'">
                  <td>{{ item.nombre }}</td>
                  <td :class="{ 'txt-rojo': item.stock_disponible <= item.stock_minimo }">{{ item.stock_disponible }}</td>
                  <td>{{ formatearMoneda(item.precio_venta) }}</td>
                </template>
                <template v-else-if="tipo === 'ventas'">
                  <td>{{ new Date(item.fecha_hora).toLocaleDateString() }}</td>
                  <td>{{ item.cliente ? `${item.cliente.primer_nombre} ${item.cliente.apellido_paterno}` : 'Cliente Final' }}</td>
                  <td><strong>{{ formatearMoneda(item.total) }}</strong></td>
                  <td>{{ item.metodo_pago }}</td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reportes-page {
  padding: 32px;
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.page-header h1 {
  font-size: 26px;
  color: #042D29;
  margin-bottom: 4px;
}

.subtitle {
  color: #929079;
}

.selector-container {
  margin-bottom: 24px;
}

.selector-tipo {
  display: flex;
  background: #fff;
  padding: 6px;
  border-radius: 14px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  gap: 4px;
  overflow-x: auto;
}

.btn-tab {
  border: none;
  background: transparent;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  color: #929079;
  white-space: nowrap;
  transition: all 0.2s;
}

.btn-tab.active {
  background: #042D29;
  color: #fff;
}

.dashboard-grid {
  margin-top: 20px;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  background: #fff;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  border-top: 4px solid #042D29;
}

.stat-card.accent { border-top-color: #741102; }
.stat-card.warning { border-top-color: #F59E0B; }

.stat-label {
  font-size: 13px;
  color: #929079;
  margin-bottom: 8px;
  font-weight: 600;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #042D29;
}

.btn-export-global {
  background: #741102;
  color: #fff;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: transform 0.2s;
}

.btn-export-global:hover { transform: translateY(-2px); }

.content-card {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.header-previa {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #f5f4f0;
}

.header-previa h3 {
  margin: 0;
  font-size: 16px;
  color: #042D29;
}

.btn-export {
  background: #FFFFFF;
  color: #042D29;
  border: 1.5px solid #042D29;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-export:hover {
  background: #042D29;
  color: #fff;
}

.tabla-reporte {
  width: 100%;
  border-collapse: collapse;
}

.tabla-reporte th {
  text-align: left;
  color: #929079;
  font-size: 13px;
  padding: 12px;
  border-bottom: 2px solid #f5f4f0;
}

.tabla-reporte td {
  padding: 16px 12px;
  border-bottom: 1px solid #f5f4f0;
  font-size: 14px;
}

.badge {
  background: #042D29;
  color: #fff;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 12px;
}

.placa {
  background: #FEF3C7;
  color: #92400E;
  font-weight: 800;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #FCD34D;
  font-family: monospace;
}

.txt-centro { text-align: center; }
.txt-rojo { color: #741102; font-weight: 700; }
.cargando { text-align: center; padding: 40px; color: #929079; }
</style>

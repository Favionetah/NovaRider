<template>
  <div class="taller-container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Órdenes de Trabajo y Reparaciones</h2>
        <p class="section-subtitle">Gestión del estado de motocicletas y control técnico</p>
      </div>
      <button @click="abrirModalNuevaOrden" class="btn-novarider-primary">
        + Registrar Nueva Orden
      </button>
    </div>

    <div class="novarider-card">
      <div class="card-filter-bar">
        <div class="search-box-wrapper">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="#929079" stroke-width="2" />
            <path d="M20 20l-4-4" stroke="#929079" stroke-width="2" stroke-linecap="round" />
          </svg>
          <input 
            type="text" 
            placeholder="Buscar por matrícula o nro. de orden..." 
            class="novarider-input"
            v-model="filtroTexto"
          >
        </div>

        <div class="select-wrapper">
          <select v-model="estadoFiltro" class="novarider-select">
            <option value="">Todos los estados</option>
            <option value="Pendiente">Pendientes</option>
            <option value="En proceso">En proceso</option>
            <option value="Esperando repuestos">Esperando repuestos</option>
            <option value="Listo para entrega">Listos para entrega</option>
            <option value="Entregado">Entregados</option>
          </select>
        </div>

        <button 
          @click="imprimirReportePdf" 
          class="btn-novarider-secondary" 
          style="display: flex; align-items: center; gap: 8px;"
          title="Exportar listado actual a PDF"
        >
          🖨️ Imprimir Reporte
        </button>
      </div>

      <div v-if="vistaActual === 'lista'" class="card-content-body">
        <div class="table-responsive">
          <table class="novarider-table">
            <thead>
              <tr>
                <th>Nro. Orden</th>
                <th>Matrícula / Placa</th>
                <th>Especialista Asignado</th>
                <th>Fecha Ingreso</th>
                <th>Estado</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="orden in ordenesFiltradas" :key="orden.id_orden">
                <td class="font-bold text-dark">#{{ orden.nro_orden }}</td>
                <td>
                  <span class="plate-badge">
                    {{ orden.motocicleta ? orden.motocicleta.placa : orden.placa_simulada || 'SIN PLACA' }}
                  </span>
                </td>
                <td>{{ formatMecanico(orden.empleado) }}</td>
                <td class="text-muted">{{ orden.fecha_ingreso }}</td>
                <td>
                  <span :class="'status-pill ' + cleanClassName(orden.estado)">
                    {{ orden.estado }}
                  </span>
                </td>
                <td class="text-right actions-cell">
                  <button 
                    v-if="orden.estado !== 'Listo para entrega' && orden.estado !== 'Entregado'"
                    @click="marcarComoListo(orden)" 
                    class="btn-table-action btn-ready" 
                    title="Marcar como Listo para Entrega"
                  >
                    🚀 Listo
                  </button>

                  <button @click="irAChecklist(orden)" class="btn-table-action btn-verify" title="Validar Calidad">
                    📋 Validar
                  </button>

                  <button @click="eliminarOrdenTrabajo(orden.id_orden)" class="btn-table-action btn-delete" title="Eliminar Orden">
                    🗑️ Eliminar
                  </button>
                </td>
              </tr>
              <tr v-if="ordenesFiltradas.length === 0">
                <td colspan="6" class="text-center empty-row">
                  No se encontraron órdenes de trabajo registradas.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-else-if="vistaActual === 'checklist'" class="card-content-body p-0">
        <ChecklistCalidad 
          :ordenActiva="ordenSeleccionada" 
          @volver="regresarAListado" 
          @ordenActualizada="actualizarEstadoOrdenLista" 
        />
      </div>
    </div>

    <div v-if="mostrarModalOrden" class="nr-modal-overlay" @click="mostrarModalOrden = false">
      <div class="nr-modal" @click.stop>
        <div class="nr-modal-header">
          <h3>Entrada de Vehículo a Taller</h3>
          <button @click="mostrarModalOrden = false" class="nr-btn-close">✕</button>
        </div>
        <form @submit.prevent="guardarNuevaOrden" class="nr-modal-body">
          
          <div class="nr-form-group">
            <label>Nro. de Orden</label>
            <input type="text" v-model="nuevaOrden.nro_orden" readonly class="novarider-input-disabled">
          </div>
          
          <div class="nr-form-group">
            <label>Matrícula / Placa del Vehículo</label>
            <select v-model="nuevaOrden.id_motocicleta" required class="novarider-select">
              <option value="" disabled selected>Seleccione la placa de la motocicleta</option>
              <option v-for="moto in motocicletasBD" :key="moto.id_motocicleta" :value="moto.id_motocicleta">
                {{ moto.placa }} - {{ moto.modelo || 'Sin Modelo' }}
              </option>
            </select>
          </div>
          
          <div class="nr-form-group">
            <label>Mecánico Encargado</label>
            <select v-model="nuevaOrden.id_empleado" required class="novarider-select">
              <option value="" disabled selected>Seleccione el mecánico responsable</option>
              <option v-for="mecanico in mecanicosBD" :key="mecanico.id_empleado" :value="mecanico.id_empleado">
                {{ mecanico.primer_nombre }} {{ mecanico.apellido_paterno }}
              </option>
            </select>
          </div>

          <div class="nr-form-group">
            <label>Condición de Entrada</label>
            <select v-model="nuevaOrden.condicion_entrada" required class="novarider-select">
              <option value="" disabled selected>Seleccione el estado del vehículo</option>
              <option value="Buena - Sin daños visibles">Buena - Sin daños visibles</option>
              <option value="Regular - Sucia / Desgaste normal">Regular - Sucia / Desgaste normal</option>
              <option value="Mala - Golpes / Rayaduras visibles">Mala - Golpes / Rayaduras visibles</option>
              <option value="Crítica - No enciende / Falla mecánica grave">Crítica - No enciende / Falla mecánica grave</option>
            </select>
          </div>
          
          <div class="nr-form-row">
            <div class="nr-form-group flex-1">
              <label>Fecha de Registro</label>
              <input type="date" v-model="nuevaOrden.fecha_ingreso" required class="novarider-input">
            </div>
            <div class="nr-form-group flex-1">
              <label>Estado Inicial</label>
              <select v-model="nuevaOrden.estado" required class="novarider-select w-full">
                <option value="Pendiente">Pendiente</option>
                <option value="En proceso">En proceso</option>
                <option value="Esperando repuestos">Esperando repuestos</option>
                <option value="Listo para entrega">Listo para entrega</option>
              </select>
            </div>
          </div>
          
          <div class="nr-modal-footer">
            <button type="button" @click="mostrarModalOrden = false" class="nr-btn-link">Cancelar</button>
            <button type="submit" class="btn-novarider-primary-submit">
              Confirmar Registro
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import tallerService from '../../services/tallerService';
import ChecklistCalidad from './ChecklistCalidad.vue';

export default {
  components: { ChecklistCalidad },
  data() {
    return {
      ordenes: [],
      mecanicosBD: [], 
      motocicletasBD: [], 
      estadoFiltro: '',
      filtroTexto: '',
      vistaActual: 'lista',
      ordenSeleccionada: null,
      mostrarModalOrden: false,
      nuevaOrden: { 
        id_orden: null, 
        nro_orden: '', 
        id_motocicleta: '', 
        id_empleado: '', 
        fecha_ingreso: '', 
        estado: 'En proceso',
        condicion_entrada: '' 
      }
    };
  },
  computed: {
    ordenesFiltradas() {
      return this.ordenes.filter(o => {
        const matchesEstado = !this.estadoFiltro || o.estado.toLowerCase() === this.estadoFiltro.toLowerCase();
        const placaText = o.motocicleta ? o.motocicleta.placa : o.placa_simulada || '';
        const nroText = o.nro_orden || '';
        const matchesTexto = !this.filtroTexto || 
                             placaText.toLowerCase().includes(this.filtroTexto.toLowerCase()) ||
                             nroText.toLowerCase().includes(this.filtroTexto.toLowerCase());

        return matchesEstado && matchesTexto;
      });
    }
  },
  mounted() { 
    this.cargarOrdenes(); 
    this.cargarAuxiliaresBD(); 
  },
  methods: {
    async cargarOrdenes() {
      try {
        const res = await tallerService.obtenerOrdenes();
        if (res.data && res.data.ordenes) {
          this.ordenes = res.data.ordenes;
        } else if (Array.isArray(res.data)) {
          this.ordenes = res.data; 
        }
      } catch (e) { 
        console.error("Error conectando a las órdenes reales, usando fallback:", e);
        this.cargarDatosDePrueba(); 
      }
    },
    async cargarAuxiliaresBD() {
      try {
        const resMecanicos = await tallerService.obtenerMecanicos();
        this.mecanicosBD = resMecanicos.data;

        const resMotos = await tallerService.obtenerMotocicletas();
        this.motocicletasBD = resMotos.data;
      } catch (e) {
        console.error("Error al cargar datos auxiliares, cargando simulados...", e);
        this.mecanicosBD = [
          { id_empleado: 1, primer_nombre: 'Andrés', apellido_paterno: 'Luna' },
          { id_empleado: 2, primer_nombre: 'Damián', apellido_paterno: 'Ortega' },
          { id_empleado: 3, primer_nombre: 'Carlos', Mendoza: 'Mendoza' } // Corregido el mapeo para consistencia
        ];
        this.motocicletasBD = [
          { id_motocicleta: 10, placa: '7112-IKD', modelo: 'Cruiser 250' },
          { id_motocicleta: 20, placa: '9921-XOW', modelo: 'Sport 150' },
          { id_motocicleta: 30, placa: '4321-BBN', modelo: 'Scooter 125' }
        ];   
      }
    },

    // 📥 Método para mandar los filtros activos al backend e imprimir
    imprimirReportePdf() {
      const baseURL = 'http://localhost:8000/ordenes/reporte/pdf'; // Ajusta si usas otra URL o prefijo de API
      const params = new URLSearchParams({
        busqueda: this.filtroTexto,
        estado: this.estadoFiltro,
        preview: 'true' // Para abrir en pestaña en vez de forzar descarga inmediata
      });

      window.open(`${baseURL}?${params.toString()}`, '_blank');
    },

    async abrirModalNuevaOrden() {
      await this.cargarAuxiliaresBD();

      this.nuevaOrden = {
        id_orden: null,
        nro_orden: 'NVR-' + (this.ordenes.length + 101), 
        id_motocicleta: '',
        id_empleado: '',
        fecha_ingreso: new Date().toISOString().substring(0, 10),
        estado: 'En proceso',
        condicion_entrada: '' 
      };
      this.mostrarModalOrden = true;
    },

    async guardarNuevaOrden() {
      if (!this.nuevaOrden.id_empleado || !this.nuevaOrden.id_motocicleta || !this.nuevaOrden.condicion_entrada) {
        alert("Atención: Debe completar todos los campos obligatorios.");
        return;
      }

      const payload = {
        nro_orden: this.nuevaOrden.nro_orden,
        fecha_ingreso: this.nuevaOrden.fecha_ingreso,
        estado: this.nuevaOrden.estado, 
        id_motocicleta: this.nuevaOrden.id_motocicleta,
        id_empleado: this.nuevaOrden.id_empleado,
        condicion_entrada: this.nuevaOrden.condicion_entrada 
      };

      try {
        await tallerService.crearOrden(payload);
        this.mostrarModalOrden = false;
        this.cargarOrdenes(); 
      } catch (e) {
        console.error("Error al persistir en el servidor:", e);
        alert("Error de procesamiento de datos en base de datos.");
      }
    },

    async eliminarOrdenTrabajo(idOrden) {
      if (confirm("¿Está completamente seguro de eliminar esta orden de trabajo?")) {
        try {
          await tallerService.eliminarOrden(idOrden);
          this.cargarOrdenes();
        } catch (e) {
          console.error("Error al borrar orden en backend:", e);
        }
      }
    },

    async marcarComoListo(orden) {
      const estadoObjetivo = 'Listo para entrega';
      
      try {
        console.log("Enviando actualización de estado para ID:", orden.id_orden);
        const respuesta = await tallerService.cambiarEstadoOrden(orden.id_orden, estadoObjetivo);
        console.log("Servidor respondió con éxito:", respuesta.data);
        
        orden.estado = estadoObjetivo;
        alert(`¡Orden #${orden.nro_orden} marcada como lista para entrega!`);
      } catch (error) {
        console.error("Detalle completo del error en Axios:", error);
        alert("No se pudo guardar el cambio en la base de datos.");
      }
    },
    formatMecanico(emp) { return emp ? `${emp.primer_nombre} ${emp.apellido_paterno}` : 'Mecánico General'; },
    cleanClassName(est) {
      const e = est.toLowerCase();
      if (e === 'en proceso') return 'pill-proceso';
      if (e === 'listo para entrega') return 'pill-listo';
      if (e === 'esperando repuestos') return 'pill-repuestos';
      if (e === 'entregado') return 'pill-entregado';
      return 'pill-pendiente';
    },
    irAChecklist(orden) { this.ordenSeleccionada = orden; this.vistaActual = 'checklist'; },
    regresarAListado() { this.vistaActual = 'lista'; this.cargarOrdenes(); },
    actualizarEstadoOrdenLista(idOrden) {
      const idx = this.ordenes.findIndex(o => o.id_orden === idOrden);
      if (idx !== -1) this.ordenes[idx].estado = 'Listo para entrega';
    },
    cargarDatosDePrueba() {
      if (this.ordenes.length === 0) {
        this.ordenes = [
          { id_orden: 101, nro_orden: '101', fecha_ingreso: '2026-06-24', estado: 'En proceso', motocicleta: { placa: '7112-IKD' }, empleado: { primer_nombre: 'Andrés', apellido_paterno: 'Luna' }, condicion_entrada: 'Regular - Sucia / Desgaste normal' },
          { id_orden: 102, nro_orden: '102', fecha_ingreso: '2026-06-23', estado: 'Listo para entrega', motocicleta: { placa: '9921-XOW' }, empleado: { primer_nombre: 'Damián', apellido_paterno: 'Ortega' }, condicion_entrada: 'Buena - Sin daños visibles' },
          { id_orden: 103, nro_orden: '103', fecha_ingreso: '2026-06-22', estado: 'Pendiente', motocicleta: { placa: '4321-BBN' }, empleado: { primer_nombre: 'Carlos', apellido_paterno: 'Mendoza' }, condicion_entrada: 'Mala - Golpes / Rayaduras visibles' }
        ];
      }
    }
  }
};
</script>
<style scoped>
/* ==========================================================================
   ESTILOS GENERALES DEL CONTENEDOR
   ========================================================================== */
.taller-container { max-width: 1100px; margin: 0 auto; width: 100%; text-align: left; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.section-title { font-size: 22px; font-weight: 700; color: #042D29; margin: 0 0 4px 0; }
.section-subtitle { font-size: 14px; color: #929079; margin: 0; }

/* ==========================================================================
   TARJETA Y FILTROS
   ========================================================================== */
.novarider-card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04); border-top: 4px solid #631B21; overflow: hidden; margin-bottom: 40px; }
.card-filter-bar { padding: 20px 24px; border-bottom: 1px solid #F1F3F2; display: flex; gap: 16px; align-items: center; background: #FFFFFF; }
.search-box-wrapper { position: relative; flex: 1; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; }
.novarider-input { width: 100%; padding: 10px 10px 10px 42px; border: 1px solid #E2E4E3; border-radius: 8px; font-size: 14px; color: #042D29; outline: none; transition: border 0.2s; }
.novarider-input:focus, .novarider-select:focus { border-color: #929079; }
.select-wrapper { min-width: 180px; }
.novarider-select { width: 100%; padding: 10px 14px; border: 1px solid #E2E4E3; border-radius: 8px; font-size: 14px; color: #042D29; background-color: #FFFFFF; outline: none; cursor: pointer; }

/* ==========================================================================
   TABLA RESPONSIVA
   ========================================================================== */
.card-content-body { padding: 0; }
.table-responsive { width: 100%; overflow-x: auto; }
.novarider-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.novarider-table th { background: #FAFAFA; color: #55574A; font-weight: 600; padding: 14px 24px; border-bottom: 1px solid #F1F3F2; text-align: left; }
.novarider-table td { padding: 16px 24px; border-bottom: 1px solid #F1F3F2; color: #2F312A; vertical-align: middle; }
.novarider-table tr:hover td { background-color: #FAFBFB; }

/* Componentes internos de la tabla */
.plate-badge { background: #F1F3F2; color: #042D29; font-family: monospace; font-size: 13px; font-weight: 600; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E4E3; }
.status-pill { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-block; }
.pill-pendiente { background: #FFF4E5; color: #B26200; }
.pill-proceso { background: #E3F2FD; color: #0D47A1; }
.pill-repuestos { background: #EDE7F6; color: #4A148C; }
.pill-listo { background: #E8F5E9; color: #1B5E20; }
.pill-entregado { background: #ECEFF1; color: #37474F; }

/* ==========================================================================
   ACCIONES COHESIVAS Y ALINEADAS (FLEXBOX)
   ========================================================================== */
.actions-cell {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 6px;                  /* Espaciado idéntico entre botones */
  white-space: nowrap;       /* Evita que los botones salten de línea */
  padding: 12px 24px !important; 
}

.btn-table-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 6px 10px;         /* Tamaño compacto y uniforme */
  font-size: 13px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #E2E4E3;
  background-color: #FFFFFF;
  transition: all 0.2s ease-in-out;
  margin: 0;                 /* Reseteo de márgenes antiguos */
  height: 32px;              /* Altura idéntica para simetría visual */
}

/* Variaciones cromáticas para cada acción */
.btn-ready { color: #1B5E20; border-color: #A5D6A7; background-color: #E8F5E9; }
.btn-ready:hover { background-color: #C8E6C9; }

.btn-verify { color: #042D29; border-color: #B2C0BF; background-color: #F4F6F6; }
.btn-verify:hover { background-color: #E3E8E8; }

.btn-edit { color: #0D47A1; border-color: #90CAF9; background-color: #E3F2FD; }
.btn-edit:hover { background-color: #BBDEFB; }

.btn-delete { color: #B71C1C; border-color: #EF9A9A; background-color: #FFEBEE; }
.btn-delete:hover { background-color: #FFCDD2; }

/* Botón principal superior */
.btn-novarider-primary { background: #042D29; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
.btn-novarider-primary:hover { opacity: 0.9; }

/* ==========================================================================
   MODALES Y FORMULARIOS
   ========================================================================== */
.nr-modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(4, 45, 41, 0.4); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.nr-modal { background: #FFFFFF; border-radius: 12px; width: 90%; max-width: 500px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.nr-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #F1F3F2; padding-bottom: 12px; }
.nr-modal-header h3 { margin: 0; font-size: 16px; color: #042D29; font-weight: 700; }
.nr-btn-close { background: none; border: none; color: #929079; font-size: 16px; cursor: pointer; }
.nr-modal-body { display: flex; flex-direction: column; gap: 16px; }
.nr-form-group { display: flex; flex-direction: column; gap: 6px; }
.nr-form-group label { font-size: 12px; color: #55574A; font-weight: 600; }
.nr-form-group input { padding: 10px; border: 1px solid #E2E4E3; border-radius: 6px; font-size: 14px; outline: none; }
.nr-form-row { display: flex; gap: 12px; }
.flex-1 { flex: 1; } .w-full { width: 100%; }

.nr-modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; }
.btn-novarider-primary-submit { background: #042D29; color: white; border: none; padding: 10px 16px; font-weight: 600; border-radius: 6px; cursor: pointer; }
.nr-btn-link { background: none; border: none; color: #929079; cursor: pointer; font-size: 14px; }

.novarider-input-disabled {
  background-color: #F1F3F2;
  color: #55574A;
  cursor: not-allowed;
  padding: 10px;
  border: 1px solid #E2E4E3;
  border-radius: 6px;
  font-size: 14px;
  outline: none;
}

/* ==========================================================================
   UTILIDADES
   ========================================================================== */
.text-right { text-align: right; } 
.text-center { text-align: center; } 
.font-bold { font-weight: 600; } 
.text-dark { color: #042D29; } 
.text-muted { color: #929079; } 
.empty-row { padding: 40px !important; color: #929079; } 
.p-0 { padding: 0 !important; }
</style>
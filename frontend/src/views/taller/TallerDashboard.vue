<template>
  <div class="taller-container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Órdenes de Trabajo y Reparaciones</h2>
        <p class="section-subtitle">Gestión del estado de motocicletas y control técnico</p>
      </div>
      <div style="display: flex; gap: 10px;">
        <button @click="abrirModalServicios" class="btn-novarider-primary" style="background-color: #f39c12;">
          🛠️ + Servicio
        </button>
        <button @click="abrirModalNuevaOrden" class="btn-novarider-primary">
          + Registrar Nueva Orden
        </button>
      </div>
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
          class="btn-export-pdf" 
          title="Exportar listado actual a PDF"
        >
          <span class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="12" y1="18" x2="12" y2="12"></line>
              <polyline points="9 15 12 18 15 15"></polyline>
            </svg>
          </span>
          Exportar PDF
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
                  <select
                    :value="orden.estado"
                    class="estado-select"
                    title="Cambiar estado de la orden"
                    @change="cambiarEstadoOrden(orden, $event.target.value)"
                  >
                    <option v-for="estado in estadosOrden" :key="estado" :value="estado">
                      {{ estado }}
                    </option>
                  </select>

                  <button 
                    @click="irAChecklist(orden)" 
                    class="btn-table-action btn-verify" 
                    :disabled="orden.validado == 1" 
                    :title="orden.validado == 1 ? 'Validación ya realizada' : 'Validar Calidad'"
                  >
                    {{ orden.validado == 1 ? '✅ Validado' : '📋 Validar' }}
                  </button>

                  <button @click="eliminarOrdenTrabajo(orden.id_orden)" class="btn-table-action btn-delete" title="Eliminar Orden">
                    🗑️ Eliminar
                  </button>

                  <button @click="abrirModalServicioOrden(orden)" class="btn-table-action btn-service" title="Agregar Servicio">
                    Servicio
                  </button>

                  <button @click="abrirModalRepuestoOrden(orden)" class="btn-table-action btn-part" title="Agregar repuesto">
                    Repuesto
                  </button>

                  <button @click="abrirQrOrden(orden)" class="btn-table-action btn-qr" title="Ver QR de la orden">
                    QR
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
          @ordenValidada="manejarValidacionExitosa" 
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
          
          <div class="nr-form-group search-autocomplete">
            <label>Matrícula / Placa del Vehículo</label>
            <input 
              type="text" 
              v-model="busquedaPlaca" 
              placeholder="Escriba la placa..." 
              @focus="mostrarSugerencias = true"
              @blur="ocultarSugerenciasConDelay"
              class="novarider-input"
              required
            >
            <ul v-if="mostrarSugerencias && motosFiltradas.length > 0" class="suggestions-list">
              <li 
                v-for="moto in motosFiltradas" 
                :key="moto.id_motocicleta" 
                @click="seleccionarMoto(moto)"
              >
                {{ moto.placa }} - {{ moto.modelo || 'Sin Modelo' }}
              </li>
            </ul>
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

    <div v-if="showModalServicios" class="nr-modal-overlay" @click="showModalServicios = false">
      <div class="nr-modal" @click.stop style="max-width: 600px;">
        <div class="nr-modal-header">
          <h3>Catálogo de Servicios</h3>
          <button @click="showModalServicios = false" class="nr-btn-close">✕</button>
        </div>
        <div class="nr-modal-body">
          <form @submit.prevent="guardarNuevoServicio" class="service-create-form">
            <div class="nr-form-row">
              <div class="nr-form-group flex-1">
                <label>Nombre del servicio</label>
                <input type="text" v-model.trim="nuevoServicio.nombre" required class="novarider-input form-input-plain" placeholder="Ej. Cambio de aceite">
              </div>
              <div class="nr-form-group service-price-field">
                <label>Precio estimado</label>
                <input type="number" min="0" step="0.01" v-model.number="nuevoServicio.precio_estimado" required class="novarider-input form-input-plain">
              </div>
            </div>
            <div class="nr-form-group">
              <label>Descripcion</label>
              <input type="text" v-model.trim="nuevoServicio.descripcion" class="novarider-input form-input-plain" placeholder="Detalle del servicio">
            </div>
            <div class="service-form-actions">
              <button type="submit" class="btn-novarider-primary-submit">
                Guardar Servicio
              </button>
            </div>
          </form>

          <table class="novarider-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio Est.</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="servicio in serviciosDisponibles" :key="servicio.id_servicio">
                <td>{{ servicio.nombre }}</td>
                <td>{{ servicio.descripcion }}</td>
                <td class="font-bold">Bs {{ servicio.precio_estimado }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="mostrarModalServicioOrden" class="nr-modal-overlay" @click="cerrarModalServicioOrden">
      <div class="nr-modal" @click.stop>
        <div class="nr-modal-header">
          <h3>Agregar servicio a orden #{{ ordenServicioActiva ? ordenServicioActiva.nro_orden : '' }}</h3>
          <button @click="cerrarModalServicioOrden" class="nr-btn-close">x</button>
        </div>

        <form @submit.prevent="guardarServicioOrden" class="nr-modal-body">
          <div class="nr-form-group">
            <label>Servicio</label>
            <select v-model="detalleServicio.id_servicio" required class="novarider-select">
              <option value="" disabled>Seleccione un servicio</option>
              <option v-for="servicio in serviciosDisponibles" :key="servicio.id_servicio" :value="servicio.id_servicio">
                {{ servicio.nombre }} - Bs {{ servicio.precio_estimado }}
              </option>
            </select>
          </div>

          <div class="nr-form-row">
            <div class="nr-form-group flex-1">
              <label>Cantidad</label>
              <input
                type="number"
                min="1"
                max="999"
                v-model.number="detalleServicio.cantidad"
                @input="limitarCantidadServicio"
                required
                class="novarider-input form-input-plain"
              >
            </div>
            <div class="nr-form-group flex-1">
              <label>Precio</label>
              <input type="text" :value="`Bs ${precioServicioSeleccionado}`" readonly class="novarider-input-disabled">
            </div>
          </div>

          <div class="nr-form-group">
            <label>Descripcion</label>
            <input type="text" v-model="detalleServicio.descripcion" class="novarider-input form-input-plain" placeholder="Detalle adicional del trabajo">
          </div>

          <div class="service-summary">
            <span>Subtotal</span>
            <strong>Bs {{ subtotalServicio }}</strong>
          </div>

          <div class="nr-modal-footer">
            <button type="button" @click="cerrarModalServicioOrden" class="nr-btn-link">Cancelar</button>
            <button type="submit" class="btn-novarider-primary-submit">
              Guardar Servicio
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="mostrarModalRepuestoOrden" class="nr-modal-overlay" @click="cerrarModalRepuestoOrden">
      <div class="nr-modal" @click.stop>
        <div class="nr-modal-header">
          <h3>Agregar repuesto a orden #{{ ordenRepuestoActiva ? ordenRepuestoActiva.nro_orden : '' }}</h3>
          <button @click="cerrarModalRepuestoOrden" class="nr-btn-close">x</button>
        </div>

        <form @submit.prevent="guardarRepuestoOrden" class="nr-modal-body">
          <div class="nr-form-group">
            <label>Repuesto</label>
            <select v-model.number="detalleRepuesto.id_producto" required class="novarider-select">
              <option value="" disabled>Seleccione un repuesto</option>
              <option v-for="repuesto in repuestosDisponibles" :key="repuesto.id_producto" :value="repuesto.id_producto">
                {{ repuesto.nombre }} - Stock {{ repuesto.stock_disponible }}
              </option>
            </select>
          </div>

          <div class="nr-form-row">
            <div class="nr-form-group flex-1">
              <label>Cantidad</label>
              <input
                type="number"
                min="1"
                :max="stockRepuestoSeleccionado || 1"
                v-model.number="detalleRepuesto.cantidad"
                required
                class="novarider-input form-input-plain"
              >
            </div>
            <div class="nr-form-group flex-1">
              <label>Costo unitario</label>
              <input type="text" :value="`Bs ${costoRepuestoSeleccionado.toFixed(2)}`" readonly class="novarider-input-disabled">
            </div>
          </div>

          <div class="nr-form-group">
            <label>Descripcion</label>
            <input type="text" v-model="detalleRepuesto.descripcion" class="novarider-input form-input-plain" placeholder="Detalle de uso del repuesto">
          </div>

          <div class="service-summary">
            <span>Subtotal repuestos</span>
            <strong>Bs {{ subtotalRepuesto.toFixed(2) }}</strong>
          </div>

          <div v-if="ordenRepuestoActiva && ordenRepuestoActiva.repuestos && ordenRepuestoActiva.repuestos.length" class="repuestos-desglose">
            <h4>Repuestos ya registrados</h4>
            <div v-for="repuesto in ordenRepuestoActiva.repuestos" :key="repuesto.id_detalle_ot" class="repuesto-row">
              <span>{{ repuesto.producto }}</span>
              <strong>{{ repuesto.cantidad }} x Bs {{ Number(repuesto.costo_unitario || 0).toFixed(2) }}</strong>
            </div>
          </div>

          <div class="nr-modal-footer">
            <button type="button" @click="cerrarModalRepuestoOrden" class="nr-btn-link">Cancelar</button>
            <button type="submit" class="btn-novarider-primary-submit">
              Guardar Repuesto
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="mostrarModalQr" class="nr-modal-overlay" @click="cerrarModalQr">
      <div class="nr-modal qr-modal" @click.stop>
        <div class="nr-modal-header">
          <h3>QR de orden #{{ ordenQrActiva ? ordenQrActiva.nro_orden : '' }}</h3>
          <button @click="cerrarModalQr" class="nr-btn-close">x</button>
        </div>
        <div class="qr-modal-body">
          <div class="recibo-seguimiento" v-if="ordenQrActiva">
            <span class="recibo-label">Codigo de seguimiento</span>
            <strong>{{ ordenQrActiva.codigo_seguimiento }}</strong>
            <small>Orden #{{ ordenQrActiva.nro_orden }}</small>
            <img :src="qrOrdenUrl" alt="QR de seguimiento de orden" class="qr-image">
            <div class="qr-info">
              <span>{{ ordenQrActiva.motocicleta ? ordenQrActiva.motocicleta.placa : ordenQrActiva.placa_simulada || 'SIN PLACA' }}</span>
              <span>{{ ordenQrActiva.estado }}</span>
              <span class="consulta-url">{{ seguimientoUrl(ordenQrActiva) }}</span>
            </div>
          </div>
          <button type="button" class="btn-novarider-primary-submit no-print" @click="imprimirReciboSeguimiento">
            Imprimir recibo
          </button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="mensajeToast" class="nr-toast">
        <div class="toast-content">
          <span class="toast-icon">✅</span>
          <p>{{ textoToast || '¡Orden lista para entrega!' }}</p>
        </div>
      </div>
    </transition>
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
      mensajeToast: false,
      textoToast: '', 
      busquedaPlaca: '',
      mostrarSugerencias: false,
      showModalServicios: false,
      serviciosDisponibles: [], 
      estadosOrden: ['Pendiente', 'En proceso', 'Esperando repuestos', 'Listo para entrega', 'Entregado'],
      nuevoServicio: {
        nombre: '',
        descripcion: '',
        precio_estimado: 0
      },
      mostrarModalServicioOrden: false,
      ordenServicioActiva: null,
      mostrarModalRepuestoOrden: false,
      ordenRepuestoActiva: null,
      mostrarModalQr: false,
      ordenQrActiva: null,
      repuestosDisponibles: [],
      detalleServicio: {
        id_servicio: '',
        cantidad: 1,
        descripcion: ''
      },
      detalleRepuesto: {
        id_producto: '',
        cantidad: 1,
        descripcion: ''
      },
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
    },
    motosFiltradas() {
      if (!this.busquedaPlaca) return this.motocicletasBD;
      return this.motocicletasBD.filter(m => 
        m.placa.toLowerCase().includes(this.busquedaPlaca.toLowerCase())
      );
    },
    servicioSeleccionado() {
      return this.serviciosDisponibles.find(servicio => servicio.id_servicio === this.detalleServicio.id_servicio) || null;
    },
    precioServicioSeleccionado() {
      return Number(this.servicioSeleccionado?.precio_estimado || 0);
    },
    subtotalServicio() {
      const cantidad = Number(this.detalleServicio.cantidad || 0);
      return this.precioServicioSeleccionado * cantidad;
    },
    repuestoSeleccionado() {
      return this.repuestosDisponibles.find(repuesto => repuesto.id_producto === this.detalleRepuesto.id_producto) || null;
    },
    costoRepuestoSeleccionado() {
      return Number(this.repuestoSeleccionado?.costo || this.repuestoSeleccionado?.precio_venta || 0);
    },
    stockRepuestoSeleccionado() {
      return Number(this.repuestoSeleccionado?.stock_disponible || 0);
    },
    subtotalRepuesto() {
      return this.costoRepuestoSeleccionado * Number(this.detalleRepuesto.cantidad || 0);
    },
    qrOrdenUrl() {
      if (!this.ordenQrActiva) return '';
      return `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(this.qrOrdenContenido(this.ordenQrActiva))}`;
    }
  },
  mounted() { 
    this.cargarOrdenes(); 
    this.cargarAuxiliaresBD();
  },
  methods: {
    async abrirModalServicios() {
      try {
        const res = await tallerService.obtenerServicios();
        this.serviciosDisponibles = res.data;
        this.showModalServicios = true;
      } catch (e) {
        console.error("Error al cargar servicios:", e);
        alert("No se pudieron cargar los servicios.");
      }
    },

    async guardarNuevoServicio() {
      try {
        const res = await tallerService.crearServicio(this.nuevoServicio);
        const servicioGuardado = res.data.servicio;

        this.serviciosDisponibles = [
          ...this.serviciosDisponibles.filter(servicio => servicio.id_servicio !== servicioGuardado.id_servicio),
          servicioGuardado
        ].sort((a, b) => a.nombre.localeCompare(b.nombre));
        this.nuevoServicio = {
          nombre: '',
          descripcion: '',
          precio_estimado: 0
        };
        this.mostrarNotificacion('Servicio registrado correctamente.');
      } catch (e) {
        console.error("Error al guardar servicio:", e);
        alert(e.response?.data?.message || "No se pudo registrar el servicio.");
      }
    },
    
    async abrirModalServicioOrden(orden) {
      try {
        if (this.serviciosDisponibles.length === 0) {
          const res = await tallerService.obtenerServicios();
          this.serviciosDisponibles = res.data;
        }

        this.ordenServicioActiva = orden;
        this.detalleServicio = {
          id_servicio: '',
          cantidad: 1,
          descripcion: ''
        };
        this.mostrarModalServicioOrden = true;
      } catch (e) {
        console.error("Error al cargar servicios:", e);
        alert("No se pudieron cargar los servicios.");
      }
    },

    cerrarModalServicioOrden() {
      this.mostrarModalServicioOrden = false;
      this.ordenServicioActiva = null;
    },

    async abrirModalRepuestoOrden(orden) {
      try {
        if (this.repuestosDisponibles.length === 0) {
          const res = await tallerService.obtenerRepuestosDisponibles();
          this.repuestosDisponibles = res.data.repuestos || [];
        }

        this.ordenRepuestoActiva = orden;
        this.detalleRepuesto = {
          id_producto: '',
          cantidad: 1,
          descripcion: ''
        };
        this.mostrarModalRepuestoOrden = true;
      } catch (e) {
        console.error("Error al cargar repuestos:", e);
        alert("No se pudieron cargar los repuestos.");
      }
    },

    cerrarModalRepuestoOrden() {
      this.mostrarModalRepuestoOrden = false;
      this.ordenRepuestoActiva = null;
    },

    abrirQrOrden(orden) {
      this.ordenQrActiva = orden;
      this.mostrarModalQr = true;
    },

    cerrarModalQr() {
      this.mostrarModalQr = false;
      this.ordenQrActiva = null;
    },

    qrOrdenContenido(orden) {
      return this.seguimientoUrl(orden);
    },

    seguimientoUrl(orden) {
      return `${window.location.origin}/seguimiento/${orden.codigo_seguimiento}`;
    },

    imprimirReciboSeguimiento() {
      window.print();
    },

    async guardarServicioOrden() {
      if (!this.ordenServicioActiva) return;
      try {
        await tallerService.guardarServicioOrden(this.ordenServicioActiva.id_orden, this.detalleServicio);
        this.cerrarModalServicioOrden();
        this.mostrarNotificacion('Servicio guardado en la orden.');
      } catch (e) {
        console.error("Error al guardar servicio:", e);
        alert("No se pudo guardar el servicio en la orden.");
      }
    },

    async guardarRepuestoOrden() {
      if (!this.ordenRepuestoActiva) return;
      try {
        const res = await tallerService.guardarRepuestoOrden(this.ordenRepuestoActiva.id_orden, this.detalleRepuesto);
        const detalle = res.data.detalle;
        const producto = detalle.producto;

        if (!this.ordenRepuestoActiva.repuestos) this.ordenRepuestoActiva.repuestos = [];
        this.ordenRepuestoActiva.repuestos.push({
          id_detalle_ot: detalle.id_detalle_ot,
          producto: producto?.nombre || this.repuestoSeleccionado?.nombre || 'Repuesto',
          cantidad: detalle.cantidad,
          costo_unitario: detalle.costo_unitario,
          subtotal: detalle.subtotal
        });
        this.repuestosDisponibles = this.repuestosDisponibles
          .map(repuesto => repuesto.id_producto === this.detalleRepuesto.id_producto
            ? { ...repuesto, stock_disponible: repuesto.stock_disponible - this.detalleRepuesto.cantidad }
            : repuesto)
          .filter(repuesto => repuesto.stock_disponible > 0);
        this.cerrarModalRepuestoOrden();
        this.mostrarNotificacion('Repuesto guardado en la orden.');
      } catch (e) {
        console.error("Error al guardar repuesto:", e);
        alert(e.response?.data?.message || "No se pudo guardar el repuesto en la orden.");
      }
    },

    mostrarNotificacion(mensaje = '¡Acción completada con éxito!') {
      this.textoToast = mensaje;
      this.mensajeToast = true;
      setTimeout(() => {
        this.mensajeToast = false;
      }, 3000);
    },

    manejarValidacionExitosa(payload) {
      const orden = this.ordenes.find(o => o.id_orden === payload.idOrden);
      if (orden) {
        orden.validado = 1; 
        orden.estado = 'Listo para entrega';
      }
      this.mostrarNotificacion(payload.mensaje);
      this.vistaActual = 'lista';
    },

    async marcarComoListo(orden) {
      const estadoObjetivo = 'Listo para entrega';
      try {
        await tallerService.cambiarEstadoOrden(orden.id_orden, estadoObjetivo);
        orden.estado = estadoObjetivo;
        this.mostrarNotificacion('¡Orden lista para entrega!');
      } catch (error) {
        console.error("Error:", error);
      }
    },

    async cambiarEstadoOrden(orden, estado) {
      const estadoAnterior = orden.estado;
      orden.estado = estado;

      try {
        const res = await tallerService.cambiarEstadoOrden(orden.id_orden, estado);
        orden.estado = res.data.orden?.estado || estado;
        this.mostrarNotificacion(`Estado actualizado: ${orden.estado}`);
      } catch (error) {
        orden.estado = estadoAnterior;
        console.error("Error al cambiar estado:", error);
        alert(error.response?.data?.message || "No se pudo cambiar el estado.");
      }
    },

    seleccionarMoto(moto) {
      this.nuevaOrden.id_motocicleta = moto.id_motocicleta;
      this.busquedaPlaca = moto.placa;
      this.mostrarSugerencias = false;
    },
    ocultarSugerenciasConDelay() {
      setTimeout(() => { this.mostrarSugerencias = false; }, 200);
    },
    async cargarOrdenes() {
      try {
        const res = await tallerService.obtenerOrdenes();
        let data = res.data.ordenes || res.data;
        
        this.ordenes = data.map(orden => ({
          ...orden,
          validado: orden.validado || 0
        }));
      } catch (e) { 
        console.error("Error cargando órdenes:", e);
      }
    },
    async cargarAuxiliaresBD() {
      try {
        const resMecanicos = await tallerService.obtenerMecanicos();
        this.mecanicosBD = resMecanicos.data;
        const resMotos = await tallerService.obtenerMotocicletas();
        this.motocicletasBD = resMotos.data;
      } catch (e) {
        console.error("Error cargando auxiliares:", e);
      }
    },
    imprimirReportePdf() {
      window.open(`http://localhost:8000/ordenes/reporte/pdf?busqueda=${this.filtroTexto}&estado=${this.estadoFiltro}`, '_blank');
    },
    async abrirModalNuevaOrden() {
      await this.cargarAuxiliaresBD();
      this.busquedaPlaca = '';
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
      try {
        await tallerService.crearOrden(this.nuevaOrden);
        this.mostrarModalOrden = false;
        this.cargarOrdenes(); 
      } catch (e) {
        console.error("Error al guardar:", e);
      }
    },
    async eliminarOrdenTrabajo(idOrden) {
      if (confirm("¿Está seguro de eliminar esta orden?")) {
        try {
          await tallerService.eliminarOrden(idOrden);
          this.cargarOrdenes();
        } catch (e) {
          console.error("Error al borrar:", e);
        }
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
    regresarAListado() { this.vistaActual = 'lista'; },
    actualizarEstadoOrdenLista(idOrden) {
      const idx = this.ordenes.findIndex(o => o.id_orden === idOrden);
      if (idx !== -1) this.ordenes[idx].estado = 'Listo para entrega';
    },
    limitarCantidadServicio() {
      if (this.detalleServicio.cantidad < 1) this.detalleServicio.cantidad = 1;
      if (this.detalleServicio.cantidad > 999) this.detalleServicio.cantidad = 999;
    }
  }
};
</script>

<style scoped>
.taller-container { max-width: 1100px; margin: 0 auto; width: 100%; text-align: left; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.section-title { font-size: 22px; font-weight: 700; color: #042D29; margin: 0 0 4px 0; }
.section-subtitle { font-size: 14px; color: #929079; margin: 0; }
.novarider-card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04); border-top: 4px solid #631B21; overflow: hidden; margin-bottom: 40px; }
.card-filter-bar { padding: 20px 24px; border-bottom: 1px solid #F1F3F2; display: flex; gap: 16px; align-items: center; background: #FFFFFF; }
.search-box-wrapper { position: relative; flex: 1; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; }
.novarider-input { width: 100%; padding: 10px 10px 10px 42px; border: 1px solid #E2E4E3; border-radius: 8px; font-size: 14px; color: #042D29; outline: none; }
.select-wrapper { min-width: 180px; }
.novarider-select { width: 100%; padding: 10px 14px; border: 1px solid #E2E4E3; border-radius: 8px; font-size: 14px; color: #042D29; background-color: #FFFFFF; outline: none; cursor: pointer; }
.card-content-body { padding: 0; }
.table-responsive { width: 100%; overflow-x: auto; }
.novarider-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.novarider-table th { background: #FAFAFA; color: #55574A; font-weight: 600; padding: 14px 24px; border-bottom: 1px solid #F1F3F2; text-align: left; }
.novarider-table td { padding: 16px 24px; border-bottom: 1px solid #F1F3F2; color: #2F312A; vertical-align: middle; }
.plate-badge { background: #F1F3F2; color: #042D29; font-family: monospace; font-size: 13px; font-weight: 600; padding: 4px 8px; border-radius: 4px; border: 1px solid #E2E4E3; }
.status-pill { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-block; }
.pill-pendiente { background: #FFF4E5; color: #B26200; }
.pill-proceso { background: #E3F2FD; color: #0D47A1; }
.pill-repuestos { background: #EDE7F6; color: #4A148C; }
.pill-listo { background: #E8F5E9; color: #1B5E20; }
.pill-entregado { background: #ECEFF1; color: #37474F; }
.actions-cell { display: flex; justify-content: flex-end; align-items: center; gap: 6px; white-space: nowrap; padding: 12px 24px !important; }
.estado-select { height: 32px; max-width: 150px; border: 1px solid #E2E4E3; border-radius: 6px; background: #FFFFFF; color: #042D29; font-size: 12px; font-weight: 700; padding: 0 8px; }
.btn-table-action { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 10px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: 1px solid #E2E4E3; background-color: #FFFFFF; height: 32px; }
.btn-ready { color: #1B5E20; border-color: #A5D6A7; background-color: #E8F5E9; }
.btn-verify { color: #042D29; border-color: #B2C0BF; background-color: #F4F6F6; }
.btn-delete { color: #B71C1C; border-color: #EF9A9A; background-color: #FFEBEE; }
.btn-service { color: #6F3F00; border-color: #F5C16C; background-color: #FFF6E5; }
.btn-part { color: #4A148C; border-color: #D3B8EA; background-color: #F7F0FF; }
.btn-qr { color: #1E3A78; border-color: #B8C7EA; background-color: #F3F6FF; }
.btn-table-action:disabled { opacity: 0.62; cursor: not-allowed; }
.btn-novarider-primary { background: #042D29; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
.nr-modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(4, 45, 41, 0.4); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.nr-modal { background: #FFFFFF; border-radius: 12px; width: 90%; max-width: 500px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.nr-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #F1F3F2; padding-bottom: 12px; }
.nr-modal-header h3 { margin: 0; font-size: 16px; color: #042D29; font-weight: 700; }
.nr-btn-close { background: none; border: none; color: #929079; font-size: 16px; cursor: pointer; }
.nr-modal-body { display: flex; flex-direction: column; gap: 16px; }
.nr-form-group { display: flex; flex-direction: column; gap: 6px; }
.nr-form-group label { font-size: 12px; color: #55574A; font-weight: 600; }
.nr-form-row { display: flex; gap: 12px; }
.flex-1 { flex: 1; }
.nr-modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; }
.btn-novarider-primary-submit { background: #042D29; color: white; border: none; padding: 10px 16px; font-weight: 600; border-radius: 6px; cursor: pointer; }
.nr-btn-link { background: none; border: none; color: #929079; cursor: pointer; font-size: 14px; }
.novarider-input-disabled { background-color: #F1F3F2; color: #55574A; cursor: not-allowed; padding: 10px; border: 1px solid #E2E4E3; border-radius: 6px; font-size: 14px; }
.form-input-plain { padding-left: 10px; box-sizing: border-box; }
.service-summary { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border: 1px solid #E2E4E3; border-radius: 8px; background: #FAFAFA; color: #042D29; }
.service-summary span { font-size: 13px; font-weight: 600; color: #55574A; }
.service-summary strong { font-size: 18px; }
.repuestos-desglose { display: grid; gap: 8px; padding: 12px; border: 1px solid #E2E4E3; border-radius: 8px; background: #FAFAFA; }
.repuestos-desglose h4 { margin: 0; color: #042D29; font-size: 13px; }
.repuesto-row { display: flex; justify-content: space-between; gap: 12px; color: #55574A; font-size: 12px; }
.repuesto-row strong { color: #042D29; white-space: nowrap; }
.service-create-form { display: flex; flex-direction: column; gap: 12px; padding: 14px; border: 1px solid #E2E4E3; border-radius: 8px; background: #FAFAFA; }
.service-price-field { width: 150px; flex-shrink: 0; }
.service-form-actions { display: flex; justify-content: flex-end; }
.qr-modal { max-width: 390px; }
.qr-modal-body { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.recibo-seguimiento { width: 100%; display: flex; flex-direction: column; align-items: center; gap: 8px; border: 1px dashed #B2C0BF; border-radius: 10px; padding: 18px; text-align: center; }
.recibo-label { font-size: 12px; font-weight: 700; color: #929079; text-transform: uppercase; }
.recibo-seguimiento strong { color: #042D29; font-size: 28px; letter-spacing: 1px; }
.recibo-seguimiento small { color: #55574A; font-weight: 700; }
.qr-image { width: 220px; height: 220px; border: 1px solid #E2E4E3; border-radius: 8px; padding: 10px; background: #FFFFFF; }
.qr-info { display: flex; flex-direction: column; align-items: center; gap: 4px; color: #042D29; font-size: 13px; }
.qr-info strong { font-size: 16px; }
.consulta-url { max-width: 280px; overflow-wrap: anywhere; color: #55574A; font-size: 11px; }
.search-autocomplete { position: relative; }
.suggestions-list { position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #E2E4E3; border-radius: 6px; list-style: none; max-height: 150px; overflow-y: auto; z-index: 1000; margin: 4px 0 0 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.suggestions-list li { padding: 10px; cursor: pointer; font-size: 13px; }
.suggestions-list li:hover { background: #F1F3F2; color: #042D29; }
.nr-toast { position: fixed; top: 20%; left: 50%; transform: translateX(-50%); background-color: rgba(4, 45, 41, 0.95); color: #FFFFFF; padding: 18px 35px; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 20000; border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); display: flex; align-items: center; gap: 15px; animation: slideDown 0.4s ease-out; }
.toast-content { display: flex; align-items: center; gap: 12px; }
.toast-icon { font-size: 24px; }
.toast-content p { margin: 0; font-weight: 700; font-size: 16px; white-space: nowrap; }
@keyframes slideDown { from { opacity: 0; transform: translate(-50%, -30px); } to { opacity: 1; transform: translate(-50%, 0); } }
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s, transform 0.4s; }
.fade-enter, .fade-leave-to { opacity: 0; transform: translate(-50%, -30px); }

@media print {
  body * {
    visibility: hidden;
  }

  .recibo-seguimiento,
  .recibo-seguimiento * {
    visibility: visible;
  }

  .recibo-seguimiento {
    position: fixed;
    top: 24px;
    left: 24px;
    width: 320px;
    border: 1px solid #042D29;
    box-shadow: none;
  }

  .no-print {
    display: none !important;
  }
}
</style>

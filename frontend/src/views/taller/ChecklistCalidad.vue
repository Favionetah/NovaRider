<template>
  <div class="corp-checklist-card">
    <div class="panel-header">
      <div>
        <h3>SISTEMA DE VERIFICACIÓN TÉCNICA (HU-12)</h3>
        <p class="panel-subtitle">Todos los componentes deben validarse de forma obligatoria para dar de alta el vehículo.</p>
      </div>
      <div class="panel-tag">ORDEN ACTIVA: <strong>#{{ ordenActiva.nro_orden }}</strong></div>
    </div>

    <form @submit.prevent="enviarFormulario" class="panel-body">
      <div class="switch-list">
        <div class="switch-item" :class="{ checked: checklist.frenos_revisados }">
          <div class="item-text">
            <h4>[01] VERIFICACIÓN DE FRENOS</h4>
            <p>Control de pastillas, presión hidráulica y discos (según solicitud).</p>
          </div>
          <label class="corp-switch">
            <input type="checkbox" v-model="checklist.frenos_revisados">
            <span class="slider"></span>
          </label>
        </div>

        <div class="switch-item" :class="{ checked: checklist.luces_revisadas }">
          <div class="item-text">
            <h4>[02] COMPONENTES ELÉCTRICOS E INSTALACIÓN</h4>
            <p>Piezas instaladas correctamente, ópticas y fusibles testeados.</p>
          </div>
          <label class="corp-switch">
            <input type="checkbox" v-model="checklist.luces_revisadas">
            <span class="slider"></span>
          </label>
        </div>

        <div class="switch-item" :class="{ checked: checklist.piezas_ajustadas }">
          <div class="item-text">
            <h4>[03] PRUEBAS DE FUNCIONAMIENTO</h4>
            <p>Torque estructural y encendido general completado con éxito.</p>
          </div>
          <label class="corp-switch">
            <input type="checkbox" v-model="checklist.piezas_ajustadas">
            <span class="slider"></span>
          </label>
        </div>

        <div class="switch-item" :class="{ checked: checklist.prueba_ruta }">
          <div class="item-text">
            <h4>[04] EVALUACIÓN DINÁMICA EN MOVIMIENTO</h4>
            <p>Test drive de seguridad completado sin anomalías.</p>
          </div>
          <label class="corp-switch">
            <input type="checkbox" v-model="checklist.prueba_ruta">
            <span class="slider"></span>
          </label>
        </div>
      </div>

      <div class="technical-inputs">
        <div class="form-group flex-1">
          <label>KILOMETRAJE DE CONTROL</label>
          <input type="number" v-model.number="checklist.kilometraje" required class="corp-field">
        </div>
        <div class="form-group flex-1">
          <label>FECHA DE REGISTRO</label>
          <input type="date" v-model="checklist.fecha_validacion" required class="corp-field">
        </div>
      </div>

      <div class="panel-footer">
        <button type="button" @click="$emit('volver')" class="btn-cancel-checklist">
          ← REGRESAR
        </button>
        <button type="submit" class="btn-submit-checklist">
          CONCLUIR Y CERTIFICAR CALIDAD
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import tallerService from '../../services/tallerService';

export default {
  props: ['ordenActiva'],
  data() {
    return {
      checklist: {
        id_orden: this.ordenActiva.id_orden,
        frenos_revisados: false,
        luces_revisadas: false,
        piezas_ajustadas: false,
        prueba_ruta: false,
        kilometraje: 0,
        fecha_validacion: new Date().toISOString().substring(0, 10)
      }
    };
  },
  mounted() { this.cargarDatos(); },
  methods: {
    async cargarDatos() {
      try {
        const res = await tallerService.obtenerChecklist(this.ordenActiva.id_orden);
        if (res.data) this.checklist = res.data;
      } catch (e) { console.log("Checklist vacío localmente."); }
    },
    async enviarFormulario() {
  try {
    // 1. Llamamos a tu servicio recién configurado
    await tallerService.guardarChecklist(this.checklist);
    
    // 2. Si todo sale bien, notificamos al padre
    this.$emit('ordenActualizada', this.ordenActiva.id_orden);
    alert('✅ Certificación de calidad registrada con éxito.');
    
    // 3. Volvemos al listado
    this.$emit('volver');
  } catch (e) {
    // 4. Si hay error, lo mostramos en consola para depurar
    console.error("Error al guardar en el servidor:", e.response ? e.response.data : e);
    alert('❌ Error al guardar la certificación. Revisa la consola para más detalles.');
  }
}
  }
};
</script>

<style scoped>
.corp-checklist-card { background: #FFFFFF; border: 1px solid #e2e8f0; border-radius: 6px; padding: 24px; text-align: left; }
.panel-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 24px; }
.panel-header h3 { margin: 0; font-size: 16px; color: #1E3A78; font-weight: 700; }
.panel-subtitle { margin: 4px 0 0 0; font-size: 11px; color: #64748b; }
.panel-tag { background: #F5F7FB; color: #2C4A9A; font-size: 12px; padding: 6px 12px; border-radius: 4px; font-weight: 700; }
.switch-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
.switch-item { background: #F5F7FB; border: 1px solid #e2e8f0; padding: 16px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
.switch-item.checked { background: rgba(33, 150, 243, 0.05); border-color: #2196F3; }
.item-text h4 { margin: 0 0 4px 0; font-size: 13px; color: #1E3A78; font-weight: 700; }
.item-text p { margin: 0; font-size: 11px; color: #64748b; }
.corp-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
.corp-switch input { display: none; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .2s; border-radius: 24px; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; }
input:checked + .slider { background-color: #2196F3; }
input:checked + .slider:before { transform: translateX(20px); }
.technical-inputs { display: flex; gap: 16px; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-bottom: 24px;}
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 11px; color: #64748b; font-weight: 700; }
.corp-field { background: #FFFFFF; color: #334155; border: 1px solid #cbd5e1; padding: 10px; border-radius: 4px; font-size: 13px; outline: none; }
.flex-1 { flex: 1; }
.panel-footer { display: flex; justify-content: space-between; align-items: center; }
.btn-cancel-checklist { background: none; border: none; color: #64748b; cursor: pointer; font-weight: 700; font-size: 13px;}
.btn-submit-checklist { background: #2C4A9A; color: white; border: none; padding: 12px 24px; font-weight: 700; font-size: 13px; border-radius: 4px; cursor: pointer; }
</style>
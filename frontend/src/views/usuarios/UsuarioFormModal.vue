<script setup>
import { ref, computed } from 'vue'
import { useUsuariosStore } from '@/stores/usuarios'

const props = defineProps({
  usuario: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['cerrar'])

const store = useUsuariosStore()
const esEdicion = computed(() => !!props.usuario)
const enviando = ref(false)
const mensajeError = ref('')

const form = ref({
  ci: props.usuario?.ci || '',
  primer_nombre: props.usuario?.primer_nombre || '',
  segundo_nombre: props.usuario?.segundo_nombre || '',
  apellido_paterno: props.usuario?.apellido_paterno || '',
  apellido_materno: props.usuario?.apellido_materno || '',
  fecha_nacimiento: props.usuario?.fecha_nacimiento || '',
  telefono: props.usuario?.telefono || '',
  cargo: props.usuario?.cargo || '',
  username: props.usuario?.username || '',
  password: '',
  id_rol: props.usuario?.id_rol || '',
})

async function guardar() {
  mensajeError.value = ''
  enviando.value = true

  try {
    if (esEdicion.value) {
      const data = { ...form.value }
      if (!data.password) delete data.password
      await store.actualizar(props.usuario.id_usuario, data)
    } else {
      await store.crear(form.value)
    }
    emit('cerrar')
  } catch (err) {
    mensajeError.value = err.response?.data?.message || 'Error al guardar usuario'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('cerrar')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>{{ esEdicion ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
        <button class="btn-cerrar" @click="emit('cerrar')">&times;</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <p v-if="mensajeError" class="mensaje-error">{{ mensajeError }}</p>

        <fieldset>
          <legend>Datos Personales</legend>
          <div class="form-grid">
            <div class="campo">
              <label for="ci">C&eacute;dula de Identidad</label>
              <input id="ci" v-model="form.ci" type="text" required />
            </div>
            <div class="campo">
              <label for="primer_nombre">Primer Nombre</label>
              <input id="primer_nombre" v-model="form.primer_nombre" type="text" required />
            </div>
            <div class="campo">
              <label for="segundo_nombre">Segundo Nombre</label>
              <input id="segundo_nombre" v-model="form.segundo_nombre" type="text" />
            </div>
            <div class="campo">
              <label for="apellido_paterno">Apellido Paterno</label>
              <input id="apellido_paterno" v-model="form.apellido_paterno" type="text" required />
            </div>
            <div class="campo">
              <label for="apellido_materno">Apellido Materno</label>
              <input id="apellido_materno" v-model="form.apellido_materno" type="text" />
            </div>
            <div class="campo">
              <label for="fecha_nacimiento">Fecha de Nacimiento</label>
              <input id="fecha_nacimiento" v-model="form.fecha_nacimiento" type="date" />
            </div>
            <div class="campo">
              <label for="telefono">Tel&eacute;fono</label>
              <input id="telefono" v-model="form.telefono" type="text" />
            </div>
            <div class="campo">
              <label for="cargo">Cargo</label>
              <input id="cargo" v-model="form.cargo" type="text" required />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Datos de Acceso</legend>
          <div class="form-grid">
            <div class="campo">
              <label for="username">Usuario</label>
              <input id="username" v-model="form.username" type="text" required />
            </div>
            <div class="campo">
              <label for="password">
                {{ esEdicion ? 'Nueva Contrase&ntilde;a (dejar vac&iacute;o para mantener)' : 'Contrase&ntilde;a' }}
              </label>
              <input id="password" v-model="form.password" type="password" :required="!esEdicion" />
            </div>
            <div class="campo">
              <label for="id_rol">Rol</label>
              <select id="id_rol" v-model="form.id_rol" required>
                <option value="" disabled>Seleccionar rol</option>
                <option v-for="r in roles" :key="r.id_rol" :value="r.id_rol">
                  {{ r.nombre }}
                </option>
              </select>
            </div>
          </div>
        </fieldset>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="emit('cerrar')">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="enviando">
            {{ enviando ? 'Guardando...' : 'Guardar' }}
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
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px 0;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #042D29;
}

.btn-cerrar {
  background: none;
  border: none;
  font-size: 28px;
  color: #929079;
  cursor: pointer;
  transition: color 0.2s;
  line-height: 1;
  padding: 0;
}

.btn-cerrar:hover {
  color: #741102;
}

.modal-form {
  padding: 16px 24px 24px;
}

.mensaje-error {
  color: #741102;
  background: #FFF5F5;
  border-left: 3px solid #741102;
  padding: 10px 14px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 13px;
}

fieldset {
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 16px;
}

legend {
  font-size: 14px;
  font-weight: 600;
  color: #042D29;
  padding: 0 8px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.campo {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.campo.full {
  grid-column: 1 / -1;
}

.campo label {
  font-size: 13px;
  font-weight: 500;
  color: #042D29;
}

.campo input,
.campo select {
  padding: 10px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #1F2937;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.campo input:focus,
.campo select:focus {
  border-color: #042D29;
  box-shadow: 0 0 0 3px rgba(4, 45, 41, 0.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-cancelar {
  padding: 10px 20px;
  background: transparent;
  color: #929079;
  border: 1.5px solid #D1D5DB;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancelar:hover {
  border-color: #929079;
  color: #042D29;
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

.btn-guardar:hover {
  background: #052E2A;
}

.btn-guardar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

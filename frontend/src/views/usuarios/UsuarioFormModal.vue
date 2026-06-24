<script setup>
import { ref, computed } from 'vue'
import { useUsuariosStore } from '@/stores/usuarios'
import { useAuthStore } from '@/stores/auth'
import ProgramacionModal from './ProgramacionModal.vue'

const props = defineProps({
  usuario: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['cerrar'])

const store = useUsuariosStore()
const auth = useAuthStore()
const esEdicion = computed(() => !!props.usuario)
const enviando = ref(false)
const mensajeError = ref('')
const mostrarHorario = ref(false)
const horarioGuardado = ref(false)
const esAdmin = computed(() => auth.tieneRol(1))

function obtenerRolesUsuario() {
  if (!props.usuario?.roles) return []
  return props.usuario.roles.map(r => r.id_rol)
}

const errores = ref({
  ci: '',
  primer_nombre: '',
  segundo_nombre: '',
  apellido_paterno: '',
  apellido_materno: '',
  fecha_nacimiento: '',
  telefono: '',
  cargo: '',
  username: '',
  password: '',
  roles: '',
})

const form = ref({
  ci: props.usuario?.ci || '',
  primer_nombre: props.usuario?.primer_nombre || '',
  segundo_nombre: props.usuario?.segundo_nombre || '',
  apellido_paterno: props.usuario?.apellido_paterno || '',
  apellido_materno: props.usuario?.apellido_materno || '',
  fecha_nacimiento: props.usuario?.fecha_nacimiento || '',
  telefono: props.usuario?.telefono || '',
  cargo: props.usuario?.cargo || '',
  sueldo_base: props.usuario?.sueldo_base || 0,
  username: props.usuario?.username || '',
  password: '',
  roles: obtenerRolesUsuario(),
})

function limpiarErrores() {
  for (const k in errores.value) {
    errores.value[k] = ''
  }
}

function validar() {
  let ok = true
  limpiarErrores()

  if (!form.value.ci) {
    errores.value.ci = 'La cédula de identidad es requerida'
    ok = false
  } else if (!/^\d+$/.test(form.value.ci)) {
    errores.value.ci = 'Debe contener solo números'
    ok = false
  } else if (form.value.ci.length < 5 || form.value.ci.length > 9) {
    errores.value.ci = 'Debe tener entre 5 y 9 dígitos'
    ok = false
  }

  if (form.value.primer_nombre.length < 2) {
    errores.value.primer_nombre = 'Mínimo 2 caracteres'
    ok = false
  }

  if (form.value.apellido_paterno.length < 2) {
    errores.value.apellido_paterno = 'Mínimo 2 caracteres'
    ok = false
  }

  if (form.value.telefono && !/^\d{8}$/.test(form.value.telefono)) {
    errores.value.telefono = 'Debe tener 8 dígitos'
    ok = false
  }

  if (form.value.cargo.length < 2) {
    errores.value.cargo = 'Mínimo 2 caracteres'
    ok = false
  }

  if (form.value.username.length < 3) {
    errores.value.username = 'Mínimo 3 caracteres'
    ok = false
  }

  if (!esEdicion.value && form.value.password.length < 6) {
    errores.value.password = 'Mínimo 6 caracteres'
    ok = false
  }
  if (esEdicion.value && form.value.password && form.value.password.length < 6) {
    errores.value.password = 'Mínimo 6 caracteres'
    ok = false
  }

  if (form.value.roles.length === 0) {
    errores.value.roles = 'Debe seleccionar al menos un rol'
    ok = false
  }

  return ok
}

async function guardar() {
  mensajeError.value = ''
  if (!validar()) return
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
            <div class="campo" :class="{ 'has-error': errores.ci }">
              <label for="ci">C&eacute;dula de Identidad</label>
              <input id="ci" v-model="form.ci" type="text" required />
              <p v-if="errores.ci" class="field-error">{{ errores.ci }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.primer_nombre }">
              <label for="primer_nombre">Primer Nombre</label>
              <input id="primer_nombre" v-model="form.primer_nombre" type="text" required />
              <p v-if="errores.primer_nombre" class="field-error">{{ errores.primer_nombre }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.segundo_nombre }">
              <label for="segundo_nombre">Segundo Nombre</label>
              <input id="segundo_nombre" v-model="form.segundo_nombre" type="text" />
              <p v-if="errores.segundo_nombre" class="field-error">{{ errores.segundo_nombre }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.apellido_paterno }">
              <label for="apellido_paterno">Apellido Paterno</label>
              <input id="apellido_paterno" v-model="form.apellido_paterno" type="text" required />
              <p v-if="errores.apellido_paterno" class="field-error">{{ errores.apellido_paterno }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.apellido_materno }">
              <label for="apellido_materno">Apellido Materno</label>
              <input id="apellido_materno" v-model="form.apellido_materno" type="text" />
              <p v-if="errores.apellido_materno" class="field-error">{{ errores.apellido_materno }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.fecha_nacimiento }">
              <label for="fecha_nacimiento">Fecha de Nacimiento</label>
              <input id="fecha_nacimiento" v-model="form.fecha_nacimiento" type="date" />
              <p v-if="errores.fecha_nacimiento" class="field-error">{{ errores.fecha_nacimiento }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.telefono }">
              <label for="telefono">Tel&eacute;fono</label>
              <input id="telefono" v-model="form.telefono" type="text" />
              <p v-if="errores.telefono" class="field-error">{{ errores.telefono }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.cargo }">
              <label for="cargo">Cargo</label>
              <input id="cargo" v-model="form.cargo" type="text" required />
              <p v-if="errores.cargo" class="field-error">{{ errores.cargo }}</p>
            </div>
            <div v-if="esAdmin" class="campo">
              <label for="sueldo_base">Sueldo Base ($)</label>
              <input id="sueldo_base" v-model.number="form.sueldo_base" type="number" min="0" step="0.01" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Datos de Acceso</legend>
          <div class="form-grid">
            <div class="campo" :class="{ 'has-error': errores.username }">
              <label for="username">Usuario</label>
              <input id="username" v-model="form.username" type="text" required />
              <p v-if="errores.username" class="field-error">{{ errores.username }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.password }">
              <label for="password">
                {{ esEdicion ? 'Nueva Contrase&ntilde;a (dejar vac&iacute;o para mantener)' : 'Contrase&ntilde;a' }}
              </label>
              <input id="password" v-model="form.password" type="password" :required="!esEdicion" />
              <p v-if="errores.password" class="field-error">{{ errores.password }}</p>
            </div>
            <div class="campo campo-roles" :class="{ 'has-error': errores.roles }">
              <label>Roles</label>
              <div class="roles-checkboxes">
                <label v-for="r in roles" :key="r.id_rol" class="rol-checkbox">
                  <input
                    type="checkbox"
                    :value="r.id_rol"
                    v-model="form.roles"
                  />
                  <span>{{ r.nombre }}</span>
                </label>
              </div>
              <p v-if="errores.roles" class="field-error">{{ errores.roles }}</p>
            </div>
          </div>
        </fieldset>

        <div v-if="esEdicion" class="horario-section">
          <hr />
          <div class="horario-section-inner">
            <p v-if="horarioGuardado" class="horario-status ok">Horario configurado</p>
            <p v-else class="horario-status pending">Sin horario semanal</p>
            <button type="button" class="btn-horario" @click="mostrarHorario = true">
              Configurar Horario Semanal
            </button>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancelar" @click="emit('cerrar')">Cancelar</button>
          <button type="submit" class="btn-guardar" :disabled="enviando">
            {{ enviando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </form>

      <Teleport to="body">
        <ProgramacionModal
          v-if="mostrarHorario"
          :id-empleado="props.usuario?.id_usuario"
          :horario-actual="[]"
          @cerrar="mostrarHorario = false"
          @guardado="horarioGuardado = true; mostrarHorario = false"
        />
      </Teleport>
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

.campo label {
  font-size: 13px;
  font-weight: 500;
  color: #042D29;
}

.campo-roles {
  grid-column: 1 / -1;
}

.roles-checkboxes {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}

.rol-checkbox {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.rol-checkbox:has(input:checked) {
  border-color: #042D29;
  background: rgba(4, 45, 41, 0.06);
  color: #042D29;
  font-weight: 500;
}

.rol-checkbox input {
  accent-color: #042D29;
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

.campo.has-error input,
.campo.has-error select {
  border-color: #741102;
}

.campo.has-error input:focus,
.campo.has-error select:focus {
  box-shadow: 0 0 0 3px rgba(116, 17, 2, 0.1);
}

.field-error {
  font-size: 11px;
  color: #741102;
  margin-top: 1px;
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

.horario-section {
  padding: 16px 0 4px;
}

.horario-section hr {
  border: none;
  border-top: 1px solid #E5E7EB;
  margin-bottom: 16px;
}

.horario-section-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.horario-status {
  font-size: 13px;
  font-weight: 500;
  margin: 0;
}

.horario-status.ok { color: #059669; }
.horario-status.pending { color: #929079; }

.btn-horario {
  padding: 8px 16px;
  background: rgba(4,45,41,0.08);
  color: #042D29;
  border: 1.5px solid #042D29;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-horario:hover { background: rgba(4,45,41,0.15); }
</style>

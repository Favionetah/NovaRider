<script setup>
import { ref, computed } from 'vue'
import { useUsuariosStore } from '@/stores/usuarios'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  usuario: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['cerrar'])

const store = useUsuariosStore()
const auth = useAuthStore()
const esEdicion = computed(() => !!props.usuario)
const enviando = ref(false)
const showPassword = ref(false)
const mensajeError = ref('')
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
  } else if (form.value.ci.length < 7 || form.value.ci.length > 8) {
    errores.value.ci = 'Debe tener 7 u 8 dígitos'
    ok = false
  }

  if (!form.value.primer_nombre.trim()) {
    errores.value.primer_nombre = 'El primer nombre es obligatorio'
    ok = false
  } else if (!/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/.test(form.value.primer_nombre)) {
    errores.value.primer_nombre = 'Solo debe contener letras'
    ok = false
  }

  if (form.value.segundo_nombre && !/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/.test(form.value.segundo_nombre)) {
    errores.value.segundo_nombre = 'Solo debe contener letras'
    ok = false
  }

  if (!form.value.apellido_paterno.trim()) {
    errores.value.apellido_paterno = 'El apellido paterno es obligatorio'
    ok = false
  } else if (!/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/.test(form.value.apellido_paterno)) {
    errores.value.apellido_paterno = 'Solo debe contener letras'
    ok = false
  }

  if (form.value.apellido_materno && !/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/.test(form.value.apellido_materno)) {
    errores.value.apellido_materno = 'Solo debe contener letras'
    ok = false
  }

  if (!form.value.telefono) {
    errores.value.telefono = 'El teléfono es requerido'
    ok = false
  } else if (!/^[67]\d{6,7}$/.test(form.value.telefono)) {
    errores.value.telefono = 'Debe empezar con 6 o 7 y tener 7 u 8 dígitos'
    ok = false
  }

  if (!form.value.fecha_nacimiento) {
    errores.value.fecha_nacimiento = 'La fecha de nacimiento es requerida'
    ok = false
  } else {
    const hoy = new Date()
    const nac = new Date(form.value.fecha_nacimiento)
    let edad = hoy.getFullYear() - nac.getFullYear()
    const mes = hoy.getMonth() - nac.getMonth()
    if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) {
      edad--
    }
    if (edad < 18) {
      errores.value.fecha_nacimiento = 'Debe ser mayor de 18 años'
      ok = false
    }
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

  if (!ok) {
    const primerError = document.querySelector('.has-error')
    if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' })
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
    emit('guardado')
    } catch (err) {
      if (err.response?.status === 422 && err.response?.data?.errors) {
        limpiarErrores()
        const errs = err.response.data.errors
        for (const [campo, msgs] of Object.entries(errs)) {
          if (campo in errores.value) {
            errores.value[campo] = Array.isArray(msgs) ? msgs[0] : msgs
          }
        }
        const primerError = document.querySelector('.has-error')
        if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' })
      } else {
        mensajeError.value = err.response?.data?.message || 'Error al guardar usuario'
      }
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="modal-overlay">
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
              <label for="segundo_nombre">Segundo Nombre (opcional)</label>
              <input id="segundo_nombre" v-model="form.segundo_nombre" type="text" />
              <p v-if="errores.segundo_nombre" class="field-error">{{ errores.segundo_nombre }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.apellido_paterno }">
              <label for="apellido_paterno">Apellido Paterno</label>
              <input id="apellido_paterno" v-model="form.apellido_paterno" type="text" required />
              <p v-if="errores.apellido_paterno" class="field-error">{{ errores.apellido_paterno }}</p>
            </div>
            <div class="campo" :class="{ 'has-error': errores.apellido_materno }">
              <label for="apellido_materno">Apellido Materno (opcional)</label>
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
              <label for="cargo">Cargo dentro del taller</label>
              <input id="cargo" v-model="form.cargo" type="text" required />
              <p v-if="errores.cargo" class="field-error">{{ errores.cargo }}</p>
            </div>
            <div v-if="esAdmin" class="campo">
              <label for="sueldo_base">Sueldo Base (Bs)</label>
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
              <div class="password-wrapper">
                <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" :required="!esEdicion" />
                <button type="button" class="btn-toggle-password" @click="showPassword = !showPassword" :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                  <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" class="eye-icon">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" class="eye-icon">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
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
  max-height: 95vh;
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
  padding: 12px 16px;
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
  gap: 10px;
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

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrapper input {
  padding-right: 36px;
  width: 100%;
}

.btn-toggle-password {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: none;
  border: none;
  color: #929079;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s ease;
}

.btn-toggle-password:hover {
  color: #042D29;
}

.eye-icon {
  width: 18px;
  height: 18px;
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

</style>

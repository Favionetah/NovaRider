<script setup>
defineProps({
  usuario: { type: Object, required: true },
})

const emit = defineEmits(['confirmar', 'cancelar'])
</script>

<template>
  <div class="modal-overlay" @click.self="emit('cancelar')">
    <div class="modal-confirmar">
      <h3>Desactivar Usuario</h3>
      <p>
        &iquest;Est&aacute; seguro de desactivar al usuario
        <strong>{{ usuario?.nombre_completo || usuario?.username }}</strong>?
      </p>
      <p class="nota">El usuario no podr&aacute; iniciar sesi&oacute;n hasta que sea reactivado.</p>
      <div class="acciones">
        <button class="btn-cancelar" @click="emit('cancelar')">Cancelar</button>
        <button class="btn-confirmar" @click="emit('confirmar')">Desactivar</button>
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
  z-index: 110;
  padding: 20px;
}

.modal-confirmar {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 28px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  text-align: center;
}

.modal-confirmar h3 {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
  margin-bottom: 12px;
}

.modal-confirmar p {
  font-size: 14px;
  color: #929079;
  margin-bottom: 8px;
  line-height: 1.5;
}

.nota {
  font-size: 13px;
  color: #929079;
  font-style: italic;
}

.acciones {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 20px;
}

.btn-cancelar {
  padding: 10px 24px;
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

.btn-confirmar {
  padding: 10px 24px;
  background: #741102;
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-confirmar:hover {
  background: #8C1503;
}
</style>

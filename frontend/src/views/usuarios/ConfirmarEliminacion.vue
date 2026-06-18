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
  background: #fff;
  border-radius: 14px;
  padding: 28px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  text-align: center;
}

.modal-confirmar h3 {
  font-size: 18px;
  color: #1a1a2e;
  margin-bottom: 12px;
}

.modal-confirmar p {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 8px;
  line-height: 1.5;
}

.nota {
  font-size: 13px;
  color: #9ca3af;
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
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
}

.btn-confirmar {
  padding: 10px 24px;
  background: #dc2626;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.btn-confirmar:hover {
  background: #b91c1c;
}
</style>

<script setup>
defineEmits(['confirmar', 'cancelar'])
</script>

<template>
  <div class="modal-overlay" @click.self="$emit('cancelar')">
    <div class="modal-confirmar">
      <svg viewBox="0 0 24 24" fill="none" class="modal-icon">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="#741102" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <h3>Cerrar Sesi&oacute;n</h3>
      <p>&iquest;Est&aacute;s seguro de cerrar la sesi&oacute;n?</p>
      <div class="acciones">
        <button class="btn-cancelar" @click="$emit('cancelar')">Cancelar</button>
        <button class="btn-confirmar" @click="$emit('confirmar')">Cerrar sesi&oacute;n</button>
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
  z-index: 200;
  padding: 20px;
}

.modal-confirmar {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 32px;
  width: 100%;
  max-width: 380px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  text-align: center;
}

.modal-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
}

.modal-confirmar h3 {
  font-size: 18px;
  font-weight: 700;
  color: #042D29;
  margin-bottom: 8px;
}

.modal-confirmar p {
  font-size: 14px;
  color: #929079;
  margin-bottom: 24px;
  line-height: 1.5;
}

.acciones {
  display: flex;
  gap: 12px;
  justify-content: center;
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

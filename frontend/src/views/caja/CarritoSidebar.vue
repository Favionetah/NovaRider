<script setup>
import { computed } from 'vue'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  items: { type: Array, default: () => [] },
})

const emit = defineEmits(['onClose', 'onRemoveItem', 'onProcesarVenta'])

const total = computed(() =>
  props.items.reduce((acc, item) => acc + parseFloat(item.precio || 0), 0)
)
</script>

<template>
  <Teleport to="body">
    <div v-if="isOpen" class="sidebar-overlay" @click="emit('onClose')"></div>
    <div class="carrito-sidebar" :class="{ abierto: isOpen }">
      <div class="sidebar-header">
        <h3>Carrito de Ventas</h3>
        <button class="close-btn" @click="emit('onClose')">
          <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <div v-if="items.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" width="40" height="40" class="empty-icon">
          <circle cx="9" cy="21" r="1" stroke="#929079" stroke-width="2"/>
          <circle cx="20" cy="21" r="1" stroke="#929079" stroke-width="2"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke="#929079" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>El carrito est&aacute; vac&iacute;o</p>
      </div>

      <div v-else class="sidebar-items">
        <div v-for="item in items" :key="item.id" class="cart-item">
          <div class="cart-item-info">
            <span class="cart-item-concepto">{{ item.concepto }}</span>
            <span class="cart-item-precio">S/ {{ parseFloat(item.precio).toFixed(2) }}</span>
          </div>
          <button class="remove-btn" @click="emit('onRemoveItem', item.id)">
            <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
              <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="sidebar-footer">
        <div class="total-row">
          <span class="total-label">Total</span>
          <span class="total-valor">S/ {{ total.toFixed(2) }}</span>
        </div>
        <button
          class="btn-primario w-100"
          :disabled="items.length === 0"
          @click="emit('onProcesarVenta')"
        >
          <svg viewBox="0 0 24 24" fill="none" width="16" height="16">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Procesar Venta
        </button>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  z-index: 200;
}

.carrito-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  width: 380px;
  height: 100vh;
  background: #FFFFFF;
  z-index: 201;
  display: flex;
  flex-direction: column;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  box-shadow: -4px 0 20px rgba(0,0,0,0.1);
}

.carrito-sidebar.abierto {
  transform: translateX(0);
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.sidebar-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #042D29;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #929079;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.close-btn:hover { background: #F5F4F0; color: #1F2937; }

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #929079;
  padding: 40px 24px;
}

.empty-state p {
  font-size: 14px;
  margin: 0;
}

.empty-icon { opacity: 0.5; }

.sidebar-items {
  flex: 1;
  overflow-y: auto;
  padding: 12px 24px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #F3F4F6;
}

.cart-item:last-child { border-bottom: none; }

.cart-item-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.cart-item-concepto {
  font-size: 14px;
  font-weight: 500;
  color: #1F2937;
}

.cart-item-precio {
  font-size: 13px;
  font-weight: 600;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.remove-btn {
  background: none;
  border: none;
  color: #929079;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  transition: background 0.2s ease, color 0.2s ease;
}

.remove-btn:hover { background: #FEF2F2; color: #741102; }

.sidebar-footer {
  padding: 16px 24px 24px;
  border-top: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label {
  font-size: 14px;
  font-weight: 500;
  color: #1F2937;
}

.total-valor {
  font-size: 22px;
  font-weight: 700;
  color: #042D29;
  font-family: 'Inter', monospace;
}

.btn-primario {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
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

.btn-primario:hover { background: #052E2A; }
.btn-primario:disabled { opacity: 0.5; cursor: not-allowed; }
.w-100 { width: 100%; }
</style>

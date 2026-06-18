<script setup>
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>NovaRider</h1>
      <div class="user-info">
        <span>{{ auth.user?.username }} ({{ auth.user?.rol }})</span>
        <button class="btn-logout" @click="auth.logout()">Cerrar sesi&oacute;n</button>
      </div>
    </header>

    <main class="dashboard-content">
      <h2>Bienvenido, {{ auth.user?.username }}</h2>
      <p class="subtitle">Panel de administraci&oacute;n</p>

      <div class="modulos">
        <router-link to="/usuarios" class="modulo-card">
          <span class="modulo-icon">&#128100;</span>
          <span class="modulo-nombre">Gesti&oacute;n de Usuarios</span>
          <span class="modulo-desc">Administrar usuarios del sistema</span>
        </router-link>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f0f2f5;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 32px;
  background: #1a1a2e;
  color: #fff;
}

.dashboard-header h1 {
  font-size: 20px;
  font-weight: 700;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 14px;
}

.btn-logout {
  padding: 6px 16px;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.2);
}

.dashboard-content {
  padding: 40px 32px;
  max-width: 1000px;
  margin: 0 auto;
}

.dashboard-content h2 {
  font-size: 28px;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.subtitle {
  color: #6b7280;
  margin-bottom: 32px;
}

.modulos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.modulo-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 24px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  text-decoration: none;
  transition: box-shadow 0.2s;
}

.modulo-card:hover {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.modulo-icon {
  font-size: 32px;
}

.modulo-nombre {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
}

.modulo-desc {
  font-size: 13px;
  color: #6b7280;
}
</style>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import CambiarContrasenaModal from './CambiarContrasenaModal.vue'
import ConfirmarCerrarSesion from './ConfirmarCerrarSesion.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const mostrarModalContrasena = ref(false)
const mostrarConfirmarCerrar = ref(false)

const navModulos = computed(() =>
  auth.modulosPermitidos.filter(m => m.ruta)
)

async function cerrarSesion() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="app-header">
    <div class="header-left">
      <router-link to="/" class="header-logo">
        <svg viewBox="0 0 48 48" fill="none" class="header-logo-svg">
          <circle cx="24" cy="24" r="22" stroke="#929079" stroke-width="2" fill="none" />
          <circle cx="24" cy="24" r="8" fill="#FFFFFF" opacity="0.2" />
          <path d="M24 4v6M24 38v6M4 24h6M38 24h6" stroke="#929079" stroke-width="1.5" stroke-linecap="round" />
          <circle cx="24" cy="24" r="3" fill="#741102" />
        </svg>
        <span class="header-title">NovaRider</span>
      </router-link>

      <nav class="header-nav">
        <router-link
          to="/"
          class="nav-link"
          :class="{ active: route.path === '/' }"
        >
          Panel Principal
        </router-link>
        <router-link
          v-for="m in navModulos"
          :key="m.id"
          :to="m.ruta"
          class="nav-link"
          :class="{ active: route.path === m.ruta }"
        >
          {{ m.nombre }}
        </router-link>
      </nav>
    </div>

    <div class="header-right">
      <button class="btn-cambiar-contrasena" @click="mostrarModalContrasena = true" title="Cambiar contraseña">
        <svg viewBox="0 0 24 24" fill="none" class="icon-cambiar-contrasena">
          <rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1.5" />
          <circle cx="12" cy="16" r="1.5" fill="currentColor" />
          <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
      </button>
      <span class="header-rol-badge" :class="'rol-' + (auth.user?.rol || '').toLowerCase()">
        {{ auth.user?.rol }}
      </span>
      <button class="btn-logout" @click="mostrarConfirmarCerrar = true">Cerrar sesi&oacute;n</button>
    </div>

    <ConfirmarCerrarSesion
      v-if="mostrarConfirmarCerrar"
      @confirmar="cerrarSesion"
      @cancelar="mostrarConfirmarCerrar = false"
    />

    <CambiarContrasenaModal
      v-if="mostrarModalContrasena"
      @close="mostrarModalContrasena = false"
    />
  </header>
</template>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 32px;
  background: #042D29;
  color: #FFFFFF;
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 32px;
}

.header-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: #FFFFFF;
}

.header-logo-svg {
  width: 40px;
  height: 40px;
}

.header-title {
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.3px;
}

.header-nav {
  display: flex;
  align-items: center;
  gap: 4px;
}

.nav-link {
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.nav-link:hover {
  color: #FFFFFF;
  background: rgba(255, 255, 255, 0.08);
}

.nav-link.active {
  color: #FFFFFF;
  background: rgba(255, 255, 255, 0.12);
  font-weight: 600;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-rol-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.rol-administrador { background: rgba(116, 17, 2, 0.2); color: #FFFFFF; }
.rol-cajero { background: rgba(146, 144, 121, 0.25); color: #FFFFFF; }
.rol-mecanico { background: rgba(4, 45, 41, 0.3); color: #FFFFFF; }
.rol-recepcionista { background: rgba(146, 144, 121, 0.2); color: #FFFFFF; }

.btn-cambiar-contrasena {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: transparent;
  color: #FFFFFF;
  border: 1.5px solid #929079;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cambiar-contrasena:hover {
  background: #052E2A;
  border-color: #FFFFFF;
}

.icon-cambiar-contrasena {
  width: 18px;
  height: 18px;
}

.btn-logout {
  padding: 6px 16px;
  background: transparent;
  color: #FFFFFF;
  border: 1.5px solid #929079;
  border-radius: 8px;
  font-size: 13px;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-logout:hover {
  background: #741102;
  border-color: #741102;
}
</style>

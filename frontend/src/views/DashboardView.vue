<script setup>
import { onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

// MODIFICACIÓN QUIRÚRGICA: Mapeamos los módulos y asignamos la ruta al taller si coincide el nombre
const modulos = auth.modulosPermitidos.map(m => {
  if (m.nombre && m.nombre.includes('Taller y Reparaciones')) {
    return { ...m, ruta: '/taller' }
  }
  return m
})

onMounted(async () => {
  await nextTick()

  const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })

  tl.from('.welcome-section', { y: 20, opacity: 0, duration: 0.4 })
    .from('.modulo-card', { y: 30, opacity: 0, duration: 0.4, stagger: 0.08 }, '-=0.1')
})

function cardEnter(e) {
  gsap.to(e.currentTarget, { y: -4, boxShadow: '0 12px 32px rgba(0,0,0,0.12)', duration: 0.25, ease: 'power2.out' })
}

function cardLeave(e) {
  gsap.to(e.currentTarget, { y: 0, boxShadow: '0 2px 12px rgba(0,0,0,0.06)', duration: 0.25, ease: 'power2.out' })
}
</script>

<template>
  <!-- TODO EL TEMPLATE SE QUEDA EXACTAMENTE IGUAL (NO SE MODIFICA NADA) -->
  <div class="dashboard">
    <main class="dashboard-content">
      <div class="welcome-section">
        <h1 class="welcome-title">Bienvenido, {{ auth.user?.username }}</h1>
        <p class="welcome-subtitle">Panel de administraci&oacute;n &mdash; NovaRider</p>
      </div>

      <section class="modulos-section">
        <div v-if="modulos.length === 0" class="empty-state">
          <p>No tienes m&oacute;dulos disponibles.</p>
        </div>
        <div v-else class="modulos-grid">
          <component
            :is="m.ruta ? 'router-link' : 'div'"
            v-for="m in modulos"
            :key="m.id"
            :to="m.ruta || undefined"
            class="modulo-card"
            :class="{ 'modulo-card-link': !!m.ruta }"
            @mouseenter="cardEnter"
            @mouseleave="cardLeave"
          >
            <div class="modulo-card-accent" :style="{ background: m.color }" />
            <div class="modulo-card-body">
              <div class="modulo-icon-wrapper">
                <svg v-if="m.ruta" viewBox="0 0 24 24" fill="none" class="modulo-icon">
                  <circle cx="12" cy="8" r="3.5" stroke="#042D29" stroke-width="1.5" />
                  <path d="M3 20c0-4.5 4-8 9-8s9 3.5 9 8" stroke="#042D29" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" class="modulo-icon">
                  <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="#929079" stroke-width="1.5" />
                  <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="#929079" stroke-width="1.5" />
                  <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="#929079" stroke-width="1.5" />
                  <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="#929079" stroke-width="1.5" />
                </svg>
              </div>
              <span class="modulo-nombre" v-html="m.nombre" />
              <span class="modulo-desc" v-html="m.descripcion" />
              <span v-if="m.ruta" class="modulo-flecha">&rarr;</span>
            </div>
          </component>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
/* TODO EL ESTILO SE QUEDA EXACTAMENTE IGUAL (NO SE MODIFICA NADA) */
.dashboard { min-height: 100vh; display: flex; flex-direction: column; }
.dashboard-content { flex: 1; padding: 40px 32px; max-width: 1100px; margin: 0 auto; width: 100%; }
.welcome-section { margin-bottom: 40px; }
.welcome-title { font-size: 28px; font-weight: 700; color: #042D29; margin-bottom: 4px; }
.welcome-subtitle { font-size: 15px; color: #929079; font-weight: 400; }
.empty-state { text-align: center; padding: 60px 20px; color: #929079; font-size: 15px; }
.modulos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
.modulo-card { background: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); text-decoration: none; cursor: default; display: flex; flex-direction: column; }
.modulo-card-link { cursor: pointer; }
.modulo-card-accent { height: 4px; flex-shrink: 0; }
.modulo-card-body { padding: 24px; display: flex; flex-direction: column; gap: 8px; flex: 1; position: relative; }
.modulo-icon-wrapper { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin-bottom: 4px; }
.modulo-icon { width: 28px; height: 28px; }
.modulo-nombre { font-size: 16px; font-weight: 600; color: #042D29; line-height: 1.3; }
.modulo-desc { font-size: 13px; color: #929079; line-height: 1.4; }
.modulo-flecha { position: absolute; bottom: 20px; right: 20px; font-size: 18px; color: #929079; transition: transform 0.2s ease; }
.modulo-card-link:hover .modulo-flecha { transform: translateX(4px); }
</style>
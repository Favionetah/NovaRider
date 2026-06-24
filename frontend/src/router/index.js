import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/usuarios',
      name: 'usuarios',
      component: () => import('@/views/usuarios/UsuariosView.vue'),
      meta: { requiresAuth: true, roles: [1] },
    },
    {
      path: '/usuarios/:id',
      name: 'usuario-detalle',
      component: () => import('@/views/usuarios/UsuarioDetalleView.vue'),
      meta: { requiresAuth: true, roles: [1] },
    },
    {
      path: '/compras',
      name: 'compras',
      component: () => import('@/views/compras/ComprasView.vue'),
      meta: { requiresAuth: true, roles: [1] },
    },
    {
      path: '/horarios',
      name: 'horarios',
      component: () => import('@/views/horarios/HorariosView.vue'),
      meta: { requiresAuth: true, roles: [1, 3] },
    },
    {
      path: '/acceso-denegado',
      name: 'acceso-denegado',
      component: () => import('@/views/AccesoDenegadoView.vue'),
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.roles && auth.user && !auth.tieneRol(...to.meta.roles)) {
    return { name: 'acceso-denegado' }
  }
})

export default router

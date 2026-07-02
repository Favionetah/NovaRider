import { createRouter, createWebHistory } from 'vue-router'

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
      path: '/seguimiento/:codigo?',
      name: 'seguimiento-publico',
      component: () => import('@/views/seguimiento/SeguimientoPublicoView.vue'),
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
      path: '/clientes',
      name: 'clientes',
      component: () => import('@/views/clientes/ClientesView.vue'),
      meta: { requiresAuth: true, roles: [1, 3, 4] },
    },
    {
      path: '/motocicletas',
      name: 'motocicletas',
      component: () => import('@/views/motocicletas/MotocicletasView.vue'),
      meta: { requiresAuth: true, roles: [1, 3, 4] },
    },
    {
      path: '/compras',
      name: 'compras',
      component: () => import('@/views/compras/ComprasView.vue'),
      meta: { requiresAuth: true, roles: [1, 5] },
    },
    {
      path: '/caja',
      name: 'caja',
      component: () => import('@/views/caja/CajaView.vue'),
      meta: { requiresAuth: true, roles: [1, 2] },
    },
    {
      path: '/inventario',
      name: 'inventario',
      component: () => import('@/views/inventario/InventarioView.vue'),
      meta: { requiresAuth: true, roles: [1, 2, 5] },
    },
    {
      path: '/horarios',
      name: 'horarios',
      component: () => import('@/views/horarios/HorariosView.vue'),
      meta: { requiresAuth: true, roles: [1, 4] },
    },
    {
      path: '/reservas',
      name: 'reservas',
      component: () => import('@/views/reservas/ReservasView.vue'),
      meta: { requiresAuth: true, roles: [1, 3, 4] },
    },
    {
      path: '/acceso-denegado',
      name: 'acceso-denegado',
      component: () => import('@/views/AccesoDenegadoView.vue'),
    },
    {
      path: '/taller',
      name: 'taller',
      component: () => import('@/views/taller/TallerDashboard.vue'),
      meta: { requiresAuth: true, roles: [1, 3] },
    },
    {
      path: '/taller/equipamiento',
      name: 'taller-equipamiento',
      component: () => import('@/views/equipamiento/EquipamientoView.vue'),
      meta: { requiresAuth: true, roles: [1, 2, 5] },
    },
  ],
})

router.beforeEach(async (to) => {
  const { useAuthStore } = await import('@/stores/auth')
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

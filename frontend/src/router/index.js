import { createRouter, createWebHistory } from 'vue-router'
<<<<<<< HEAD
// Quitamos la importación directa de useAuthStore de aquí arriba para romper la referencia circular
=======
import { useAuthStore } from '@/stores/auth'
>>>>>>> respaldo-caja

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
<<<<<<< HEAD
=======
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
>>>>>>> respaldo-caja
      path: '/compras',
      name: 'compras',
      component: () => import('@/views/compras/ComprasView.vue'),
      meta: { requiresAuth: true, roles: [1] },
    },
    {
<<<<<<< HEAD
=======
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
>>>>>>> respaldo-caja
      path: '/horarios',
      name: 'horarios',
      component: () => import('@/views/horarios/HorariosView.vue'),
      meta: { requiresAuth: true, roles: [1, 3] },
    },
    {
<<<<<<< HEAD
=======
      path: '/reservas',
      name: 'reservas',
      component: () => import('@/views/reservas/ReservasView.vue'),
      meta: { requiresAuth: true, roles: [1, 3, 4] },
    },
    {
>>>>>>> respaldo-caja
      path: '/acceso-denegado',
      name: 'acceso-denegado',
      component: () => import('@/views/AccesoDenegadoView.vue'),
    },
<<<<<<< HEAD
    {
      path: '/taller/caja',
      name: 'caja',
      component: () => import('@/views/caja/CajaView.vue'),
    },
    {
      path: '/taller/equipamiento',
      name: 'taller-equipamiento',
      component: () => import('@/views/equipamiento/EquipamientoView.vue'),
    }
=======
    /* =========================================================================
       NUEVA RUTA QUIRÚRGICA: MÓDULO DE TALLER Y REPARACIONES
       ========================================================================= */
    {
      path: '/taller',
      name: 'taller',
      // Apunta directo a la carpeta aislada que creaste en el Paso 1
      component: () => import('@/views/taller/TallerDashboard.vue'),
      // Requiere sesión activa y permite acceso a los roles autorizados (Ej: Admin = 1, Mecánicos = 3)
      meta: { requiresAuth: true, roles: [1, 3] }, 
    },
>>>>>>> respaldo-caja
  ],
})

router.beforeEach(async (to) => {
<<<<<<< HEAD
  // 💡 IMPORTANTE: Importamos dinámicamente la tienda aquí adentro.
  // Esto evita que Pinia falle por intentar llamarse antes de inicializarse.
  const { useAuthStore } = await import('@/stores/auth')
=======
>>>>>>> respaldo-caja
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
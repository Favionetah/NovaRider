# NovaRider — AGENTS.md

Motorcycle repair shop management system.

## Stack

- **Backend:** `backend/` — Laravel 12 (PHP ^8.2), MySQL, Sanctum auth
- **Frontend:** `frontend/` — Vue 3 + Vite 8 + Pinia + Vue Router (history mode) + axios
- **Animations:** GSAP 3.12 (CDN via `index.html` — no install needed)
- **Testing:** PHPUnit 11 (SQLite :memory: in tests), no frontend test framework configured
- **Linting (frontend):** `npm run lint` runs oxlint + ESLint sequentially (via `npm-run-all2`)

## Developer commands

### Backend (`backend/`)
```bash
composer setup                   # install + copy .env + key:generate + migrate + npm i + build
composer dev                     # concurrently: php artisan serve + queue:listen + pail + npm run dev
composer test                    # config:clear + artisan test (runs PHPUnit via Laravel)
php artisan serve                # dev server (default :8000)
```

### Frontend (`frontend/`)
```bash
npm run dev       # vite dev server
npm run build     # vite build
npm run lint      # oxlint --fix . + eslint --fix --cache
```

### DB management
- Schema reference: `CreacionDB.txt` (full MySQL DDL — all tables prefixed `T`, every table has audit columns `estadoA`/`usuarioA`/`fechahoraA`)
- Seed data: `DatosIniciales.txt` (creates 4 roles + admin user)
- **Admin login:** `admin` / `admin123` (role: Administrador)
- `.env` currently configured for MySQL `novarider` database (root/root)
- Tests use SQLite in-memory (see `phpunit.xml`)
- **Tables added after DDL:** `TProgramaciones` (id_programacion, id_empleado, dia_semana, hora_entrada, hora_salida, activo, estadoA, usuarioA, fechahoraA)
- **Columns added after DDL:** `TTurnos` now has `hora_entrada_esperada` and `hora_salida_esperada` (frozen at registration time from TProgramaciones)

## What is already built (Módulo 1 — Seguridad y Usuarios)

- **Autenticación:** Sanctum SPA (cookies, sesión), login/logout/me, CSRF
- **Control de acceso:** Middleware `CheckRole`, rutas protegidas por `id_rol`
- **CRUD Usuarios:** Crear/Editar/Desactivar/Reactivar (Persona + Empleado + Usuario en transacción)
- **Cambio de contraseña:** Endpoint + modal desde el header
- **Dashboard dinámico:** Módulos filtrados por rol, cards con GSAP
- **Header global:** `AppHeader.vue` con logo, menú de navegación, badge de rol, cambiar contraseña, cerrar sesión (con confirmación)
- **Router guard:** `meta.roles` redirige a `/acceso-denegado` si no tiene permiso
- **Auditoría:** Todas las operaciones registradas en `TAuditoriaGeneral` con formato pipes (`|`)
- **Fuente global:** Inter + fondo `#F5F4F0` desde `App.vue` (no más Times New Roman al navegar)
- **Horarios Semanales (TProgramaciones):** Configurar plantilla Lun–Sáb por empleado, planilla global en `/horarios`. Auto‑completación de hora esperada en asistencias según el horario vigente al momento del registro.
- **Asistencia Automática (TTurnos):** Botón "Registrar Entrada"/"Registrar Salida" desde el card "Hoy". Cruza con TProgramaciones para guardar `hora_entrada_esperada`/`hora_salida_esperada` (congeladas al momento del registro). Columna `minutos_tarde` calculada en backend. Edición manual solo para observaciones (lápiz en cada fila).

## Architecture

- Backend routes: `routes/web.php` (applies Sanctum SPA auth for stateful domains)
- Frontend entry: `frontend/src/main.js` — creates Vue app with Pinia + Router
- Vue alias `@` → `frontend/src/` (configured in vite.config.js)
- Backend models under `App\Models`, controllers under `App\Http\Controllers`
- Header global in `App.vue` with `<AppHeader v-if="auth.isAuthenticated" />` (every authenticated page gets the same navbar)
- Navigation menu builds from `auth.modulosPermitidos` (returned by `GET /me`)
- Role/permissions live in `auth.tieneRol(...)` and `auth.tieneAcceso(ruta)` (computed from `/me` response)

## Conventions

- **Indentation:** PHP = 4 spaces, JS/Vue = 2 spaces
- **Line endings:** LF, UTF-8, trailing whitespace trimmed, final newline
- **Node engine:** `^22.18.0 \|\| >=24.12.0`
- **DB tables:** All prefixed with `T` (e.g., `TPersonas`, `TUsuarios`) — do NOT use plain names
- **Soft delete / audit:** Every table has `estadoA` (boolean), `usuarioA` (int FK), `fechahoraA` (datetime)
- **Audit trail:** `TAuditoriaGeneral` table for insert/update/delete tracking. See "Audit trail rules" below.
- **API response format:** Wrap collections in singular keys: `{ "usuarios": [...] }`, `{ "clientes": [...] }`, `{ "roles": [...] }`
- **Global styles:** `body` font-family and background go in `App.vue` `<style>` (NOT scoped). Do NOT put global rules in component `<style scoped>`.
- **Route protection backend:** Use `Route::middleware('role:1,2')` to restrict by `id_rol`
- **Route protection frontend:** Use `meta: { requiresAuth: true, roles: [1, 2] }` in Vue Router

## How to add a new module

### Step-by-step

```
Backend:
  1. Model  → app/Models/TuModelo.php
  2. Controller → app/Http/Controllers/TuController.php (uses AuditoriaTrait)
  3. Routes  → routes/web.php (inside auth group)

Frontend:
  4. Store   → frontend/src/stores/tuStore.js (Pinia)
  5. View    → frontend/src/views/tuModulo/TuVista.vue
  6. Route   → frontend/src/router/index.js (meta: { requiresAuth: true, roles: [...] })

Registration:
  7. Module  → backend/app/Http/Controllers/AuthController.php → cargarUsuario() → $modulos[]
```

#### Backend details

**Model:**
```php
class TuModelo extends Model
{
    protected $table = 'TTuTabla';
    protected $primaryKey = 'id_tutabla';
    public $timestamps = false;

    protected $fillable = ['campo1', 'campo2', 'estadoA', 'usuarioA', 'fechahoraA'];
}
```

**Controller** — must use `AuditoriaTrait` and call `$this->registrarAuditoria()` on every insert/update/delete:

```php
use App\Traits\AuditoriaTrait;

class TuController extends Controller
{
    use AuditoriaTrait;

    public function store(Request $request)
    {
        // ... crear registro ...
        $this->registrarAuditoria('TTuTabla', $registro->id, 'I', null, null, 'valor1|valor2|...', 'Creacion de ...');
    }

    public function update(Request $request, $id)
    {
        // recolectar campos modificados en arrays $campos, $valoresAnt, $valoresNue
        $this->registrarAuditoria('TTuTabla', $id, 'U',
            implode('|', $campos),
            implode('|', $valoresAnt),
            implode('|', $valoresNue),
            'Actualizacion de ...'
        );
    }

    public function destroy($id)
    {
        // soft delete: estadoA = false
        $this->registrarAuditoria('TTuTabla', $id, 'U', 'estadoA', '1', '0', 'Desactivacion de ...');
    }
}
```

**Routes:**
```php
Route::middleware('auth')->group(function () {
    Route::middleware('role:1,3')->group(function () {  // roles que pueden acceder
        Route::get('/tucurso', [TuController::class, 'index']);
        Route::post('/tucurso', [TuController::class, 'store']);
        Route::put('/tucurso/{id}', [TuController::class, 'update']);
        Route::delete('/tucurso/{id}', [TuController::class, 'destroy']);
    });
});
```

#### Frontend details

**Store (Pinia):**
```js
import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useTuStore = defineStore('tuStore', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function listar() {
    loading.value = true
    error.value = null
    try {
      const res = await api.get('/tucurso')
      items.value = res.data.tucurso
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar'
    } finally {
      loading.value = false
    }
  }

  return { items, loading, error, listar }
})
```

**View:** Create at `frontend/src/views/tuModulo/TuVista.vue`. The global header (`AppHeader`) is already rendered by `App.vue` — you only need the page content. Use the Design System colors below.

**Router:**
```js
{
  path: '/tucurso',
  name: 'tucurso',
  component: () => import('@/views/tuModulo/TuVista.vue'),
  meta: { requiresAuth: true, roles: [1, 2] },
},
```

**Module registration** — in `AuthController.php` → `cargarUsuario()` → `$modulos` array:
```php
[
    'id' => 'tucurso',
    'nombre' => 'Nombre Visible',
    'descripcion' => 'Descripción corta',
    'ruta' => '/tucurso',
    'color' => '#042D29',
    'roles_permitidos' => [1, 3],
],
```

### Audit trail rules

All audit log entries go to `TAuditoriaGeneral` via `AuditoriaTrait::registrarAuditoria()`.

| Acción | TablaNombre | Accion | Campo | ValorAnterior | ValorNuevo | Detalles |
|---|---|---|---|---|---|---|
| **Crear** | `TTuTabla` | `I` | `null` | `null` | `valor1\|valor2\|...` | Descripción |
| **Actualizar** | `TTuTabla` | `U` | `campo1\|campo2` | `ant1\|ant2` | `nue1\|nue2` | Descripción |
| **Eliminar** | `TTuTabla` | `U` | `estadoA` | `1` | `0` | Descripción |
| **Reactivar** | `TTuTabla` | `U` | `estadoA` | `0` | `1` | Descripción |

- **Creación:** agrupar todos los valores insertados en `ValorNuevo` separados por `|`. `Campo` y `ValorAnterior` en `null`.
- **Actualización:** si se modifican varios campos de la misma tabla, agruparlos en **1 sola fila** con `|`. No crear una fila por campo.
- **Eliminación/Reactivación:** son updates de `estadoA`, se registra como update simple.

### Available global components

| Component | Location | Usage |
|---|---|---|
| `AppHeader` | `components/AppHeader.vue` | Already included in `App.vue`. Provides nav menu, role badge, logout with confirmation, change password. |
| `CambiarContrasenaModal` | `components/CambiarContrasenaModal.vue` | Import and use with `v-if`. Emits `@close`. |
| `ConfirmarCerrarSesion` | `components/ConfirmarCerrarSesion.vue` | Emits `@confirmar` and `@cancelar`. |
| `ProgramacionModal` | `views/usuarios/ProgramacionModal.vue` | Modal para configurar horario semanal (grilla Lun–Sáb, toggle activo, selector hora entrada, auto‑cálculo 6h/8h). Emite `@cerrar` y `@guardado`. |

### Roles

| id_rol | Nombre | Módulos que ve |
|---|---|---|
| 1 | Administrador | Todos + Horarios |
| 2 | Cajero | Clientes/Vehículos (consulta), Inventario (consulta), Ventas/Caja |
| 3 | Mecánico | Clientes/Vehículos, Taller/Reparaciones, Inventario (consulta), Horarios (vista global) |
| 4 | Recepcionista | Clientes/Vehículos, Taller/Reparaciones (consulta) |

When defining `roles_permitidos` in a module, use these IDs.

## Design System

### Color Palette
- `#042D29` — Primary (headers, buttons, navbar, backgrounds)
- `#929079` — Secondary (subtle text, borders, disabled states)
- `#741102` — Accent (hover, errors, active states, decorative elements)
- `#FFFFFF` — White (cards, inputs, modal backgrounds)
- `#F5F4F0` — Page background (light warm tone)

### Typography
- Font: `Inter` from Google Fonts (loaded in `index.html`)
- Headings: 600–700 weight, `#042D29`
- Body: 400–500 weight, `#1F2937`
- Subtle text: `#929079`

### Animations
- Library: **GSAP 3.12** loaded from CDN in `index.html` — no install needed, available globally as `gsap`
- Entrance animations: `gsap.timeline()` with `from()` / `fromTo()` in `onMounted`
- Micro-interactions: `gsap.to()` for hover effects (scale, y position)
- CSS transitions for input focus, color changes (better perf for simple cases)

### Component Patterns
- **Cards:** White background, 16px border-radius, soft shadow, 4px accent border-top gradient (`#042D29` → `#741102`)
- **Buttons:** 10px radius, 14px padding, primary bg `#042D29`, hover `#052E2A`, active `#741102`. GSAP hover: `scale(1.02)` + `y(-2)`
- **Inputs:** 1.5px border `#D1D5DB`, 10px radius, 12px padding, 40px left padding when icon present, focus border `#042D29` + box-shadow glow
- **Modals:** Overlay rgba(0,0,0,0.4), card 14px radius, max-width 600px, slide/animate with GSAP or CSS
- **Error messages:** Left border `#741102`, bg `#FFF5F5`, 8px radius, slide-down animation
- **Icons:** SVG inline (no icon library), consistent 20px for inputs, 16px for errors

### Layout
- All pages: `max-width 1100px`, centered, `padding: 32px`
- No external UI libraries — pure Vue + CSS + GSAP

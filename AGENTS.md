# NovaRider

Motorcycle repair shop management system.

## Stack

- **Backend:** `backend/` — Laravel 12 (PHP ^8.2), MySQL, Sanctum SPA auth
- **Frontend:** `frontend/` — Vue 3 + Vite 8 + Pinia + Vue Router 5 (history mode) + axios
- **Testing:** PHPUnit 11 (SQLite `:memory:` in tests), no frontend tests
- **Linting (frontend):** `npm run lint` runs oxlint then ESLint via `npm-run-all2`
- **GSAP 3.12.5:** loaded from CDN in `frontend/index.html`, global `gsap`

## Developer commands

```bash
# Backend (run from backend/)
composer setup      # install + .env + key:generate + migrate + npm i + build
composer dev        # concurrently: serve + queue:listen + pail + npm run dev
composer test       # config:clear + artisan test (PHPUnit)
php artisan serve   # dev server :8000

# Frontend (run from frontend/)
npm run dev         # vite dev server
npm run build       # vite build
npm run lint        # oxlint --fix . + eslint --fix --cache
```

## DB schema facts

- All tables prefixed `T`. Every business table has `estadoA`/`usuarioA`/`fechahoraA`.
- **Source of truth = migrations** (`backend/database/migrations/`). `CreacionDB.txt` is a visual reference only.
- `DatosIniciales.txt` is a **manual reference** — not run by migrations. The actual schema has diverged (no `TPersonas` table; `TEmpleados`/`TClientes` have person fields merged in; `TUsuarios` uses `TUsuarioRol` pivot for many-to-many roles, not a single `id_rol` column).
- **Admin login:** `admin` / `admin123` (role: Administrador, id_rol=1)
- Tests use SQLite `:memory:` (see `backend/phpunit.xml`)

## Authentication & authorization

- **Sanctum SPA:** Login = `GET /sanctum/csrf-cookie` → `POST /login`. Sends credentials via cookies.
- **`/me`** returns `{ user: { id_usuario, username, roles: [...], rol: "comma-joined names", id_empleado, estadoA, modulos: [...] } }`
- **CheckRole middleware:** `Route::middleware('role:1,2')` — guards by `id_rol` via `TUsuarioRol` pivot
- **Vue Router guard:** `meta: { requiresAuth: true, roles: [1, 3] }` redirects to `/acceso-denegado`
- **Auth store** (`frontend/src/stores/auth.js`): `isAuthenticated` (computed), `modulosPermitidos` (computed), `tieneRol(...roles)` (function), `tieneAcceso(ruta)` (function)

## Module definitions (in `AuthController::cargarUsuario()`)

| id | nombre | ruta | roles |
|---|---|---|---|
| usuarios | Gestión de Personal | `/usuarios` | 1 |
| clientes | Clientes y Vehículos | `/clientes` | 1, 3, 4 |
| taller | Taller y Reparaciones | `null` | 1, 3 |
| inventario | Inventario | `null` | 1, 2 |
| ventas | Ventas y Caja | `null` | 1, 2 |
| compras | Compras | `/compras` | 1 |
| horarios | Horarios | `/horarios` | 1, 3 |
| reservas | Reservas y Envíos | `/reservas` | 1, 3, 4 |

Modules with `ruta: null` (taller, inventario, ventas) have no nav link but can still be accessed via URL if the user knows it.

## Architecture

- **Routes:** `backend/routes/web.php` (Sanctum SPA auth for stateful domains)
- **Frontend entry:** `frontend/src/main.js` — creates Vue app with Pinia + Router
- **Alias `@` → `frontend/src/`** (vite.config.js)
- **Global header:** `App.vue` renders `<AppHeader v-if="auth.isAuthenticated" />` — every authed page gets the same navbar
- **Navigation menu** builds from `auth.modulosPermitidos` (`/me` → `modulos`)
- **API client:** `frontend/src/services/api.js` — `axios.create({ baseURL: 'http://localhost:8000', withCredentials: true, withXSRFToken: true })`

## How to add a new module

1. **Model** → `app/Models/TTuModelo.php` (extends Model, `$table`, `$primaryKey`, `$timestamps = false`)
2. **Controller** → `app/Http/Controllers/TuController.php` with `AuditoriaTrait`, call `$this->registrarAuditoria()` on every I/U/D
3. **Routes** → `routes/web.php` inside `auth` + `role` middleware groups
4. **Store** → `frontend/src/stores/tuStore.js` (Pinia)
5. **View** → `frontend/src/views/tuModulo/TuVista.vue`
6. **Route** → `frontend/src/router/index.js` with `meta: { requiresAuth: true, roles: [...] }`
7. **Register module** → in `AuthController::cargarUsuario()` → `$modulos` array with `id, nombre, descripcion, ruta, color, roles_permitidos`

## Auditoría (`TAuditoriaGeneral`)

Columnas: `idAuditoria, TablaNombre, RegistroId, Accion (I/U), Campo, ValorAnterior, ValorNuevo, usuarioA, fechaHoraA, direccionIP, Detalles`

Trait signature: `registrarAuditoria(string $tablaNombre, int $registroId, string $accion, ?string $campo, ?string $valorAnterior, ?string $valorNuevo, ?string $detalles)`

| Acción | Campo/ValorNuevo |
|---|---|
| Crear (I) | `campo=null, valorAnterior=null, valorNuevo=val1\|val2\|...` |
| Actualizar (U) | `campo=c1\|c2, valorAnterior=ant1\|ant2, valorNuevo=nue1\|nue2` |
| Soft delete (U) | `campo=estadoA, valorAnterior=1, valorNuevo=0` |
| Reactivar (U) | `campo=estadoA, valorAnterior=0, valorNuevo=1` |

Agrupar múltiples campos modificados en **1 sola fila** separados por `|`.

## Available global components

| Component | Location | Usage |
|---|---|---|
| `AppHeader` | `components/AppHeader.vue` | Rendered by `App.vue`. Nav menu, role badge, logout confirmation, change password modal |
| `CambiarContrasenaModal` | `components/CambiarContrasenaModal.vue` | Import with `v-if`, emits `@close` |
| `ConfirmarCerrarSesion` | `components/ConfirmarCerrarSesion.vue` | Emits `@confirmar` and `@cancelar` |
| `ProgramacionModal` | `views/usuarios/ProgramacionModal.vue` | Weekly schedule grid Lun–Sáb. Emits `@cerrar` and `@guardado` |

## Conventions

- **Indentation:** PHP = 4 spaces, JS/Vue = 2 spaces
- **Line endings:** LF, UTF-8, trailing whitespace trimmed, final newline
- **Node engine:** `^22.18.0 \|\| >=24.12.0`
- **DB tables:** Always prefixed `T` (e.g., `TUsuarios`, `TEmpleados`)
- **API response format:** Wrap collections in singular keys: `{ "usuarios": [...] }`
- **Global styles:** Put in `App.vue` `<style>` (NOT scoped). `body { font: Inter; background: #F5F4F0; }`

## Design System

- **Colors:** Primary `#042D29` / Secondary `#929079` / Accent `#741102` / White `#FFFFFF` / Page bg `#F5F4F0`
- **Font:** Inter (Google Fonts via `index.html`)
- **Cards:** White bg, 16px radius, soft shadow, 4px top border gradient `#042D29`→`#741102`
- **Buttons:** 10px radius, 14px padding, bg `#042D29`, hover `#052E2A`, active `#741102`. GSAP hover: `scale(1.02)` + `y(-2)`
- **Inputs:** 1.5px border `#D1D5DB`, 10px radius, 12px padding, 40px left padding when icon present, focus `#042D29` + glow
- **Modals:** Overlay `rgba(0,0,0,0.4)`, card 14px radius, max-width 600px
- **Icons:** SVG inline, 20px for inputs, 16px for errors
- **Layout:** `max-width 1100px`, centered, `padding: 32px`
- No external UI libraries — pure Vue + CSS + GSAP

# Módulo 1 — Seguridad y Usuarios (Avance Boceto)

## Backend (Laravel 12)

### Modelos
- `app/Models/User.php` — `TUsuarios`, `getAuthPassword()` retorna `password_hash`, relaciones `rol()` y `empleado()`
- `app/Models/Rol.php` — `TRoles`, scope `activos()`
- `app/Models/Persona.php` — `TPersonas`
- `app/Models/Empleado.php` — `TEmpleados`

### Trait
- `app/Traits/AuditoriaTrait.php` — método `registrarAuditoria()` que inserta en `TAuditoriaGeneral` (TablaNombre, RegistroId, Accion, Campo, ValorAnterior, ValorNuevo, usuarioA, fechaHoraA, direccionIP, Detalles)

### Controladores
- `app/Http/Controllers/AuthController.php` — `login()`, `logout()`, `me()`. Usa `AuditoriaTrait`
- `app/Http/Controllers/UsuarioController.php` — CRUD completo en transacción (Persona + Empleado + Usuario):
  - `index()` — lista usuarios con `estadoA = true`
  - `store()` — crea persona, empleado, usuario. Hashea password. Logs por cada tabla
  - `show()` — detalle de usuario
  - `update()` — actualiza campos, log por campo modificado
  - `destroy()` — soft delete (`estadoA = 0`), protege admin (id_usuario = 1)
  - `roles()` — lista roles activos para selects

### Rutas (`routes/web.php`)
```
POST   /login                    (guest)
POST   /logout                   (auth)
GET    /me                       (auth)
GET    /roles                    (auth)
GET    /usuarios                 (auth)
POST   /usuarios                 (auth)
GET    /usuarios/{id}            (auth)
PUT    /usuarios/{id}            (auth)
DELETE /usuarios/{id}            (auth)
```

### Migración
- `database/migrations/2026_06_18_000001_hash_passwords_in_tusuarios.php` — hashea passwords existentes con bcrypt

### Config
- `config/cors.php` — `allowed_origins` desde `FRONTEND_URL`, `supports_credentials: true`
- `config/sanctum.php` — stateful domains incluye `localhost:5173`

## Frontend (Vue 3 + Pinia + Vue Router)

### Servicios
- `src/services/api.js` — axios con `withCredentials: true` y `withXSRFToken: true`

### Stores
- `src/stores/auth.js` — `user`, `isAuthenticated`, `login()`, `logout()`, `fetchUser()`
- `src/stores/usuarios.js` — `usuarios`, `roles`, `listar()`, `crear()`, `actualizar()`, `eliminar()`, `obtenerRoles()`

### Vistas
- `src/views/auth/LoginView.vue` — formulario de inicio de sesión (username + password), carga/error, redirige a dashboard
- `src/views/DashboardView.vue` — navbar con usuario y cerrar sesión, grilla de módulos, enlace a Gestión de Usuarios
- `src/views/usuarios/UsuariosView.vue` — tabla con todos los usuarios activos, columnas: # / Empleado / Usuario / Rol / Teléfono / Acciones
- `src/views/usuarios/UsuarioFormModal.vue` — modal crear/editar con campos: CI, Primer Nombre, Segundo Nombre, Apellido Paterno, Apellido Materno, Fecha de Nacimiento, Teléfono, Cargo, Usuario, Contraseña, Rol
- `src/views/usuarios/ConfirmarEliminacion.vue` — modal de confirmación con "¿Está seguro de desactivar a {nombre}?"

### Router
- `src/router/index.js` — rutas `/login` (guest), `/` (dashboard), `/usuarios` (CRUD). Navigation guard que redirige a login si no autenticado

## Funcionalidades clave
- Login con sesión vía Sanctum SPA (cookies, no tokens)
- Auditoría obligatoria en cada acción (creación, modificación, desactivación, inicio/cierre de sesión)
- Soft delete con `estadoA`, el administrador principal (id=1) no se puede desactivar
- Transacciones DB al crear/actualizar usuarios (Persona + Empleado + Usuario en una sola operación)
- Nombres amigables en toda la interfaz (sin exponer nombres técnicos de tablas/columnas)
- Admin por defecto: `admin` / `admin123`

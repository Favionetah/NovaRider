# NovaRider — División de Trabajo

## 👤 Persona 1: Módulo Seguridad y Usuarios (Módulo Base)

**Responsable de:**
- Login y autenticación
- Roles y permisos
- Gestión de usuarios (Administrador, Cajero, Mecánicos, Recepcionista)
- Control de acceso por rol

**Tablas principales:**
- `TPersonas` — Datos personales de cualquier persona registrada (clientes, empleados)
- `TEmpleados` — Empleados del taller (vinculados a una persona)
- `TUsuarios` — Usuarios del sistema con credenciales de acceso
- `TRoles` — Roles del sistema (Administrador, Cajero, Mecánico, Recepcionista)

**Tabla maestra del desarrollador (no visible al cliente):**
- `TAuditoriaGeneral` — Registro de todas las acciones (I/U/D) sobre cualquier tabla del sistema; permite rastrear qué usuario hizo qué cambio, cuándo y desde qué IP.

# NovaRider — MÓDULO 5: Gestión de Caja y Control de Recibos

Este módulo gestiona el flujo de dinero en efectivo en el taller, permitiendo el control de la jornada financiera diaria, la apertura y cierre de caja, el registro de transacciones y la visualización del historial de ventas de cara al usuario.

## Funcionalidades Principales

* **Control de Apertura de Caja:** Inicialización de la caja diaria con montos específicos controlados bajo flujos asíncronos y validación de estado activo.
* **Historial de Ventas Dinámico:** Renderizado interactivo del flujo de transacciones en tiempo real consumiendo los registros directamente del servidor.
* **Vinculación Dinámica de Clientes:** Mapeo real de los identificadores de clientes (`id_cliente`) asociados a cada venta, eliminando registros estáticos o genéricos.
* **Flexibilidad en Transacciones:** Tratamiento condicional para ventas directas de mostrador que no requieren registrar una placa de motocicleta obligatoriamente.
* **Respaldo de Métodos de Pago:** Sistema de validación lógica que define por defecto el pago en efectivo ante la ausencia de especificación en la base de datos.

---

## Arquitectura Técnica

### Backend (Laravel)

* **Controladores:**
  * `CajaController.php`: Gestión de solicitudes HTTP, respuestas estructuradas en formato JSON, control de excepciones `try-catch` y manejo de la lógica de negocio de la caja.
* **Base de Datos:**
  * Uso de fachadas integradas (`use Illuminate\Support\Facades\DB;`) para la persistencia de datos en el esquema financiero de la tabla `tventas`.

### Frontend (Vue.js)

* **Vistas y Componentes:**
  * `CajaView.vue`: Interfaz de usuario interactiva montada sobre funciones asíncronas (`async/await`) para la carga pesada del historial de movimientos (`cargarHistorialDesdeBD`) sin bloquear la experiencia de navegación.

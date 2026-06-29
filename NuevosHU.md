# Nuevas Historias de Usuario — Implementaciones Adicionales

Historias de usuario implementadas que **no estaban cubiertas** en el archivo original `HistoriasDeUsuario.md`.

---

## MÓDULO: Seguridad y Usuarios

### HU-N01: CRUD Completo de Usuarios
**Historia de Usuario:** Como administrador, quiero gestionar (crear, editar, desactivar y reactivar) los usuarios del sistema, para administrar el personal que accede a la plataforma.

**Descripción:** El sistema cuenta con un módulo completo de gestión de usuarios que permite registrar un empleado junto con su usuario y contraseña en una sola transacción. Incluye validaciones de CI única, teléfono de 8 dígitos, nombre mínimo de 2 caracteres y asignación de múltiples roles mediante una tabla pivote (TUsuarioRol). Se protege al administrador principal (id_usuario=1) de ser desactivado.

**Criterios de Aceptación:**
- **Escenario 1 - Creación exitosa:** Dado que el administrador completa todos los campos obligatorios con datos válidos, cuando guarda el formulario, entonces el sistema crea el empleado y el usuario en una transacción, hashea la contraseña, asigna los roles seleccionados y registra la auditoría.
- **Escenario 2 - CI duplicada:** Dado que se intenta registrar un empleado con una cédula de identidad ya existente, cuando el administrador intenta guardar, entonces el sistema rechaza el registro y muestra un error de validación.
- **Escenario 3 - Desactivación protegida:** Dado que se intenta desactivar al administrador principal (id_usuario=1), cuando se ejecuta la acción, entonces el sistema deniega la operación con un mensaje de error.
- **Escenario 4 - Reactivación:** Dado que un usuario está desactivado, cuando el administrador ejecuta la reactivación, entonces el sistema restaura su estado a activo y registra la auditoría.

**Prioridad:** 1 - Alta
**Módulo:** Seguridad y Usuarios
**Archivos clave:** `UsuarioController.php` (store, update, destroy, reactivar), `User.php` (TUsuarioRol pivot), `UsuariosView.vue`, `UsuarioFormModal.vue`

---

### HU-N02: Auditoría Automática de Operaciones
**Historia de Usuario:** Como administrador, quiero que cada acción realizada en el sistema quede registrada automáticamente en un historial de auditoría, para tener trazabilidad completa de quién hizo qué y cuándo.

**Descripción:** El sistema utiliza un Trait `AuditoriaTrait` que registra automáticamente en la tabla `TAuditoriaGeneral` todas las operaciones de creación (I), actualización (U) y soft delete sobre cualquier tabla del sistema. Cada registro incluye: nombre de la tabla, ID del registro, acción, campos modificados (separados por `|`), valores anterior y nuevo, usuario responsable, fecha/hora y dirección IP.

**Criterios de Aceptación:**
- **Escenario 1 - Auditoría de creación:** Dado que se crea un nuevo registro en cualquier tabla del sistema, cuando la operación se completa exitosamente, entonces el sistema inserta una fila en TAuditoriaGeneral con Accion='I' y los valores creados concatenados en ValorNuevo.
- **Escenario 2 - Auditoría de actualización:** Dado que se modifican campos de un registro existente, cuando la actualización se guarda, entonces el sistema registra una sola fila con Accion='U' y los campos/cambios agrupados con `|`.
- **Escenario 3 - Auditoría de desactivación:** Dado que se desactiva un registro (soft delete), cuando la operación se ejecuta, entonces el sistema registra Accion='U' con campo='estadoA', valorAnterior='1', valorNuevo='0'.

**Prioridad:** 1 - Alta
**Módulo:** Seguridad y Usuarios
**Archivos clave:** `AuditoriaTrait.php`, `TAuditoriaGeneral` (migración)

---

### HU-N03: Soft Delete y Protección del Administrador Principal
**Historia de Usuario:** Como administrador, quiero que los registros eliminados solo se marquen como inactivos sin borrarse físicamente, y que el administrador principal no pueda ser desactivado, para preservar la integridad de los datos y asegurar que siempre haya un superadmin disponible.

**Descripción:** Todas las tablas del sistema utilizan la columna `estadoA` (boolean) para soft delete. Ningún registro se elimina físicamente de la base de datos. El administrador con id_usuario=1 está protegido contra desactivación. Además, existe un endpoint de reactivación para restaurar usuarios eliminados.

**Criterios de Aceptación:**
- **Escenario 1 - Soft delete exitoso:** Dado que se elimina un registro, cuando la operación se confirma, entonces el sistema cambia estadoA a false pero conserva el registro en la base de datos.
- **Escenario 2 - Protección de admin principal:** Dado que se intenta desactivar al usuario con id_usuario=1, cuando se ejecuta la acción, entonces el sistema responde con error 403 y el mensaje "No se puede desactivar al administrador principal".
- **Escenario 3 - Reactivación:** Dado que un usuario está con estadoA=false, cuando el administrador ejecuta la reactivación, entonces el sistema restaura estadoA a true y registra la auditoría.

**Prioridad:** 1 - Alta
**Módulo:** Seguridad y Usuarios
**Archivos clave:** `UsuarioController.php` (destroy, reactivar)

---

## MÓDULO: Vehículos, Clientes e Historial

### HU-N04: Dashboard de Métricas Clave del Sistema
**Historia de Usuario:** Como administrador, quiero visualizar un dashboard con indicadores clave del negocio (cantidad de clientes, motocicletas, usuarios, productos, ventas del mes, ingresos acumulados y stock crítico), para tener una visión rápida del estado general del taller.

**Descripción:** El sistema cuenta con un endpoint `/reportes/stats` que devuelve métricas consolidadas de todas las áreas. El frontend consume estos datos para mostrar cards informativos en el centro de reportes.

**Criterios de Aceptación:**
- **Escenario 1 - Visualización de KPIs:** Dado que el administrador accede al centro de reportes, cuando el sistema carga la vista, entonces muestra cards con los indicadores numéricos de clientes, motocicletas, usuarios activos, productos, ventas del mes, ingresos acumulados y productos con stock crítico.
- **Escenario 2 - Datos actualizados:** Dado que existen registros en la base de datos, cuando el dashboard se carga, entonces los números reflejan el estado actual de la información.

**Prioridad:** 2 - Media
**Módulo:** Vehículos, Clientes e Historial (Reportes)
**Archivos clave:** `ReporteController.php` (systemStats), `ReportesView.vue`

---

### HU-N05: Acceso Directo a Reportes desde Módulos
**Historia de Usuario:** Como administrador, quiero acceder al centro de reportes directamente desde los módulos de Clientes y Motocicletas, para consultar información sin tener que navegar manualmente entre secciones.

**Descripción:** Los módulos de Clientes y Motocicletas incluyen un botón "Reportes" que redirige al centro de reportes global con el contexto del módulo actual pre-seleccionado.

**Criterios de Aceptación:**
- **Escenario 1 - Navegación directa:** Dado que el usuario está en el módulo de Clientes o Motocicletas, cuando hace clic en el botón "Reportes", entonces el sistema lo redirige al centro de reportes con el tipo de reporte correspondiente pre-seleccionado.

**Prioridad:** 2 - Media
**Módulo:** Vehículos, Clientes e Historial (Reportes)
**Archivos clave:** `ClientesView.vue`, `MotocicletasView.vue`, `ReportesView.vue`

---

## MÓDULO: Taller y Certificación de Calidad

### HU-N06: Control de Kilometraje en Lista de Verificación
**Historia de Usuario:** Como mecánico, quiero registrar el kilometraje actual de la motocicleta como parte de la lista de verificación de calidad, para tener un registro del odómetro al momento de la entrega.

**Descripción:** El formulario de lista de verificación (`TListasVerificacion`) incluye un campo obligatorio de kilometraje junto con los ítems de revisión (frenos, luces, piezas ajustadas, prueba de ruta). El kilometraje es requerido para poder certificar la orden como "Listo para entrega".

**Criterios de Aceptación:**
- **Escenario 1 - Checklist con kilometraje:** Dado que el mecánico completa la lista de verificación, cuando ingresa el kilometraje y todos los ítems están aprobados, entonces el sistema guarda la verificación y cambia el estado de la orden a "Listo para entrega".
- **Escenario 2 - Kilometraje requerido:** Dado que el mecánico intenta guardar la lista de verificación sin ingresar el kilometraje, cuando envía el formulario, entonces el sistema muestra un error de validación y no permite completar la certificación.

**Prioridad:** 1 - Alta
**Módulo:** Taller y Certificación de Calidad
**Archivos clave:** `OrdenController.php` (guardarListaVerificacion), `ChecklistCalidad.vue`

---

### HU-N07: Exportación de Órdenes de Trabajo a PDF
**Historia de Usuario:** Como administrador o mecánico, quiero exportar las órdenes de trabajo a PDF con filtros por estado, mecánico asignado y búsqueda por placa o cliente, para generar reportes profesionales imprimibles.

**Descripción:** El sistema permite generar un PDF profesional de las órdenes de trabajo aplicando filtros dinámicos. El PDF incluye logo de la empresa, datos de la orden, filtros aplicados y detalle de cada orden. Puede descargarse o previsualizarse.

**Criterios de Aceptación:**
- **Escenario 1 - Exportación con filtros:** Dado que el usuario selecciona filtros (estado, mecánico, búsqueda), cuando genera el PDF, entonces el sistema produce un documento con las órdenes filtradas, logo corporativo y encabezados profesionales.
- **Escenario 2 - Previsualización:** Dado que el usuario selecciona la opción de previsualizar, cuando genera el PDF, entonces el sistema muestra el documento en el navegador en lugar de descargarlo.
- **Escenario 2 - Reporte de usuarios en PDF:** Dado que el administrador accede al módulo de usuarios, cuando selecciona generar reporte PDF, entonces el sistema produce un documento con la lista de usuarios filtrados por nombre, rol y estado.

**Prioridad:** 2 - Media
**Módulo:** Taller y Certificación de Calidad
**Archivos clave:** `OrdenController.php` (reportePdf), `ordenes_pdf.blade.php`

---

## MÓDULO: Caja y Flujos Financieros

### HU-N08: Apertura de Caja con Monto Inicial
**Historia de Usuario:** Como cajero o administrador, quiero abrir la caja registradora al inicio de la jornada ingresando un monto inicial en efectivo, para habilitar el módulo de ventas y llevar un control del saldo de apertura.

**Descripción:** El sistema presenta una pantalla de apertura de jornada donde el usuario debe ingresar el monto inicial antes de acceder a las funcionalidades de ventas. Sin apertura, el módulo permanece bloqueado con los campos desenfocados.

**Criterios de Aceptación:**
- **Escenario 1 - Apertura exitosa:** Dado que el cajero ingresa un monto inicial válido, cuando confirma la apertura, entonces el sistema inicializa la caja en estado "ABIERTA" y habilita todas las funcionalidades del módulo de ventas.
- **Escenario 2 - Módulo bloqueado:** Dado que la caja no ha sido abierta, cuando el usuario accede al módulo de caja, entonces el sistema muestra únicamente la pantalla de apertura y el resto del contenido aparece desenfocado e inaccesible.

**Prioridad:** 1 - Alta
**Módulo:** Caja y Flujos Financieros
**Archivos clave:** `CajaController.php` (abrirCaja), `CajaView.vue`

---

### HU-N09: Carrito de Ventas
**Historia de Usuario:** Como cajero, quiero agregar productos y servicios a un carrito de ventas antes de generar el comprobante, para revisar y confirmar los items antes de finalizar la transacción.

**Descripción:** El sistema incluye un carrito de compras interactivo donde se pueden agregar items (servicios y productos), visualizar el subtotal, descuento y total, y gestionar las cantidades antes de confirmar la venta.

**Criterios de Aceptación:**
- **Escenario 1 - Agregar items al carrito:** Dado que el cajero selecciona productos o servicios, cuando los agrega al carrito, entonces el sistema calcula automáticamente los subtotales y muestra el resumen de la venta.
- **Escenario 2 - Gestión del carrito:** Dado que hay items en el carrito, cuando el cajero modifica cantidades o elimina items, entonces el sistema actualiza los totales en tiempo real.
- **Escenario 3 - Confirmación de venta:** Dado que el carrito contiene los items correctos, cuando el cajero confirma la venta, entonces el sistema registra la transacción, genera el número de recibo y actualiza el stock.

**Prioridad:** 1 - Alta
**Módulo:** Caja y Flujos Financieros
**Archivos clave:** `CajaView.vue` (carrito, agregar items), `CajaController.php` (crearRecibo)

---

### HU-N10: Ventas Rápidas de Mostrador
**Historia de Usuario:** Como cajero, quiero realizar ventas rápidas de repuestos o accesorios sin necesidad de asociar una placa o motocicleta específica, para agilizar la atención de clientes que solo compran productos de mostrador.

**Descripción:** El sistema permite registrar ventas que no requieren estar vinculadas a una ficha de vehículo. La placa se configura como un campo dinámico opcional (string vacío en lugar de "S/P"), discriminando entre servicios técnicos de taller con vehículo asignado y compras directas de mostrador.

**Criterios de Aceptación:**
- **Escenario 1 - Venta sin placa:** Dado que un cliente compra un repuesto directamente en mostrador, cuando el cajero registra la venta, entonces el sistema procesa la transacción sin requerir una placa o motocicleta asociada.
- **Escenario 2 - Visualización correcta:** Dado que existen ventas con y sin placa, cuando se consulta el historial, entonces el sistema muestra correctamente los datos de cada tipo sin etiquetas genéricas como "S/P".

**Prioridad:** 1 - Alta
**Módulo:** Caja y Flujos Financieros
**Archivos clave:** `CajaView.vue`, `CajaController.php` (crearRecibo)

---

### HU-N11: Reportes por Mes/Año en Módulo de Caja
**Historia de Usuario:** Como cajero o administrador, quiero visualizar reportes agregados de ventas por mes y año directamente en el módulo de caja, para consultar el rendimiento financiero sin salir de la sección.

**Descripción:** El módulo de caja incluye una pestaña de "Reportes por Mes/Año" que muestra el acumulado del mes actual y del año actual con montos totales y cantidad de órdenes liquidadas. También incluye una tabla de rendimiento mensual con el resumen histórico del año.

**Criterios de Aceptación:**
- **Escenario 1 - Reporte mensual:** Dado que existen ventas registradas en el mes actual, cuando el usuario accede a la pestaña de reportes, entonces el sistema muestra el total recaudado y la cantidad de órdenes liquidadas en el mes.
- **Escenario 2 - Reporte anual:** Dado que existen ventas registradas en el año actual, cuando el usuario accede a la pestaña de reportes, entonces el sistema muestra el acumulado anual y una tabla desglosada por mes.

**Prioridad:** 2 - Media
**Módulo:** Caja y Flujos Financieros
**Archivos clave:** `CajaView.vue` (reportes por mes/año)

---

### HU-N12: Identificación Real del Cliente y Método de Pago en Ventas
**Historia de Usuario:** Como cajero, quiero que las ventas registren el identificador real del cliente y el método de pago utilizado, para tener un historial de transacciones fiable y evitable datos genéricos.

**Descripción:** El sistema reemplazó el texto fijo "Cliente General" por el identificador real del cliente extraído de la base de datos. Además, cada venta registra el método de pago (Efectivo, QR) con un valor por defecto dinámico.

**Criterios de Aceptación:**
- **Escenario 1 - Cliente real:** Dado que una venta está asociada a un cliente registrado, cuando se consulta el historial de ventas, entonces el sistema muestra el nombre o identificador real del cliente en lugar de un texto genérico.
- **Escenario 2 - Método de pago:** Dado que se registra una venta con un método de pago específico, cuando se consulta la transacción, entonces el sistema muestra el método de pago correcto con respaldo a "Efectivo" si no se especificó.

**Prioridad:** 2 - Media
**Módulo:** Caja y Flujos Financieros
**Archivos clave:** `CajaView.vue`, `CajaController.php` (crearRecibo)

HISTORIAS DE USUARIO
MÓDULO DE SEGURIDAD
HU-01: Inicio de Sesión
Historia de Usuario: Como empleado, quiero ingresar al sistema con mis credenciales únicas, para acceder de forma segura a la plataforma.
Descripción: El sistema debe contar con una pantalla de inicio de sesión que solicite un identificador único (usuario) y una contraseña. El acceso estará restringido a empleados registrados en la plataforma.
Criterios de Aceptación:
Escenario 1 - Inicio de sesión exitoso: Dado que el empleado posee credenciales válidas registradas en el sistema, cuando ingresa su identificador y contraseña correctamente, entonces el sistema le permite el acceso y redirige al panel principal.
Escenario 2 - Credenciales incorrectas: Dado que el empleado ingresa un identificador o contraseña incorrectos, cuando intenta iniciar sesión, entonces el sistema muestra un mensaje de error y niega el acceso.
Prioridad: 1 - Alta
Módulo: Seguridad / Autenticación
HU-02: Control de Accesos
Historia de Usuario: Como administrador, quiero que cada empleado acceda únicamente a las funciones permitidas según su cargo, para garantizar la seguridad y el control de acceso dentro del sistema.
Descripción: El sistema debe identificar el cargo o rol del empleado después de la autenticación y mostrar únicamente los módulos, opciones y acciones que le correspondan. Esto garantiza que cada usuario acceda solo a la información y funcionalidades autorizadas para su puesto de trabajo.
Criterios de Aceptación:
Escenario 1 - Rol de administrador: Dado que el usuario autenticado tiene rol de administrador, cuando accede al sistema, entonces visualiza todos los módulos disponibles, incluidos los financieros, inventario, ventas, compras y personal.
Escenario 2 - Rol de mecánico: Dado que el usuario autenticado tiene rol de mecánico, cuando accede al sistema, entonces solo visualiza los módulos relacionados con órdenes de trabajo, seguimiento de reparaciones e historial de servicios.
Escenario 3 - Rol de cajero: Dado que el usuario autenticado tiene rol de cajero, cuando accede al sistema, entonces solo visualiza los módulos de ventas, cobros y consultas autorizadas.
Prioridad: 1 - Alta
Módulo: Seguridad / Control de Accesos
HU-03: Cambiar Contraseña
Historia de Usuario: Como empleado, quiero cambiar mi contraseña, para mantener segura mi cuenta.
Descripción: El sistema debe permitir que los empleados autenticados cambien su contraseña ingresando su contraseña actual y una nueva contraseña que cumpla con las políticas de seguridad establecidas.
Criterios de Aceptación:
Escenario 1 - Cambio exitoso de contraseña: Dado que el empleado conoce su contraseña actual, cuando ingresa correctamente la contraseña actual y una nueva contraseña válida, entonces el sistema actualiza la contraseña y confirma el cambio realizado.
Escenario 2 - Contraseña actual incorrecta: Dado que el empleado ingresa una contraseña actual incorrecta, cuando intenta guardar los cambios, entonces el sistema muestra un mensaje de error y no actualiza la contraseña.
Prioridad: 2 - Media
Módulo: Seguridad / Control de Accesos
MÓDULO DE REGISTRO DE VEHÍCULOS
HU-04: Registrar Cliente Nuevo
Historia de Usuario: Como recepcionista, quiero registrar la información de un cliente nuevo, para tenerlo vinculado en el sistema y asociarle sus vehículos.
Descripción: El sistema debe proveer un formulario para capturar los datos básicos del cliente: nombre, documento de identidad, teléfono y dirección.
Criterios de Aceptación:
Escenario 1 - Registro exitoso: Dado que el recepcionista completa todos los campos obligatorios del formulario, cuando guarda la información, entonces el sistema crea el cliente y lo hace disponible para asociarle vehículos.
Escenario 2 - Datos incompletos: Dado que el recepcionista deja campos obligatorios vacíos, cuando intenta guardar, entonces el sistema muestra un mensaje indicando los campos requeridos y no guarda el registro.
Prioridad: 1 - Alta
Módulo: Registro de Vehículos
HU-05: Crear Ficha Técnica de Vehículo
Historia de Usuario: Como recepcionista, quiero crear una ficha técnica para el vehículo de un cliente, para asociar correctamente la motocicleta a su propietario.
Descripción: El sistema debe permitir registrar los datos técnicos del vehículo: placa, marca, modelo, año, color y número de chasis. La ficha técnica debe quedar vinculada al cliente previamente registrado.
Criterios de Aceptación:
Escenario 1 - Creación de ficha técnica exitosa: Dado que el cliente existe en el sistema y el recepcionista completa los datos del vehículo, cuando guarda la ficha técnica, entonces el sistema registra el vehículo y lo asocia al cliente.
Escenario 2 - Placa duplicada: Dado que se intenta registrar una placa que ya existe en el sistema, cuando el recepcionista guarda la ficha, entonces el sistema advierte la duplicidad y no genera un nuevo registro.
Prioridad: 1 - Alta
Módulo: Registro de Vehículos
HU-06: Buscar Vehículo y Propietario
Historia de Usuario: Como recepcionista o mecánico, quiero buscar un vehículo y su propietario mediante una barra de búsqueda, para encontrar rápidamente la información requerida.
Descripción: El sistema debe ofrecer una barra de búsqueda que permita localizar clientes y vehículos por nombre, placa u otros criterios relevantes.
Criterios de Aceptación:
Escenario 1 - Búsqueda con resultado: Dado que el usuario ingresa una placa o nombre existente en el sistema, cuando ejecuta la búsqueda, entonces el sistema muestra el vehículo y los datos del cliente asociado.
Escenario 2 - Sin resultados: Dado que el criterio de búsqueda no coincide con ningún registro, cuando ejecuta la búsqueda, entonces el sistema muestra un mensaje indicando que no se encontraron resultados.
Prioridad: 2 - Media
Módulo: Registro de Vehículos
HU-07: Modificar Datos de Cliente
Historia de Usuario: Como recepcionista, quiero modificar los datos de contacto de un cliente, para mantener la información actualizada.
Descripción: El sistema debe permitir editar información previamente registrada de un cliente, como teléfono, dirección y correo electrónico, conservando su historial de servicios y vehículos asociados.
Criterios de Aceptación:
Escenario 1 - Actualización exitosa: Dado que el cliente existe en el sistema, cuando el recepcionista modifica los datos y guarda los cambios, entonces el sistema actualiza la información del cliente correctamente.
Escenario 2 - Datos inválidos: Dado que el recepcionista ingresa información incorrecta o incompleta, cuando intenta guardar los cambios, entonces el sistema muestra un mensaje indicando el error y no actualiza el registro.
Prioridad: 2 - Media
Módulo: Registro de Vehículos
HU-08: Actualizar Datos de Motocicleta
Historia de Usuario: Como recepcionista, quiero actualizar los datos de una motocicleta registrada, para reflejar cambios o correcciones.
Descripción: El sistema debe permitir modificar la información de una motocicleta registrada, incluyendo marca, modelo, color y otros datos técnicos, manteniendo la asociación con su propietario.
Criterios de Aceptación:
Escenario 1 - Actualización exitosa de motocicleta: Dado que la motocicleta existe en el sistema, cuando el recepcionista modifica los datos y guarda los cambios, entonces el sistema actualiza correctamente la ficha técnica.
Escenario 2 - Vehículo inexistente: Dado que el vehículo no se encuentra registrado, cuando el usuario intenta actualizar la información, entonces el sistema muestra un mensaje indicando que el registro no existe.
Prioridad: 2 - Alta * (nota: el documento indica "Prioridad 2 - Alta")
Módulo: Registro de Vehículos
MÓDULO DE SEGUIMIENTO DE REPARACIÓN
HU-09: Registrar Orden de Trabajo
Historia de Usuario: Como administrador, quiero registrar una orden de trabajo para un vehículo, para asignar el tipo de servicio y el mecánico responsable.
Descripción: Al ingresar un vehículo al taller, el sistema permite abrir una orden de trabajo especificando el servicio solicitado y el mecánico asignado.
Criterios de Aceptación:
Escenario 1 - Registro exitoso de orden: Dado que el vehículo está registrado y se selecciona el tipo de servicio y mecánico, cuando el administrador guarda la orden, entonces el sistema genera la orden de trabajo con estado 'En proceso'.
Escenario 2 - Sin mecánico asignado: Dado que el administrador no selecciona un mecánico, cuando intenta guardar la orden, entonces el sistema muestra un error e impide guardar hasta que se asigne un responsable.
Prioridad: 1 - Alta
Módulo: Seguimiento de Reparación
HU-10: Generar Código QR y Código Único
Historia de Usuario: Como sistema, quiero generar un código QR y un código único al registrar el ingreso del vehículo, para que el recibo de recepción sea imprimible y trazable.
Descripción: Al crear la orden de trabajo, el sistema genera automáticamente un código único de orden y un QR que puede ser impreso en el recibo de recepción.
Criterios de Aceptación:
Escenario 1 - Generación automática: Dado que se guarda exitosamente una orden de trabajo, cuando el sistema la registra, entonces genera y muestra un código QR y un código único vinculados a la orden.
Escenario 2 - Impresión del recibo: Dado que la orden fue generada con su QR, cuando el recepcionista selecciona imprimir el recibo, entonces el sistema produce un documento con el QR y el código único imprimibles.
Prioridad: 1 - Alta
Módulo: Seguimiento de Reparación
HU-11: Consulta Pública de Estado para Clientes
Historia de Usuario: Como cliente, quiero consultar el estado de mi vehículo con mi código o QR sin necesidad de autenticarme, para conocer el avance del servicio en tiempo real.
Descripción: El sistema provee una interfaz pública accesible mediante el código único o el QR entregado al cliente, sin requerir credenciales de acceso.
Criterios de Aceptación:
Escenario 1 - Consulta exitosa con código: Dado que el cliente posee el código único de su orden, cuando lo ingresa en la interfaz pública, entonces el sistema muestra el estado actual del servicio sin solicitar inicio de sesión.
Escenario 2 - Código inválido: Dado que el cliente ingresa un código que no existe, cuando realiza la consulta, entonces el sistema muestra un mensaje de código no encontrado.
Prioridad: 2 - Media
Módulo: Seguimiento de Reparación
HU-12: Lista de Verificación de Calidad Obligatoria
Historia de Usuario: Como mecánico o administrador, quiero completar una lista de verificación de calidad antes de marcar la orden como 'Listo para entrega', para garantizar que el servicio se realizó correctamente.
Descripción: El sistema obliga al operario a completar un checklist de control de calidad antes de permitir el cambio de estado a 'Listo para entrega'. El checklist valida: servicio realizado según solicitud, piezas instaladas correctamente y pruebas de funcionamiento realizadas.
Criterios de Aceptación:
Escenario 1 - Checklist completo: Dado que todos los ítems del checklist de calidad están marcados como aprobados, cuando el mecánico intenta cambiar el estado de la orden, entonces el sistema permite actualizar el estado a 'Listo para entrega'.
Escenario 2 - Checklist incompleto: Dado que uno o más ítems del checklist no han sido verificados, cuando el mecánico intenta cambiar el estado, entonces el sistema bloquea el cambio y solicita completar la verificación.
Prioridad: 1 - Alta
Módulo: Seguimiento de Reparación
HU-13 y HU-14: Cambiar Estado de Orden de Trabajo (Nota: Aparece duplicada con idéntico texto en las páginas 22 y 23 del documento)
Historia de Usuario: Como mecánico, quiero cambiar el estado de una orden de trabajo, para informar el avance de la reparación.
Descripción: El sistema debe permitir actualizar el estado de una orden de trabajo durante el proceso de reparación. Los estados disponibles serán: Pendiente, En Proceso, Esperando Repuestos, Listo para Entrega y Entregado.
Criterios de Aceptación:
Escenario 1 - Actualización de estado: Dado que el mecánico tiene una orden asignada, cuando selecciona un nuevo estado y guarda los cambios, entonces el sistema actualiza la orden y registra la fecha de modificación.
Escenario 2 - Cambio no autorizado: Dado que un usuario no asignado intenta modificar la orden, cuando intenta actualizar el estado, entonces el sistema deniega la acción y muestra un mensaje de acceso restringido.
Prioridad: 1 - Alta
Módulo: Seguimiento de Reparación
MÓDULO DE HISTORIAL DE SERVICIOS
HU-15: Historial Cronológico por Placa
Historia de Usuario: Como recepcionista o mecánico, quiero visualizar el historial cronológico de órdenes de servicio de un vehículo por placa, para conocer los trabajos realizados anteriormente.
Descripción: El sistema muestra todas las órdenes de servicio entregadas asociadas a un vehículo, ordenadas cronológicamente.
Criterios de Aceptación:
Escenario 1 - Historial con registros: Dado que el vehículo tiene órdenes de servicio previas entregadas, cuando el usuario busca por placa, entonces el sistema muestra el listado cronológico de servicios.
Escenario 2 - Sin historial: Dado que el vehículo no tiene órdenes entregadas anteriormente, cuando se consulta su historial, entonces el sistema indica que no hay registros disponibles.
Prioridad: 2 - Media
Módulo: Historial de Servicios
HU-16: Detalle Completo de Antecedentes Técnicos
Historia de Usuario: Como mecánico o administrador, quiero consultar el detalle completo de trabajos anteriores realizados a una motocicleta, para contar con antecedentes técnicos al atender un nuevo servicio.
Descripción: Al seleccionar una orden del historial, el sistema despliega el detalle completo: tipo de servicio, mecánico responsable, fecha, repuestos usados y observaciones.
Criterios de Aceptación:
Escenario 1 - Consulta de detalle: Dado que el usuario selecciona una orden del historial, cuando accede al detalle, entonces el sistema muestra toda la información técnica registrada en esa orden.
Prioridad: 2 - Media
Módulo: Historial de Servicios
HU-17: Desglose de Repuestos Utilizados
Historia de Usuario: Como mecánico o administrador, quiero ver el desglose de repuestos y accesorios utilizados en cada servicio, para tener un registro preciso de los materiales empleados.
Descripción: Cada registro del historial debe incluir la lista específica de repuestos y accesorios utilizados en ese servicio.
Criterios de Aceptación:
Escenario 1 - Detalle de repuestos: Dado que una orden de servicio registró el uso de repuestos, cuando el usuario consulta el historial de esa orden, entonces el sistema muestra el listado desglosado de repuestos y accesorios utilizados.
Prioridad: 2 - Media
Módulo: Historial de Servicios
MÓDULO DE INVENTARIO
HU-18: Registrar y Actualizar Ficha de Repuesto
Historia de Usuario: Como administrador, quiero registrar y actualizar la ficha de cada repuesto o accesorio, para mantener la información del inventario actualizada y precisa.
Descripción: El sistema permite crear y editar fichas de producto con datos como código, nombre, descripción, precio de venta, costo de adquisición y unidad de medida.
Criterios de Aceptación:
Escenario 1 - Registro exitoso: Dado que el administrador completa todos los campos de la ficha del producto, cuando guarda el registro, entonces el sistema crea la ficha y la disponibiliza en el inventario.
Escenario 2 - Actualización de ficha: Dado que un producto ya existe en el inventario, cuando el administrador edita y guarda los cambios, entonces el sistema actualiza la ficha conservando el historial.
Prioridad: 1 - Alta
Módulo: Inventario
HU-19: Matriz de Compatibilidad de Motocicletas
Historia de Usuario: Como administrador, quiero asociar cada repuesto con los modelos y marcas de motocicletas compatibles, para facilitar la búsqueda de piezas adecuadas durante el servicio.
Descripción: El sistema permite vincular cada repuesto con uno o más modelos y marcas de motocicletas del mercado frecuente mediante una matriz de compatibilidad.
Criterios de Aceptación:
Escenario 1 - Asociación de compatibilidad: Dado que el administrador selecciona un repuesto y marca/modelo de moto compatible, cuando guarda la asociación, entonces el sistema registra la compatibilidad y la muestra al consultar el repuesto.
Prioridad: 2 - Media
Módulo: Inventario
HU-20: Alertas Automáticas de Stock Mínimo
Historia de Usuario: Como administrador, quiero recibir alertas automáticas cuando el stock de un producto alcance o esté por debajo del mínimo configurado, para gestionar la reposición de inventario a tiempo.
Descripción: El sistema monitorea el stock disponible de cada producto y despliega notificaciones en el panel cuando se alcanza o supera el umbral mínimo configurado.
Criterios de Aceptación:
Escenario 1 - Alerta de stock mínimo: Dado que la cantidad disponible de un producto es igual o menor al stock mínimo configurado, cuando el administrador accede al panel, entonces el sistema muestra una notificación de alerta para ese producto.
Escenario 2 - Sin alerta: Dado que el stock de un producto supera el mínimo configurado, cuando el administrador accede al panel, entonces no se genera ninguna alerta para ese producto.
Prioridad: 2 - Media
Módulo: Inventario
HU-21: Diferenciación de Stock Físico y Disponible
Historia de Usuario: Como administrador, quiero que el sistema calcule y diferencie el stock físico del stock disponible, para evitar la venta accidental de unidades reservadas.
Descripción: El sistema ya gestiona dos estados de inventario: Stock Físico (total real) y Stock Disponible (descontando reservas activas).
Criterios de Aceptación:
Escenario 1 - Visualización de estados de stock: Dado que un producto tiene reservas activas, cuando el usuario consulta el inventario, entonces el sistema muestra el stock físico y el stock disponible de forma diferenciada.
Prioridad: 1 - Alta
Módulo: Inventario
HU-22: Gestión de Estantes y Coordenadas de Almacenamiento
Historia de Usuario: Como administrador, quiero un módulo de gestión de estantes para registrar, estructurar y mapear físicamente los muebles, pasillos y secciones del almacén de repuestos sin depender de asistencia técnica externa.
Descripción: El sistema debe permitir el mantenimiento de la infraestructura del almacén (CRUD de estantes) y proveer casillas obligatorias al registrar un producto para indicar su coordenada exacta de almacenamiento para encontrar el repuesto rápido.
Criterios de Aceptación:
Escenario 1 — Creación de nuevas unidades de almacenamiento: Dado que el administrador necesita expandir la capacidad del almacén, cuando accede al panel de "Gestión de Estantes" e introduce un nuevo número de estante (del 1 al 50), define sus secciones (de la A a la D) y guarda los cambios, entonces el sistema almacena las nuevas ubicaciones y las habilita inmediatamente como opciones seleccionables en la ficha del producto.
Escenario 2 — Ubicación exacta obligatoria en la ficha del producto: Dado que un almacenero está registrando o modificando un repuesto en el catálogo, cuando intenta guardar el formulario de registro, entonces el sistema le exige rellenar de forma obligatoria las casillas parametrizadas de: Número de Estante (1-50), Sección (A-D) y Nivel (Alto, Medio, Bajo). Si falta alguna casilla, bloquea el guardado.
Prioridad: 1 - Alta
Módulo: Inventario
(Nota: De la HU-23 a la HU-29, la información no se encuentra detallada de forma extendida en las secciones finales del documento adjunto, pasando directamente a la HU-30 en los datos estructurados).
MÓDULO DE CAJA
HU-30: Arqueo y Cierre de Caja
Historia de Usuario: (Sin texto explícito de la historia, derivado del Requerimiento Funcional asociado)
Descripción: Al cierre del turno, el sistema solicita el conteo físico del efectivo y realiza automáticamente el contraste con los movimientos registrados digitalmente.
Criterios de Aceptación:
Escenario 1 - Cierre de caja sin descuadre: Dado que el conteo físico coincide con el balance digital, cuando el cajero confirma el cierre, entonces el sistema registra el cierre de turno como cuadrado.
Escenario 2 - Descuadre detectado: Dado que hay diferencia entre el conteo físico y el balance digital, cuando el cajero intenta cerrar, entonces el sistema marca el descuadre y exige una observación antes de permitir el cierre.
Prioridad: 1 - Alta
Módulo: Caja
HU-31: Control de Descuadres y Observaciones
Historia de Usuario: Como cajero, quiero registrar una observación detallada cuando exista diferencia entre el arqueo físico y el balance digital, para justificar y documentar el descuadre.
Descripción: Si el cierre de caja detecta una diferencia, el sistema obliga al usuario a ingresar una observación que justifique el descuadre antes de permitir el cierre.
Criterios de Aceptación:
Escenario 1 - Registro de observación obligatoria: Dado que se detectó un descuadre en el cierre de caja, cuando el cajero intenta confirmar el cierre sin observación, entonces el sistema bloquea el cierre y exige el registro de una justificación.
Escenario 2 - Cierre con observación: Dado que el cajero registra una observación que justifica el descuadre, cuando confirma el cierre, entonces el sistema permite finalizar el turno y guarda la observación en el registro.
Prioridad: 1 - Alta
Módulo: Caja
MÓDULO DE REPORTES
HU-32: Filtros Avanzados de Consulta
Historia de Usuario: Como administrador, quiero generar reportes dinámicos aplicando filtros específicos, para consultar información relevante del negocio según el periodo o criterio deseado.
Descripción: El sistema permite generar y visualizar reportes con filtros avanzados como rango de fechas, módulo, empleado u otros criterios configurables.
Criterios de Aceptación:
Escenario 1 - Reporte con filtros aplicados: Dado que el administrador selecciona uno o más filtros disponibles, cuando genera el reporte, entonces el sistema muestra los datos correspondientes al criterio seleccionado.
Prioridad: 2 - Media
Módulo: Reportes
HU-33: Reporte de Inventario
Historia de Usuario: Como administrador, quiero consultar un reporte específico de inventario, para conocer todas las acciones y movimientos realizados sobre el inventario.
Descripción: El sistema provee un reporte de inventario que consolida entradas, salidas, reservas y ajustes de stock por periodo.
Criterios de Aceptación:
Escenario 1 - Reporte de inventario exitoso: Dado que el administrador accede al módulo de reportes y selecciona inventario, cuando aplica los filtros de periodo deseado, entonces el sistema muestra todas las acciones registradas sobre el inventario en ese rango.
Prioridad: 2 - Media
Módulo: Reportes
HU-34: Reporte de Ganancias Estimadas
Historia de Usuario: Como administrador, quiero consultar las ganancias estimadas cruzando el precio de venta con el costo de adquisición, para evaluar la rentabilidad por producto y período.
Descripción: El sistema cruza el precio de venta aplicado y el costo de adquisición registrado en compras para calcular y mostrar la ganancia estimada por producto y periodo.
Criterios de Aceptación:
Escenario 1 - Reporte de ganancias: Dado que existen ventas y costos de adquisición registrados para un periodo, cuando el administrador genera el reporte de ganancias, entonces el sistema muestra la ganancia estimada por producto y el total del periodo.
Prioridad: 2 - Media
Módulo: Reportes
HU-35: Exportar Reportes a PDF
Historia de Usuario: Como administrador, quiero exportar reportes a PDF, para compartir información con la gerencia.
Descripción: El sistema debe permitir generar archivos PDF de los reportes disponibles, conservando filtros, fechas y datos mostrados en pantalla.
Criterios de Aceptación:
Escenario 1 - Exportación exitosa: Dado que el administrador genera un reporte, cuando selecciona la opción exportar a PDF, entonces el sistema crea un archivo PDF descargable con la información consultada.
Escenario 2 - Reporte sin datos: Dado que el reporte seleccionado no contiene registros, cuando intenta exportarlo, entonces el sistema informa que no existen datos disponibles para generar el archivo.
Prioridad: 2 - Media
Módulo: Reportes
MÓDULO DE RESERVAS Y ENVÍOS
HU-36: Bloqueo de Inventario por Reserva
Historia de Usuario: Como cajero o administrador, quiero que al registrar una reserva el sistema aísle los repuestos seleccionados del stock disponible, para evitar que sean vendidos a otro cliente.
Descripción: Al crear una reserva, el sistema descuenta los repuestos del stock disponible, manteniéndolos en stock físico hasta su conversión a venta o cancelación.
Criterios de Aceptación:
Escenario 1 - Bloqueo de stock al reservar: Dado que el cliente solicita una reserva de repuestos con stock disponible, cuando se confirma la reserva, entonces el sistema reduce el stock disponible e indica las unidades como reservadas.
Prioridad: 2 - Media
Módulo: Reservas y Envíos
HU-37: Captura de Datos de Despacho
Historia de Usuario: Como administrador, quiero registrar las solicitudes de clientes de otros departamentos, para gestionar los envíos de repuestos a otras ubicaciones.
Descripción: El sistema permite capturar los datos de despacho de pedidos departamentales: cliente, dirección de entrega, producto y cantidad solicitada.
Criterios de Aceptación:
Escenario 1 - Registro de solicitud de despacho: Dado que un cliente de otro departamento solicita repuestos, cuando el administrador ingresa los datos del pedido, entonces el sistema registra la solicitud y la asocia a los repuestos bloqueados.
Prioridad: 2 - Media
Módulo: Reservas y Envíos
HU-38: Control de Logística de Envíos
Historia de Usuario: Como administrador, quiero registrar los datos del transporte para cada pedido departamental, para controlar la logística de los envíos.
Descripción: El sistema permite asociar a cada pedido de envío los datos del transportista: empresa, número de guía, fecha estimada de entrega.
Criterios de Aceptación:
Escenario 1 - Registro de logística: Dado que existe un pedido departamental pendiente, cuando el administrador registra los datos del transporte, entonces el sistema asocia la información logística al pedido.
Prioridad: 3 - Baja
Módulo: Reservas y Envíos
HU-39: Conversión de Reserva a Venta
Historia de Usuario: Como cajero o administrador, quiero convertir automáticamente una reserva en venta formal cuando el cliente confirme el pago o realice el recojo del repuesto, para cerrar el proceso de reserva eficientemente.
Descripción: El sistema cuenta con una función que transforma la reserva en venta, libera el stock reservado y genera el comprobante correspondiente.
Criterios de Aceptación:
Escenario 1 - Conversión de reserva a venta: Dado que el cliente confirma el pago o realiza el recojo del repuesto, cuando el cajero ejecuta la conversión, entonces el sistema crea la venta formal, actualiza el inventario y genera el comprobante.
Prioridad: 2 - Media
Módulo: Reservas y Envíos
MÓDULO DE COMPRAS
HU-40: Registro de Adquisiciones
Historia de Usuario: Como administrador, quiero registrar las compras de repuestos y accesorios realizadas a proveedores, para mantener un control formal de las adquisiciones.
Descripción: El sistema permite crear registros de compra especificar proveedor, productos adquiridos, cantidades y precios de costo.
Criterios de Aceptación:
Escenario 1 - Registro de compra exitoso: Dado que el administrador ingresa los datos del proveedor y los productos adquiridos, cuando confirma y guarda la compra, entonces el sistema registra la adquisición y queda disponible para incrementar el stock.
Prioridad: 1 - Alta
Módulo: Compras
HU-41: Incremento Automatizado de Stock
Historia de Usuario: Como sistema, quiero incrementar automáticamente el stock físico de los productos al confirmar una compra, para mantener el inventario actualizado sin intervención manual.
Descripción: Al confirmar y guardar un registro de compra, el sistema incrementa de forma automática el stock físico de los productos incluidos en la compra.
Criterios de Aceptación:
Escenario 1 - Incremento de stock automático: Dado que se confirma una compra con repuestos registrados en inventario, cuando el sistema procesa la compra, entonces incrementa el stock físico de cada producto en la cantidad adquirida.
Prioridad: 1 - Alta
Módulo: Compras
HU-42: Base de Costos para Utilidades
Historia de Usuario: Como sistema, quiero almacenar el precio de costo de cada compra de forma histórica y restringida, para utilizarlo como base en el cálculo de ganancias estimadas del módulo de reportes.
Descripción: Los precios de adquisición quedan almacenados de forma histórica, accesibles únicamente por el módulo de reportes y usuarios con permisos de administrador.
Criterios de Aceptación:
Escenario 1 - Almacenamiento restringido: Dado que una compra es registrada con su precio de costo, cuando el sistema la procesa, entonces guarda el precio de costo de forma histórica y lo hace disponible solo para el módulo de reportes y el administrador.
Prioridad: 1 - Alta
Módulo: Compras
MÓDULO DE PERSONAL
HU-43: Asignación de Horarios y Turnos
Historia de Usuario: Como administrador, quiero registrar los horarios y turnos de los empleados, para organizar la distribución del trabajo en la empresa.
Descripción: El sistema permite asignar horarios de trabajo, turnos y días laborables a cada empleado.
Criterios de Aceptación:
Escenario 1 - Asignación de turno: Dado que el administrador selecciona un empleado y define su horario, cuando guarda la asignación, entonces el sistema registra el turno y lo hace visible en el calendario de personal.
Prioridad: 2 - Media
Módulo: Personal
HU-44: Registro de Asistencia
Historia de Usuario: Como administrador, quiero registrar la asistencia de cada empleado, para llevar un control de puntualidad y presencia.
Descripción: El sistema permite registrar las entradas y salidas de los empleados para el control de asistencia diaria.
Criterios de Aceptación:
Escenario 1 - Registro de entrada: Dado que un empleado comienza su turno, cuando el administrador registra su entrada en el sistema, entonces el sistema guarda la hora de ingreso vinculada al empleado y la fecha.
Escenario 2 - Registro de falta: Dado que un empleado no se presenta en su turno asignado, cuando el sistema no registra entrada en el horario establecido, entonces marca la ausencia del empleado en el control de asistencia.
Prioridad: 2 - Media
Módulo: Personal
HU-45: Planilla de Sueldo Base (Restringido)
Historia de Usuario: Como dueño, quiero configurar los datos de contratación y sueldo base de cada empleado, para establecer los parámetros salariales de forma centralizada y restringida.
Descripción: Solo el dueño puede configurar el sueldo base y los datos de contratación de los empleados. Esta información queda restringida para otros roles.
Criterios de Aceptación:
Escenario 1 - Configuración por el dueño: Dado que el usuario autenticado tiene rol de dueño, cuando ingresa y guarda los datos de contratación de un empleado, entonces el sistema registra el sueldo base y lo protege de edición por otros roles.
Escenario 2 - Acceso restringido: Dado que un usuario sin rol de dueño intenta editar el sueldo base, cuando intenta acceder a esa configuración, entonces el sistema deniega el acceso.
Prioridad: 1 - Alta
Módulo: Personal
HU-46: Cálculo de Modificadores de Sueldo
Historia de Usuario: Como administrador, quiero registrar deducciones y adiciones manuales sobre el salario bruto mensual de cada empleado, para reflejar adelantos, descuentos por faltas o bonos en la liquidación.
Descripción: El sistema permite al administrador agregar modificadores salariales (adelantos, descuentos, bonos) sobre el salario bruto de cada empleado para el cálculo de la nómina.
Criterios de Aceptación:
Escenario 1 - Registro de deducción: Dado que un empleado tuvo faltas durante el mes, cuando el administrador registra la deducción correspondiente, entonces el sistema la asocia al empleado y la incluye en el cálculo del sueldo neto.
Escenario 2 - Registro de bono: Dado que el administrador autoriza un bono para un empleado, cuando registra el monto del bono, entonces el sistema lo suma al sueldo bruto en el resumen de liquidación.
Prioridad: 2 - Media
Módulo: Personal
HU-47: Generación de Reporte de Liquidación
Historia de Usuario: Como administrador, quiero que el sistema calcule el sueldo neto de cada empleado y genere un reporte de liquidación mensual, para contar con un resumen consolidado de pagos para el archivo de administración.
Descripción: El sistema procesa el sueldo base, asistencia, deducciones y adiciones de cada empleado para calcular el sueldo neto y generar un reporte mensual de liquidación.
Criterios de Aceptación:
Escenario 1 - Generación de reporte de liquidación: Dado que el mes ha concluido y se han registrado todas las variables salariales, cuando el administrador solicita el reporte de liquidación, entonces el sistema calcula el sueldo neto de cada empleado y genera el reporte mensual consolidado.
Prioridad: 2 - Media
Módulo: Personal
HU-48: Actualizar Estado de Reparación
Historia de Usuario: Como mecánico, quiero actualizar el estado de una orden de trabajo, para informar el avance de la reparación.
Descripción: El sistema permitirá cambiar el estado de una orden entre: Pendiente, En proceso, Esperando repuestos, Listo para entrega y Entregado.
Criterios de Aceptación:
Escenario 1: Dado que el mecánico tiene una orden asignada, cuando actualiza el estado, entonces el sistema registra el cambio y la fecha.
Escenario 2: Dado que el usuario no es el mecánico asignado, cuando intenta modificar la orden, entonces el sistema deniega la acción.
Prioridad: Alta
Módulo: Personal
HU-49: Registro de Repuestos Utilizados
Historia de Usuario: Como mecánico, quiero registrar los repuestos utilizados durante una reparación, para mantener actualizado el historial técnico y el inventario.
Descripción: El sistema permitirá agregar repuestos consumidos a una orden de trabajo.
Criterios de Aceptación:
Escenario 1: Dado que existe stock suficiente, cuando el mecánico registra el repuesto, entonces el sistema descuenta automáticamente la cantidad utilizada.
Escenario 2: Dado que no existe stock suficiente, cuando intenta registrar el repuesto, entonces el sistema muestra una advertencia.
Prioridad: Alta
Módulo: Personal
HU-50: Registro de Proveedores
Historia de Usuario: Como administrador, quiero registrar y administrar proveedores, para asociarlos a las compras de repuestos.
Descripción: El sistema permitirá registrar: Nombre, Teléfono, Dirección y Correo.
Criterios de Aceptación:
Escenario 1: Dado que el administrador completa los datos, cuando guarda el proveedor, entonces el sistema lo registra correctamente.
Prioridad: Media
Módulo: Personal
HU-51: Panel de Control de Órdenes (Dashboard)
Historia de Usuario: Como administrador, quiero visualizar todas las órdenes pendientes y en proceso, para supervisar e identificar los indicadores principales del negocio de forma rápida.

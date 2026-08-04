# INFORME DE AUDITORÍA DE LÓGICA DE NEGOCIO - FASE 3
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Fecha:** 2026-08-04
**Auditor:** Equipo Multidisciplinario de Ingeniería de Software

---

## RESUMEN EJECUTIVO

Se ha completado la auditoría de los procesos de negocio del sistema AutoGest. La lógica de negocio está bien implementada en general, con procesos robustos de autenticación, autorización y gestión de datos. Se identificaron algunos riesgos y áreas de mejora en la integración entre procesos.

---

## 1. PROCESO DE AUTENTICACIÓN

### 1.1 Objetivo
Controlar el acceso al sistema mediante credenciales válidas y gestión de sesiones.

### 1.2 ¿Quién inicia?
Usuario (cliente, mecánico, asesor, administrador)

### 1.3 ¿Qué recibe?
- Email (validado y normalizado a lowercase)
- Password (validado)
- Remember token (opcional)

### 1.4 ¿Qué valida?
- Formato de email válido
- Password requerido
- Credenciales contra base de datos
- Estado de usuario (activo/inactivo)
- Redirección según rol

### 1.5 ¿Qué guarda?
- Registro en ActivityLog (login)
- Actualización de last_login_at
- Regeneración de sesión

### 1.6 ¿Qué actualiza?
- User.last_login_at
- Session (regenerate)

### 1.7 ¿Qué notifica?
- No hay notificaciones automáticas (solo logs)

### 1.8 ¿Qué módulos utiliza?
- AuthController
- User model
- ActivityLog
- Middleware de autenticación

### 1.9 ¿Qué riesgos existen?
- **MEDIO:** No hay límite de intentos de login
- **BAJO:** No hay verificación de email
- **BAJO:** No hay 2FA
- **BAJO:** No hay logout en otros dispositivos

### 1.10 Estado: MUY BUENO
✅ Validaciones robustas
✅ Normalización de email
✅ Verificación de estado activo
✅ Redirección por rol
✅ ActivityLog implementado
✅ Session management correcto

⚠️ Faltan medidas de seguridad adicionales

---

## 2. PROCESO DE GESTIÓN DE USUARIOS

### 2.1 Objetivo
Gestión completa de usuarios del sistema con roles y permisos.

### 2.2 ¿Quién inicia?
Administrador (exclusivo)

### 2.3 ¿Qué recibe?
- Datos personales (name, email, phone)
- Credenciales (password)
- Rol (admin, asesor, mecanico, cliente)
- Estado (activo, inactivo)

### 2.4 ¿Qué valida?
- Email único
- Password según políticas Laravel
- Rol válido según enum
- Estado válido
- Campos requeridos

### 2.5 ¿Qué guarda?
- Usuario en base de datos
- ActivityLog de creación/actualización

### 2.6 ¿Qué actualiza?
- Datos del usuario
- Relaciones si existen

### 2.7 ¿Qué notifica?
- No hay notificaciones automáticas

### 2.8 ¿Qué módulos utiliza?
- UserController
- User model
- UserRole enum
- UserPolicy
- ActivityLog

### 2.9 ¿Qué riesgos existen?
- **BAJO:** Auto-eliminación prevenida
- **BAJO:** Eliminación con relaciones manejada (desactivación)
- **MEDIO:** No hay notificación de cuenta creada

### 2.10 Estado: EXCELENTE
✅ CRUD completo implementado
✅ Validaciones robustas
✅ Protección contra auto-eliminación
✅ Manejo inteligente de relaciones
✅ ActivityLog consistente
✅ Policies implementadas

---

## 3. PROCESO DE GESTIÓN DE VEHÍCULOS

### 3.1 Objetivo
Gestión de vehículos con asignación a clientes y programación automática de mantenimientos.

### 3.2 ¿Quién inicia?
Administrador, Asesor

### 3.3 ¿Qué recibe?
- Datos del vehículo (placa, marca, modelo, año, color)
- Kilometraje actual
- Cliente asociado
- Fechas de vencimiento (seguro, inspección)
- VIN opcional

### 3.4 ¿Qué valida?
- Placa única
- Cliente existe y está activo
- Año dentro de rango válido
- Campos requeridos

### 3.5 ¿Qué guarda?
- Vehículo en base de datos
- MaintenanceSchedule generados automáticamente
- ActivityLog de creación/actualización

### 3.6 ¿Qué actualiza?
- Datos del vehículo
- Estado del vehículo según contexto

### 3.7 ¿Qué notifica?
- No hay notificaciones automáticas
- Podría generar alertas de vencimiento

### 3.8 ¿Qué módulos utiliza?
- VehicleController
- Vehicle model
- VehicleModelTemplate
- MaintenanceSchedule
- ActivityLog
- VehiclePolicy

### 3.9 ¿Qué riesgos existen?
- **BAJO:** Inconsistencia maintenance_type vs service_type en plantillas
- **BAJO:** No hay validación de VIN
- **MEDIO:** No hay notificación de vencimientos automáticos

### 3.10 Estado: MUY BUENO
✅ CRUD completo
✅ Integración con VehicleModelTemplate
✅ Generación automática de MaintenanceSchedule
✅ Manejo de relaciones críticas
✅ ActivityLog implementado
✅ Validaciones completas

⚠️ Inconsistencia de nomenclatura detectada

---

## 4. PROCESO DE ÓRDENES DE SERVICIO

### 4.1 Objetivo
Gestión de órdenes de trabajo desde recepción hasta entrega.

### 4.2 ¿Quién inicia?
Administrador, Asesor

### 4.3 ¿Qué recibe?
- Vehículo asociado
- Descripción del problema
- Prioridad (baja, normal, alta, urgente)
- Mecánico asignado (opcional)
- Fecha programada (opcional)
- Costo estimado (opcional)

### 4.4 ¿Qué valida?
- Vehículo existe
- Mecánico existe y está activo (si asignado)
- Prioridad válida
- Campos requeridos

### 4.5 ¿Qué guarda?
- ServiceOrder con número generado automáticamente
- Estado inicial: 'recibida'
- Progreso inicial: 0
- ActivityLog de creación

### 4.6 ¿Qué actualiza?
- Estado de vehículo a 'en_taller'
- Estado de orden según progreso
- Costo total según mantenimientos

### 4.7 ¿Qué notifica?
- No hay notificaciones automáticas
- Debería notificar a mecánico asignado
- Debería notificar a cliente

### 4.8 ¿Qué módulos utiliza?
- OrderController (Admin, Advisor, Mechanic)
- ServiceOrder model
- ServiceOrderPolicy
- Vehicle model
- User model
- ActivityLog
- OrderComment

### 4.9 ¿Qué riesgos existen?
- **MEDIO:** No hay notificaciones automáticas
- **BAJO:** Cambio de estado manual sin validaciones complejas
- **BAJO:** No hay validación de disponibilidad de mecánico

### 4.10 Estado: BUENO
✅ Número de orden automático
✅ Estados bien definidos
✅ Seguimiento de progreso
✅ Comentarios y avances
✅ ActivityLog implementado
✅ Policies por rol

⚠️ Faltan notificaciones automáticas
⚠️ No hay validación de disponibilidad

---

## 5. PROCESO DE MANTENIMIENTOS

### 5.1 Objetivo
Registro detallado de trabajos realizados con sincronización de costos y estados.

### 5.2 ¿Quién inicia?
Administrador, Mecánico

### 5.3 ¿Qué recibe?
- Orden de servicio asociada (opcional)
- Vehículo
- Tipo (preventivo, correctivo, garantia)
- Descripción del trabajo
- Kilometraje al servicio
- Nivel de combustible
- Inventario (llanta, herramientas, radio, documentos)
- Repuestos utilizados
- Notas técnicas
- Costos desglosados (repuestos, mano de obra)
- Estado del mantenimiento

### 5.4 ¿Qué valida?
- Vehículo existe
- Tipo válido
- Campos requeridos
- Costos no negativos

### 5.5 ¿Qué guarda?
- Maintenance con todos los datos
- OrderComment automático si está asociado a orden
- ActivityLog de creación

### 5.6 ¿Qué actualiza?
- Kilometraje del vehículo (si es mayor)
- Estado del vehículo (en_taller vs activo)
- Estado de la orden asociada
- Progreso de la orden
- Costo total de la orden
- started_at y completed_at de la orden

### 5.7 ¿Qué notifica?
- No hay notificaciones automáticas
- OrderComment sirve como notificación interna

### 5.8 ¿Qué módulos utiliza?
- MaintenanceController (Admin, Mechanic)
- Maintenance model
- ServiceOrder model
- Vehicle model
- ActivityLog
- OrderComment

### 5.9 ¿Qué riesgos existen?
- **MEDIO:** Lógica compleja en syncOperationalState
- **BAJO:** Validación de type inconsistente entre módulos
- **BAJO:** No hay validación de stock de repuestos

### 5.10 Estado: MUY BUENO
✅ Sincronización automática de estados
✅ Actualización de kilometraje
✅ Cálculo automático de costos
✅ Campos avanzados de inventario
✅ Transacciones DB implementadas
✅ ActivityLog implementado

⚠️ Lógica compleja podría extraerse a Service Layer
⚠️ Inconsistencia de validación de type

---

## 6. PROCESO DE CITAS

### 6.1 Objetivo
Gestión de solicitudes y citas con clientes mediante chatbot y manual.

### 6.2 ¿Quién inicia?
Cliente (chatbot), Asesor (manual)

### 6.3 ¿Qué recibe?
- Cliente
- Vehículo
- Fecha solicitada
- Hora preferida (opcional)
- Tipo de servicio
- Descripción del problema
- Prioridad
- Notas adicionales

### 6.4 ¿Qué valida?
- Cliente existe y está activo
- Vehículo existe y pertenece al cliente
- Fecha futura válida
- Campos requeridos

### 6.5 ¿Qué guarda?
- AppointmentRequest en estado 'pendiente' o 'confirmada'
- Origen (manual/chatbot)
- Plantilla de vehículo si aplica

### 6.6 ¿Qué actualiza?
- Estado de la cita (pendiente → confirmada → convertida → completada)
- service_order_id cuando se convierte a orden

### 6.7 ¿Qué notifica?
- No hay notificaciones automáticas
- Debería notificar al cliente de confirmación
- Debería notificar al asesor de nuevas solicitudes

### 6.8 ¿Qué módulos utiliza?
- AppointmentController (Advisor)
- AppointmentRequestController (Advisor)
- AppointmentRequest model
- ChatbotAppointmentService
- ServiceOrder model
- VehicleModelTemplate

### 6.9 ¿Qué riesgos existen?
- **MEDIO:** No hay notificaciones automáticas
- **BAJO:** No hay validación de disponibilidad de horarios
- **BAJO:** No hay límite de citas por día/hora

### 6.10 Estado: MUY BUENO
✅ Estados bien definidos
✅ Flujo completo (solicitud → confirmación → conversión)
✅ Integración con chatbot
✅ Protección de estados
✅ Reprogramación implementada
✅ Integración con plantillas

⚠️ Faltan notificaciones automáticas
⚠️ No hay validación de disponibilidad

---

## 7. PROCESO DE CHATBOT

### 7.1 Objetivo
Asistente inteligente para atención automatizada y agendamiento de citas.

### 7.2 ¿Quién inicia?
Cliente autenticado

### 7.3 ¿Qué recibe?
- Mensaje de texto
- Contexto de sesión
- Historial de conversación

### 7.4 ¿Qué valida?
- Usuario autenticado
- Formato de mensaje
- Longitud de mensaje

### 7.5 ¿Qué guarda?
- ChatbotMessage (user)
- ChatbotMessage (bot)
- Contexto en sesión
- Draft de cita en sesión

### 7.6 ¿Qué actualiza?
- Contexto conversacional
- Estado de draft de cita
- Sesión de chat

### 7.7 ¿Qué notifica?
- Escalamiento a asesor cuando no puede responder
- Notificación a asesores de consultas (Job)

### 7.8 ¿Qué módulos utiliza?
- ChatbotController
- ChatbotService
- ChatbotAppointmentService
- ChatbotMessage model
- ChatbotFaq model
- User model
- Vehicle model
- ServiceOrder model
- Maintenance model
- AppointmentRequest model

### 7.9 ¿Qué riesgos existen?
- **CRÍTICO:** CSRF bypass en rutas de mensajes
- **MEDIO:** No hay rate limiting
- **BAJO:** Servicio muy extenso (671 líneas)
- **BAJO:** IA integration opcional

### 7.10 Estado: BUENO
✅ Procesamiento de lenguaje natural
✅ Detección de intenciones múltiples
✅ Gestión de contexto conversacional
✅ Integración completa con sistema
✅ Escalamiento a humano
✅ FAQ dinámicas
✅ Gestión de citas multi-paso

❌ **CSRF bypass (security risk)**
⚠️ Falta rate limiting
⚠️ Servicio muy extenso

---

## 8. PROCESO DE FOTOGRAFÍAS

### 8.1 Objetivo
Documentación fotográfica de mantenimientos con categorización temporal.

### 8.2 ¿Quién inicia?
Mecánico, Asesor

### 8.3 ¿Qué recibe?
- Orden de servicio asociada
- Archivo de imagen
- Tipo (reception, before, after, evidence)
- Descripción (opcional)

### 8.4 ¿Qué valida?
- Usuario autenticado
- Orden existe y usuario tiene acceso
- Archivo es imagen válida
- Tamaño máximo 10MB
- Tipo válido

### 8.5 ¿Qué guarda?
- ServicePhoto en Storage (public)
- Registro en base de datos
- Metadata de usuario y orden

### 8.6 ¿Qué actualiza?
- No actualiza otros datos (solo registro)

### 8.7 ¿Qué notifica?
- No hay notificaciones automáticas

### 8.8 ¿Qué módulos utiliza?
- ServicePhotoController
- ServicePhoto model
- ServiceOrder model
- Storage (public disk)
- ServicePhotoPolicy (implícita en controller)

### 8.9 ¿Qué riesgos existen?
- **CRÍTICO:** Sin interfaz frontend (módulo inutilizable)
- **MEDIO:** No hay validación de dimensiones de imagen
- **BAJO:** No hay límite de fotos por orden
- **BAJO:** No hay compresión automática

### 8.10 Estado: EXCELENTE (Backend)
✅ Validaciones robustas
✅ Storage implementado correctamente
✅ Categorización de tipos
✅ Autorización por rol
✅ Logging extensivo
✅ Manejo de errores

❌ **Sin interfaz frontend (CRÍTICO)**
⚠️ Faltan validaciones de imagen
⚠️ No hay límites de almacenamiento

---

## 9. PROCESO DE INVENTARIO

### 9.1 Objetivo
Gestión de productos, proveedores, compras y stock.

### 9.2 ¿Quién inicia?
Administrador

### 9.3 ¿Qué recibe?
- **Productos:** nombre, SKU, categoría, marca, precios, stock
- **Proveedores:** datos de contacto
- **Compras:** proveedor, fecha, items, cantidades, precios

### 9.4 ¿Qué valida?
- SKU único
- Categoría y marca existen
- Stock no negativo
- Proveedor existe y está activo
- Items válidos

### 9.5 ¿Qué guarda?
- Product en base de datos
- Supplier en base de datos
- Purchase con items
- StockMovement al recibir compras

### 9.6 ¿Qué actualiza?
- Stock de productos al recibir compras
- Estado de compras (pendiente → recibida)
- Historial de movimientos de stock

### 9.7 ¿Qué notifica?
- No hay notificaciones automáticas
- Debería alertar stock bajo

### 9.8 ¿Qué módulos utiliza?
- ProductController
- SupplierController
- PurchaseController
- InventoryController
- Product model
- Supplier model
- Purchase model
- PurchaseItem model
- StockMovement model

### 9.9 ¿Qué riesgos existen?
- **MEDIO:** No hay alertas de stock bajo automáticas
- **BAJO:** No hay validación de stock negativo en ventas
- **BAJO:** No hay previsión de demanda

### 9.10 Estado: MUY BUENO
✅ CRUD completo de productos
✅ Gestión de proveedores
✅ Proceso de compras completo
✅ Actualización automática de stock
✅ StockMovement para auditoría
✅ Cálculo automático de impuestos
✅ Protección de estados

⚠️ Faltan alertas automáticas
⚠️ No hay previsión de stock

---

## 10. ANÁLISIS DE INTEGRACIÓN ENTRE PROCESOS

### 10.1 Cadena: Vehículo → Mantenimiento Schedule → Orden → Mantenimiento
**Estado:** EXCELENTE
- ✅ Creación de vehículo genera MaintenanceSchedule automáticamente
- ✅ MaintenanceSchedule puede convertirse en orden
- ✅ Orden contiene múltiples mantenimientos
- ✅ Mantenimiento actualiza estado de orden y vehículo
- ⚠️ Inconsistencia maintenance_type vs service_type

### 10.2 Cadena: Cita (Chatbot) → PreOrden → Orden → Mantenimiento
**Estado:** MUY BUENO
- ✅ Chatbot crea AppointmentRequest
- ✅ Asesor puede confirmar/rechazar
- ✅ PreOrden puede convertirse en orden
- ✅ Orden se asigna a mecánico
- ✅ Mantenimiento se registra en orden
- ⚠️ No hay notificaciones automáticas entre etapas

### 10.3 Cadena: Inventario → Compra → Stock → Mantenimiento
**Estado:** BUENO
- ✅ Productos gestionados en inventario
- ✅ Compras generan Purchase
- ✅ Recepción de compra actualiza stock
- ✅ StockMovement registra auditoría
- ❌ Mantenimiento no valida stock de repuestos
- ❌ No hay integración de consumo en mantenimientos

### 10.4 Cadena: Fotografías → Orden → Cliente
**Estado:** INCOMPLETO
- ✅ Backend de fotografías implementado
- ❌ Sin interfaz para subir fotos
- ❌ Sin visualización para cliente
- ❌ Sin integración en flujo de trabajo

---

## 11. RIESGOS CRÍTICOS DE NEGOCIO

### 11.1 Seguridad
- **CRÍTICO:** CSRF bypass en chatbot
- **MEDIO:** No hay rate limiting en API
- **BAJO:** No hay límite de intentos de login

### 11.2 Notificaciones
- **MEDIO:** No hay notificaciones automáticas en la mayoría de procesos
- **MEDIO:** Cliente no recibe confirmación de citas
- **MEDIO:** Mecánico no recibe notificación de asignación

### 11.3 Validaciones
- **MEDIO:** No hay validación de disponibilidad de mecánicos
- **MEDIO:** No hay validación de stock en mantenimientos
- **BAJO:** No hay validación de horarios en citas

### 11.4 Integración
- **CRÍTICO:** Módulo de fotografías sin interfaz
- **MEDIO:** Inconsistencia maintenance_type vs service_type
- **BAJO:** No hay integración de consumo de inventario

---

## 12. PATRONES DE DISEÑO IDENTIFICADOS

### 12.1 Patrones Implementados
- ✅ **MVC:** Arquitectura limpia
- ✅ **Repository:** Parcial (algunos modelos)
- ✅ **Service:** ChatbotService, ChatbotAppointmentService
- ✅ **Policy:** Autorización por recurso
- ✅ **Factory:** DemoSeeder
- ✅ **Strategy:** Redirección por rol

### 12.2 Patrones Faltantes
- ❌ **Repository:** No implementado completamente
- ❌ **Service:** Podría expandirse a más lógica
- ❌ **Observer:** Para eventos automáticos
- ❌ **Command:** Para acciones complejas
- ❌ **Event:** Para notificaciones

---

## 13. RECOMENDACIONES DE LÓGICA DE NEGOCIO

### Prioridad CRÍTICA
1. Corregir CSRF bypass en chatbot
2. Implementar interfaz de fotografías
3. Estandarizar nomenclatura service_type

### Prioridad ALTA
1. Implementar sistema de notificaciones
2. Agregar validación de disponibilidad
3. Integrar consumo de inventario en mantenimientos

### Prioridad MEDIA
1. Implementar rate limiting
2. Agregar límite de intentos de login
3. Implementar Observer para eventos automáticos

### Prioridad BAJA
1. Expandir Service Layer
2. Implementar Repository Pattern completo
3. Agregar 2FA opcional

---

## 14. ESTIMACIÓN DE ESFUERZO DE CORRECCIONES

### Seguridad
- CSRF bypass: 2-3 horas
- Rate limiting: 4-6 horas
- Límite de login: 2-3 horas
**Subtotal:** 8-12 horas

### Notificaciones
- Sistema de notificaciones: 12-16 horas
- Integración en procesos: 8-12 horas
**Subtotal:** 20-28 horas

### Validaciones
- Disponibilidad de mecánicos: 6-8 horas
- Validación de stock: 4-6 horas
- Validación de horarios: 4-6 horas
**Subtotal:** 14-20 horas

### Integración
- Interfaz fotografías: 26-36 horas
- Estandarización nomenclatura: 6-8 horas
- Integración inventario: 8-12 horas
**Subtotal:** 40-56 horas

**TOTAL ESTIMADO:** 82-116 horas

---

## CONCLUSIÓN

La lógica de negocio de AutoGest está **bien implementada** en general, con procesos robustos y bien estructurados. Los controladores siguen buenas prácticas, las validaciones son completas, y la mayoría de los procesos tienen ActivityLog para auditoría.

Sin embargo, existen **tres áreas críticas** que requieren atención inmediata:
1. Vulnerabilidad de seguridad en chatbot (CSRF bypass)
2. Módulo de fotografías sin interfaz funcional
3. Inconsistencia de nomenclatura en el sistema

Además, el sistema se beneficiaría significativamente de:
- Un sistema de notificaciones automáticas
- Validaciones de disponibilidad y stock
- Mejor integración entre procesos

Una vez resueltos los problemas críticos, el sistema tendrá una lógica de negocio sólida y lista para producción.

---

**Firma del Auditor:** Equipo Multidisciplinario de Ingeniería de Software  
**Fecha de Finalización:** 2026-08-04  
**Próxima Fase:** Auditoría Completa del Chatbot (FASE 4)

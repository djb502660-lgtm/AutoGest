# SPEC INDIVIDUAL POR MÓDULO - FASE 7
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Fecha:** 2026-08-04
**Estado:** Especificaciones Técnicas Detalladas

---

## ÍNDICE DE MÓDULOS

1. [Módulo Administrador](#1-módulo-administrador)
2. [Módulo Asesor](#2-módulo-asesor)
3. [Módulo Mecánico](#3-módulo-mecánico)
4. [Módulo Cliente](#4-módulo-cliente)
5. [Módulo Chatbot](#5-módulo-chatbot)
6. [Módulo Fotografías](#6-módulo-fotografías)
7. [Módulo Inventario](#7-módulo-inventario)

---

## 1. MÓDULO ADMINISTRADOR

### 1.1 Objetivo del Módulo
Proporcionar control total del sistema para administradores, permitiendo gestión de usuarios, vehículos, órdenes, inventario, reportes y configuración del sistema.

### 1.2 Usuarios Objetivo
- **Principal:** Administradores del sistema
- **Secundario:** Gerentes y auditores (acceso de lectura)

### 1.3 Flujo de Usuario Principal

```
Login → Dashboard Ejecutivo → Módulo Específico → Acción → Resultado
```

**Flujo Detallado:**
1. Administrador inicia sesión
2. Redirigido a Dashboard con KPIs
3. Navega a módulo deseado (usuarios, vehículos, órdenes, etc.)
4. Realiza acción (crear, editar, eliminar, consultar)
5. Sistema valida y procesa
6. Usuario recibe confirmación/resultado
7. Sistema registra ActivityLog

### 1.4 Requisitos Funcionales

#### 1.4.1 Gestión de Usuarios
- **CRUD completo** de usuarios
- **Asignación de roles** (admin, asesor, mecanico, cliente)
- **Control de estado** (activo/inactivo)
- **Búsqueda y filtrado** por nombre, email, rol, estado
- **Protección contra auto-eliminación**
- **ActivityLog** de cambios

**Casos de Uso:**
- UC-ADMIN-01: Crear nuevo usuario
- UC-ADMIN-02: Editar usuario existente
- UC-ADMIN-03: Desactivar usuario
- UC-ADMIN-04: Eliminar usuario (sin auto-eliminación)
- UC-ADMIN-05: Asignar rol a usuario
- UC-ADMIN-06: Buscar usuarios por criterios

#### 1.4.2 Gestión de Vehículos
- **CRUD completo** de vehículos
- **Asociación con clientes**
- **Generación automática** de MaintenanceSchedule
- **Control de estado** (activo, inactivo, en_taller)
- **Búsqueda y filtrado** por placa, cliente, estado
- **ActivityLog** de cambios

**Casos de Uso:**
- UC-ADMIN-07: Registrar nuevo vehículo
- UC-ADMIN-08: Editar información de vehículo
- UC-ADMIN-09: Cambiar estado de vehículo
- UC-ADMIN-10: Eliminar vehículo
- UC-ADMIN-11: Consultar historial de vehículo
- UC-ADMIN-12: Buscar vehículos por criterios

#### 1.4.3 Gestión de Órdenes
- **Consulta de órdenes** (index, show)
- **Generación de facturas** (PDF)
- **Filtrado por estado, fecha, cliente**
- **Visualización de detalles** completos
- **ActivityLog** de consultas

**Casos de Uso:**
- UC-ADMIN-13: Listar todas las órdenes
- UC-ADMIN-14: Ver detalles de orden específica
- UC-ADMIN-15: Generar factura PDF de orden
- UC-ADMIN-16: Filtrar órdenes por criterios

#### 1.4.4 Gestión de Mantenimientos
- **CRUD completo** de mantenimientos
- **Sincronización automática** de costos con ServiceOrder
- **Campos avanzados** (inventario, combustible, costos desglosados)
- **Filtros múltiples** implementados
- **ActivityLog** de cambios

**Casos de Uso:**
- UC-ADMIN-17: Registrar mantenimiento
- UC-ADMIN-18: Editar mantenimiento
- UC-ADMIN-19: Eliminar mantenimiento
- UC-ADMIN-20: Consultar mantenimientos por vehículo
- UC-ADMIN-21: Filtrar mantenimientos por criterios

#### 1.4.5 Gestión de Calendario
- **Vista calendario mensual** completo
- **Integración con MaintenanceSchedule** y ServiceOrder
- **CRUD de eventos** de calendario
- **Cálculo automático** de end_time
- **Navegación por meses**
- **Lista de próximos mantenimientos**

**Casos de Uso:**
- UC-ADMIN-22: Ver calendario mensual
- UC-ADMIN-23: Crear evento de calendario
- UC-ADMIN-24: Editar evento existente
- UC-ADMIN-25: Eliminar evento
- UC-ADMIN-26: Navegar por meses
- UC-ADMIN-27: Ver próximos mantenimientos

#### 1.4.6 Gestión de Reportes
- **Generación de múltiples tipos** de reportes
- **Exportación a PDF** (DOMPDF)
- **Exportación a CSV** con BOM UTF-8
- **Envío por email**
- **Filtros avanzados**
- **ActivityLog** de generación

**Casos de Uso:**
- UC-ADMIN-28: Generar reporte de mantenimientos
- UC-ADMIN-29: Generar reporte de gastos
- UC-ADMIN-30: Generar reporte de órdenes
- UC-ADMIN-31: Exportar reporte a PDF
- UC-ADMIN-32: Exportar reporte a CSV
- UC-ADMIN-33: Enviar reporte por email

#### 1.4.7 Gestión de Inventario
- **Vista de inventario consolidada**
- **Integración con Product, Category, Brand, Purchase**
- **CRUD de productos**
- **CRUD de categorías**
- **CRUD de marcas**
- **CRUD de proveedores**
- **Gestión de compras** con recepción
- **Gestión de stock** con movimientos

**Casos de Uso:**
- UC-ADMIN-34: Ver inventario consolidado
- UC-ADMIN-35: Crear producto
- UC-ADMIN-36: Editar producto
- UC-ADMIN-37: Crear categoría
- UC-ADMIN-38: Crear marca
- UC-ADMIN-39: Crear proveedor
- UC-ADMIN-40: Registrar compra
- UC-ADMIN-41: Recibir compra
- UC-ADMIN-42: Consultar stock bajo

### 1.5 Requisitos Técnicos

#### 1.5.1 Controladores
- **UserController:** CRUD de usuarios
- **VehicleController:** CRUD de vehículos
- **OrderController:** Consulta de órdenes (index, show, invoice)
- **MaintenanceController:** CRUD de mantenimientos
- **CalendarController:** Gestión de calendario
- **ReportController:** Generación de reportes
- **InventoryController:** Vista de inventario
- **ProductController:** CRUD de productos
- **CategoryController:** CRUD de categorías
- **BrandController:** CRUD de marcas
- **SupplierController:** CRUD de proveedores
- **PurchaseController:** Gestión de compras
- **StockController:** Gestión de stock
- **ProfileController:** Gestión de perfil
- **DashboardController:** KPIs y estadísticas

#### 1.5.2 Policies
- **UserPolicy:** Autorización de acciones sobre usuarios
- **VehiclePolicy:** Autorización de acciones sobre vehículos
- **MaintenancePolicy:** Autorización de acciones sobre mantenimientos
- **ServiceOrderPolicy:** Autorización de acciones sobre órdenes

#### 1.5.3 Middleware
- **auth:** Verificación de autenticación
- **role:admin:** Verificación de rol administrador

#### 1.5.4 Rutas
- **Prefijo:** `/admin`
- **Middleware:** auth, role:admin
- **Ejemplo:** `/admin/users`, `/admin/vehicles`, `/admin/orders`

### 1.6 Interfaz de Usuario

#### 1.6.1 Dashboard
- **KPIs principales:**
  - Total de usuarios activos
  - Total de vehículos registrados
  - Órdenes en proceso
  - Mantenimientos programados
  - Alertas activas
- **Calendario resumen** del mes actual
- **Órdenes recientes** con estado
- **Graficos de tendencias** (opcional)

#### 1.6.2 Formularios
- **Validación en tiempo real**
- **Mensajes de error claros**
- **Autocompletado** donde aplique
- **Selects dinámicos** (ej: clientes para vehículos)
- **Confirmación** para acciones destructivas

#### 1.6.3 Tablas
- **Paginación** configurable
- **Filtros** por columnas
- **Búsqueda** global
- **Ordenamiento** por columnas
- **Acciones rápidas** (editar, eliminar, ver)

### 1.7 Integración con Otros Módulos

#### 1.7.1 Módulo Asesor
- **Compartir:** Gestión de clientes y vehículos
- **Diferenciar:** Asesor crea órdenes, Admin solo consulta
- **Flujo:** Admin asigna roles, Asesor usa sistema

#### 1.7.2 Módulo Mecánico
- **Compartir:** Gestión de mantenimientos
- **Diferenciar:** Mecánico ejecuta, Admin supervisiona
- **Flujo:** Admin asigna mecánicos, Mecánico ejecuta

#### 1.7.3 Módulo Cliente
- **Compartir:** Consulta de información
- **Diferenciar:** Cliente solo lectura, Admin completo control
- **Flujo:** Admin configura, Cliente consume

#### 1.7.4 Módulo Chatbot
- **Compartir:** Consulta de usuarios y vehículos
- **Diferenciar:** Chatbot consulta, Admin configura
- **Flujo:** Admin configura FAQs, Chatbot usa

### 1.8 Validaciones

#### 1.8.1 Usuarios
- **Email:** Único, formato válido
- **Password:** Mínimo 8 caracteres, mixto
- **Rol:** Enum válido (admin, asesor, mecanico, cliente)
- **Status:** Enum válido (activo, inactivo)
- **Name:** Requerido, máximo 255 caracteres

#### 1.8.2 Vehículos
- **Placa:** Única, formato válido
- **Cliente:** Existente y activo
- **Marca/Modelo:** Requeridos
- **Año:** Rango válido (1900-actual+1)
- **Kilometraje:** No negativo

#### 1.8.3 Mantenimientos
- **Vehículo:** Existente
- **Tipo:** Enum válido (preventivo, correctivo, garantia)
- **Descripción:** Requerida
- **Costos:** No negativos
- **Kilometraje:** No negativo

### 1.9 Estados y Transiciones

#### 1.9.1 Estados de Usuarios
```
activo → inactivo (desactivación)
inactivo → activo (reactivación)
```

#### 1.9.2 Estados de Vehículos
```
activo → en_taller (recepción)
en_taller → activo (entrega)
activo → inactivo (desactivación)
inactivo → activo (reactivación)
```

#### 1.9.3 Estados de Órdenes
```
recibida → en_proceso (inicio)
en_proceso → completada (finalización)
completada → entregada (entrega)
cualquiera → cancelada (cancelación)
```

### 1.10 Métricas y Criterios de Aceptación

#### 1.10.1 Métricas de Performance
- **Tiempo de carga de dashboard:** < 2 segundos
- **Tiempo de respuesta de consultas:** < 500ms
- **Tiempo de generación de reportes:** < 5 segundos

#### 1.10.2 Criterios de Aceptación
- [ ] Todos los controladores funcionando correctamente
- [ ] Policies implementadas y funcionando
- [ ] Validaciones en frontend y backend
- [ ] ActivityLog registrando todas las acciones
- [ ] Dashboard mostrando KPIs correctos
- [ ] Reportes generando correctamente (PDF, CSV)
- [ ] Calendario funcionando con navegación
- [ ] Inventario mostrando datos correctos

---

## 2. MÓDULO ASESOR

### 2.1 Objetivo del Módulo
Gestionar relaciones con clientes, recepción de vehículos, agendamiento de citas y coordinación de órdenes de servicio.

### 2.2 Usuarios Objetivo
- **Principal:** Asesores de servicio
- **Secundario:** Administradores (supervisión)

### 2.3 Flujo de Usuario Principal

```
Login → Dashboard Asesor → Gestión de Clientes → Recepción de Vehículo → Creación de Orden → Asignación de Mecánico → Seguimiento → Entrega
```

**Flujo Detallado:**
1. Asesor inicia sesión
2. Redirigido a Dashboard especializado
3. Gestiona clientes y vehículos
4. Recibe solicitudes de cita (chatbot/manual)
5. Confirma/rechaza solicitudes
6. Crea órdenes de servicio manualmente
7. Asigna mecánicos a órdenes
8. Coordina entrega de vehículos
9. Sistema registra ActivityLog

### 2.4 Requisitos Funcionales

#### 2.4.1 Gestión de Órdenes
- **CRUD completo** de órdenes
- **Integración con VehicleModelTemplate**
- **Asignación de mecánicos**
- **Generación de facturas**
- **ActivityLog** de cambios
- **Autorización con Policies**

**Casos de Uso:**
- UC-ASESOR-01: Crear nueva orden de servicio
- UC-ASESOR-02: Editar orden existente
- UC-ASESOR-03: Asignar mecánico a orden
- UC-ASESOR-04: Actualizar estado de orden
- UC-ASESOR-05: Generar factura de orden
- UC-ASESOR-06: Consultar órdenes por cliente

#### 2.4.2 Gestión de Pre-Órdenes
- **CRUD completo** de pre-órdenes
- **Estados:** pendiente, confirmada, rechazada, convertida
- **Conversión a ServiceOrder**
- **Protección de estados**
- **Integración con VehicleModelTemplate**

**Casos de Uso:**
- UC-ASESOR-07: Crear pre-orden
- UC-ASESOR-08: Confirmar pre-orden
- UC-ASESOR-09: Rechazar pre-orden
- UC-ASESOR-10: Convertir pre-orden a orden
- UC-ASESOR-11: Consultar pre-órdenes

#### 2.4.3 Gestión de Citas
- **CRUD completo** de citas
- **Reprogramación de citas**
- **Cancelación con validaciones**
- **Vista calendario**
- **Filtros por fecha y estado**
- **Protección de estados completados/cancelados**

**Casos de Uso:**
- UC-ASESOR-12: Crear cita
- UC-ASESOR-13: Editar cita
- UC-ASESOR-14: Reprogramar cita
- UC-ASESOR-15: Cancelar cita
- UC-ASESOR-16: Ver calendario de citas
- UC-ASESOR-17: Filtrar citas por criterios

#### 2.4.4 Gestión de Solicitudes de Cita
- **Gestión de solicitudes** (chatbot/manual)
- **Confirmación/rechazo**
- **Integración con plantillas** de vehículos
- **Filtros y búsqueda**
- **Conversión a citas**

**Casos de Uso:**
- UC-ASESOR-18: Ver solicitudes pendientes
- UC-ASESOR-19: Confirmar solicitud
- UC-ASESOR-20: Rechazar solicitud
- UC-ASESOR-21: Convertir solicitud a cita
- UC-ASESOR-22: Filtrar solicitudes

#### 2.4.5 Gestión de Clientes
- **Consulta de clientes**
- **Edición de información básica**
- **Consulta de vehículos** por cliente
- **Historial de servicios** por cliente
- **Filtros y búsqueda**

**Casos de Uso:**
- UC-ASESOR-23: Listar clientes
- UC-ASESOR-24: Ver detalles de cliente
- UC-ASESOR-25: Editar información de cliente
- UC-ASESOR-26: Ver vehículos de cliente
- UC-ASESOR-27: Ver historial de cliente

#### 2.4.6 Gestión de Vehículos
- **Consulta de vehículos**
- **Edición de información básica**
- **Estado actual** del vehículo
- **Historial de mantenimientos**
- **Órdenes asociadas**

**Casos de Uso:**
- UC-ASESOR-28: Listar vehículos
- UC-ASESOR-29: Ver detalles de vehículo
- UC-ASESOR-30: Editar información de vehículo
- UC-ASESOR-31: Ver historial de vehículo
- UC-ASESOR-32: Ver órdenes de vehículo

### 2.5 Requisitos Técnicos

#### 2.5.1 Controladores
- **DashboardController:** KPIs especializados
- **OrderController:** CRUD completo de órdenes
- **PreOrderController:** Gestión de pre-órdenes
- **AppointmentController:** Gestión de citas
- **AppointmentRequestController:** Gestión de solicitudes
- **ClientController:** Gestión de clientes
- **VehicleController:** Gestión de vehículos

#### 2.5.2 Policies
- **ServiceOrderPolicy:** Autorización de órdenes
- **VehiclePolicy:** Autorización de vehículos
- **UserPolicy:** Autorización de clientes

#### 2.5.3 Middleware
- **auth:** Verificación de autenticación
- **role:asesor:** Verificación de rol asesor

#### 2.5.4 Rutas
- **Prefijo:** `/asesor`
- **Middleware:** auth, role:asesor
- **Ejemplo:** `/asesor/orders`, `/asesor/appointments`

### 2.6 Interfaz de Usuario

#### 2.6.1 Dashboard Asesor
- **KPIs especializados:**
  - Citas pendientes hoy
  - Órdenes en proceso
  - Solicitudes pendientes
  - Vehículos en taller
- **Calendario** de citas del día
- **Lista de clientes** recientes
- **Órdenes prioritarias**

#### 2.6.2 Formularios
- **Creación de órdenes** con selectores dinámicos
- **Asignación de mecánicos** con disponibilidad
- **Gestión de citas** con calendario integrado
- **Validación en tiempo real**

### 2.7 Integración con Otros Módulos

#### 2.7.1 Módulo Administrador
- **Compartir:** Gestión de usuarios y vehículos
- **Diferenciar:** Admin configura, Asesor usa
- **Flujo:** Admin asigna roles, Asesor gestiona clientes

#### 2.7.2 Módulo Mecánico
- **Compartir:** Gestión de órdenes
- **Diferenciar:** Asesor asigna, Mecánico ejecuta
- **Flujo:** Asesor crea orden, Mecánico recibe asignación

#### 2.7.3 Módulo Cliente
- **Compartir:** Información de clientes y vehículos
- **Diferenciar:** Cliente consulta, Asesor gestiona
- **Flujo:** Cliente solicita, Asesor coordina

#### 2.7.4 Módulo Chatbot
- **Compartir:** Solicitudes de citas
- **Diferenciar:** Chatbot crea, Asesor gestiona
- **Flujo:** Chatbot genera solicitud, Asesor confirma

### 2.8 Validaciones

#### 2.8.1 Órdenes
- **Vehículo:** Existente y activo
- **Mecánico:** Existente y activo (si asignado)
- **Prioridad:** Enum válido
- **Descripción:** Requerida
- **Fecha programada:** Futura (si aplica)

#### 2.8.2 Citas
- **Cliente:** Existente y activo
- **Vehículo:** Existente y del cliente
- **Fecha:** Futura
- **Hora:** Dentro de horario laboral
- **Estado:** Protegido según flujo

### 2.9 Estados y Transiciones

#### 2.9.1 Estados de Pre-Órdenes
```
pendiente → confirmada (confirmación)
pendiente → rechazada (rechazo)
confirmada → convertida (conversión a orden)
```

#### 2.9.2 Estados de Citas
```
pendiente → confirmada (confirmación)
pendiente → cancelada (cancelación)
confirmada → completada (finalización)
completada → cancelada (cancelación posterior)
```

### 2.10 Métricas y Criterios de Aceptación

#### 2.10.1 Métricas de Performance
- **Tiempo de carga de dashboard:** < 2 segundos
- **Tiempo de creación de orden:** < 1 segundo
- **Tiempo de asignación de mecánico:** < 500ms

#### 2.10.2 Criterios de Aceptación
- [ ] Dashboard mostrando KPIs correctos
- [ ] Creación de órdenes funcionando
- [ ] Asignación de mecánicos funcionando
- [ ] Gestión de citas funcionando
- [ ] Integración con chatbot funcionando
- [ ] Policies implementadas correctamente

---

## 3. MÓDULO MECÁNICO

### 3.1 Objetivo del Módulo
Ejecución de trabajos asignados, registro de mantenimientos, actualización de estados y documentación técnica.

### 3.2 Usuarios Objetivo
- **Principal:** Mecánicos
- **Secundario:** Administradores y asesores (supervisión)

### 3.3 Flujo de Usuario Principal

```
Login → Dashboard Mecánico → Consultar Órdenes Asignadas → Seleccionar Orden → Actualizar Progreso → Registrar Mantenimiento → Subir Evidencias → Finalizar
```

**Flujo Detallado:**
1. Mecánico inicia sesión
2. Redirigido a Dashboard de asignaciones
3. Consulta órdenes asignadas
4. Actualiza estados de órdenes
5. Registra mantenimientos detallados
6. Sube evidencias fotográficas
7. Agrega comentarios técnicos
8. Actualiza progreso de trabajos
9. Sistema registra ActivityLog

### 3.4 Requisitos Funcionales

#### 3.4.1 Gestión de Órdenes Asignadas
- **Listado de órdenes asignadas**
- **Actualización de estados**
- **Gestión de progreso**
- **Comentarios técnicos**
- **Historial de trabajos**
- **ActivityLog** de cambios
- **Autorización con Policies**
- **Filtrado por vehículos en taller**

**Casos de Uso:**
- UC-MECANICO-01: Ver órdenes asignadas
- UC-MECANICO-02: Actualizar estado de orden
- UC-MECANICO-03: Actualizar progreso de orden
- UC-MECANICO-04: Agregar comentario técnico
- UC-MECANICO-05: Ver historial de trabajos
- UC-MECANICO-06: Filtrar órdenes por criterios

#### 3.4.2 Gestión de Mantenimientos
- **Creación de mantenimientos**
- **Sincronización con ServiceOrder**
- **Actualización de estado de vehículos**
- **Gestión de kilometraje**
- **Transacciones DB**
- **ActivityLog** de cambios
- **Comentarios automáticos** en órdenes

**Casos de Uso:**
- UC-MECANICO-07: Registrar mantenimiento
- UC-MECANICO-08: Editar mantenimiento
- UC-MECANICO-09: Finalizar mantenimiento
- UC-MECANICO-10: Consultar mantenimientos por orden
- UC-MECANICO-11: Actualizar kilometraje
- UC-MECANICO-12: Agregar notas técnicas

#### 3.4.3 Gestión de Calendario
- **Vista calendario personal**
- **Mantenimientos programados**
- **Órdenes asignadas** en calendario
- **Navegación por fechas**

**Casos de Uso:**
- UC-MECANICO-13: Ver calendario personal
- UC-MECANICO-14: Ver mantenimientos del día
- UC-MECANICO-15: Navegar por fechas

#### 3.4.4 Gestión de Vehículos
- **Consulta de vehículos accesibles**
- **Estado actual** del vehículo
- **Historial de mantenimientos**
- **Información técnica**

**Casos de Uso:**
- UC-MECANICO-16: Ver vehículos asignados
- UC-MECANICO-17: Ver detalles de vehículo
- UC-MECANICO-18: Ver historial de vehículo

### 3.5 Requisitos Técnicos

#### 3.5.1 Controladores
- **DashboardController:** Órdenes asignadas
- **OrderController:** Órdenes asignadas, actualización de estado
- **MaintenanceController:** Registro de mantenimientos
- **CalendarController:** Calendario personal
- **VehicleController:** Vehículos accesibles

#### 3.5.2 Policies
- **ServiceOrderPolicy:** Autorización de órdenes
- **MaintenancePolicy:** Autorización de mantenimientos
- **VehiclePolicy:** Autorización de vehículos

#### 3.5.3 Middleware
- **auth:** Verificación de autenticación
- **role:mecanico:** Verificación de rol mecánico

#### 3.5.4 Rutas
- **Prefijo:** `/mecanico`
- **Middleware:** auth, role:mecanico
- **Ejemplo:** `/mecanico/orders`, `/mecanico/maintenances`

### 3.6 Interfaz de Usuario

#### 3.6.1 Dashboard Mecánico
- **Órdenes asignadas** con prioridad
- **Progreso de cada orden**
- **Mantenimientos pendientes**
- **Vehículos en taller**
- **Calendario resumen**

#### 3.6.2 Formularios
- **Registro de mantenimientos** con campos avanzados
- **Actualización de progreso** con slider
- **Comentarios técnicos** con textarea
- **Subida de fotografías** (cuando se implemente)

### 3.7 Integración con Otros Módulos

#### 3.7.1 Módulo Administrador
- **Compartir:** Consulta de información
- **Diferenciar:** Admin supervisiona, Mecánico ejecuta
- **Flujo:** Admin asigna, Mecánico ejecuta

#### 3.7.2 Módulo Asesor
- **Compartir:** Gestión de órdenes
- **Diferenciar:** Asesor asigna, Mecánico ejecuta
- **Flujo:** Asesor crea orden, Mecánico recibe

#### 3.7.3 Módulo Cliente
- **Compartir:** Consulta de estado
- **Diferenciar:** Cliente consulta, Mecánico actualiza
- **Flujo:** Mecánico actualiza, Cliente ve progreso

#### 3.7.4 Módulo Fotografías
- **Compartir:** Evidencias de trabajos
- **Diferenciar:** Mecánico sube, Cliente ve
- **Flujo:** Mecánico registra evidencias

### 3.8 Validaciones

#### 3.8.1 Mantenimientos
- **Vehículo:** Existente y accesible
- **Orden:** Existente y asignada
- **Tipo:** Enum válido (preventivo, correctivo, garantia)
- **Descripción:** Requerida
- **Kilometraje:** No negativo
- **Costos:** No negativos

#### 3.8.2 Actualización de Orden
- **Orden:** Existente y asignada
- **Estado:** Transición válida
- **Progreso:** 0-100
- **Comentario:** Requerido para ciertos estados

### 3.9 Estados y Transiciones

#### 3.9.1 Estados de Mantenimientos
```
pendiente → en_proceso (inicio)
en_proceso → completado (finalización)
completado → cancelado (cancelación)
```

#### 3.9.2 Estados de Órdenes (para Mecánico)
```
recibida → en_proceso (inicio)
en_proceso → completada (finalización)
```

### 3.10 Métricas y Criterios de Aceptación

#### 3.10.1 Métricas de Performance
- **Tiempo de carga de dashboard:** < 2 segundos
- **Tiempo de registro de mantenimiento:** < 1 segundo
- **Tiempo de actualización de estado:** < 500ms

#### 3.10.2 Criterios de Aceptación
- [ ] Dashboard mostrando órdenes asignadas
- [ ] Registro de mantenimientos funcionando
- [ ] Actualización de estados funcionando
- [ ] Sincronización con ServiceOrder funcionando
- [ ] Policies implementadas correctamente
- [ ] ActivityLog registrando cambios

---

## 4. MÓDULO CLIENTE

### 4.1 Objetivo del Módulo
Portal personal para consulta de vehículos, seguimiento de órdenes, control de gastos e interacción con chatbot.

### 4.2 Usuarios Objetivo
- **Principal:** Clientes del taller
- **Secundario:** Ninguno (acceso exclusivo)

### 4.3 Flujo de Usuario Principal

```
Login → Dashboard Personal → Consultar Vehículos → Ver Estado → Seguimiento de Órdenes → Control de Gastos → Interacción con Chatbot
```

**Flujo Detallado:**
1. Cliente inicia sesión
2. Redirigido a Dashboard personal
3. Consulta sus vehículos y estados
4. Revisa historial de mantenimientos
5. Seguimiento de órdenes activas
6. Control de gastos vehiculares
7. Interacción con chatbot
8. Gestión de notificaciones
9. Sistema registra ActivityLog

### 4.4 Requisitos Funcionales

#### 4.4.1 Dashboard Personal
- **Estadísticas personales**
- **Lista de vehículos** con mantenimientos programados
- **Órdenes recientes**
- **Alertas activas**
- **Vista consolidada**

**Casos de Uso:**
- UC-CLIENTE-01: Ver dashboard personal
- UC-CLIENTE-02: Ver estadísticas de vehículos
- UC-CLIENTE-03: Ver mantenimientos programados
- UC-CLIENTE-04: Ver alertas activas

#### 4.4.2 Gestión de Vehículos
- **Consulta de vehículos** personales
- **Estado actual** de cada vehículo
- **Historial de mantenimientos**
- **Próximos mantenimientos**
- **Detalles técnicos**

**Casos de Uso:**
- UC-CLIENTE-05: Listar vehículos personales
- UC-CLIENTE-06: Ver detalles de vehículo
- UC-CLIENTE-07: Ver historial de mantenimientos
- UC-CLIENTE-08: Ver próximos mantenimientos
- UC-CLIENTE-09: Ver estado actual

#### 4.4.3 Gestión de Órdenes
- **Listado de órdenes** personales
- **Filtros y búsqueda**
- **Vista detallada**
- **Autorización con Policies**
- **Sin capacidad de crear** (intencional)

**Casos de Uso:**
- UC-CLIENTE-10: Listar órdenes personales
- UC-CLIENTE-11: Ver detalles de orden
- UC-CLIENTE-12: Ver factura de orden
- UC-CLIENTE-13: Filtrar órdenes por criterios

#### 4.4.4 Gestión de Mantenimientos
- **Historial completo** de mantenimientos
- **Filtros por vehículo y tipo**
- **Detalles de cada mantenimiento**
- **Costos acumulados**

**Casos de Uso:**
- UC-CLIENTE-14: Ver historial de mantenimientos
- UC-CLIENTE-15: Ver detalles de mantenimiento
- UC-CLIENTE-16: Filtrar mantenimientos
- UC-CLIENTE-17: Ver costos acumulados

#### 4.4.5 Control de Gastos
- **Gastos totales** por vehículo
- **Gastos por tipo** de servicio
- **Gastos por período**
- **Gráficos de tendencias**

**Casos de Uso:**
- UC-CLIENTE-18: Ver gastos totales
- UC-CLIENTE-19: Ver gastos por vehículo
- UC-CLIENTE-20: Ver gastos por período
- UC-CLIENTE-21: Ver tendencias de gastos

#### 4.4.6 Gestión de Notificaciones
- **Lista de notificaciones**
- **Marcado como leídas**
- **Filtros por tipo**
- **Notificaciones de alertas**

**Casos de Uso:**
- UC-CLIENTE-22: Ver notificaciones
- UC-CLIENTE-23: Marcar notificación como leída
- UC-CLIENTE-24: Filtrar notificaciones

#### 4.4.7 Gestión de Perfil
- **Edición de información personal**
- **Cambio de contraseña**
- **Actualización de contacto**

**Casos de Uso:**
- UC-CLIENTE-25: Ver perfil personal
- UC-CLIENTE-26: Editar información personal
- UC-CLIENTE-27: Cambiar contraseña

### 4.5 Requisitos Técnicos

#### 4.5.1 Controladores
- **DashboardController:** Estadísticas personales
- **VehicleController:** Consulta de vehículos
- **OrderController:** Consulta de órdenes personales
- **MaintenanceController:** Historial y próximos mantenimientos
- **ExpenseController:** Control de gastos
- **NotificationController:** Gestión de notificaciones
- **ProfileController:** Gestión de perfil

#### 4.5.2 Policies
- **VehiclePolicy:** Autorización de vehículos (solo propios)
- **ServiceOrderPolicy:** Autorización de órdenes (solo propias)

#### 4.5.3 Middleware
- **auth:** Verificación de autenticación
- **role:cliente:** Verificación de rol cliente

#### 4.5.4 Rutas
- **Prefijo:** `/cliente`
- **Middleware:** auth, role:cliente
- **Ejemplo:** `/cliente/vehicles`, `/cliente/orders`

### 4.6 Interfaz de Usuario

#### 4.6.1 Dashboard Cliente
- **Resumen de vehículos** con estado
- **Mantenimientos programados** más cercanos
- **Órdenes activas** con progreso
- **Alertas importantes**
- **Acceso rápido** a chatbot

#### 4.6.2 Vistas
- **Tablas con paginación**
- **Filtros por vehículo y fecha**
- **Gráficos de gastos**
- **Timeline de mantenimientos**
- **Estado visual** de vehículos

### 4.7 Integración con Otros Módulos

#### 4.7.1 Módulo Administrador
- **Compartir:** Consulta de información
- **Diferenciar:** Admin configura, Cliente consulta
- **Flujo:** Admin configura vehículo, Cliente consulta

#### 4.7.2 Módulo Asesor
- **Compartir:** Gestión de citas
- **Diferenciar:** Asesor gestiona, Cliente solicita
- **Flujo:** Cliente solicita, Asesor coordina

#### 4.7.3 Módulo Mecánico
- **Compartir:** Seguimiento de órdenes
- **Diferenciar:** Mecánico actualiza, Cliente consulta
- **Flujo:** Mecánico actualiza, Cliente ve progreso

#### 4.7.4 Módulo Chatbot
- **Compartir:** Interacción conversacional
- **Diferenciar:** Chatbot responde, Cliente interactúa
- **Flujo:** Cliente usa chatbot para consultas

### 4.8 Validaciones

#### 4.8.1 Perfil
- **Name:** Requerido, máximo 255 caracteres
- **Email:** Único, formato válido
- **Phone:** Formato válido
- **Password:** Mínimo 8 caracteres, mixto (cambio)

#### 4.8.2 Consultas
- **Solo vehículos propios** del cliente
- **Solo órdenes propias** del cliente
- **Solo mantenimientos** de vehículos propios

### 4.9 Estados y Transiciones

#### 4.9.1 Estados de Notificaciones
```
no_leída → leída (lectura)
```

#### 4.9.2 Estados de Vehículos (lectura)
```
activo (consulta)
en_taller (consulta)
inactivo (consulta)
```

### 4.10 Métricas y Criterios de Aceptación

#### 4.10.1 Métricas de Performance
- **Tiempo de carga de dashboard:** < 2 segundos
- **Tiempo de consulta de historial:** < 1 segundo
- **Tiempo de carga de gastos:** < 1.5 segundos

#### 4.10.2 Criterios de Aceptación
- [ ] Dashboard mostrando datos correctos
- [ ] Consulta de vehículos funcionando
- [ ] Seguimiento de órdenes funcionando
- [ ] Control de gastos funcionando
- [ ] Chatbot integrado funcionando
- [ ] Policies protegiendo datos de otros clientes
- [ ] Notificaciones funcionando

---

## 5. MÓDULO CHATBOT

### 5.1 Objetivo del Módulo
Asistente inteligente para atención automatizada, agendamiento de citas, consultas de estado y guía del sistema.

### 5.2 Usuarios Objetivo
- **Principal:** Clientes (autenticados)
- **Secundario:** Asesores (escalation)

### 5.3 Flujo de Usuario Principal

```
Login → Acceso a Chatbot → Interacción Conversacional → Detección de Intención → Respuesta Inteligente → Escalamiento (si necesario)
```

**Flujo Detallado:**
1. Cliente accede a chatbot
2. Envía mensaje
3. Chatbot procesa lenguaje natural
4. Detecta intención y contexto
5. Accede a datos del sistema
6. Proporciona respuesta inteligente
7. Escala a asesor humano cuando necesario
8. Sistema registra conversación

### 5.4 Requisitos Funcionales

#### 5.4.1 Procesamiento de Lenguaje Natural
- **Detección de intenciones** múltiples
- **Normalización de texto**
- **Detección de saludos**
- **Detección de preguntas específicas**
- **Gestión de contexto conversacional**
- **Atajos numéricos** (1, 2, 3, 4)

**Casos de Uso:**
- UC-CHATBOT-01: Procesar mensaje de usuario
- UC-CHATBOT-02: Detectar intención de vehículo
- UC-CHATBOT-03: Detectar intención de gastos
- UC-CHATBOT-04: Detectar intención de citas
- UC-CHATBOT-05: Procesar atajos numéricos

#### 5.4.2 Consulta de Vehículos
- **Estado del vehículo** actual
- **Última orden** de servicio
- **Historial de mantenimientos**
- **Próximos mantenimientos**
- **Extracción de placa** de mensajes

**Casos de Uso:**
- UC-CHATBOT-06: Consultar estado de vehículo
- UC-CHATBOT-07: Consultar historial de mantenimientos
- UC-CHATBOT-08: Consultar órdenes activas
- UC-CHATBOT-09: Consultar por placa específica

#### 5.4.3 Gestión de Citas
- **Detección de intención** de agendar
- **Flujo multi-paso** completo
- **Selección de vehículo**
- **Selección de fecha y hora**
- **Selección de tipo de servicio**
- **Confirmación de cita**
- **Modificación de citas existentes**
- **Cancelación de citas**

**Casos de Uso:**
- UC-CHATBOT-10: Iniciar agendamiento de cita
- UC-CHATBOT-11: Seleccionar vehículo
- UC-CHATBOT-12: Seleccionar fecha
- UC-CHATBOT-13: Seleccionar hora
- UC-CHATBOT-14: Confirmar cita
- UC-CHATBOT-15: Modificar cita existente
- UC-CHATBOT-16: Cancelar cita

#### 5.4.4 Consulta de Gastos
- **Resumen de gastos** totales
- **Gastos por vehículo**
- **Gastos por tipo** de servicio
- **Gastos por período**

**Casos de Uso:**
- UC-CHATBOT-17: Consultar gastos totales
- UC-CHATBOT-18: Consultar gastos por vehículo
- UC-CHATBOT-19: Consultar gastos por tipo

#### 5.4.5 Diagnóstico Guiado
- **Detección de síntomas** mecánicos
- **Preguntas de seguimiento** específicas
- **Diagnóstico basado** en respuestas
- **Recomendación de acción**
- **Oferta de agendar cita**

**Casos de Uso:**
- UC-CHATBOT-20: Diagnosticar ruido en frenos
- UC-CHATBOT-21: Diagnosticar problemas con llantas
- UC-CHATBOT-22: Diagnosticar consumo de combustible
- UC-CHATBOT-23: Recomendar acción basada en diagnóstico

#### 5.4.6 FAQ Dinámicas
- **Búsqueda por keywords**
- **Búsqueda por similitud** de pregunta
- **Filtro por activo/inactivo**
- **Ordenamiento por sort_order**
- **Fallback a IA** si no hay FAQ

**Casos de Uso:**
- UC-CHATBOT-24: Buscar respuesta en FAQ
- UC-CHATBOT-25: Responder con FAQ encontrada
- UC-CHATBOT-26: Fallback a IA si no hay FAQ

#### 5.4.7 Escalamiento a Humano
- **Detección de consultas** no resueltas
- **Dispatch de Job** asíncrono
- **Notificación a asesores**
- **Información del usuario** y consulta
- **Mensaje de confirmación** al usuario

**Casos de Uso:**
- UC-CHATBOT-27: Detectar consulta no resuelta
- UC-CHATBOT-28: Escalar a asesor
- UC-CHATBOT-29: Notificar a asesores
- UC-CHATBOT-30: Confirmar escalamiento al usuario

### 5.5 Requisitos Técnicos

#### 5.5.1 Servicios
- **ChatbotService:** Coordenador general (671 líneas - requiere modularización)
- **ChatbotAppointmentService:** Gestión especializada de citas
- **DashboardCalendarService:** Calendario de dashboard

#### 5.5.2 Controladores
- **ChatbotController:** Interfaz de chat

#### 5.5.3 Models
- **ChatbotMessage:** Historial de conversaciones
- **ChatbotFaq:** Preguntas frecuentes
- **ChatbotConfiguration:** Configuración del chatbot

#### 5.5.4 Jobs
- **NotifyAdvisorsOfChatbotQuery:** Notificación a asesores

#### 5.5.5 Middleware
- **auth:** Verificación de autenticación
- **role:cliente:** Verificación de rol cliente
- **CSRF:** **CRÍTICO - Actualmente bypassed**

#### 5.5.6 Rutas
- **Prefijo:** `/cliente`
- **Middleware:** auth, role:cliente
- **Ejemplo:** `/cliente/chatbot`, `/cliente/chatbot/mensaje`

### 5.6 Interfaz de Usuario

#### 5.6.1 Interfaz de Chat
- **Diseño moderno** y limpio
- **Historial de conversación** visible
- **Indicador de mensajes** enviados/recibidos
- **Soporte para markdown** en respuestas
- **Responsive design**

#### 5.6.2 Usabilidad
- **Atajos numéricos** (1, 2, 3, 4)
- **Sugerencias de FAQ**
- **Mensajes de ayuda** claros
- **Opciones explícitas** cuando es necesario
- **Confirmación de acciones** importantes

### 5.7 Integración con Otros Módulos

#### 5.7.1 Módulo Cliente
- **Compartir:** Información de usuarios y vehículos
- **Diferenciar:** Chatbot consulta, Cliente interactúa
- **Flujo:** Cliente usa chatbot para consultas

#### 5.7.2 Módulo Asesor
- **Compartir:** Solicitudes de citas
- **Diferenciar:** Chatbot crea, Asesor gestiona
- **Flujo:** Chatbot genera solicitud, Asesor confirma

#### 5.7.3 Módulo Administrador
- **Compartir:** Configuración de FAQs
- **Diferenciar:** Admin configura, Chatbot usa
- **Flujo:** Admin configura FAQs, Chatbot responde

### 5.8 Validaciones

#### 5.8.1 Mensajes
- **Longitud máxima:** 1000 caracteres
- **Tipo:** String
- **Usuario:** Autenticado
- **Sanitización:** Básica de caracteres especiales

#### 5.8.2 Citas
- **Cliente:** Autenticado y activo
- **Vehículo:** Existente y del cliente
- **Fecha:** Futura
- **Hora:** Dentro de horario laboral

### 5.9 Estados y Transiciones

#### 5.9.1 Estados de Contexto Conversacional
```
idle → vehicle_selection (selección de vehículo)
vehicle_selection → date_selection (selección de fecha)
date_selection → time_selection (selección de hora)
time_selection → confirmation (confirmación)
confirmation → completed (completado)
```

#### 5.9.2 Estados de Draft de Cita
```
pending → validated (validación)
validated → confirmed (confirmación)
validated → cancelled (cancelación)
```

### 5.10 Métricas y Criterios de Aceptación

#### 5.10.1 Métricas de Performance
- **Tiempo de respuesta del chatbot:** < 2 segundos
- **Tiempo de detección de intención:** < 500ms
- **Tiempo de generación de respuesta:** < 1 segundo

#### 5.10.2 Criterios de Aceptación
- [ ] Chatbot respondiendo a saludos
- [ ] Detección de intenciones funcionando
- [ ] Consulta de vehículos funcionando
- [ ] Gestión de citas funcionando
- [ ] Diagnóstico guiado funcionando
- [ ] FAQ dinámicas funcionando
- [ ] Escalamiento a humano funcionando
- [ ] **CRÍTICO:** CSRF protection activo
- [ ] **CRÍTICO:** Rate limiting implementado
- [ ] ActivityLog registrando conversaciones

---

## 6. MÓDULO FOTOGRAFÍAS

### 6.1 Objetivo del Módulo
Sistema de evidencias fotográficas para documentación de mantenimientos (antes, durante, después).

### 6.2 Usuarios Objetivo
- **Principal:** Mecánicos y Asesores (subida)
- **Secundario:** Clientes (visualización)
- **Terciario:** Administradores (auditoría)

### 6.3 Flujo de Usuario Principal

```
Selección de Orden → Subida de Foto → Categorización → Almacenamiento → Visualización (Cliente) → Auditoría (Admin)
```

**Flujo Detallado:**
1. Mecánico/Asesor selecciona orden
2. Sube foto de evidencia
3. Categoriza foto (reception, before, after, evidence)
4. Sistema almacena en Storage
5. Cliente visualiza fotos de sus mantenimientos
6. Administrador audita todas las fotos
7. Sistema registra ActivityLog

### 6.4 Requisitos Funcionales

#### 6.4.1 Subida de Fotografías
- **CRUD completo** de fotos
- **Validación de archivos** (imagen, max 10MB)
- **Categorización de tipos** (reception, before, after, evidence)
- **Storage implementado** correctamente
- **Autorización por rol**
- **Logging extensivo**
- **Respuestas JSON** para AJAX
- **Manejo de errores** robusto

**Casos de Uso:**
- UC-FOTO-01: Subir foto de recepción
- UC-FOTO-02: Subir foto antes de trabajo
- UC-FOTO-03: Subir foto después de trabajo
- UC-FOTO-04: Subir foto de evidencia
- UC-FOTO-05: Eliminar foto
- UC-FOTO-06: Ver foto

#### 6.4.2 Visualización de Fotografías
- **Galería de fotos** por orden
- **Filtros por tipo** de foto
- **Visualización en tamaño completo**
- **Descarga de fotos**
- **Ordenamiento cronológico**

**Casos de Uso:**
- UC-FOTO-07: Ver galería de orden
- UC-FOTO-08: Filtrar fotos por tipo
- UC-FOTO-09: Ver foto en tamaño completo
- UC-FOTO-10: Descargar foto

#### 6.4.3 Auditoría de Fotografías
- **Listado de todas las fotos**
- **Filtros por fecha, tipo, usuario**
- **Visualización para auditoría**
- **Eliminación de fotos inapropiadas**

**Casos de Uso:**
- UC-FOTO-11: Ver todas las fotos
- UC-FOTO-12: Filtrar fotos por criterios
- UC-FOTO-13: Eliminar foto inapropiada

### 6.5 Requisitos Técnicos

#### 6.5.1 Controladores
- **ServicePhotoController:** CRUD de fotografías

#### 6.5.2 Models
- **ServicePhoto:** Modelo de fotografías

#### 6.5.3 Middleware
- **auth:** Verificación de autenticación
- **Rol específico:** Mecánico/Asesor para subida, Cliente para visualización

#### 6.5.4 Rutas
- **Prefijo:** Variable según rol
- **Middleware:** auth + rol específico
- **Ejemplo:** `/mecanico/photos`, `/asesor/photos`

#### 6.5.5 Storage
- **Disco:** public
- **Directorio:** `service-photos/`
- **Acceso web:** `/storage/service-photos/...`

### 6.6 Interfaz de Usuario

#### 6.6.1 Interfaz de Subida (Mecánico/Asesor)
- **Selector de archivo** con preview
- **Selector de tipo** de foto
- **Campo de descripción** opcional
- **Botón de subida**
- **Galería de fotos** ya subidas

#### 6.6.2 Interfaz de Visualización (Cliente)
- **Galería responsive**
- **Filtros por tipo**
- **Lightbox para visualización**
- **Botón de descarga**
- **Cronología de fotos**

#### 6.6.3 Interfaz de Auditoría (Admin)
- **Tabla de todas las fotos**
- **Filtros avanzados**
- **Acciones de auditoría**
- **Vista previa rápida**

### 6.7 Integración con Otros Módulos

#### 6.7.1 Módulo Mecánico
- **Compartir:** Subida de evidencias
- **Diferenciar:** Mecánico sube, Sistema almacena
- **Flujo:** Mecánico registra mantenimiento, sube fotos

#### 6.7.2 Módulo Asesor
- **Compartir:** Subida de fotos de recepción
- **Diferenciar:** Asesor sube, Sistema almacena
- **Flujo:** Asesor recibe vehículo, sube foto de recepción

#### 6.7.3 Módulo Cliente
- **Compartir:** Visualización de fotos
- **Diferenciar:** Cliente consulta, Sistema muestra
- **Flujo:** Cliente ve evidencias de sus mantenimientos

#### 6.7.4 Módulo Administrador
- **Compartir:** Auditoría de fotos
- **Diferenciar:** Admin supervisa, Sistema muestra
- **Flujo:** Admin audita todas las fotos del sistema

### 6.8 Validaciones

#### 6.8.1 Archivos
- **Tipo:** image/jpeg, image/png, image/webp
- **Tamaño máximo:** 10MB
- **Ancho/Alto:** No hay límite específico
- **Contenido:** Validación de que sea imagen válida

#### 6.8.2 Metadata
- **Orden:** Existente y accesible
- **Usuario:** Autenticado y con permiso
- **Tipo:** Enum válido (reception, before, after, evidence)
- **Descripción:** Máximo 500 caracteres

### 6.9 Estados y Transiciones

#### 6.9.1 Estados de Fotos
```
subida → visible (instantáneo)
visible → eliminada (eliminación)
```

### 6.10 Métricas y Criterios de Aceptación

#### 6.10.1 Métricas de Performance
- **Tiempo de subida de foto:** < 5 segundos (según tamaño)
- **Tiempo de carga de galería:** < 2 segundos
- **Tiempo de visualización:** < 1 segundo

#### 6.10.2 Criterios de Aceptación
- [ ] **CRÍTICO:** Interfaz de subida implementada
- [ ] **CRÍTICO:** Interfaz de visualización implementada
- [ ] **CRÍTICO:** Interfaz de auditoría implementada
- [ ] Validación de archivos funcionando
- [ ] Storage implementado correctamente
- [ ] Autorización por rol funcionando
- [ ] ActivityLog registrando subidas
- [ ] Filtros por tipo funcionando
- [ ] Responsive design implementado

---

## 7. MÓDULO INVENTARIO

### 7.1 Objetivo del Módulo
Gestión de productos, proveedores, compras y stock con auditoría completa de movimientos.

### 7.2 Usuarios Objetivo
- **Principal:** Administradores
- **Secundario:** Asesores (consulta)

### 7.3 Flujo de Usuario Principal

```
Gestión de Productos → Gestión de Proveedores → Registro de Compras → Recepción de Compras → Actualización de Stock → Auditoría de Movimientos
```

**Flujo Detallado:**
1. Administrador gestiona productos
2. Configura proveedores
3. Registra compras
4. Recibe compras
5. Sistema actualiza stock automáticamente
6. Sistema registra movimientos
7. Administrador audita movimientos
8. Sistema genera alertas de stock bajo

### 7.4 Requisitos Funcionales

#### 7.4.1 Gestión de Productos
- **CRUD completo** de productos
- **Filtros por categoría** y marca
- **Validaciones de stock**
- **Respuesta JSON** para AJAX
- **Protección contra eliminación** con relaciones

**Casos de Uso:**
- UC-INV-01: Crear producto
- UC-INV-02: Editar producto
- UC-INV-03: Eliminar producto
- UC-INV-04: Buscar productos
- UC-INV-05: Filtrar productos por categoría
- UC-INV-06: Ver stock de producto

#### 7.4.2 Gestión de Categorías
- **CRUD completo** de categorías
- **Asociación con productos**
- **Ordenamiento**

**Casos de Uso:**
- UC-INV-07: Crear categoría
- UC-INV-08: Editar categoría
- UC-INV-09: Eliminar categoría
- UC-INV-10: Ver productos por categoría

#### 7.4.3 Gestión de Marcas
- **CRUD completo** de marcas
- **Asociación con productos**
- **Ordenamiento**

**Casos de Uso:**
- UC-INV-11: Crear marca
- UC-INV-12: Editar marca
- UC-INV-13: Eliminar marca
- UC-INV-14: Ver productos por marca

#### 7.4.4 Gestión de Proveedores
- **CRUD completo** de proveedores
- **Asociación con compras**
- **Validación de estado**

**Casos de Uso:**
- UC-INV-15: Crear proveedor
- UC-INV-16: Editar proveedor
- UC-INV-17: Desactivar proveedor
- UC-INV-18: Ver compras por proveedor

#### 7.4.5 Gestión de Compras
- **CRUD completo** de compras
- **Gestión de estados** (pendiente/recibida)
- **Cálculo automático** de impuestos
- **Integración con stock** y StockMovement
- **Recepción de compras** actualiza inventario
- **Protección contra edición/eliminación** de recibidas

**Casos de Uso:**
- UC-INV-19: Crear compra
- UC-INV-20: Editar compra (pendiente)
- UC-INV-21: Recibir compra
- UC-INV-22: Ver detalles de compra
- UC-INV-23: Ver historial de compras

#### 7.4.6 Gestión de Stock
- **Vista consolidada** de stock
- **Alertas de stock bajo**
- **Movimientos de stock** (StockMovement)
- **Ajustes manuales** de stock
- **Auditoría de movimientos**

**Casos de Uso:**
- UC-INV-24: Ver stock consolidado
- UC-INV-25: Ver productos con stock bajo
- UC-INV-26: Ver movimientos de stock
- UC-INV-27: Ajustar stock manualmente
- UC-INV-28: Ver auditoría de movimientos

### 7.5 Requisitos Técnicos

#### 7.5.1 Controladores
- **InventoryController:** Vista de inventario
- **ProductController:** CRUD de productos
- **CategoryController:** CRUD de categorías
- **BrandController:** CRUD de marcas
- **SupplierController:** CRUD de proveedores
- **PurchaseController:** Gestión de compras
- **StockController:** Gestión de stock

#### 7.5.2 Models
- **Product:** Modelo de productos
- **Category:** Modelo de categorías
- **Brand:** Modelo de marcas
- **Supplier:** Modelo de proveedores
- **Purchase:** Modelo de compras
- **PurchaseItem:** Items de compras
- **StockMovement:** Movimientos de stock

#### 7.5.3 Middleware
- **auth:** Verificación de autenticación
- **role:admin:** Verificación de rol administrador

#### 7.5.4 Rutas
- **Prefijo:** `/admin`
- **Middleware:** auth, role:admin
- **Ejemplo:** `/admin/products`, `/admin/purchases`

### 7.6 Interfaz de Usuario

#### 7.6.1 Vista de Inventario
- **Tabla consolidada** de productos
- **Indicadores de stock** (bajo, normal, alto)
- **Filtros por categoría y marca**
- **Búsqueda global**
- **Acciones rápidas**

#### 7.6.2 Formularios
- **Creación de productos** con selectores dinámicos
- **Registro de compras** con items dinámicos
- **Recepción de compras** con validación
- **Ajustes de stock** con justificación

### 7.7 Integración con Otros Módulos

#### 7.7.1 Módulo Mecánico
- **Compartir:** Consumo de repuestos
- **Diferenciar:** Mecánico consume, Inventario registra
- **Flujo:** Mecánico usa repuestos, Inventario debería actualizar (NO IMPLEMENTADO)

#### 7.7.2 Módulo Administrador
- **Compartir:** Gestión completa
- **Diferenciar:** Solo módulo de inventario
- **Flujo:** Admin gestiona todo el inventario

#### 7.7.3 Módulo Asesor
- **Compartir:** Consulta de stock
- **Diferenciar:** Asesor consulta, Admin gestiona
- **Flujo:** Asesor verifica disponibilidad

### 7.8 Validaciones

#### 7.8.1 Productos
- **SKU:** Único
- **Nombre:** Requerido
- **Categoría:** Existente (si aplica)
- **Marca:** Existente (si aplica)
- **Stock:** No negativo
- **Precios:** No negativos

#### 7.8.2 Compras
- **Proveedor:** Existente y activo
- **Items:** Al menos uno
- **Cantidades:** Positivas
- **Precios:** No negativos
- **Estado:** Protegido según flujo

### 7.9 Estados y Transiciones

#### 7.9.1 Estados de Compras
```
pendiente → recibida (recepción)
pendiente → cancelada (cancelación)
recibida → bloqueada (no editable)
```

#### 7.9.2 Estados de Stock
```
normal → bajo (bajo min_stock)
bajo → normal (supera min_stock)
```

### 7.10 Métricas y Criterios de Aceptación

#### 7.10.1 Métricas de Performance
- **Tiempo de carga de inventario:** < 2 segundos
- **Tiempo de registro de compra:** < 1 segundo
- **Tiempo de recepción de compra:** < 1.5 segundos

#### 7.10.2 Criterios de Aceptación
- [ ] CRUD de productos funcionando
- [ ] Gestión de categorías funcionando
- [ ] Gestión de marcas funcionando
- [ ] Gestión de proveedores funcionando
- [ ] Gestión de compras funcionando
- [ ] Recepción de compras actualizando stock
- [ ] StockMovement registrando movimientos
- [ ] Alertas de stock bajo funcionando
- [ ] Auditoría de movimientos funcionando

---

## 8. MATRIZ DE INTEGRACIÓN ENTRE MÓDULOS

### 8.1 Diagrama de Dependencias

```
┌─────────────────────────────────────────────────────────────┐
│                       MÓDULO ADMINISTRADOR                     │
│  (Gestión central, configuración, supervisión)               │
└──────────────┬──────────────────┬────────────────────────────┘
               │                  │
               ├──────────────────┤
               │                  │
┌──────────────▼──────┐  ┌───────▼──────────────────┐
│   MÓDULO ASESOR     │  │   MÓDULO INVENTARIO     │
│  (Gestión clientes) │  │  (Gestión productos)    │
└──────────┬───────────┘  └──────────────────────────┘
           │
           ├──────────────────┐
           │                  │
┌──────────▼──────┐  ┌───────▼──────────────────┐
│ MÓDULO MECÁNICO  │  │   MÓDULO CHATBOT         │
│  (Ejecución)     │  │  (Atención inteligente)  │
└──────────┬───────┘  └──────────────────────────┘
           │
           │
┌──────────▼──────────────────────────┐
│      MÓDULO CLIENTE                 │
│   (Consulta y seguimiento)          │
└─────────────────────────────────────┘

MÓDULO FOTOGRAFÍAS (Integrado con Mecánico, Asesor, Cliente, Admin)
```

### 8.2 Matriz de Interacción

| Módulo | Admin | Asesor | Mecánico | Cliente | Chatbot | Fotografías | Inventario |
|--------|-------|--------|-----------|---------|---------|-------------|------------|
| **Admin** | - | Configura usuarios | Asigna mecánicos | Configura vehículos | Configura FAQs | Audita fotos | Gestiona inventario |
| **Asesor** | Reporta | - | Asigna órdenes | Gestiona clientes | Recibe solicitudes | Sube fotos recepción | Consulta stock |
| **Mecánico** | Reporta progreso | Recibe asignaciones | - | Actualiza estado | - | Sube fotos trabajo | Debería consumir |
| **Cliente** | Consulta | Solicita citas | Consulta progreso | - | Interactúa | Visualiza fotos | - |
| **Chatbot** | Consulta | Escala | - | Atiende | - | - | - |
| **Fotografías** | Audita | Sube | Sube | Visualiza | - | - | - |
| **Inventario** | Gestiona | Consulta | - | - | - | - | - |

### 8.3 Flujos de Datos Principales

#### 8.3.1 Flujo de Orden de Servicio
```
Cliente (Chatbot/Manual) → Asesor → Mecánico → Cliente
Asesor crea orden → Mecánico ejecuta → Cliente ve progreso
```

#### 8.3.2 Flujo de Citas
```
Cliente (Chatbot) → Asesor → Mecánico
Chatbot crea solicitud → Asesor confirma → Mecánico recibe
```

#### 8.3.3 Flujo de Mantenimiento
```
Mecánico → Vehículo → Orden → Cliente
Mecánico registra → Vehículo actualiza → Orden sincroniza → Cliente consulta
```

#### 8.3.4 Flujo de Fotografías
```
Mecánico/Asesor → Storage → Cliente/Admin
Sube foto → Almacena → Visualiza/Audita
```

---

## 9. CRITERIOS DE ACEPTACIÓN GENERALES

### 9.1 Criterios Funcionales
- [ ] Todos los módulos funcionando correctamente
- [ ] Integración entre módulos operativa
- [ ] Flujos de negocio completos
- [ ] ActivityLog registrando todas las acciones
- [ ] Policies protegiendo recursos adecuadamente

### 9.2 Criterios Técnicos
- [ ] Laravel 12 funcionando correctamente
- [ ] MySQL configurado y optimizado
- [ ] Vistas compiladas sin errores
- [ ] Assets optimizados
- [ ] Responsive design implementado

### 9.3 Criterios de Seguridad
- [ ] **CRÍTICO:** CSRF protection activo en todas las rutas
- [ ] **CRÍTICO:** Rate limiting implementado
- [ ] Autenticación funcionando correctamente
- [ ] Autorización por rol implementada
- [ ] Input validation robusto

### 9.4 Criterios de Calidad
- [ ] Código siguiendo PSR-12
- [ ] Sin código muerto
- [ ] Documentación PHPDoc en controladores
- [ ] Nomenclatura consistente
- [ ] Testing implementado

---

## 10. ESTIMACIÓN DE ESFUERZO POR MÓDULO

### 10.1 Implementación Inicial
- **Módulo Administrador:** 40-60 horas
- **Módulo Asesor:** 30-45 horas
- **Módulo Mecánico:** 25-40 horas
- **Módulo Cliente:** 20-35 horas
- **Módulo Chatbot:** 35-50 horas
- **Módulo Fotografías:** 30-45 horas
- **Módulo Inventario:** 25-40 horas

**Total Implementación:** 205-270 horas

### 10.2 Correcciones Críticas
- **CSRF bypass (Chatbot):** 2-3 horas
- **Módulo Fotografías sin Frontend:** 26-36 horas
- **Estandarización nomenclatura:** 6-8 horas

**Total Correcciones:** 34-47 horas

### 10.3 Refactoring Arquitectónico
- **Estandarización estructura:** 8-12 horas
- **Repository Pattern:** 16-24 horas
- **Service Layer:** 20-30 horas
- **Event System:** 12-16 horas
- **Cache y Colas:** 20-28 horas

**Total Refactoring:** 76-110 horas

**TOTAL GLOBAL:** 315-427 horas

---

## CONCLUSIÓN

Este documento proporciona especificaciones técnicas detalladas para cada módulo del sistema AutoGest, incluyendo objetivos, flujos de usuario, requisitos funcionales y técnicos, interfaces de usuario, integración entre módulos, validaciones, estados, y criterios de aceptación.

Las especificaciones están diseñadas para guiar la implementación controlada y asegurar que cada módulo cumpla con los estándares de calidad, seguridad, y mantenibilidad requeridos para el sistema.

---

**Especificación preparada por:** Equipo Multidisciplinario de Ingeniería de Software  
**Fecha:** 2026-08-04  
**Versión:** 1.0  
**Estado:** Aprobada para revisión y comentarios

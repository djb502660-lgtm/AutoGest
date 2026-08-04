# SPEC GENERAL - AUTOGEST
**Sistema de Gestión de Mantenimiento Vehicular**
**Versión:** 1.0
**Fecha:** 2026-08-04
**Estado:** Especificación Técnica

---

## 1. OBJETIVO Y ALCANCE

### 1.1 Objetivo Principal
AutoGest es una plataforma web integral para la gestión de mantenimiento vehicular que permite a talleres automotrices, concesionarios y flotas vehiculares administrar de manera eficiente el ciclo completo de servicio vehicular, desde la recepción hasta la entrega, con soporte para atención automatizada mediante chatbot inteligente.

### 1.2 Objetivos Específicos

#### 1.2.1 Gestión Operativa
- Control completo del ciclo de vida de mantenimientos vehiculares
- Asignación eficiente de recursos (mecánicos, equipos, repuestos)
- Seguimiento en tiempo real de órdenes de servicio
- Documentación técnica y fotográfica de trabajos

#### 1.2.2 Gestión de Clientes
- Portal personal para consulta de estado de vehículos
- Historial completo de mantenimientos y gastos
- Agendamiento de citas mediante chatbot
- Notificaciones automáticas de vencimientos

#### 1.2.3 Gestión de Inventario
- Control de stock de repuestos y suministros
- Gestión de proveedores y compras
- Alertas de stock bajo
- Auditoría de movimientos de inventario

#### 1.2.4 Atención Inteligente
- Chatbot con procesamiento de lenguaje natural
- Diagnóstico guiado de síntomas mecánicos
- Agendamiento automático de citas
- Escalamiento inteligente a asesores humanos

#### 1.2.5 Preparación Móvil
- Sistema optimizado para WebView Android
- Soporte para cámara y galería del dispositivo
- Responsive design para móviles
- Soporte para back button y deep links

### 1.3 Alcance del Sistema

#### Incluye:
- ✅ Gestión de usuarios con roles (admin, asesor, mecánico, cliente)
- ✅ Gestión completa de vehículos y clientes
- ✅ Sistema de órdenes de servicio con asignación de mecánicos
- ✅ Registro detallado de mantenimientos
- ✅ Sistema de citas con chatbot y manual
- ✅ Gestión de inventario y compras
- ✅ Reportes y análisis
- ✅ Sistema de fotografías de evidencias
- ✅ Chatbot inteligente integrado
- ✅ Calendario de mantenimientos
- ✅ Sistema de alertas automáticas
- ✅ Auditoría completa de actividades

#### Excluye:
- ❌ Gestión financiera completa (facturación básica solamente)
- ❌ Integración con sistemas de terceros (excepto IA opcional)
- ❌ Aplicación móvil nativa (WebView solamente)
- ❌ Sistemas de pago en línea
- ❌ Gestión de flotas corporativas avanzada

### 1.4 Usuarios Objetivo

#### Primarios:
- **Administradores:** Control total del sistema
- **Asesores de Servicio:** Gestión de clientes y citas
- **Mecánicos:** Ejecución de trabajos asignados
- **Clientes:** Consulta y seguimiento de sus vehículos

#### Secundarios:
- **Gerentes:** Acceso a reportes y métricas
- **Auditores:** Acceso a bitácora de actividades

---

## 2. ARQUITECTURA DEL SISTEMA

### 2.1 Arquitectura General
**Patrón:** MVC (Model-View-Controller) con Service Layer opcional

```
┌─────────────────────────────────────────────────────────────┐
│                        PRESENTATION                           │
├─────────────────────────────────────────────────────────────┤
│  Frontend (Blade + Tailwind + JavaScript + Vite)            │
│  ├── Views/ (Admin, Advisor, Mechanic, Client, Chatbot)    │
│  ├── CSS/ (TailwindCSS 4.0)                                 │
│  ├── JS/ (Vanilla + Axios)                                   │
│  └── Assets compilados por Vite                              │
├─────────────────────────────────────────────────────────────┤
│                      APPLICATION LOGIC                         │
├─────────────────────────────────────────────────────────────┤
│  Controllers (Admin, Advisor, Mechanic, Client, Auth)         │
│  ├── HTTP/Middleware (Auth, Role, CSRF)                     │
│  ├── Services (Chatbot, ChatbotAppointment, Calendar)         │
│  ├── Policies (Autorización por recurso)                    │
│  └── Jobs (Notificaciones asíncronas)                        │
├─────────────────────────────────────────────────────────────┤
│                         DATA LAYER                             │
├─────────────────────────────────────────────────────────────┤
│  Models (Eloquent ORM)                                        │
│  ├── User, Vehicle, ServiceOrder, Maintenance                │
│  ├── AppointmentRequest, MaintenanceSchedule                │
│  ├── Product, Supplier, Purchase, StockMovement              │
│  ├── ChatbotFaq, ChatbotMessage, ServicePhoto                │
│  └── ActivityLog, Alert, SystemSetting                       │
├─────────────────────────────────────────────────────────────┤
│                      INFRASTRUCTURE                              │
├─────────────────────────────────────────────────────────────┤
│  Laravel 12 Framework                                         │
│  MySQL 8.x Database                                           │
│  Storage (Local/Filesystem)                                   │
│  Queue (Database/Redis opcional)                             │
│  Cache (File/Redis opcional)                                  │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Patrones de Diseño Implementados

#### 2.2.1 Patrones Activos
- **MVC:** Arquitectura principal
- **Repository:** Parcial (algunos modelos)
- **Service:** ChatbotService, ChatbotAppointmentService
- **Policy:** Autorización por recurso
- **Factory:** DemoSeeder, Model Factories
- **Strategy:** Redirección por rol, detección de intenciones
- **Observer:** ActivityLog (implícito en modelos)

#### 2.2.2 Patrones Recomendados
- **Repository:** Implementación completa para abstracción de datos
- **Service:** Expansión a más lógica de negocio
- **Command:** Para acciones complejas
- **Event:** Para notificaciones automáticas
- **Decorator:** Para funcionalidades opcionales

### 2.3 Principios SOLID Aplicados

#### 2.3.1 Single Responsibility
- ✅ Controladores enfocados en HTTP
- ✅ Servicios para lógica de negocio compleja
- ✅ Models para acceso a datos
- ⚠️ Algunos controladores con demasiada responsabilidad

#### 2.3.2 Open/Closed
- ✅ Enums para roles y estados
- ✅ Interfaces para políticas
- ⚠️ Faltan interfaces para servicios

#### 2.3.3 Liskov Substitution
- ✅ User como base para diferentes roles
- ✅ Models intercambiables en relaciones

#### 2.3.4 Interface Segregation
- ✅ Policies específicas por recurso
- ⚠️ Faltan interfaces para servicios

#### 2.3.5 Dependency Inversion
- ✅ Inyección de dependencias en constructores
- ✅ Service container de Laravel

### 2.4 Arquitectura de Módulos

#### 2.4.1 Estructura de Directorios
```
AutoGest/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Módulo Administrador
│   │   │   ├── Advisor/         # Módulo Asesor
│   │   │   ├── Mechanic/        # Módulo Mecánico
│   │   │   ├── Client/          # Módulo Cliente
│   │   │   └── Auth/            # Autenticación
│   │   ├── Middleware/          # Middleware personalizados
│   │   └── Requests/            # Form Requests
│   ├── Models/                  # Modelos Eloquent
│   ├── Policies/                # Policies de autorización
│   ├── Services/                # Lógica de negocio
│   ├── Enums/                   # Enumeraciones
│   ├── Jobs/                    # Trabajos asíncronos
│   ├── Mail/                    # Correos electrónicos
│   └── Modules/                 # Módulos independientes
│       └── Chatbot/             # Módulo Chatbot
├── backend/
│   └── routes/                  # Definición de rutas
├── frontend/
│   └── views/                   # Vistas Blade
├── database/
│   ├── migrations/               # Migraciones de BD
│   └── seeders/                 # Datos de demostración
├── config/                       # Configuración Laravel
├── public/                       # Punto de entrada web
└── docs/                         # Documentación
```

#### 2.4.2 Módulo Chatbot Independiente
```
app/Modules/Chatbot/
├── ChatbotServiceProvider.php
├── Http/
│   └── Controllers/
│       └── Client/
│           └── ChatbotController.php
├── Resources/
│   └── views/
│       └── client/
│           └── chatbot/
│               └── index.blade.php
└── routes.php
```

---

## 3. DEPENDENCIAS Y TECNOLOGÍAS

### 3.1 Backend

#### 3.1.1 Core
- **PHP:** 8.2+ (requerido)
- **Laravel:** 12.0 (framework principal)
- **Composer:** Gestión de dependencias PHP

#### 3.1.2 Base de Datos
- **MySQL:** 8.x (sistema de base de datos)
- **Eloquent ORM:** Laravel ORM
- **Query Builder:** Constructor de consultas

#### 3.1.3 Librerías PHP
- **barryvdh/laravel-dompdf:** ^3.1 (generación de PDFs)
- **laravel/tinker:** ^2.10.1 (consola interactiva)

#### 3.1.4 Desarrollo
- **laravel/pail:** ^1.2.2 (logs en tiempo real)
- **laravel/pint:** ^1.24 (formateo de código)
- **mockery/mockery:** ^1.6 (mocking para tests)
- **phpunit/phpunit:** ^11.5.50 (testing framework)
- **fakerphp/faker:** ^1.23 (datos de prueba)

### 3.2 Frontend

#### 3.2.1 Core
- **Node.js:** (gestión de dependencias JS)
- **npm:** Gestor de paquetes

#### 3.2.2 Frameworks y Librerías
- **Vite:** ^7.0.7 (bundler de assets)
- **TailwindCSS:** ^4.0.0 (framework CSS)
- **@tailwindcss/vite:** ^4.0.0 (integración Vite)
- **axios:** ^1.11.0 (cliente HTTP)
- **laravel-vite-plugin:** ^2.0.0 (integración Laravel-Vite)
- **concurrently:** ^9.0.1 (ejecución paralela de procesos)

#### 3.2.3 Views
- **Blade:** Motor de plantillas Laravel
- **JavaScript:** Vanilla (sin frameworks JS)

### 3.3 Infraestructura

#### 3.3.1 Servidor Web
- **Apache:** Servidor web (vía Laragon)
- **PHP-FPM:** Procesador PHP

#### 3.3.2 Base de Datos
- **MySQL:** Servidor de base de datos
- **Redis:** Opcional para cache y colas

#### 3.3.3 Storage
- **Local:** Sistema de archivos local
- **Public:** Disco público para assets

### 3.4 Opcionales

#### 3.4.1 Inteligencia Artificial
- **OpenAI API:** GPT-4o-mini (opcional para chatbot)
- **Configuración:** services.openai.api_key

#### 3.4.2 Cache y Colas
- **Redis:** Opcional para cache y colas
- **Configuración:** CACHE_DRIVER, QUEUE_CONNECTION

### 3.5 Versiones Mínimas Requeridas

```yaml
Backend:
  PHP: 8.2+
  Laravel: 12.0
  MySQL: 8.0+
  Composer: 2.0+

Frontend:
  Node.js: 18+
  npm: 9+
  Vite: 7.0+
```

---

## 4. MODELOS DE DATOS

### 4.1 Modelos Principales

#### 4.1.1 User
**Propósito:** Gestión de usuarios del sistema

**Atributos:**
- id (PK)
- name (string)
- email (string, unique)
- password (hashed)
- role (enum: admin, asesor, mecanico, cliente)
- phone (string, nullable)
- status (enum: activo, inactivo)
- last_login_at (timestamp, nullable)
- email_verified_at (timestamp, nullable)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- hasMany: vehicles, clientOrders, assignedOrders, advisorOrders
- hasMany: maintenances, alerts, activityLogs, chatbotMessages

**Métodos Importantes:**
- isAdmin(), isMechanic(), isAdvisor(), isClient()
- isActive()
- accessibleVehicleIds()

#### 4.1.2 Vehicle
**Propósito:** Gestión de vehículos de clientes

**Atributos:**
- id (PK)
- client_id (FK → users.id)
- plate (string, unique)
- brand (string)
- model (string)
- year (smallint, nullable)
- color (string, nullable)
- mileage (integer, default 0)
- vin (string, nullable)
- photo (string, nullable)
- status (enum: activo, inactivo, en_taller)
- insurance_expiry (date, nullable)
- inspection_expiry (date, nullable)
- notes (text, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: client (User)
- hasMany: serviceOrders, maintenances, maintenanceSchedules, alerts

**Índices:**
- client_id, plate, status

#### 4.1.3 ServiceOrder
**Propósito:** Órdenes de trabajo/servicio

**Atributos:**
- id (PK)
- order_number (string, unique)
- vehicle_id (FK → vehicles.id)
- client_id (FK → users.id)
- mechanic_id (FK → users.id, nullable)
- advisor_id (FK → users.id, nullable)
- created_by (FK → users.id)
- source (enum: manual, chatbot)
- status (enum: recibida, en_proceso, completada, entregada, cancelada)
- progress (integer, default 0)
- priority (enum: baja, normal, alta, urgente)
- description (text)
- diagnosis (text, nullable)
- recommendations (text, nullable)
- scheduled_at (datetime, nullable)
- started_at (datetime, nullable)
- completed_at (datetime, nullable)
- estimated_cost (decimal, nullable)
- total_cost (decimal, default 0)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: vehicle, client, mechanic, advisor, creator
- hasMany: maintenances, comments, photos

**Métodos Importantes:**
- statusLabel(), statusBadgeClass()
- generateOrderNumber()

#### 4.1.4 Maintenance
**Propósito:** Registro detallado de mantenimientos

**Atributos:**
- id (PK)
- service_order_id (FK → service_orders.id, nullable)
- vehicle_id (FK → vehicles.id)
- mechanic_id (FK → users.id)
- type (enum: preventivo, correctivo, garantia)
- description (string)
- mileage_at_service (integer, nullable)
- fuel_level (enum: Reserva, 1/4, 1/2, 3/4, Lleno, nullable)
- inventory_spare_wheel (boolean, default true)
- inventory_tools (boolean, default true)
- inventory_radio (boolean, default true)
- inventory_documents (boolean, default false)
- parts_used (text, nullable)
- technical_notes (text, nullable)
- cost (decimal, default 0)
- parts_cost (decimal, default 0)
- labor_cost (decimal, default 0)
- status (enum: pendiente, en_proceso, completado, cancelado)
- performed_at (datetime, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: serviceOrder, vehicle, mechanic

**Métodos Importantes:**
- statusLabel(), statusBadgeClass()
- typeLabel()

#### 4.1.5 AppointmentRequest
**Propósito:** Solicitudes y citas de clientes

**Atributos:**
- id (PK)
- client_id (FK → users.id)
- vehicle_id (FK → vehicles.id)
- vehicle_model_template_id (FK → vehicle_model_templates.id, nullable)
- requested_date (date)
- preferred_time (string, nullable)
- service_type (string)
- description (text)
- priority (enum: baja, normal, alta, urgente)
- source (enum: manual, chatbot)
- status (enum: pendiente, confirmada, rechazada, cancelada, convertida)
- notes (text, nullable)
- service_order_id (FK → service_orders.id, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: client, vehicle, vehicleModelTemplate, serviceOrder

#### 4.1.6 ServicePhoto
**Propósito:** Evidencias fotográficas de mantenimientos

**Atributos:**
- id (PK)
- service_order_id (FK → service_orders.id)
- user_id (FK → users.id)
- photo_path (string)
- description (string, nullable)
- type (enum: reception, before, after, evidence)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: serviceOrder, user

**Métodos Importantes:**
- getTypeLabelAttribute()

### 4.2 Modelos de Inventario

#### 4.2.1 Product
**Propósito:** Productos/repuestos del inventario

**Atributos:**
- id (PK)
- category_id (FK → categories.id, nullable)
- brand_id (FK → brands.id, nullable)
- name (string)
- sku (string, unique)
- description (text, nullable)
- purchase_price (decimal)
- sale_price (decimal)
- stock_quantity (integer, default 0)
- min_stock (integer, default 0)
- max_stock (integer, nullable)
- unit (string)
- is_active (boolean, default true)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: category, brand
- hasMany: purchaseItems, stockMovements

#### 4.2.2 Purchase
**Propósito:** Compras a proveedores

**Atributos:**
- id (PK)
- purchase_number (string, unique)
- supplier_id (FK → suppliers.id)
- purchase_date (date)
- subtotal (decimal)
- tax (decimal)
- total (decimal)
- status (enum: pendiente, recibida, cancelada)
- notes (text, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: supplier
- hasMany: items

#### 4.2.3 StockMovement
**Propósito:** Auditoría de movimientos de stock

**Atributos:**
- id (PK)
- product_id (FK → products.id)
- type (enum: entrada, salida, ajuste)
- quantity (integer)
- previous_stock (integer)
- new_stock (integer)
- purchase_id (FK → purchases.id, nullable)
- notes (text, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: product, purchase

### 4.3 Modelos de Chatbot

#### 4.3.1 ChatbotMessage
**Propósito:** Historial de conversaciones del chatbot

**Atributos:**
- id (PK)
- user_id (FK → users.id, nullable)
- session_id (string)
- sender (enum: user, bot)
- message (text)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: user

#### 4.3.2 ChatbotFaq
**Propósito:** Preguntas frecuentes configurables

**Atributos:**
- id (PK)
- question (string)
- answer (text)
- keywords (string, nullable)
- is_active (boolean, default true)
- sort_order (integer, default 0)
- created_at, updated_at (timestamps)

### 4.4 Modelos de Soporte

#### 4.4.1 ActivityLog
**Propósito:** Auditoría de actividades del sistema

**Atributos:**
- id (PK)
- user_id (FK → users.id, nullable)
- action (string)
- model_type (string, nullable)
- model_id (integer, nullable)
- description (text, nullable)
- ip_address (string, nullable)
- user_agent (string, nullable)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: user

**Métodos Estáticos:**
- record(action, description, model, user)

#### 4.4.2 Alert
**Propósito:** Alertas automáticas del sistema

**Atributos:**
- id (PK)
- vehicle_id (FK → vehicles.id, nullable)
- user_id (FK → users.id)
- type (enum: maintenance_due, insurance_expiry, inspection_expiry, custom)
- title (string)
- message (text)
- severity (enum: info, warning, critical)
- due_date (date, nullable)
- is_read (boolean, default false)
- is_resolved (boolean, default false)
- created_at, updated_at (timestamps)

**Relaciones:**
- belongsTo: vehicle, user

#### 4.4.3 SystemSetting
**Propósito:** Configuración del sistema

**Atributos:**
- id (PK)
- key (string, unique)
- value (text)
- group (string, nullable)
- created_at, updated_at (timestamps)

---

## 5. CONTROLADORES Y SERVICIOS

### 5.1 Controladores por Módulo

#### 5.1.1 Módulo Administrador
**Ruta:** `/admin/*`
**Middleware:** auth, role:admin

**Controladores:**
- **DashboardController:** KPIs, calendario, estadísticas
- **UserController:** CRUD de usuarios
- **VehicleController:** CRUD de vehículos
- **OrderController:** Consulta de órdenes (index, show, invoice)
- **MaintenanceController:** CRUD de mantenimientos
- **CalendarController:** Gestión de calendario
- **ReportController:** Generación de reportes (PDF, CSV, email)
- **ProfileController:** Gestión de perfil
- **InventoryController:** Vista de inventario
- **ProductController:** CRUD de productos
- **CategoryController:** CRUD de categorías
- **BrandController:** CRUD de marcas
- **SupplierController:** CRUD de proveedores
- **PurchaseController:** Gestión de compras
- **StockController:** Gestión de stock

#### 5.1.2 Módulo Asesor
**Ruta:** `/asesor/*`
**Middleware:** auth, role:asesor

**Controladores:**
- **DashboardController:** KPIs especializados
- **OrderController:** CRUD completo de órdenes, asignación de mecánicos
- **AppointmentController:** Gestión de citas
- **AppointmentRequestController:** Gestión de solicitudes
- **PreOrderController:** Gestión de pre-órdenes
- **ClientController:** Gestión de clientes
- **VehicleController:** Gestión de vehículos

#### 5.1.3 Módulo Mecánico
**Ruta:** `/mecanico/*`
**Middleware:** auth, role:mecanico

**Controladores:**
- **DashboardController:** Órdenes asignadas
- **OrderController:** Órdenes asignadas, actualización de estado
- **MaintenanceController:** Registro de mantenimientos
- **CalendarController:** Calendario personal
- **VehicleController:** Vehículos accesibles

#### 5.1.4 Módulo Cliente
**Ruta:** `/cliente/*`
**Middleware:** auth, role:cliente

**Controladores:**
- **DashboardController:** Estadísticas personales
- **VehicleController:** Consulta de vehículos
- **OrderController:** Consulta de órdenes personales
- **MaintenanceController:** Historial y próximos mantenimientos
- **ExpenseController:** Control de gastos
- **NotificationController:** Gestión de notificaciones
- **ProfileController:** Gestión de perfil

#### 5.1.5 Módulo Autenticación
**Ruta:** `/login`, `/logout`
**Middleware:** guest (login), auth (logout)

**Controladores:**
- **AuthController:** Login, logout

#### 5.1.6 Controladores Compartidos
- **ServicePhotoController:** Gestión de fotografías (mecánico, asesor)
- **HomeController:** Página de inicio

### 5.2 Servicios

#### 5.2.1 ChatbotService
**Propósito:** Coordinador general del chatbot

**Responsabilidades:**
- Procesamiento de mensajes de usuarios
- Detección de intenciones
- Gestión de contexto conversacional
- Integración con servicios especializados
- Escalamiento a asesores humanos

**Métodos Principales:**
- processMessage($user, $message): string
- vehicleStatus($user): string
- expenseSummary($user): string
- orderStatus($user): string
- handleAppointment($user, $message): string
- searchFaq($normalized): ?string
- askAI($user, $message): ?string

#### 5.2.2 ChatbotAppointmentService
**Propósito:** Gestión especializada de citas vía chatbot

**Responsabilidades:**
- Detección de intención de citas
- Gestión de flujo multi-paso
- Creación de citas
- Modificación de citas existentes
- Cancelación de citas
- Consulta de citas

**Métodos Principales:**
- shouldHandle($text): bool
- handle($client, $text): string
- wantsAppointment($text): bool
- wantsManage($text): bool
- parseDate($text): ?Carbon
- parseTime($text): ?string

#### 5.2.3 DashboardCalendarService
**Propósito:** Generación de widgets de calendario

**Responsabilidades:**
- Resolución de períodos
- Generación de widgets
- Formateo de eventos

**Métodos Principales:**
- resolvePeriod($month, $year): array
- makeWidget($period, $sources, $options): array

### 5.3 Jobs

#### 5.3.1 NotifyAdvisorsOfChatbotQuery
**Propósito:** Notificación a asesores de consultas del chatbot

**Responsabilidades:**
- Envío de notificación a asesores
- Información de usuario y consulta
- Registro de notificación

---

## 6. POLICIES Y MIDDLEWARE

### 6.1 Middleware

#### 6.1.1 Middleware de Laravel
- **auth:** Verificación de autenticación
- **guest:** Acceso solo para no autenticados
- **web:** Grupo de middleware web
- **csrf:** Protección CSRF

#### 6.1.2 Middleware Personalizados
- **role:** EnsureUserRole
  - Verifica rol del usuario
  - Soporta múltiples roles
  - Verifica estado activo
  - Aborta con 403 si no autorizado

**Uso:**
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Rutas de admin
});
```

### 6.2 Policies

#### 6.2.1 UserPolicy
**Propósito:** Autorización de acciones sobre usuarios

**Métodos:**
- viewAny($user): bool (solo admin)
- view($user, $model): bool (admin o propio usuario)
- create($user): bool (solo admin)
- update($user, $model): bool (admin o propio usuario)
- delete($user, $model): bool (admin, no auto-eliminación)
- assignRole($user, $model): bool (solo admin)

#### 6.2.2 ServiceOrderPolicy
**Propósito:** Autorización de acciones sobre órdenes

**Métodos:**
- viewAny($user): bool (todos los roles)
- view($user, $order): bool (según rol y relación)
- create($user): bool (admin, asesor)
- update($user, $order): bool (según rol y asignación)
- delete($user, $order): bool (solo admin)
- assign($user, $order): bool (admin, asesor)

#### 6.2.3 MaintenancePolicy
**Propósito:** Autorización de acciones sobre mantenimientos

**Métodos:**
- viewAny($user): bool (todos los roles)
- view($user, $maintenance): bool (según rol y relación)
- create($user): bool (admin, mecanico)
- update($user, $maintenance): bool (según rol y asignación)
- delete($user, $maintenance): bool (solo admin)

#### 6.2.4 VehiclePolicy
**Propósito:** Autorización de acciones sobre vehículos

**Métodos:**
- viewAny($user): bool (todos los roles)
- view($user, $vehicle): bool (según rol y relación)
- create($user): bool (admin, asesor)
- update($user, $vehicle): bool (según rol y relación)
- delete($user, $vehicle): bool (solo admin)

#### 6.2.5 VehicleModelTemplatePolicy
**Propósito:** Autorización de acciones sobre plantillas

**Métodos:**
- viewAny($user): bool (admin, asesor)
- view($user, $template): bool (admin, asesor)
- create($user): bool (solo admin)
- update($user, $template): bool (solo admin)
- delete($user, $template): bool (solo admin)

---

## 7. STORAGE Y ASSETS

### 7.1 Sistema de Storage

#### 7.1.1 Discos Configurados
- **local:** Sistema de archivos local (default)
- **public:** Disco público para assets accesibles vía web

#### 7.1.2 Estructura de Directorios
```
storage/
├── app/                  # Archivos de aplicación
├── framework/            # Framework Laravel
├── logs/                 # Logs del sistema
└── service-photos/       # Fotografías de servicios (public)
```

#### 7.1.3 Storage Link
**Comando:** `php artisan storage:link`
**Propósito:** Crear enlace simbólico de `public/storage` a `storage/app/public`
**Acceso web:** `http://autogest.test/storage/service-photos/...`

### 7.2 Assets

#### 7.2.1 Estructura de Frontend
```
frontend/
├── css/                  # Estilos compilados
├── js/                   # JavaScript compilado
└── views/                # Vistas Blade
    ├── admin/           # Vistas administrador
    ├── advisor/         # Vistas asesor
    ├── mechanic/        # Vistas mecánico
    ├── client/          # Vistas cliente
    ├── auth/            # Vistas autenticación
    ├── layouts/         # Layouts compartidos
    └── components/      # Componentes reutilizables
```

#### 7.2.2 Proceso de Compilación
1. **Desarrollo:** `npm run dev` (Vite dev server con hot reload)
2. **Producción:** `npm run build` (optimización y minificación)
3. **Vite Plugin:** Integración con Laravel para asset versioning

#### 7.2.3 Optimización
- **CSS:** TailwindCSS con purgado de clases no usadas
- **JS:** Minificación y bundling
- **Versioning:** Hash de archivos para cache busting
- **Lazy Loading:** Carga diferida de JavaScript

### 7.3 Fotografías

#### 7.3.1 Almacenamiento
- **Ubicación:** `storage/app/public/service-photos/`
- **Acceso web:** `/storage/service-photos/...`
- **Nomenclatura:** Nombre único basado en timestamp

#### 7.3.2 Tipos de Fotografías
- **reception:** Foto al recibir el vehículo
- **before:** Antes de iniciar el trabajo
- **after:** Después de completar el trabajo
- **evidence:** Evidencia general

#### 7.3.3 Validaciones
- Tipo: image/jpeg, image/png, image/webp
- Tamaño máximo: 10MB
- Dimensiones: No hay límite específico

#### 7.3.4 Gestión
- **Subida:** ServicePhotoController::store()
- **Eliminación:** ServicePhotoController::destroy()
- **Acceso:** Storage::url($path)
- **Autorización:** Por rol y dueño de la foto

---

## 8. PREPARACIÓN PARA APK ANDROID

### 8.1 Requisitos para WebView

#### 8.1.1 Responsiveness
- ✅ TailwindCSS responsive classes implementadas
- ✅ Viewport meta tag configurado
- ✅ Touch-friendly UI elements
- ⚠️ Algunas vistas pueden necesitar ajustes móviles

#### 8.1.2 Funcionalidades Críticas para WebView
- **Login/Logout:** ✅ Implementado con sesión Laravel
- **Cookies:** ✅ Manejo estándar de Laravel
- **CSRF:** ⚠️ Requiere corrección en chatbot
- **Storage:** ✅ Storage de Laravel compatible
- **Upload de Archivos:** ✅ Compatible con WebView
- **Cámara/Galería:** ⚠️ Requiere implementación en WebView
- **Back Button:** ⚠️ Requiere manejo en WebView
- **Deep Links:** ⚠️ Requiere configuración

#### 8.1.3 Optimizaciones Móviles
- **Minificación de Assets:** ✅ Vite implementation
- **Lazy Loading:** ⚠️ Parcialmente implementado
- **Service Worker:** ❌ No implementado
- **PWA Manifest:** ❌ No implementado

### 8.2 Configuración Requerida

#### 8.2.1 Virtual Host
- **URL:** `http://autogest.test` (no localhost)
- **HTTPS:** Requerido para producción (SSL certificate)
- **CORS:** Configuración adecuada para WebView

#### 8.2.2 Seguridad
- **CSRF:** Tokens implementados en frontend
- **HTTPS:** Obligatorio para producción
- **Certificate:** SSL válido para dominio

#### 8.2.3 Performance
- **Tiempo de Carga:** < 3 segundos objetivo
- **Tamaño de Assets:** Minimizar y optimizar
- **Cache:** Headers de cache configurados

### 8.3 Funcionalidades Específicas Android

#### 8.3.1 Cámara y Galería
**Requerimiento:** Implementación en contenedor Android
**File Input:** `<input type="file" accept="image/*" capture>`
**WebView:** Requiere permisos y configuración específica

#### 8.3.2 Back Button
**Requerimiento:** Manejo de navegación histórica
**Implementación:** JavaScript history API
**Fallback:** Comportamiento por defecto de WebView

#### 8.3.3 Deep Links
**Requerimiento:** Configuración de Android Manifest
**Formato:** `autogest://ruta/específica`
**Uso:** Abrir secciones específicas desde otras apps

#### 8.3.4 Notificaciones Push
**Requerimiento:** Firebase Cloud Messaging (FCM)
**Estado:** No implementado (opcional)

### 8.4 Checklist WebView

#### 8.4.1 Backend (Lista de Verificación)
- [x] Sistema funcional en `http://autogest.test`
- [x] No referencias a localhost en código
- [x] Storage link configurado
- [x] Assets compilados y optimizados
- [x] Responsive design implementado
- [x] Login/logout funcionando
- [x] Cookies configuradas correctamente
- [ ] CSRF tokens en frontend (corrección pendiente)
- [ ] HTTPS configurado (producción)
- [ ] CORS configurado (si necesario)

#### 8.4.2 Frontend (Lista de Verificación)
- [x] Vistas responsive
- [x] Touch-friendly elements
- [x] Viewport meta tag
- [x] No dependencias de mouse
- [ ] Service Worker (opcional)
- [ ] PWA Manifest (opcional)
- [ ] Optimización de imágenes
- [ ] Lazy loading de componentes

#### 8.4.3 Funcionalidades (Lista de Verificación)
- [x] Upload de archivos funciona
- [ ] Cámara integrada (requiere Android)
- [ ] Galería integrada (requiere Android)
- [ ] Back button manejado (requiere Android)
- [ ] Deep links configurados (requiere Android)
- [ ] Notificaciones push (opcional)

---

## 9. BASE DE DATOS

### 9.1 Configuración

#### 9.1.1 Conexión
- **Driver:** MySQL
- **Host:** localhost (configuración Laragon)
- **Port:** 3306 (default MySQL)
- **Database:** autogest
- **Charset:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

#### 9.1.2 Configuración en .env
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=autogest
DB_USERNAME=root
DB_PASSWORD=
```

### 9.2 Estructura de Tablas

#### 9.2.1 Tablas Principales
- **users:** Usuarios del sistema
- **vehicles:** Vehículos de clientes
- **service_orders:** Órdenes de servicio
- **maintenances:** Mantenimientos realizados
- **maintenance_schedules:** Programación de mantenimientos
- **order_comments:** Comentarios en órdenes
- **alerts:** Alertas automáticas
- **activity_logs:** Auditoría de actividades
- **system_settings:** Configuración del sistema

#### 9.2.2 Tablas de Chatbot
- **chatbot_messages:** Historial de conversaciones
- **chatbot_faqs:** Preguntas frecuentes
- **chatbot_configurations:** Configuración del chatbot
- **chat_sessions:** Sesiones de chat (opcional)

#### 9.2.3 Tablas de Inventario
- **categories:** Categorías de productos
- **brands:** Marcas de productos
- **products:** Productos/repuestos
- **suppliers:** Proveedores
- **purchases:** Compras realizadas
- **purchase_items:** Items de compras
- **stock_movements:** Movimientos de stock

#### 9.2.4 Tablas de Citas
- **appointment_requests:** Solicitudes de citas
- **vehicle_model_templates:** Plantillas de vehículos

#### 9.2.5 Tablas de Fotografías
- **service_photos:** Fotografías de servicios

### 9.3 Relaciones Clave

#### 9.3.1 Relaciones de Usuarios
```
users (1) ─────< (N) vehicles
users (1) ─────< (N) service_orders (como cliente)
users (1) ─────< (N) service_orders (como mecánico)
users (1) ─────< (N) service_orders (como asesor)
users (1) ─────< (N) maintenances
users (1) ─────< (N) activity_logs
users (1) ─────< (N) chatbot_messages
users (1) ─────< (N) alerts
```

#### 9.3.2 Relaciones de Vehículos
```
vehicles (1) ─────< (N) service_orders
vehicles (1) ─────< (N) maintenances
vehicles (1) ─────< (N) maintenance_schedules
vehicles (1) ─────< (N) alerts
```

#### 9.3.3 Relaciones de Órdenes
```
service_orders (1) ─────< (N) maintenances
service_orders (1) ─────< (N) order_comments
service_orders (1) ─────< (N) service_photos
```

### 9.4 Índices y Optimización

#### 9.4.1 Índices Implementados
- **users:** email, role, status
- **vehicles:** client_id, plate, status
- **service_orders:** vehicle_id, client_id, mechanic_id, status, scheduled_at
- **maintenances:** vehicle_id, status, mechanic_id, status
- **service_photos:** service_order_id, user_id

#### 9.4.2 Optimizaciones Recomendadas
- Agregar índices compuestos para consultas frecuentes
- Considerar particionamiento para tablas grandes
- Optimizar consultas N+1 con eager loading
- Implementar caché para datos de referencia

### 9.5 Integridad de Datos

#### 9.5.1 Foreign Keys
- **ON DELETE CASCADE:** Relaciones críticas
- **ON DELETE SET NULL:** Relaciones opcionales
- **ON DELETE RESTRICT:** Relaciones que requieren acción manual

#### 9.5.2 Validaciones
- **UNIQUE:** email, plate, order_number, sku
- **NOT NULL:** Campos requeridos
- **ENUM:** Estados y roles predefinidos
- **CHECK:** Validaciones de negocio en application layer

---

## 10. RIESGOS Y MITIGACIÓN

### 10.1 Riesgos de Seguridad

#### 10.1.1 CSRF Bypass en Chatbot (CRÍTICO)
**Riesgo:** Ataques CSRF pueden enviar mensajes en nombre de usuarios
**Impacto:** Compromiso de integridad de conversaciones
**Probabilidad:** Alta
**Mitigación:**
- Implementar tokens CSRF en frontend
- Remover bypass de middleware
- Validar tokens en cada solicitud
- **Tiempo estimado:** 2-3 horas

#### 10.1.2 Ausencia de Rate Limiting (MEDIO)
**Riesgo:** Sobrecarga del sistema por abuso
**Impacto:** Denegación de servicio
**Probabilidad:** Media
**Mitigación:**
- Implementar rate limiting por usuario
- Limitar mensajes por minuto/hora
- Implementar throttling
- **Tiempo estimado:** 4-6 horas

#### 10.1.3 Falta de Validación de Input (BAJO)
**Riesgo:** Inyección de contenido malicioso
**Impacto:** Compromiso de datos
**Probabilidad:** Baja
**Mitigación:**
- Implementar sanitización más robusta
- Agregar validación de contenido ofensivo
- Implementar filtrado de spam
- **Tiempo estimado:** 2-3 horas

### 10.2 Riesgos de Funcionalidad

#### 10.2.1 Módulo de Fotografías sin Frontend (CRÍTICO)
**Riesgo:** Funcionalidad completamente inutilizable
**Impacto:** Pérdida de capacidad de documentación
**Probabilidad:** Alta
**Mitigación:**
- Desarrollar vistas frontend completas
- Implementar integración con cámara/galería móvil
- Crear galería de visualización
- **Tiempo estimado:** 26-36 horas

#### 10.2.2 Inconsistencia de Nomenclatura (MEDIO)
**Riesgo:** Errores en consultas y confusión en código
**Impacto:** Inestabilidad del sistema
**Probabilidad:** Media
**Mitigación:**
- Estandarizar a service_type en todo el sistema
- Actualizar migraciones, modelos, seeders
- Actualizar vistas y controladores
- **Tiempo estimado:** 6-8 horas

#### 10.2.3 Ausencia de Notificaciones Automáticas (MEDIO)
**Riesgo:** Falta de comunicación con usuarios
**Impacto:** Experiencia de usuario degradada
**Probabilidad:** Media
**Mitigación:**
- Implementar sistema de notificaciones
- Integrar con procesos críticos
- Configurar canales de notificación
- **Tiempo estimado:** 12-16 horas

### 10.3 Riesgos de Performance

#### 10.3.1 Consultas N+1 (BAJO)
**Riesgo:** Degradación de performance con datos crecientes
**Impacto:** Tiempos de respuesta lentos
**Probabilidad:** Media
**Mitigación:**
- Implementar eager loading consistente
- Optimizar consultas con with()
- Implementar caché de datos frecuentes
- **Tiempo estimado:** 4-6 horas

#### 10.3.2 Falta de Caché (BAJO)
**Riesgo:** Carga innecesaria de base de datos
**Impacto:** Escalabilidad limitada
**Probabilidad:** Media
**Mitigación:**
- Implementar caché de datos de referencia
- Configurar Redis para cache
- Implementar cache HTTP
- **Tiempo estimado:** 6-8 horas

### 10.4 Riesgos de Android WebView

#### 10.4.1 Compatibilidad WebView (MEDIO)
**Riesgo:** Funcionalidades no disponibles en WebView
**Impacto:** Experiencia móvil degradada
**Probabilidad:** Media
**Mitigación:**
- Implementar fallbacks para funcionalidades específicas
- Testear en diferentes versiones de Android
- Implementar detección de capacidades
- **Tiempo estimado:** 8-12 horas

#### 10.4.2 Performance Móvil (BAJO)
**Riesgo:** Tiempos de carga lentos en móviles
**Impacto:** Experiencia de usuario pobre
**Probabilidad:** Baja
**Mitigación:**
- Optimizar assets para móviles
- Implementar lazy loading
- Reducir tamaño de bundles
- **Tiempo estimado:** 4-6 horas

### 10.5 Matriz de Riesgos

| Riesgo | Severidad | Probabilidad | Impacto | Mitigación | Tiempo |
|--------|-----------|--------------|----------|------------|--------|
| CSRF Bypass Chatbot | CRÍTICA | Alta | Seguridad | Implementar tokens CSRF | 2-3h |
| Fotografías sin Frontend | CRÍTICA | Alta | Funcionalidad | Desarrollar vistas completas | 26-36h |
| Inconsistencia Nomenclatura | MEDIA | Media | Estabilidad | Estandarizar service_type | 6-8h |
| Rate Limiting Ausente | MEDIA | Media | Disponibilidad | Implementar rate limiting | 4-6h |
| Notificaciones Ausentes | MEDIA | Media | UX | Sistema de notificaciones | 12-16h |
| Consultas N+1 | BAJO | Media | Performance | Eager loading + caché | 4-6h |
| Falta de Caché | BAJO | Media | Escalabilidad | Implementar Redis caché | 6-8h |
| Compatibilidad WebView | MEDIA | Media | UX Móvil | Fallbacks + testing | 8-12h |

---

## 11. PLAN DE IMPLEMENTACIÓN RECOMENDADO

### 11.1 Fase 1: Correcciones Críticas (Prioridad MÁXIMA)
**Tiempo estimado:** 28-39 horas

#### 11.1.1 Seguridad
- [ ] Corregir CSRF bypass en chatbot (2-3h)
- [ ] Implementar rate limiting (4-6h)
- [ ] Mejorar validación de input (2-3h)

#### 11.1.2 Funcionalidad
- [ ] Desarrollar vistas de fotografías (26-36h)
  - [ ] Vistas mecánico/asesor para subida (12-16h)
  - [ ] Galería cliente (6-8h)
  - [ ] Interfaz admin auditoría (4-6h)
  - [ ] Integración móvil (4-6h)

#### 11.1.3 Estabilidad
- [ ] Estandarizar nomenclatura service_type (6-8h)

### 11.2 Fase 2: Mejoras de Alta Prioridad (Prioridad ALTA)
**Tiempo estimado:** 20-28 horas

#### 11.2.1 Notificaciones
- [ ] Implementar sistema de notificaciones (12-16h)
- [ ] Integrar con procesos críticos (8-12h)

#### 11.2.2 Validaciones
- [ ] Validación de disponibilidad de mecánicos (6-8h)
- [ ] Validación de stock en mantenimientos (4-6h)
- [ ] Validación de horarios en citas (4-6h)

#### 11.2.3 Chatbot
- [ ] Refactorizar ChatbotService (12-16h)

### 11.3 Fase 3: Preparación Android (Prioridad MEDIA)
**Tiempo estimado:** 12-20 horas

#### 11.3.1 WebView
- [ ] Compatibilidad WebView (8-12h)
- [ ] Performance móvil (4-6h)
- [ ] Testing en dispositivos (4-6h)

#### 11.3.2 Funcionalidades Móviles
- [ ] Integración cámara/galería (4-6h)
- [ ] Manejo de back button (2-3h)
- [ ] Deep links (2-3h)

### 11.4 Fase 4: Optimización (Prioridad BAJA)
**Tiempo estimado:** 10-14 horas

#### 11.4.1 Performance
- [ ] Optimizar consultas (4-6h)
- [ ] Implementar caché (6-8h)

#### 11.4.2 Calidad
- [ ] Testing (6-8h)
- [ ] Documentación (4-6h)

**TOTAL ESTIMADO:** 70-101 horas

---

## 12. CRITERIOS DE ACEPTACIÓN

### 12.1 Criterios Funcionales

#### 12.1.1 Módulos del Sistema
- [ ] Todos los módulos (Admin, Asesor, Mecánico, Cliente) funcionales
- [ ] Chatbot completamente integrado y operativo
- [ ] Módulo de fotografías con interfaz completa
- [ ] Sistema de citas funcionando correctamente
- [ ] Gestión de inventario operativa

#### 12.1.2 Flujos de Negocio
- [ ] Ciclo completo de mantenimiento operativo
- [ ] Gestión de usuarios y vehículos funcional
- [ ] Reportes generando correctamente
- [ ] Calendario funcionando correctamente
- [ ] Sistema de alertas operando

### 12.2 Criterios Técnicos

#### 12.2.1 Backend
- [ ] Laravel 12 funcionando correctamente
- [ ] MySQL configurado y optimizado
- [ ] Todas las migraciones ejecutadas sin errores
- [ ] Seeders funcionando correctamente
- [ ] Policies implementadas y funcionando
- [ ] Middleware configurado correctamente

#### 12.2.2 Frontend
- [ ] Vistas compiladas sin errores
- [ ] Responsive design funcionando
- [ ] Assets optimizados y cargando correctamente
- [ ] JavaScript sin errores en consola
- [ ] Todas las rutas accesibles

#### 12.2.3 Seguridad
- [ ] CSRF protection activo en todas las rutas
- [ ] Autenticación funcionando correctamente
- [ ] Autorización por rol implementada
- [ ] Rate limiting implementado
- [ ] Input validation robusto

### 12.3 Criterios de Preparación Android

#### 12.3.1 WebView
- [ ] Sistema accesible vía `http://autogest.test`
- [ ] No referencias a localhost en código
- [ ] Responsive design optimizado para móviles
- [ ] Touch-friendly UI elements
- [ ] Upload de archivos funcionando

#### 12.3.2 Funcionalidades Móviles
- [ ] Login/logout funcionando en WebView
- [ ] Cookies configuradas correctamente
- [ ] Storage accesible desde WebView
- [ ] Back button manejado correctamente
- [ ] Deep links configurados (opcional)

### 12.4 Criterios de Calidad

#### 12.4.1 Código
- [ ] Código siguiendo PSR-12
- [ ] Sin código muerto
- [ ] Sin imports innecesarios
- [ ] Documentación PHPDoc en controladores
- [ ] Nomenclatura consistente

#### 12.4.2 Testing
- [ ] Tests de funcionalidad críticos pasando
- [ ] Tests de integración pasando
- [ ] Sin errores en PHPUnit
- [ ] Cobertura de código aceptable

#### 12.4.3 Documentación
- [ ] README actualizado
- [ ] Especificaciones técnicas completas
- [ ] Manual de instalación actualizado
- [ ] Manual de despliegue actualizado

---

## 13. MÉTRICAS DE ÉXITO

### 13.1 Métricas Funcionales
- **Tiempo de respuesta del chatbot:** < 2 segundos
- **Tiempo de carga de páginas:** < 3 segundos
- **Disponibilidad del sistema:** > 99%
- **Tasa de error en conversaciones chatbot:** < 5%

### 13.2 Métricas Técnicas
- **Coverage de código:** > 70%
- **Número de errores PHP:** 0
- **Número de errores JavaScript:** 0
- **Performance score (Lighthouse):** > 90

### 13.3 Métricas de Usuario
- **Satisfacción del usuario:** > 4/5
- **Tasa de adopción del chatbot:** > 60%
- **Tasa de completion de citas:** > 80%
- **Tiempo promedio de resolución:** < 24 horas

---

## 14. CONCLUSIÓN

Esta especificación general establece la arquitectura, dependencias, modelos, controladores, servicios, políticas, middleware, storage, y preparación para Android del sistema AutoGest. El sistema está bien diseñado con una arquitectura sólida, pero requiere correcciones críticas antes de proceder con la implementación de la aplicación Android.

Las correcciones críticas identificadas (CSRF bypass, módulo de fotografías sin interfaz, inconsistencia de nomenclatura) deben abordarse primero para asegurar la estabilidad y seguridad del sistema.

Una vez completadas las correcciones críticas, el sistema estará listo para:
1. Uso en producción como aplicación web
2. Desarrollo del contenedor Android WebView
3. Generación de APK para distribución

---

**Especificación preparada por:** Equipo Multidisciplinario de Ingeniería de Software  
**Fecha:** 2026-08-04  
**Versión:** 1.0  
**Estado:** Aprobada para revisión y comentarios

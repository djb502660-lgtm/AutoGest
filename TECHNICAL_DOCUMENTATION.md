# Documentación Técnica - AutoGest

## Versión
v0.10-sprint5a-evidence-photos

## Fecha
2026-08-05

---

## 1. Descripción General del Sistema

AutoGest es un sistema de gestión de taller de vehículos diseñado para automatizar y optimizar las operaciones de mantenimiento vehicular. El sistema permite a talleres gestionar clientes, vehículos, órdenes de servicio, inventario de repuestos, y generar reportes administrativos.

### Objetivos Principales
- Centralizar la gestión de operaciones del taller
- Proporcionar trazabilidad completa de acciones
- Automatizar procesos manuales
- Facilitar la comunicación con clientes
- Mantener control sobre inventario y gastos

---

## 2. Arquitectura del Sistema

### 2.1 Patrón Arquitectónico

AutoGest implementa una arquitectura por capas siguiendo el patrón **Repository → Service → DTO → Controller**:

```
Usuario
   ↓
Laravel Controller
   ↓
DTO (Data Transfer Object)
   ↓
Service Layer (Lógica de negocio)
   ↓
Repository Layer (Acceso a datos)
   ↓
Eloquent Model
   ↓
MySQL Database
```

### 2.2 Capas del Sistema

#### Controller Layer
- Maneja solicitudes HTTP
- Valida inputs
- Delega lógica a Services
- Retorna respuestas

#### DTO Layer
- Data Transfer Objects
- Estandariza transferencia de datos
- Evita acoplamiento directo a Models

#### Service Layer
- Contiene lógica de negocio
- Coordina entre Repositories
- Implementa reglas del negocio
- Centraliza operaciones complejas

#### Repository Layer
- Abstrae acceso a datos
- Implementa patrón Repository
- Facilita testing y cambios de DB

#### Model Layer
- Eloquent ORM
- Define estructura de datos
- Relaciones entre entidades

---

## 3. Tecnologías Utilizadas

### Backend
- **Framework**: Laravel 11.x
- **PHP**: 8.3.30
- **Base de Datos**: MySQL
- **ORM**: Eloquent

### Frontend
- **Framework**: Blade Templates
- **CSS**: Bootstrap 5.3
- **JavaScript**: Vanilla JS + Alpine.js (opcional)

### Librerías Principales
- **Barryvdh DomPDF**: Generación de PDF
- **Carbon**: Manejo de fechas
- **Laravel Policies**: Control de acceso

---

## 4. Módulos del Sistema

### 4.1 Gestión de Usuarios y Roles (RBAC)

**Descripción**: Sistema de control de acceso basado en roles.

**Roles Implementados**:
- **Administrador**: Acceso completo al sistema
- **Asesor**: Gestión de órdenes y clientes
- **Mecánico**: Ejecución de mantenimientos
- **Cliente**: Consulta de sus vehículos y órdenes

**Components**:
- `UserService`: Lógica de gestión de usuarios
- `UserPolicy`: Control de permisos
- `UserController`: Endpoints de gestión

**Funcionalidades**:
- Creación de usuarios
- Asignación de roles
- Activación/desactivación
- Protección por ownership

---

### 4.2 Gestión de Vehículos

**Descripción**: Gestión completa del ciclo de vida de vehículos.

**Components**:
- `VehicleService`: Lógica de vehículos
- `VehicleRepository`: Acceso a datos
- `VehiclePolicy`: Control de permisos

**Funcionalidades**:
- Registro de vehículos
- Historial de mantenimiento
- Programación de mantenimientos
- Estado del vehículo

---

### 4.3 Órdenes de Servicio

**Descripción**: Núcleo operativo del taller.

**Components**:
- `ServiceOrderService`: Lógica de órdenes
- `OrderStatusService`: Máquina de estados
- `ServiceOrderRepository`: Acceso a datos

**Estados de Órdenes**:
- `recibida` → `en_proceso` → `completada` → `entregada`
- `cancelada` (estado terminal)

**Funcionalidades**:
- Creación de órdenes
- Asignación de mecánicos
- Seguimiento de progreso
- Registro de diagnóstico
- Gestión de recomendaciones

---

### 4.4 Mantenimientos

**Descripción**: Gestión de mantenimientos preventivos y correctivos.

**Components**:
- `MaintenanceService`: Lógica de mantenimientos
- `MaintenanceRepository`: Acceso a datos

**Funcionalidades**:
- Registro de mantenimientos
- Historial de servicios
- Programación de pautas
- Control de costos

---

### 4.5 Chatbot Inteligente

**Descripción**: Asistente conversacional para clientes.

**Components**:
- `ChatbotService`: Orquestador de respuestas
- `ChatbotAppointmentService`: Gestión de citas
- Integración con Services existentes

**Funcionalidades**:
- Consulta de estado de vehículo
- Agendamiento de citas
- Consulta de historial
- Respuestas contextuales

**Arquitectura**:
```
Chatbot → VehicleService → Repository
Chatbot → ServiceOrderService → Repository
Chatbot → MaintenanceService → Repository
```

---

### 4.6 Módulo Administrativo

**Descripción**: Centro de control operativo del taller.

**Submódulos**:

#### Dashboard
- `DashboardService`: Métricas del sistema
- Indicadores en tiempo real
- Calendario integrado

#### Inventario
- `InventoryService`: Gestión de stock
- Control de repuestos
- Alertas de stock bajo

#### Reportes
- `ReportService`: Generación de reportes
- Exportación PDF/CSV
- Métricas financieras

#### Usuarios
- `UserService`: Gestión de usuarios
- Control de roles
- Auditoría de cambios

---

## 5. Base de Datos

### 5.1 Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios del sistema |
| `vehicles` | Vehículos registrados |
| `service_orders` | Órdenes de servicio |
| `maintenances` | Mantenimientos realizados |
| `maintenance_schedules` | Programación de mantenimientos |
| `products` | Repuestos y productos |
| `purchases` | Compras de inventario |
| `audit_logs` | Auditoría de acciones |
| `activity_logs` | Registro de actividad |

### 5.2 Relaciones Clave

```
users (1:N) vehicles
users (1:N) service_orders (como cliente)
users (1:N) service_orders (como mecánico)
users (1:N) service_orders (como asesor)
vehicles (1:N) service_orders
service_orders (1:N) maintenances
service_orders (1:N) audit_logs
```

---

## 6. Seguridad

### 6.1 Autenticación
- Laravel Authentication System
- Middleware `auth` en rutas protegidas
- Hashing de contraseñas (bcrypt)

### 6.2 Autorización (RBAC)
- Middleware `role` por módulo
- Policies granulares por recurso
- Protección por ownership del recurso

### 6.3 Auditoría
- Sistema de audit logs completo
- Registro de cambios críticos
- Trazabilidad de acciones
- Captura de IP address

### 6.4 Protección CSRF
- Middleware CSRF activo en todos los formularios
- Tokens por sesión

---

## 7. Auditoría y Trazabilidad

### 7.1 Sistema de Audit Logs

**Modelo**: `AuditLog`

**Campos**:
- `user_id`: Usuario que realizó la acción
- `module`: Módulo afectado (users, orders, inventory, reports)
- `action`: Tipo de acción (create, update, delete, view, export)
- `description`: Descripción legible
- `old_values`: Valores anteriores (JSON)
- `new_values`: Nuevos valores (JSON)
- `ip_address`: Dirección IP

**Eventos Registrados**:
- Cambios de roles de usuarios
- Cambios de estado de órdenes
- Actualizaciones de inventario
- Generación de reportes
- Modificaciones críticas

---

## 8. Máquina de Estados

### 8.1 Estados de Órdenes de Servicio

```php
recibida → en_proceso → completada → entregada
                        ↓
                     cancelada
```

**Validaciones**:
- Transiciones controladas por `OrderStatusService`
- Timestamps automáticos
- Protección contra transiciones inválidas

---

## 9. API Endpoints

### 9.1 Rutas Administrativas
- `GET /dashboard` - Dashboard principal
- `GET /users` - Listado de usuarios
- `POST /users` - Crear usuario
- `PUT /users/{id}` - Actualizar usuario
- `DELETE /users/{id}` - Eliminar usuario
- `GET /ordenes` - Listado de órdenes
- `GET /reportes` - Generación de reportes
- `GET /inventario` - Gestión de inventario

### 9.2 Rutas de Asesor
- `GET /asesor/ordenes` - Gestión de órdenes
- `POST /asesor/ordenes` - Crear orden
- `PUT /asesor/ordenes/{id}/asignar-mecanico` - Asignar mecánico

### 9.3 Rutas de Mecánico
- `GET /mecanico/ordenes` - Órdenes asignadas
- `PUT /mecanico/ordenes/{id}/estado` - Actualizar estado
- `POST /mecanico/ordenes/{id}/comentarios` - Agregar comentarios

### 9.4 Rutas de Cliente
- `GET /cliente/vehiculos` - Sus vehículos
- `GET /cliente/ordenes` - Sus órdenes
- `GET /cliente/gastos` - Sus gastos

---

## 10. Testing

### 10.1 Tests Implementados
- **Tests Unitarios**: 1 test básico
- **Tests de Integración**: 55 tests
- **Total**: 56 tests

### 10.2 Cobertura de Tests
- Autenticación y autorización
- Operaciones CRUD principales
- Generación de reportes
- Funcionalidad del chatbot
- Gestión de dashboard

---

## 11. Configuración de Producción

### 11.1 Variables de Entorno Requeridas

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://autogest.example.com

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_PORT=3306
DB_DATABASE=autogest_prod
DB_USERNAME=production_user
DB_PASSWORD=secure_password

SESSION_DRIVER=redis
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@autogest.example.com
MAIL_PASSWORD=secure_password
```

### 11.2 Requisitos de Servidor
- PHP 8.3+
- MySQL 8.0+
- Redis (opcional, recomendado para sesiones)
- Composer
- Node.js/NPM (para assets)

---

## 12. Versionamiento

### 12.1 Historial de Versiones

- **v0.0-baseline**: Estado inicial del proyecto
- **v0.1-sprint1-estabilizacion**: Estabilización inicial
- **v0.2-sprint2a-repository-pattern**: Implementación de Repository Pattern
- **v0.2.1-sprint2a.1-interface-fix**: Corrección de interfaces
- **v0.3-sprint2b-service-layer**: Implementación de Service Layer
- **v0.4-sprint2c-dto-layer**: Implementación de DTO Layer
- **v0.5-sprint2d-controller-refactor**: Simplificación de Controllers
- **v0.6-sprint3a-service-order-flow**: Gestión del ciclo vehicular
- **v0.7-sprint3b-chatbot-service-integration**: Chatbot inteligente
- **v0.8-sprint3c-admin-module**: Módulo administrativo

---

## 13. Conclusiones

AutoGest es un sistema empresarial de gestión de taller vehicular con:

- ✅ Arquitectura profesional por capas
- ✅ Separación clara de responsabilidades
- ✅ Sistema de seguridad y auditoría completo
- ✅ Chatbot integrado con lógica real del negocio
- ✅ Módulo administrativo funcional
- ✅ Sistema de trazabilidad implementado
- ✅ Tests de integración robustos

El sistema está listo para:
- Despliegue en producción
- Defensa de tesis
- Expansión de funcionalidades adicionales

---

## Contacto y Soporte

Para más información sobre el sistema, consulte:
- Documentación de usuario (manual_usuario.md)
- Auditoría de seguridad (SECURITY_AUDIT.md)
- Changelog (CHANGELOG.md)

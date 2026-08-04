# MATRIZ DE DEPENDENCIAS - FASE 0 BASELINE
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Fecha:** 2026-08-04

---

## RELACIONES ENTRE MODELOS (ELOQUENT)

### User (Central)
- **HasMany** → Vehicle (como client_id)
- **HasMany** → ServiceOrder (como client_id)
- **HasMany** → ServiceOrder (como mechanic_id)
- **HasMany** → ServiceOrder (como advisor_id)
- **HasMany** → Maintenance (como mechanic_id)
- **HasMany** → Alert
- **HasMany** → ActivityLog
- **HasMany** → ChatbotMessage

### Vehicle
- **BelongsTo** → User (client_id)
- **HasMany** → ServiceOrder
- **HasMany** → Maintenance
- **HasMany** → MaintenanceSchedule
- **HasMany** → Alert

### ServiceOrder
- **BelongsTo** → Vehicle
- **BelongsTo** → User (client_id)
- **BelongsTo** → User (mechanic_id)
- **BelongsTo** → User (advisor_id)
- **BelongsTo** → User (created_by)
- **HasMany** → Maintenance
- **HasMany** → OrderComment
- **HasMany** → ServicePhoto

### Maintenance
- **BelongsTo** → ServiceOrder
- **BelongsTo** → Vehicle
- **BelongsTo** → User (mechanic_id)

---

## DEPENDENCIAS DE CONTROLADORES

### Admin Controllers (16)
- **BrandController** → Brand Model
- **CalendarController** → MaintenanceSchedule Model
- **CategoryController** → Category Model
- **DashboardController** → Multiple Models (User, ServiceOrder, Maintenance, etc.)
- **InventoryController** → Product, StockMovement Models
- **MaintenanceController** → Maintenance Model
- **OrderController** → ServiceOrder Model
- **ProductController** → Product Model
- **ProfileController** → User Model
- **PurchaseController** → Purchase, PurchaseItem Models
- **ReportController** → Multiple Models
- **StockController** → StockMovement Model
- **SupplierController** → Supplier Model
- **UserController** → User Model
- **VehicleController** → Vehicle Model

### Advisor Controllers (7)
- **AppointmentController** → AppointmentRequest Model
- **AppointmentRequestController** → AppointmentRequest Model
- **ClientController** → User Model
- **DashboardController** → Multiple Models
- **OrderController** → ServiceOrder Model
- **PreOrderController** → ServiceOrder Model
- **VehicleController** → Vehicle Model

### Client Controllers (7)
- **DashboardController** → Multiple Models
- **ExpenseController** → ServiceOrder Model
- **MaintenanceController** → Maintenance Model
- **NotificationController** → Alert Model
- **OrderController** → ServiceOrder Model
- **ProfileController** → User Model
- **VehicleController** → Vehicle Model

### Mechanic Controllers (5)
- **CalendarController** → MaintenanceSchedule Model
- **DashboardController** → Multiple Models
- **MaintenanceController** → Maintenance Model
- **OrderController** → ServiceOrder Model
- **VehicleController** → Vehicle Model

---

## DEPENDENCIAS DE SERVICIOS

### ChatbotService
- **Dependencias**: ChatbotFaq, ChatbotMessage, AppointmentRequest, Vehicle, MaintenanceSchedule
- **Responsabilidad**: Procesamiento de consultas del chatbot

### ChatbotAppointmentService
- **Dependencias**: AppointmentRequest, Vehicle, MaintenanceSchedule, User
- **Responsabilidad**: Gestión de citas vía chatbot

### DashboardCalendarService
- **Dependencias**: ServiceOrder, MaintenanceSchedule, AppointmentRequest
- **Responsabilidad**: Generación de calendarios integrados

---

## DEPENDENCIAS DE MIGRACIONES

### Batch 1 (Core)
- 0001_01_01_000000_create_users_table
- 0001_01_01_000001_create_cache_table
- 0001_01_01_000002_create_jobs_table
- 2026_05_28_000001_add_role_to_users_table
- 2026_05_29_000001_extend_users_table
- 2026_05_29_000002_create_vehicles_table (depende de users)
- 2026_05_29_000003_create_service_orders_table (depende de users, vehicles)
- 2026_05_29_000004_create_maintenances_table (depende de service_orders, vehicles)
- 2026_05_29_000005_create_maintenance_schedules_table (depende de vehicles)
- 2026_05_29_000006_create_order_comments_table (depende de service_orders)
- 2026_05_29_000007_create_alerts_table (depende de users, vehicles)
- 2026_05_29_000008_create_notifications_table (depende de users)
- 2026_05_29_000009_create_activity_logs_table (depende de users)
- 2026_05_29_000010_create_system_settings_table
- 2026_05_29_000011_create_chatbot_faqs_table
- 2026_05_29_000012_create_chatbot_messages_table (depende de users)

### Batch 2 (Enhancements)
- 2026_05_30_000001_add_progress_to_service_orders_table (depende de service_orders)
- 2026_06_02_000001_add_advisor_to_service_orders_table (depende de service_orders)
- 2026_06_03_000001_create_vehicle_model_templates_table (depende de vehicles)
- 2026_06_03_000002_create_appointment_requests_table (depende de users, vehicles)

### Batch 3 (Inventory)
- 2026_07_31_000001_create_categories_table
- 2026_07_31_000002_create_brands_table
- 2026_07_31_000003_create_products_table (depende de categories, brands)
- 2026_07_31_000004_create_suppliers_table
- 2026_07_31_000005_create_purchases_table (depende de suppliers)
- 2026_07_31_000006_create_purchase_items_table (depende de purchases, products)
- 2026_07_31_000007_create_stock_movements_table (depende de products, purchases)
- 2026_07_31_000008_create_chatbot_configurations_table
- 2026_07_31_000010_add_advanced_fields_to_vehicles_table (depende de vehicles)
- 2026_07_31_000011_add_advanced_fields_to_maintenances_table (depende de maintenances)
- 2026_07_31_000012_add_appointment_fields_to_maintenance_schedules_table (depende de maintenance_schedules)

### Batch 4 (Advanced Features)
- 2026_08_01_000001_setup_chatbot_tables
- 2026_08_03_000001_allow_cancelada_status_on_appointment_requests (depende de appointment_requests)
- 2026_08_03_000001_create_chat_sessions_table
- 2026_08_03_000002_create_advisor_notifications_table (depende de users)
- 2026_08_03_222928_create_service_photos_table (depende de service_orders)

---

## PUNTOS CRÍTICOS DE DEPENDENCIA

### Alto Impacto
1. **User Model**: Central en el sistema, afecta a casi todos los módulos
2. **Vehicle Model**: Core del negocio, utilizado por Clientes, Asesores, Mecánicos
3. **ServiceOrder Model**: Principal entidad de negocio, extensa relación con otros modelos

### Medio Impacto
1. **Maintenance Model**: Dependiente de ServiceOrder y Vehicle
2. **AppointmentRequest Model**: Funcionalidad crítica del chatbot
3. **Chatbot Services**: Afectan la experiencia del cliente

### Bajo Impacto
1. **Inventory Models**: Sistema aislado de gestión de inventario
2. **System Settings**: Configuración global, impacto limitado
3. **Activity Logs**: Solo logging, no afecta funcionalidad

---

## RECOMENDACIONES PARA REFACTORIZACIÓN

1. **User Model**: Considerar usar Repository Pattern para reducir acoplamiento
2. **ServiceOrder**: Extensa lógica de negocio, considerar Service Layer
3. **Chatbot Services**: Bien estructurados, mantener arquitectura actual
4. **Controllers**: Varios controllers tienen lógica de negocio, considerar mover a Services

Fecha verificación: 2026-08-04

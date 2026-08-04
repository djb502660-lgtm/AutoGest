# SKILLS Y MATRIZ DE INTEGRACIÓN - FASE 8
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Fecha:** 2026-08-04
**Estado:** Propuesta de Skills y Matriz de Integración

---

## 1. INTRODUCCIÓN A SKILLS

Las Skills (habilidades) son personalizaciones de Devin para tareas específicas del proyecto. En el contexto de AutoGest, las skills permitirán:

- Automatización de tareas repetitivas
- Estandarización de procesos
- Consistencia en el código
- Aceleración del desarrollo
- Cumplimiento de convenciones del proyecto

---

## 2. SKILLS PROPUESTAS PARA AUTOGEST

### 2.1 Skill: Laravel Controller Generator

**Propósito:** Generar controladores Laravel siguiendo las convenciones de AutoGest

**Ubicación:** `.devin/skills/laravel-controller-generator/SKILL.md`

**Descripción:**
Esta skill automatiza la creación de controladores Laravel siguiendo las convenciones específicas de AutoGest:
- Estructura por roles (Admin, Advisor, Mechanic, Client)
- Implementación de Policies
- ActivityLog en métodos de cambio
- Validaciones con Form Requests
- Respuestas JSON para AJAX

**Uso:**
```
"Crear un controlador para el módulo [MÓDULO] con las siguientes acciones: [ACCIONES]"
```

**Salida:**
- Controlador con estructura estándar
- Form Request para validaciones
- Policy si aplica
- ActivityLog implementado

---

### 2.2 Skill: Laravel Model Generator

**Propósito:** Generar modelos Laravel con relaciones y casts apropiados

**Ubicación:** `.devin/skills/laravel-model-generator/SKILL.md`

**Descripción:**
Esta skill crea modelos Eloquent siguiendo las convenciones de AutoGest:
- Fillables y guarded apropiados
- Casts para datetime y enums
- Relaciones Eloquent (hasMany, belongsTo, etc.)
- Scopes para consultas frecuentes
- Accessors y mutators si aplica

**Uso:**
```
"Crear un modelo [NOMBRE] con las siguientes relaciones: [RELACIONES]"
```

**Salida:**
- Model con estructura estándar
- Migración correspondiente
- Factory si aplica
- Relationships implementadas

---

### 2.3 Skill: Laravel View Generator

**Propósito:** Generar vistas Blade con TailwindCSS siguiendo el diseño de AutoGest

**Ubicación:** `.devin/skills/laravel-view-generator/SKILL.md`

**Descripción:**
Esta skill genera vistas Blade con:
- Layouts compartidos
- Componentes reutilizables
- Clases TailwindCSS
- Responsive design
- Formularios con validación
- Tablas con paginación

**Uso:**
```
"Crear una vista [NOMBRE] para el módulo [MÓDULO] con los siguientes elementos: [ELEMENTOS]"
```

**Salida:**
- Vista Blade completa
- Integración con layout
- Componentes reutilizables
- Responsive design

---

### 2.4 Skill: Laravel Policy Generator

**Propósito:** Generar Policies de autorización siguiendo las convenciones de AutoGest

**Ubicación:** `.devin/skills/laravel-policy-generator/SKILL.md`

**Descripción:**
Esta skill crea Policies con:
- Métodos por acción (viewAny, view, create, update, delete)
- Lógica de autorización por rol
- Verificación de relaciones
- Protección de recursos

**Uso:**
```
"Crear una Policy para [MODEL] con las siguientes reglas: [REGLAS]"
```

**Salida:**
- Policy con métodos estándar
- Autorización por rol
- Verificación de relaciones
- Registro en AuthServiceProvider

---

### 2.5 Skill: Laravel Service Generator

**Propósito:** Generar Services para lógica de negocio compleja

**Ubicación:** `.devin/skills/laravel-service-generator/SKILL.md`

**Descripción:**
Esta skill crea Services con:
- Constructor con inyección de dependencias
- Métodos públicos por acción
- Manejo de errores robusto
- ActivityLog si aplica
- Transacciones DB si aplica

**Uso:**
```
"Crear un Service [NOMBRE] con las siguientes responsabilidades: [RESPONSABILIDADES]"
```

**Salida:**
- Service con estructura estándar
- Inyección de dependencias
- Manejo de errores
- ActivityLog

---

### 2.6 Skill: Laravel Migration Generator

**Propósito:** Generar migraciones con estructura estándar de AutoGest

**Ubicación:** `.devin/skills/laravel-migration-generator/SKILL.md`

**Descripción:**
Esta skill crea migraciones con:
- Foreign keys con cascade
- Índices apropiados
- Enums definidos
- Valores por defecto
- Comentarios descriptivos

**Uso:**
```
"Crear una migración para [TABLA] con los siguientes campos: [CAMPOS]"
```

**Salida:**
- Migración completa
- Foreign keys
- Índices
- Enums

---

### 2.7 Skill: Laravel Route Generator

**Propósito:** Generar rutas siguiendo la estructura de AutoGest

**Ubicación:** `.devin/skills/laravel-route-generator/SKILL.md`

**Descripción:**
Esta skill genera rutas con:
- Prefijo por módulo
- Middleware apropiado
- Resource routes
- Custom routes
- Nomenclatura consistente

**Uso:**
```
"Crear rutas para el módulo [MÓDULO] con las siguientes acciones: [ACCIONES]"
```

**Salida:**
- Archivo de rutas
- Prefijo y middleware
- Resource routes
- Custom routes

---

### 2.8 Skill: Laravel Repository Generator

**Propósito:** Generar Repositories para abstracción de datos

**Ubicación:** `.devin/skills/laravel-repository-generator/SKILL.md`

**Descripción:**
Esta skill crea Repositories con:
- Interface correspondiente
- Implementación con Eloquent
- Métodos CRUD estándar
- Métodos personalizados por consulta
- Testing facilitado

**Uso:**
```
"Crear un Repository para [MODEL] con los siguientes métodos: [MÉTODOS]"
```

**Salida:**
- Interface de Repository
- Implementación de Repository
- Binding en ServiceProvider
- Métodos CRUD

---

### 2.9 Skill: AutoGest Debug Assistant

**Propósito:** Asistente especializado para debugging de AutoGest

**Ubicación:** `.devin/skills/autogest-debug-assistant/SKILL.md`

**Descripción:**
Esta skill ayuda a debuggear problemas específicos de AutoGest:
- Análisis de logs
- Verificación de configuración
- Debug de consultas SQL
- Debug de relaciones Eloquent
- Verificación de Policies y Middleware

**Uso:**
```
"Debuggear el siguiente problema en AutoGest: [PROBLEMA]"
```

**Salida:**
- Análisis del problema
- Sugerencias de solución
- Pasos para verificar
- Comandos de debugging

---

### 2.10 Skill: AutoGest Refactoring Assistant

**Propósito:** Asistente para refactoring de código de AutoGest

**Ubicación:** `.devin/skills/autogest-refactoring-assistant/SKILL.md`

**Descripción:**
Esta skill ayuda a refactorizar código siguiendo las convenciones de AutoGest:
- Extracción de lógica a Services
- Implementación de Repositories
- Creación de Form Requests
- Optimización de consultas
- Aplicación de principios SOLID

**Uso:**
```
"Refactorizar el siguiente código de AutoGest: [CÓDIGO]"
```

**Salida:**
- Código refactorizado
- Explicación de cambios
- Tests sugeridos
- Documentación

---

## 3. MATRIZ DE INTEGRACIÓN ENTRE SKILLS

### 3.1 Relación de Skills con Componentes

| Skill | Controller | Model | View | Policy | Service | Migration | Route | Repository |
|-------|------------|-------|------|--------|--------|-----------|-------|------------|
| **Laravel Controller Generator** | ✅ | ⚠️ | ❌ | ⚠️ | ⚠️ | ❌ | ❌ | ⚠️ |
| **Laravel Model Generator** | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Laravel View Generator** | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Laravel Policy Generator** | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Laravel Service Generator** | ⚠️ | ⚠️ | ❌ | ❌ | ✅ | ❌ | ❌ | ⚠️ |
| **Laravel Migration Generator** | ❌ | ⚠️ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Laravel Route Generator** | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Laravel Repository Generator** | ⚠️ | ⚠️ | ❌ | ❌ | ⚠️ | ❌ | ❌ | ✅ |
| **AutoGest Debug Assistant** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **AutoGest Refactoring Assistant** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

**Leyenda:**
- ✅ Genera directamente
- ⚠️ Genera o modifica relacionado
- ❌ No aplica

### 3.2 Flujo de Trabajo con Skills

#### 3.2.1 Flujo: Creación de Nuevo Módulo

```
1. Laravel Model Generator
   ↓ (genera model + migration)
2. Laravel Migration Generator
   ↓ (ejecuta migración)
3. Laravel Repository Generator
   ↓ (crea interface + implementación)
4. Laravel Service Generator
   ↓ (crea service con repositorio)
5. Laravel Policy Generator
   ↓ (crea policy)
6. Laravel Controller Generator
   ↓ (crea controller con service + policy)
7. Laravel Route Generator
   ↓ (crea rutas)
8. Laravel View Generator
   ↓ (crea vistas)
9. AutoGest Debug Assistant
   ↓ (verifica funcionamiento)
10. AutoGest Refactoring Assistant
    ↓ (optimiza código)
```

#### 3.2.2 Flujo: Corrección de Problemas

```
1. AutoGest Debug Assistant
   ↓ (identifica problema)
2. AutoGest Refactoring Assistant
   ↓ (refactoriza código)
3. Skills específicas (según necesidad)
   ↓ (regeneran componentes)
4. AutoGest Debug Assistant
   ↓ (verifica solución)
```

---

## 4. MATRIZ DE INTEGRACIÓN ENTRE MÓDULOS DEL SISTEMA

### 4.1 Matriz de Dependencias Técnicas

| Módulo | Dependencias de Datos | Dependencias de Lógica | Dependencias de UI | Dependencias de Configuración |
|--------|----------------------|------------------------|-------------------|------------------------------|
| **Administrador** | Users, Vehicles, Orders, Maintenances, Inventory | Services, Policies, Jobs | Views Admin, Layouts | Config Admin, Roles |
| **Asesor** | Users, Vehicles, Orders, Appointments | Services, Policies | Views Advisor, Layouts | Config Advisor, Roles |
| **Mecánico** | Users, Vehicles, Orders, Maintenances | Services, Policies | Views Mechanic, Layouts | Config Mechanic, Roles |
| **Cliente** | Users, Vehicles, Orders, Maintenances | Services, Policies | Views Client, Layouts | Config Client, Roles |
| **Chatbot** | Users, Vehicles, Orders, FAQs | ChatbotService, Services | Views Chatbot, Layouts | Config Chatbot, OpenAI |
| **Fotografías** | Users, Orders, Photos | ServicePhotoController | Views Photos (FALTAN) | Config Storage |
| **Inventario** | Products, Suppliers, Purchases, Stock | Services, Policies | Views Inventory, Layouts | Config Inventory |

### 4.2 Matriz de Flujo de Datos

| Flujo de Datos | Origen | Destino | Tipo de Dato | Frecuencia | Latencia Requerida |
|----------------|--------|---------|--------------|------------|---------------------|
| **Creación de Orden** | Asesor | Mecánico | ServiceOrder | Event-driven | < 1s |
| **Actualización de Mantenimiento** | Mecánico | Cliente | Maintenance | Event-driven | < 1s |
| **Solicitud de Cita** | Chatbot | Asesor | AppointmentRequest | Event-driven | < 2s |
| **Notificación de Alerta** | Sistema | Cliente | Alert | Scheduled | < 5s |
| **Actualización de Stock** | Inventario | Mecánico | Stock | Event-driven | < 1s |
| **Subida de Foto** | Mecánico/Asesor | Storage | ServicePhoto | User-triggered | < 5s |
| **Visualización de Foto** | Storage | Cliente | ServicePhoto | User-triggered | < 2s |
| **Escalamiento de Chatbot** | Chatbot | Asesor | Job | Event-driven | < 10s |

### 4.3 Matriz de Sincronización

| Sincronización | Componente A | Componente B | Mecanismo | Estado Actual | Necesidad |
|----------------|--------------|--------------|-----------|---------------|-----------|
| **Orden ↔ Mantenimiento** | ServiceOrder | Maintenance | Actualización de costos | ✅ Implementado | Funcional |
| **Orden ↔ Vehículo** | ServiceOrder | Vehicle | Actualización de estado | ✅ Implementado | Funcional |
| **Vehículo ↔ MaintenanceSchedule** | Vehicle | MaintenanceSchedule | Generación automática | ✅ Implementado | Funcional |
| **Cita ↔ Orden** | AppointmentRequest | ServiceOrder | Conversión | ✅ Implementado | Funcional |
| **Compra ↔ Stock** | Purchase | Product | Actualización de stock | ✅ Implementado | Funcional |
| **Mantenimiento ↔ Inventario** | Maintenance | Product | Consumo de repuestos | ❌ NO Implementado | CRÍTICO |
| **Orden ↔ Foto** | ServiceOrder | ServicePhoto | Asociación | ⚠️ Parcial | FALTA UI |
| **Chatbot ↔ FAQ** | ChatbotService | ChatbotFaq | Consulta dinámica | ✅ Implementado | Funcional |

---

## 5. MATRIZ DE RIESGOS DE INTEGRACIÓN

### 5.1 Riesgos por Dependencia

| Dependencia | Riesgo | Impacto | Probabilidad | Mitigación |
|-------------|--------|---------|--------------|------------|
| **Database** | Fallo de conexión | CRÍTICO | Baja | Connection pooling, retries |
| **Storage** | Fallo de almacenamiento | ALTO | Baja | Storage fallback, validación |
| **Redis (Cache/Colas)** | Fallo de servicio | MEDIO | Media | Fallback a file/database |
| **OpenAI API** | Fallo de servicio | BAJO | Baja | Fallback a FAQs, timeout |
| **Email Service** | Fallo de envío | MEDIO | Media | Queue, retries, logging |

### 5.2 Riesgos por Integración

| Integración | Riesgo | Impacto | Probabilidad | Mitigación |
|-------------|--------|---------|--------------|------------|
| **Chatbot → Asesor** | Escalamiento fallido | MEDIO | Baja | Logging, fallback manual |
| **Mecánico → Inventario** | Consumo no registrado | ALTO | Alta | Implementar integración |
| **Orden → Foto** | Fotos no asociadas | MEDIO | Media | Validación, requerimiento |
| **Vehículo → Schedule** | Schedule no generado | MEDIO | Baja | Validación, retry |
| **Orden → Cliente** | Notificación no enviada | BAJO | Media | Queue, logging |

---

## 6. MATRIZ DE COMUNICACIÓN ENTRE COMPONENTES

### 6.1 Comunicación Síncrona

| Componente A | Componente B | Método | Protocolo | Estado |
|--------------|--------------|--------|-----------|--------|
| **Controller** | Model | Eloquent ORM | PHP | ✅ Funcional |
| **Controller** | Service | Dependency Injection | PHP | ✅ Funcional |
| **Service** | Repository | Interface | PHP | ⚠️ Parcial |
| **Service** | Model | Eloquent ORM | PHP | ✅ Funcional |
| **Controller** | Policy | Authorization | PHP | ✅ Funcional |
| **ChatbotService** | ChatbotAppointmentService | Method Call | PHP | ✅ Funcional |

### 6.2 Comunicación Asíncrona

| Componente A | Componente B | Método | Protocolo | Estado |
|--------------|--------------|--------|-----------|--------|
| **ChatbotService** | Job (NotifyAdvisors) | Dispatch | Queue | ✅ Funcional |
| **Controller** | Job (Report Generation) | Dispatch | Queue | ⚠️ Parcial |
| **Service** | Event | Dispatch | Event Bus | ❌ NO Implementado |
| **Model** | Observer | Hook | Event Bus | ❌ NO Implementado |

---

## 7. MATRIZ DE TESTING POR COMPONENTE

### 7.1 Pruebas Unitarias

| Componente | Tests Unitarios | Cobertura Actual | Cobertura Objetivo | Prioridad |
|------------|-----------------|------------------|-------------------|-----------|
| **Models** | Eloquent relationships, scopes, casts | 0% | 80% | ALTA |
| **Services** | Lógica de negocio, errores | 0% | 85% | ALTA |
| **Repositories** | CRUD, consultas personalizadas | 0% | 90% | ALTA |
| **Policies** | Autorización por rol | 0% | 85% | MEDIA |
| **Controllers** | HTTP requests, respuestas | 0% | 70% | MEDIA |
| **Jobs** | Ejecución, errores | 0% | 85% | MEDIA |
| **Events** | Dispatch, listeners | 0% | 80% | BAJA |
| **Observers** | Hooks, side effects | 0% | 80% | BAJA |

### 7.2 Pruebas de Integración

| Flujo | Tests de Integración | Estado | Prioridad |
|-------|---------------------|--------|-----------|
| **Usuario → Dashboard** | Login, redirección, KPIs | ❌ NO Implementado | ALTA |
| **Asesor → Orden → Mecánico** | Creación, asignación, actualización | ❌ NO Implementado | ALTA |
| **Chatbot → Cita → Asesor** | Solicitud, confirmación, conversión | ❌ NO Implementado | ALTA |
| **Mantenimiento → Vehículo** | Registro, actualización de estado | ❌ NO Implementado | MEDIA |
| **Compra → Stock** | Registro, recepción, actualización | ❌ NO Implementado | MEDIA |
| **Orden → Foto** | Subida, asociación, visualización | ❌ NO Implementado | MEDIA |

### 7.3 Pruebas E2E (End-to-End)

| Escenario | Tests E2E | Estado | Prioridad |
|-----------|-----------|--------|-----------|
| **Flujo completo de mantenimiento** | Cliente → Asesor → Mecánico → Cliente | ❌ NO Implementado | ALTA |
| **Flujo de citas vía chatbot** | Cliente → Chatbot → Asesor → Mecánico | ❌ NO Implementado | ALTA |
| **Flujo de inventario** | Admin → Compra → Stock → Mecánico | ❌ NO Implementado | MEDIA |
| **Flujo de fotografías** | Mecánico → Storage → Cliente | ❌ NO Implementado | MEDIA |

---

## 8. PLAN DE IMPLEMENTACIÓN DE SKILLS

### 8.1 Prioridad ALTA (Implementación Inmediata)

#### 8.1.1 AutoGest Debug Assistant
**Tiempo:** 4-6 horas
**Beneficio:** Facilita debugging de problemas específicos
**Dependencias:** Ninguna

#### 8.1.2 AutoGest Refactoring Assistant
**Tiempo:** 6-8 horas
**Beneficio:** Estandariza refactoring de código
**Dependencias:** Conocimiento de arquitectura AutoGest

### 8.2 Prioridad MEDIA (Implementación Temprana)

#### 8.2.1 Laravel Controller Generator
**Tiempo:** 8-12 horas
**Beneficio:** Acelera creación de controladores
**Dependencias:** Conocimiento de convenciones AutoGest

#### 8.2.2 Laravel Model Generator
**Tiempo:** 6-8 horas
**Beneficio:** Acelera creación de modelos
**Dependencias:** Conocimiento de estructura AutoGest

#### 8.2.3 Laravel Service Generator
**Tiempo:** 8-12 horas
**Beneficio:** Acelera creación de services
**Dependencias:** Conocimiento de arquitectura AutoGest

### 8.3 Prioridad BAJA (Implementación Opcional)

#### 8.3.1 Laravel View Generator
**Tiempo:** 10-14 horas
**Beneficio:** Acelera creación de vistas
**Dependencias:** Conocimiento de TailwindCSS y diseño AutoGest

#### 8.3.2 Laravel Policy Generator
**Tiempo:** 4-6 horas
**Beneficio:** Acelera creación de policies
**Dependencias:** Conocimiento de autorización AutoGest

#### 8.3.3 Laravel Repository Generator
**Tiempo:** 8-12 horas
**Beneficio:** Facilita implementación de Repository Pattern
**Dependencias:** Conocimiento de arquitectura AutoGest

#### 8.3.4 Laravel Migration Generator
**Tiempo:** 4-6 horas
**Beneficio:** Acelera creación de migraciones
**Dependencias:** Conocimiento de estructura BD AutoGest

#### 8.3.5 Laravel Route Generator
**Tiempo:** 4-6 horas
**Beneficio:** Acelera creación de rutas
**Dependencias:** Conocimiento de estructura rutas AutoGest

**Total Estimado Skills:** 62-90 horas

---

## 9. MATRIZ DE DEPENDENCIAS ENTRE FASES DE IMPLEMENTACIÓN

### 9.1 Dependencias Lógicas

| Fase | Dependencias Previas | Dependencias Posteriores |
|------|---------------------|--------------------------|
| **Configuración del Entorno** | Ninguna | Todas las fases |
| **Corrección de Seguridad** | Configuración del Entorno | Refactoring Arquitectónico |
| **Refactoring Arquitectónico** | Corrección de Seguridad | Implementación de Módulos |
| **Implementación de Módulos** | Refactoring Arquitectónico | Testing |
| **Implementación de Fotografías** | Implementación de Módulos | Testing |
| **Refactoring de Chatbot** | Implementación de Módulos | Testing |
| **Optimización de Módulos** | Implementación de Módulos | Testing |
| **Pruebas Funcionales** | Implementación de Módulos | Validación Técnica |
| **Pruebas Técnicas** | Pruebas Funcionales | Preparación WebView |
| **Preparación WebView** | Pruebas Técnicas | Generación APK |
| **Generación APK** | Preparación WebView | Documentación Final |

### 9.2 Dependencias de Datos

| Fase | Datos Requeridos | Datos Generados |
|------|-----------------|-----------------|
| **Configuración del Entorno** | Ninguno | .env, database configurada |
| **Corrección de Seguridad** | Sistema configurado | Sistema seguro |
| **Refactoring Arquitectónico** | Sistema seguro | Código refactorizado |
| **Implementación de Módulos** | Código refactorizado | Módulos funcionales |
| **Implementación de Fotografías** | Módulos funcionales | Fotos subidas y visibles |
| **Refactoring de Chatbot** | Módulos funcionales | Chatbot modularizado |
| **Optimización de Módulos** | Módulos funcionales | Módulos optimizados |
| **Pruebas Funcionales** | Módulos optimizados | Tests pasando |
| **Pruebas Técnicas** | Tests funcionales | Métricas técnicas |
| **Preparación WebView** | Métricas técnicas | Sistema optimizado móvil |
| **Generación APK** | Sistema optimizado móvil | APK generada |

---

## 10. CONCLUSIÓN

Este documento establece la propuesta de Skills para automatizar tareas específicas de AutoGest y la matriz de integración detallada entre componentes del sistema.

### Puntos Clave:

1. **10 Skills Propuestas** para automatización de tareas
2. **Matriz de Integración** completa entre skills y componentes
3. **Matriz de Dependencias** técnicas entre módulos
4. **Matriz de Riesgos** de integración
5. **Matriz de Comunicación** síncrona y asíncrona
6. **Matriz de Testing** por componente
7. **Plan de Implementación** de skills priorizado
8. **Matriz de Dependencias** entre fases de implementación

### Recomendación:

Implementar las skills de **Prioridad ALTA** (AutoGest Debug Assistant y AutoGest Refactoring Assistant) inmediatamente para facilitar el proceso de corrección y refactoring del sistema.

Las skills de **Prioridad MEDIA** deberían implementarse durante la fase de refactoring arquitectónico para acelerar el proceso.

Las skills de **Prioridad BAJA** son opcionales y pueden implementarse según necesidad durante el desarrollo.

---

**Documento preparado por:** Equipo Multidisciplinario de Ingeniería de Software  
**Fecha:** 2026-08-04  
**Versión:** 1.0  
**Estado:** Propuesta para revisión y aprobación

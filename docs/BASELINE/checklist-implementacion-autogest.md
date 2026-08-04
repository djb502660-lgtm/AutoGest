# CHECKLIST DE IMPLEMENTACIÓN - AutoGest
**Plantilla Específica - AutoGest**
**Versión:** 2.0 (Específica para AutoGest)
**Uso:** Obligatorio para todas las tareas de implementación en AutoGest

---

## Tarea: [Nombre de la Tarea]
**Fase:** [Fase X]
**Sprint:** [Sprint X]
**Fecha:** [YYYY-MM-DD]
**Responsable:** [Nombre]
**Estado:** [Pendiente/En Progreso/Completado]
**Prioridad:** [P0/P1/P2/P3]

---

## 1. CONTEXTO DE LA TAREA

### Objetivo
[Descripción clara del objetivo de la tarea]

### Requerimiento
[Requerimiento específico que se cumple]

### Módulo Afectado
- [ ] Admin
- [ ] Advisor
- [ ] Client
- [ ] Mechanic
- [ ] Chatbot
- [ ] Inventory
- [ ] Core

---

## 2. ARCHIVOS A MODIFICAR

### Archivo: [ruta/archivo.php]
- [ ] **Tipo de cambio:** [refactor/nuevo/eliminación]
- [ ] **Líneas afectadas:** [estimación]
- [ ] **Justificación:** [por qué se modifica]
- [ ] **Riesgo asociado:** [Alto/Medio/Bajo]
- [ ] **Backup creado:** [Sí/No]
- [ ] **Modelos afectados:** [lista de modelos]
- [ ] **Rutas afectadas:** [lista de rutas]

---

## 3. ARCHIVOS NUEVOS A CREAR

### Archivo: [ruta/nuevo-archivo.php]
- [ ] **Propósito:** [qué hace]
- [ ] **Dependencias:** [qué requiere]
- [ ] **Integración:** [cómo se integra]
- [ ] **Testing:** [cómo se probará]
- [ ] **Tipo:** [Controller/Model/Migration/Service/View/etc]

---

## 4. ARCHIVOS A ELIMINAR/DESUSAR

### Archivo: [ruta/archivo-obsoleto.php]
- [ ] **Motivo:** [por qué se elimina]
- [ ] **Impacto:** [qué afecta]
- [ ] **Plan de migración:** [cómo se migra]
- [ ] **Backup conservado:** [Sí/No]
- [ ] **Referencias actualizadas:** [Sí/No]

---

## 5. PRUEBAS A EJECUTAR

### Pruebas Automatizadas
- [ ] **php artisan test:** [Estado]
- [ ] **Tests específicos:** [lista de tests relevantes]
- [ ] **Cobertura de código:** [%]

### Pruebas Manuales - Admin
- [ ] **Dashboard admin:** [Estado]
- [ ] **Gestión de usuarios:** [Estado]
- [ ] **Gestión de vehículos:** [Estado]
- [ ] **Gestión de órdenes:** [Estado]
- [ ] **Reportes:** [Estado]

### Pruebas Manuales - Advisor
- [ ] **Dashboard advisor:** [Estado]
- [ ] **Gestión de citas:** [Estado]
- [ ] **Gestión de clientes:** [Estado]
- [ ] **Calendario integrado:** [Estado]

### Pruebas Manuales - Client
- [ ] **Dashboard client:** [Estado]
- [ ] **Chatbot:** [Estado]
- [ ] **Gestión de vehículos:** [Estado]
- [ ] **Historial de mantenimientos:** [Estado]

### Pruebas Manuales - Mechanic
- [ ] **Dashboard mechanic:** [Estado]
- [ ] **Gestión de órdenes:** [Estado]
- [ ] **Calendario:** [Estado]
- [ ] **Fotos de servicio:** [Estado]

---

## 6. ANÁLISIS DE RIESGO - AutoGest

### Evaluación de Riesgo
- **Nivel de Riesgo:** [Alto/Medio/Bajo]
- **Impacto si falla:** [Crítico/Alto/Medio/Bajo]
- **Probabilidad de fallo:** [Alta/Media/Baja]
- **Áreas afectadas:** [lista de módulos]

### Impacto en Roles
- [ ] **Admin:** [Alto/Medio/Bajo/Ninguno]
- [ ] **Advisor:** [Alto/Medio/Bajo/Ninguno]
- [ ] **Client:** [Alto/Medio/Bajo/Ninguno]
- [ ] **Mechanic:** [Alto/Medio/Bajo/Ninguno]

### Dependencias Críticas - AutoGest
- [ ] **User Model:** [Sí/No] - [Impacto]
- [ ] **Vehicle Model:** [Sí/No] - [Impacto]
- [ ] **ServiceOrder Model:** [Sí/No] - [Impacto]
- [ ] **Maintenance Model:** [Sí/No] - [Impacto]
- [ ] **Chatbot Services:** [Sí/No] - [Impacto]

---

## 7. PLAN DE REVERSIÓN - AutoGest

### Reversión de Código
- **Comando de reversión Git:** [git revert / git reset]
- **Branch de reversión:** [nombre del branch]
- **Commit hash de reversión:** [hash]
- [ ] **Reversión ejecutada:** [Sí/No]

### Reversión de Base de Datos
- **Migraciones a revertir:** [lista]
- **Comando:** [php artisan migrate:rollback --step=X]
- **Tablas afectadas:** [lista]
- [ ] **Backup de base de datos:** [Sí/No]
- [ ] **Datos afectados:** [descripción]

### Reversión de Configuración
- **Archivos .env:** [lista de cambios]
- **Archivos de configuración:** [lista]
- [ ] **Reversión ejecutada:** [Sí/No]

### Tiempo Estimado de Reversión
- **Tiempo total:** [X minutos]
- **Personal requerido:** [X personas]
- **Complejidad:** [Alta/Media/Baja]

---

## 8. CRITERIOS DE VALIDACIÓN - AutoGest

### Criterios Técnicos
- [ ] **Código compila sin errores:** [Sí/No]
- [ ] **Tests pasan (55/56 mínimo):** [Sí/No]
- [ ] **Funcionalidad verificada manualmente:** [Sí/No]
- [ ] **No hay warnings en Laravel:** [Sí/No]
- [ ] **No hay errores en logs:** [Sí/No]
- [ ] **PSR-12 cumplido:** [Sí/No]
- [ ] **Laravel Pint pasa:** [Sí/No]

### Criterios de Performance
- [ ] **Performance no degradada:** [Sí/No]
- [ ] **Tiempo de respuesta < 2s:** [Sí/No]
- [ ] **Sin memory leaks:** [Sí/No]
- [ ] **Queries optimizadas:** [Sí/No]
- [ ] **N+1 queries eliminados:** [Sí/No]

### Criterios de Seguridad
- [ ] **Sin vulnerabilidades introducidas:** [Sí/No]
- [ ] **Input validation implementada:** [Sí/No]
- [ ] **Authorization verificada (Policies):** [Sí/No]
- [ ] **CSRF protegido:** [Sí/No]
- [ ] **SQL injection prevenido:** [Sí/No]
- [ ] **XSS prevenido:** [Sí/No]

### Criterios de Calidad - AutoGest
- [ ] **Sin código duplicado:** [Sí/No]
- [ ] **Nomenclatura consistente:** [Sí/No]
- [ ] **Comentarios claros:** [Sí/No]
- [ ] **Code readability aceptable:** [Sí/No]
- [ ] **Service Layer usado cuando apropiado:** [Sí/No]
- [ ] **Repository Pattern considerado:** [Sí/No]

---

## 9. DOCUMENTACIÓN - AutoGest

### Documentación de Cambios
- [ ] **CHANGELOG.md actualizado:** [Sí/No]
- [ ] **Comentarios en código agregados:** [Sí/No]
- [ ] **README.md actualizado:** [Sí/No]
- [ ] **Technical debt documentado:** [Sí/No]

### Documentación de Usuario
- [ ] **Manual de usuario actualizado:** [Sí/No]
- [ ] **Capturas de pantalla agregadas:** [Sí/No]
- [ ] **Notas de versión creadas:** [Sí/No]

### Documentación Técnica - AutoGest
- [ ] **Matriz de dependencias actualizada:** [Sí/No]
- [ ] **Inventario actualizado:** [Sí/No]
- [ ] **Baseline actualizado:** [Sí/No]
- [ ] **Diagramas actualizados:** [Sí/No]

---

## 10. VALIDACIÓN POST-IMPLEMENTACIÓN

### Review de Código
- [ ] **Self-review completado:** [Sí/No]
- [ ] **Peer review completado:** [Sí/No]
- [ ] **Review de seguridad:** [Sí/No]
- [ ] **Review de performance:** [Sí/No]

### Aprobación
- [ ] **Aprobación obtenida:** [Sí/No]
- [ ] **Aprobador:** [Nombre]
- [ ] **Fecha de aprobación:** [YYYY-MM-DD]
- [ ] **Comentarios:** [notas]

### Actualización de Baseline
- [ ] **Baseline actualizado:** [Sí/No]
- [ ] **Inventario actualizado:** [Sí/No]
- [ ] **Matriz de dependencias actualizada:** [Sí/No]
- [ ] **Evidencias guardadas:** [Sí/No]

---

## 11. CIERRE DE TAREA

### Resumen de Cambios
- **Archivos modificados:** [X]
- **Archivos nuevos:** [X]
- **Archivos eliminados:** [X]
- **Líneas de código agregadas:** [X]
- **Líneas de código eliminadas:** [X]

### Métricas - AutoGest
- **Tiempo estimado:** [X horas]
- **Tiempo real:** [X horas]
- **Desviación:** [+/- X horas]
- **Tests creados:** [X]
- **Tests pasando:** [X]
- **Tests fallando:** [X]

### Lecciones Aprendidas
- [ ] **Lección 1:** [descripción]
- [ ] **Lección 2:** [descripción]
- [ ] **Lección 3:** [descripción]

### Siguientes Pasos
- [ ] **Paso 1:** [descripción]
- [ ] **Paso 2:** [descripción]
- [ ] **Paso 3:** [descripción]

---

## FIRMA Y APROBACIÓN

**Implementado por:** [Nombre] - [Fecha]
**Revisado por:** [Nombre] - [Fecha]
**Aprobado por:** [Nombre] - [Fecha]

**Estado Final:** [Completado/Requiere Ajustes/Fallido]

---

## NOTAS ESPECÍFICAS DE AutoGEST

[Notas específicas para el proyecto AutoGest, consideraciones especiales, integraciones con chatbot, inventario, etc.]

---

## REFERENCIAS

- **Baseline Fase 0:** docs/BASELINE/
- **Matriz de Dependencias:** docs/BASELINE/dependency-matrix.md
- **Roadmap:** docs/ROADMAP_IMPLEMENTACION_FASE_10.md
- **Spec General:** docs/SPEC_GENERAL.md

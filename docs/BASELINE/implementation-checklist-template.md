# CHECKLIST DE IMPLEMENTACIÓN
**Plantilla Estándar - AutoGest**
**Versión:** 1.0
**Uso:** Obligatorio para todas las tareas de implementación

---

## Tarea: [Nombre de la Tarea]
**Fase:** [Fase X]
**Sprint:** [Sprint X]
**Fecha:** [YYYY-MM-DD]
**Responsable:** [Nombre]
**Estado:** [Pendiente/En Progreso/Completado]

---

## 1. ARCHIVOS A MODIFICAR

### Archivo: [ruta/archivo.php]
- [ ] **Tipo de cambio:** [refactor/nuevo/eliminación]
- [ ] **Líneas afectadas:** [estimación]
- [ ] **Justificación:** [por qué se modifica]
- [ ] **Riesgo asociado:** [Alto/Medio/Bajo]
- [ ] **Backup creado:** [Sí/No]

### Archivo: [ruta/archivo2.php]
- [ ] **Tipo de cambio:** [refactor/nuevo/eliminación]
- [ ] **Líneas afectadas:** [estimación]
- [ ] **Justificación:** [por qué se modifica]
- [ ] **Riesgo asociado:** [Alto/Medio/Bajo]
- [ ] **Backup creado:** [Sí/No]

---

## 2. ARCHIVOS NUEVOS A CREAR

### Archivo: [ruta/nuevo-archivo.php]
- [ ] **Propósito:** [qué hace]
- [ ] **Dependencias:** [qué requiere]
- [ ] **Integración:** [cómo se integra]
- [ ] **Testing:** [cómo se probará]

### Archivo: [ruta/nuevo-archivo2.php]
- [ ] **Propósito:** [qué hace]
- [ ] **Dependencias:** [qué requiere]
- [ ] **Integración:** [cómo se integra]
- [ ] **Testing:** [cómo se probará]

---

## 3. ARCHIVOS A ELIMINAR/DESUSAR

### Archivo: [ruta/archivo-obsoleto.php]
- [ ] **Motivo:** [por qué se elimina]
- [ ] **Impacto:** [qué afecta]
- [ ] **Plan de migración:** [cómo se migra]
- [ ] **Backup conservado:** [Sí/No]
- [ ] **Referencias actualizadas:** [Sí/No]

---

## 4. PRUEBAS A EJECUTAR

### Pruebas Unitarias
- [ ] **Prueba unitaria:** [test específico]
  - [ ] Archivo: [ruta/test.php]
  - [ ] Estado: [Pasando/Fallando]
  - [ ] Tiempo de ejecución: [X segundos]

### Pruebas Funcionales
- [ ] **Prueba funcional:** [qué funcionalidad]
  - [ ] Escenario: [descripción]
  - [ ] Estado: [Pasando/Fallando]
  - [ ] Observaciones: [notas]

### Pruebas de Integración
- [ ] **Prueba de integración:** [qué sistema]
  - [ ] Componentes: [lista]
  - [ ] Estado: [Pasando/Fallando]
  - [ ] Observaciones: [notas]

### Pruebas de Regresión
- [ ] **Prueba de regresión:** [qué no debe romperse]
  - [ ] Funcionalidad: [descripción]
  - [ ] Estado: [Pasando/Fallando]
  - [ ] Observaciones: [notas]

---

## 5. ANÁLISIS DE RIESGO

### Evaluación de Riesgo
- **Nivel de Riesgo:** [Alto/Medio/Bajo]
- **Impacto si falla:** [Crítico/Alto/Medio/Bajo]
- **Probabilidad de fallo:** [Alta/Media/Baja]
- **Áreas afectadas:** [lista de módulos/componentes]

### Plan de Mitigación
- [ ] **Mitigación 1:** [cómo se reduce el riesgo]
- [ ] **Mitigación 2:** [cómo se reduce el riesgo]
- [ ] **Mitigación 3:** [cómo se reduce el riesgo]

### Dependencias Críticas
- [ ] **Dependencia 1:** [descripción]
- [ ] **Dependencia 2:** [descripción]
- [ ] **Dependencia 3:** [descripción]

---

## 6. PLAN DE REVERSIÓN

### Reversión de Código
- **Comando de reversión Git:** [git revert / git reset]
- **Branch de reversión:** [nombre del branch]
- **Commit hash de reversión:** [hash]
- [ ] **Reversión ejecutada:** [Sí/No]

### Reversión de Base de Datos
- **Migraciones a revertir:** [lista]
- **Comando:** [php artisan migrate:rollback]
- **Datos afectados:** [descripción]
- [ ] **Backup de base de datos:** [Sí/No]
- [ ] **Reversión ejecutada:** [Sí/No]

### Reversión de Configuración
- **Archivos de configuración:** [lista]
- **Valores originales:** [descripción]
- [ ] **Reversión ejecutada:** [Sí/No]

### Tiempo Estimado de Reversión
- **Tiempo total:** [X minutos]
- **Personal requerido:** [X personas]
- **Complejidad:** [Alta/Media/Baja]

---

## 7. CRITERIOS DE VALIDACIÓN

### Criterios Técnicos
- [ ] **Código compila sin errores:** [Sí/No]
- [ ] **Tests pasan:** [Sí/No]
- [ ] **Funcionalidad verificada manualmente:** [Sí/No]
- [ ] **No hay warnings:** [Sí/No]
- [ ] **No hay errores en logs:** [Sí/No]
- [ ] **PSR-12 cumplido:** [Sí/No]

### Criterios de Performance
- [ ] **Performance no degradada:** [Sí/No]
- [ ] **Tiempo de respuesta aceptable:** [Sí/No]
- [ ] **Sin memory leaks:** [Sí/No]
- [ ] **Queries optimizadas:** [Sí/No]

### Criterios de Seguridad
- [ ] **Sin vulnerabilidades introducidas:** [Sí/No]
- [ ] **Input validation implementada:** [Sí/No]
- [ ] **Authorization verificada:** [Sí/No]
- [ ] **CSRF protegido:** [Sí/No]

### Criterios de Calidad
- [ ] **Sin código duplicado:** [Sí/No]
- [ ] **Nomenclatura consistente:** [Sí/No]
- [ ] **Comentarios claros:** [Sí/No]
- [ ] **Code readability aceptable:** [Sí/No]

---

## 8. DOCUMENTACIÓN

### Documentación de Cambios
- [ ] **Changelog actualizado:** [Sí/No]
- [ ] **Comentarios en código agregados:** [Sí/No]
- [ ] **README actualizado:** [Sí/No]
- [ ] **Technical debt documentado:** [Sí/No]

### Documentación de Usuario
- [ ] **Manual de usuario actualizado:** [Sí/No]
- [ ] **Capturas de pantalla agregadas:** [Sí/No]
- [ ] **Notas de versión creadas:** [Sí/No]

### Documentación Técnica
- [ ] **Documentación de API actualizada:** [Sí/No]
- [ ] **Diagramas actualizados:** [Sí/No]
- [ ] **Arquitectura documentada:** [Sí/No]

---

## 9. VALIDACIÓN POST-IMPLEMENTACIÓN

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

## 10. CIERRE DE TAREA

### Resumen de Cambios
- **Archivos modificados:** [X]
- **Archivos nuevos:** [X]
- **Archivos eliminados:** [X]
- **Líneas de código agregadas:** [X]
- **Líneas de código eliminadas:** [X]

### Métricas
- **Tiempo estimado:** [X horas]
- **Tiempo real:** [X horas]
- **Desviación:** [+/- X horas]
- **Tests creados:** [X]
- **Tests pasando:** [X]

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

## NOTAS ADICIONALES

[Espacio para notas adicionales, observaciones, o comentarios relevantes]

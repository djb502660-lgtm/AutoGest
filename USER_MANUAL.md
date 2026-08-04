# Manual de Usuario - AutoGest

## Versión
v0.8-sprint3c-admin-module

## Fecha
2026-08-04

---

## 1. Introducción

AutoGest es un sistema de gestión de taller de vehículos que permite a clientes, asesores, mecánicos y administradores gestionar el ciclo completo de mantenimiento vehicular de manera eficiente y organizada.

### Objetivos del Manual
Este manual guía a los usuarios en el uso básico del sistema, explicando las funcionalidades principales de cada rol.

---

## 2. Roles del Sistema

AutoGest cuenta con cuatro tipos de usuarios:

### 2.1 Cliente
- Consultar estado de sus vehículos
- Ver historial de mantenimientos
- Agendar citas
- Consultar sus órdenes de servicio
- Interactuar con el chatbot

### 2.2 Asesor de Servicio
- Crear órdenes de servicio
- Asignar mecánicos
- Gestionar citas
- Atender clientes
- Generar reportes básicos

### 2.3 Mecánico
- Ver órdenes asignadas
- Actualizar progreso de trabajos
- Registrar mantenimientos
- Agregar comentarios y observaciones
- Cargar evidencias fotográficas

### 2.4 Administrador
- Gestión completa del sistema
- Gestión de usuarios y roles
- Control de inventario
- Generación de reportes avanzados
- Configuración del sistema

---

## 3. Guía para Clientes

### 3.1 Acceso al Sistema

1. Visite la URL del sistema
2. Ingrese su email y contraseña
3. Haga clic en "Iniciar sesión"

### 3.2 Dashboard Principal

El dashboard del cliente muestra:
- Resumen de sus vehículos
- Órdenes activas
- Próximos mantenimientos
- Gastos totales del año

### 3.3 Consultar Estado de Vehículo

**Opción 1: Via Dashboard**
1. Haga clic en "Mis Vehículos"
2. Seleccione el vehículo deseado
3. Verifique el estado actual

**Opción 2: Via Chatbot**
1. Escriba: "¿Cómo va mi carro?"
2. Si tiene un solo vehículo, mostrará automáticamente
3. Si tiene varios, indique la placa

### 3.4 Agendar Cita

**Opción 1: Formulario**
1. Haga clic en "Agendar Cita"
2. Seleccione el vehículo
3. Elija fecha y hora
4. Describa el servicio requerido
5. Confirme la cita

**Opción 2: Chatbot**
1. Escriba: "Quiero agendar una cita"
2. Siga las instrucciones del asistente
3. Confirme fecha y hora

### 3.5 Ver Historial de Mantenimientos

1. Haga clic en "Historial de Mantenimientos"
2. Filtre por vehículo si es necesario
3. Ver detalles de cada servicio realizado

### 3.6 Consultar Gastos

1. Haga clic en "Mis Gastos"
2. Ver desglose de servicios realizados
3. Consulte total anual

---

## 4. Guía para Asesores de Servicio

### 4.1 Dashboard del Asesor

El dashboard muestra:
- Órdenes del día
- Citas pendientes
- Vehículos en taller
- Resumen de operaciones

### 4.2 Crear Orden de Servicio

1. Haga clic en "Nueva Orden"
2. Seleccione el cliente
3. Seleccione el vehículo
4. Describa el servicio requerido
5. Asigne prioridad
6. Asigne mecánico (opcional, puede hacerse después)
7. Cree la orden

### 4.3 Asignar Mecánico

1. Abra la orden de servicio
2. Haga clic en "Asignar Mecánico"
3. Seleccione el mecánico disponible
4. Confirme la asignación

### 4.4 Gestionar Citas

**Crear Cita:**
1. Haga clic en "Nueva Cita"
2. Seleccione cliente y vehículo
3. Defina fecha y hora
4. Confirme

**Cancelar Cita:**
1. Abra la cita
2. Haga clic en "Cancelar"
3. Confirme la cancelación

### 4.5 Atender Pre-ordenes

Las pre-ordenes son solicitudes de servicio que pueden convertirse en órdenes reales:

1. Revise la lista de pre-ordenes
2. Complete la información necesaria
3. Haga clic en "Convertir a Orden"
4. Confirme la conversión

---

## 5. Guía para Mecánicos

### 5.1 Dashboard del Mecánico

El dashboard muestra:
- Órdenes asignadas
- Calendario de trabajos
- Historial de trabajos realizados

### 5.2 Ver Órdenes Asignadas

1. Acceda a "Mis Órdenes"
2. Ver lista de trabajos pendientes
3. Seleccione una orden para ver detalles

### 5.3 Actualizar Progreso

1. Abra la orden de servicio
2. Haga clic en "Actualizar Progreso"
3. Seleccione el porcentaje completado (0-100%)
4. Confirme la actualización

**Nota:** Cuando el progreso llega a 100%, la orden cambia automáticamente a estado "completada".

### 5.4 Cambiar Estado de Orden

1. Abra la orden de servicio
2. Seleccione el nuevo estado
3. Agregue observaciones si es necesario
4. Confirme el cambio

**Estados disponibles:**
- recibida
- en_proceso
- completada
- entregada
- cancelada

### 5.5 Registrar Mantenimiento

1. Abra la orden de servicio
2. Haga clic en "Registrar Mantenimiento"
3. Complete:
   - Tipo de mantenimiento
   - Descripción
   - Costo
   - Evidencias (fotos)
4. Guarde el registro

### 5.6 Agregar Comentarios

1. Abra la orden de servicio
2. Escriba el comentario en el campo correspondiente
3. Haga clic en "Agregar Comentario"
4. El comentario se guarda con fecha y hora

---

## 6. Guía para Administradores

### 6.1 Dashboard Administrativo

El dashboard muestra métricas del taller:
- Total de vehículos registrados
- Mantenimientos realizados este mes
- Órdenes pendientes
- Alertas activas
- Usuarios activos
- Gastos del mes

### 6.2 Gestión de Usuarios

**Crear Usuario:**
1. Haga clic en "Nuevo Usuario"
2. Complete:
   - Nombre
   - Email
   - Contraseña
   - Rol (Administrador, Asesor, Mecánico, Cliente)
   - Teléfono (opcional)
   - Estado (activo/inactivo)
3. Guarde el usuario

**Editar Usuario:**
1. Seleccione el usuario
2. Modifique los campos necesarios
3. Guarde los cambios

**Cambiar Rol:**
1. Abra el usuario
2. Seleccione el nuevo rol
3. Guarde los cambios
4. Se registra automáticamente en el audit log

**Desactivar Usuario:**
1. Abra el usuario
2. Cambie estado a "inactivo"
3. Guarde
4. El usuario no podrá acceder al sistema

### 6.3 Gestión de Inventario

**Ver Inventario:**
1. Haga clic en "Inventario"
2. Ver productos, categorías, marcas
3. Consultar stock actual

**Registrar Compra:**
1. Haga clic en "Nueva Compra"
2. Seleccione proveedor
3. Agregue productos comprados
4. Confirme la compra
5. El stock se actualiza automáticamente

**Alertas de Stock Bajo:**
1. Haga clic en "Stock Bajo"
2. Ver productos con stock mínimo
3. Generar órdenes de reposición

### 6.4 Generación de Reportes

**Tipos de Reportes:**
- Mantenimientos
- Gastos
- Vehículos
- Pendientes

**Generar Reporte:**
1. Haga clic en "Reportes"
2. Seleccione el tipo de reporte
3. Aplicar filtros (opcional):
   - Vehículo específico
   - Rango de fechas
4. Haga clic en "Generar"
5. Ver resultados

**Exportar Reporte:**
- **PDF**: Haga clic en "Descargar PDF"
- **CSV**: Haga clic en "Descargar CSV"
- **Email**: Haga clic en "Enviar a mi correo"

### 6.5 Gestión de Vehículos

**Registrar Vehículo:**
1. Haga clic en "Nuevo Vehículo"
2. Seleccione el cliente
3. Complete:
   - Placa
   - Marca
   - Modelo
   - Año
   - Kilometraje
   - Estado
4. Guarde el vehículo

**Editar Vehículo:**
1. Seleccione el vehículo
2. Modifique los campos necesarios
3. Guarde los cambios

### 6.6 Calendario

**Ver Calendario:**
1. Haga clic en "Calendario"
2. Ver agenda mensual
3. Filtrar por mecánico si es necesario

**Crear Evento:**
1. Haga clic en "Nuevo Evento"
2. Seleccione vehículo y mecánico
3. Defina fecha y tipo de servicio
4. Guarde el evento

---

## 7. Chatbot Inteligente

### 7.1 Acceso al Chatbot

El chatbot está disponible en el portal del cliente 24/7.

### 7.2 Funciones del Chatbot

**Consultas comunes:**
- "¿Cómo va mi carro?"
- "¿Cuándo fue mi último mantenimiento?"
- "¿Cuánto he gastado este año?"
- "Quiero agendar una cita"

**Respuestas contextuales:**
- Reconoce si tienes un solo vehículo o varios
- Mantiene contexto de conversación
- Sugiere acciones basadas en estado del vehículo

### 7.3 Tips de Uso

- Sea específico en sus preguntas
- Indique la placa si tiene múltiples vehículos
- Use lenguaje natural (ej: "mi carro hace ruido al frenar")
- El chatbot puede agendar citas automáticamente

---

## 8. Seguridad y Best Practices

### 8.1 Contraseñas
- Use contraseñas fuertes (mínimo 8 caracteres)
- No comparta su contraseña
- Cámbiela periódicamente

### 8.2 Sesiones
- Cierre sesión al terminar de usar el sistema
- No deje la sesión abierta en computadoras compartidas
- El sistema cierra sesión automáticamente después de inactividad

### 8.3 Datos Sensibles
- No imprime reportes con datos sensibles en impresoras compartidas
- Cierre documentos después de usarlos
- El sistema registra todas las acciones críticas

---

## 9. Soporte y Ayuda

### 9.1 Problemas Comunes

**No puedo iniciar sesión:**
- Verifique email y contraseña
- Contacte al administrador si olvidó su contraseña

**No veo mis vehículos:**
- Contacte al taller para verificar que sus vehículos estén registrados
- Verifique que está en la cuenta correcta

**El chatbot no responde:**
- Verifique su conexión a internet
- Intente recargar la página
- Contacte al soporte técnico

### 9.2 Contacto de Soporte

Para problemas técnicos o preguntas adicionales:
- Email: soporte@autogest.example.com
- Teléfono: +1-234-567-8900
- Horario: Lunes a Viernes, 8:00 AM - 6:00 PM

---

## 10. Conclusión

AutoGest está diseñado para ser intuitivo y fácil de usar. Si tiene sugerencias para mejorar el sistema, no dude en contactar al equipo de desarrollo.

¡Gracias por usar AutoGest!

# ADR-003: Arquitectura Modular del Chatbot

## Estado
Propuesta

## Contexto y Problema
El chatbot actual está implementado principalmente en dos servicios grandes (`ChatbotService` y `ChatbotAppointmentService`), lo que crea:
- Monolito difícil de mantener y expandir
- Dificultad para agregar nuevas funcionalidades sin romper existentes
- Testing complejo por acoplamiento
- Difícil reutilización de componentes
- Escalabilidad limitada

El chatbot es el componente con mayor probabilidad de crecimiento (nuevos intents, integraciones, funcionalidades).

## Decisiones Consideradas

### Opción 1: Mantener arquitectura monolítica actual
- **Ventajas:**
  - Sin cambios disruptivos
  - Código existente funciona
  - Curva de aprendizaje nula
- **Desventajas:**
  - Difícil de mantener
  - Riesgo de regresiones al agregar funcionalidades
  - Testing complejo
  - No escalable a largo plazo

### Opción 2: Arquitectura basada en componentes modulares
- **Ventajas:**
  - Alta mantenibilidad
  - Fácil agregar nuevos componentes
  - Testing aislado por componente
  - Reutilización de componentes
  - Escalabilidad clara
  - Separación de responsabilidades
- **Desventajas:**
  - Mayor complejidad inicial
  - Requiere refactorización significativa
  - Curva de aprendizaje
  - Tiempo de desarrollo inicial

### Opción 3: Arquitectura híbrida (componentes + servicios actuales)
- **Ventajas:**
  - Migración gradual
  - Menor riesgo inicial
  - Permite evaluar beneficios incrementalmente
- **Desventajas:**
  - Arquitectura inconsistente temporalmente
  - Difícil definir límites
  - Posible duplicación temporal

## Decisión
Implementar arquitectura basada en componentes modulares para el chatbot, siguiendo esta estructura:

### Estructura de Componentes
```
Chatbot/
├── Core/
│   ├── IntentDetector          # Detección de intenciones
│   ├── ContextManager          # Gestión de contexto conversacional
│   ├── ConversationManager    # Gestión del flujo de conversación
│   └── ResponseFormatter      # Formateo de respuestas
├── Managers/
│   ├── AppointmentManager     # Gestión de citas
│   ├── VehicleManager         # Gestión de vehículos
│   ├── FAQManager            # Gestión de preguntas frecuentes
│   ├── NotificationManager   # Gestión de notificaciones
│   └── EscalationManager     # Gestión de escalas a humanos
├── Integrations/
│   ├── CalendarIntegration    # Integración con calendario
│   ├── VehicleIntegration     # Integración con vehículos
│   └── NotificationIntegration # Integración con notificaciones
└── Services/
    ├── ChatbotOrchestrator    # Orquestador de componentes
    └── ChatbotValidator      # Validación de inputs
```

### Flujo de Arquitectura
```
Usuario Input
    ↓
IntentDetector (analiza intención)
    ↓
ContextManager (recupera/mantiene contexto)
    ↓
ConversationManager (determina flujo)
    ↓
Manager correspondiente (ej: AppointmentManager)
    ↓
Integrations (si necesarias)
    ↓
ResponseFormatter (formatea respuesta)
    ↓
Usuario Output
```

## Consecuencias

### Positivas
- Alta mantenibilidad y escalabilidad
- Testing aislado por componente
- Fácil agregar nuevas funcionalidades
- Reutilización de componentes
- Mejor separación de responsabilidades
- Preparado para integraciones futuras (IA, ML)

### Negativas
- Mayor complejidad inicial
- Requiere refactorización significativa
- Curva de aprendizaje para el equipo
- Tiempo de desarrollo inicial aumentado

### Riesgos
- Complejidad de orquestación entre componentes
- Performance si no se optimiza correctamente
- Dificultad en debugging de flujos complejos
- Posible sobre-ingeniería

## Implementación

### Archivos afectados
- `app/Services/ChatbotService.php` (refactorizar a ChatbotOrchestrator)
- `app/Services/ChatbotAppointmentService.php` (mover a AppointmentManager)
- `app/Modules/Chatbot/` (reorganizar estructura)
- `app/Chatbot/` (nueva estructura modular)
- `tests/Unit/Chatbot/` (tests por componente)

### Esfuerzo estimado
- FASE 3 o FASE 4
- 1-2 semanas de desarrollo
- 1 semana de testing
- 3-4 días de documentación

### Dependencias
- Completar FASE 1 (estabilización)
- ADR-001 aprobado (Repository Pattern)
- Tests de chatbot existentes pasando

### Modelo de implementación
```php
// IntentDetector
class IntentDetector {
    public function detect(string $message): Intent {
        // Analiza mensaje y retorna intención
        return Intent::fromMessage($message);
    }
}

// ContextManager
class ContextManager {
    public function getContext(int $userId): ConversationContext {
        // Recupera contexto del usuario
    }
    
    public function updateContext(int $userId, array $data): void {
        // Actualiza contexto
    }
}

// AppointmentManager
class AppointmentManager {
    public function handleAppointment(Intent $intent, Context $context): Response {
        // Maneja flujo de citas
    }
}

// ChatbotOrchestrator
class ChatbotOrchestrator {
    public function processMessage(string $message, int $userId): Response {
        $intent = $this->intentDetector->detect($message);
        $context = $this->contextManager->getContext($userId);
        
        $manager = $this->getManagerForIntent($intent);
        $response = $manager->handle($intent, $context);
        
        $this->contextManager->updateContext($userId, $response->getContextUpdates());
        
        return $this->responseFormatter->format($response);
    }
}
```

### Estrategia de migración
1. Crear nueva estructura modular
2. Migrar funcionalidad existente componente por componente
3. Mantener servicios antiguos como wrappers durante transición
4. Actualizar tests incrementalmente
5. Eliminar código obsoleto al finalizar migración

## Referencias
- [Designing Chatbot Architecture](https://chatbotsmagazine.com/designing-chatbot-architecture-for-scalability)
- [Microservices for Chatbots](https://medium.com/@nitya/chatbot-architecture-design)
- Baseline actual: docs/BASELINE/dependency-matrix.md

## Fecha
2026-08-04

## Autor
Technical Lead - AutoGest Project

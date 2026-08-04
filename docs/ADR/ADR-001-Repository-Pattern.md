# ADR-001: Implementación de Repository Pattern

## Estado
Aprobada e Implementada (Sprint 2A)

## Contexto y Problema
Actualmente, los controladores de AutoGest interactúan directamente con los modelos Eloquent, lo que crea:
- Acoplamiento fuerte entre controladores y ORM
- Dificultad para testing (requiere base de datos)
- Lógica de consulta duplicada en múltiples controladores
- Dificultad para cambiar la implementación de persistencia en el futuro

## Decisiones Consideradas

### Opción 1: Continuar con acceso directo a Eloquent
- **Ventajas:**
  - Sin overhead adicional
  - Código más simple inicialmente
  - Curva de aprendizaje menor
- **Desventajas:**
  - Acoplamiento fuerte a Laravel/Eloquent
  - Testing difícil sin base de datos real
  - Lógica de consulta duplicada
  - Difícil de refactorizar

### Opción 2: Implementar Repository Pattern completo
- **Ventajas:**
  - Desacoplamiento de la capa de persistencia
  - Testing más sencillo (mocks de repositorios)
  - Lógica de consulta centralizada
  - Fácil cambio de implementación
  - Mejor separación de responsabilidades
- **Desventajas:**
  - Mayor complejidad inicial
  - Más código boilerplate
  - Curva de aprendizaje para el equipo
  - Overhead de abstracción

### Opción 3: Implementar Repository Pattern parcial (solo para modelos complejos)
- **Ventajas:**
  - Balance entre simplicidad y beneficios
  - Aplicado donde más se necesita
  - Menor overhead que opción completa
- **Desventajas:**
  - Inconsistencia en arquitectura
  - Difícil decidir cuándo usar
  - Puede llevar a confusión

## Decisión
Implementar Repository Pattern completo para todos los modelos principales del sistema, siguiendo estos principios:

1. **Interfaz por cada repositorio:** Definir contratos claros
2. **Implementación Eloquent:** Repositorios concretos usando Eloquent
3. **Inyección de dependencias:** Inyectar interfaces en controladores
4. **Métodos estándar:** CRUD + consultas específicas del dominio
5. **Caching opcional:** Capacidad de agregar caching sin cambiar controladores

## Consecuencias

### Positivas
- Mejor testabilidad con mocks
- Lógica de consulta centralizada y reutilizable
- Fácil migración a otra tecnología de persistencia
- Separación clara de responsabilidades
- Código más mantenible a largo plazo

### Negativas
- Mayor complejidad inicial del proyecto
- Más archivos y capas en la arquitectura
- Curva de aprendizaje para desarrolladores
- Tiempo de desarrollo inicial aumentado

### Riesgos
- Sobre-ingeniería si el proyecto no crece
- Dificultad para desarrolladores junior
- Posible resistencia al cambio por el equipo

## Implementación

### Estado Actual (Sprint 2A)
- ✅ Documento de guías creado: `docs/REPOSITORY_GUIDELINES.md`
- ✅ Interfaces implementadas para 4 modelos núcleo:
  - `VehicleRepositoryInterface`
  - `ServiceOrderRepositoryInterface`
  - `MaintenanceRepositoryInterface`
  - `UserRepositoryInterface`
- ✅ BaseRepository implementado con métodos estándar
- ✅ 4 repositorios Eloquent implementados
- ✅ RepositoryServiceProvider creado y registrado
- ✅ Controller migrado: `Client\VehicleController`
- ✅ Quality Gate: 56/56 tests pasando
- ✅ Build exitoso sin degradación

### Archivos afectados
- `app/Contracts/Repositories/` (nuevo - interfaces)
- `app/Repositories/BaseRepository.php` (nuevo)
- `app/Repositories/Eloquent/` (nuevo - implementaciones)
- `app/Providers/RepositoryServiceProvider.php` (nuevo)
- `bootstrap/providers.php` (modificado)
- `app/Http/Controllers/Client/VehicleController.php` (migrado)
- `docs/REPOSITORY_GUIDELINES.md` (nuevo)

### Esfuerzo Real
- Sprint 2A completado en 1 día
- 56/56 tests manteniéndose
- Sin degradación de performance
- Enfoque incremental controlado

### Dependencias
- ✅ Sprint 1 completado (seguridad y consistencia)
- ✅ Sprint 1.5 completado (revisión técnica)
- ✅ Tests existentes pasando (56/56)

### Modelo de implementación
```php
// Interfaz
interface UserRepositoryInterface {
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function getAll(): Collection;
    public function create(array $data): User;
    public function update(int $id, array $data): User;
    public function delete(int $id): bool;
}

// Implementación
class EloquentUserRepository implements UserRepositoryInterface {
    public function findById(int $id): ?User {
        return User::find($id);
    }
    // ... otros métodos
}

// Uso en controller
class UserController extends Controller {
    private UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository) {
        $this->userRepository = $userRepository;
    }
}
```

## Referencias
- [Repository Pattern - Martin Fowler](https://martinfowler.com/eaaCatalog/repository.html)
- [Laravel Repository Pattern Tutorial](https://medium.com/@calebporzio/laravel-repository-pattern)
- Baseline de dependencias: docs/BASELINE/dependency-matrix.md

## Fecha
2026-08-04 (aprobada e implementada Sprint 2A)

## Autor
Technical Lead - AutoGest Project

## Lecciones Aprendidas
- Implementación incremental es crucial para mantener estabilidad
- Compatibilidad de tipos en interfaces requiere cuidado con PHP
- Migración de un controlador a la vez minimiza riesgos
- Documentación de guías antes de implementación evita inconsistencias

# REPOSITORY GUIDELINES - AutoGest
**Guías de Implementación del Repository Pattern**
**Fecha:** 2026-08-04
**Versión:** 1.0

---

## PROPÓSITO

Establecer reglas claras y consistentes para la implementación del Repository Pattern en AutoGest, garantizando que los repositorios sean mantenibles, predecibles y sigan buenas prácticas de arquitectura.

---

## PRINCIPIOS FUNDAMENTALES

### 1. Single Responsibility
Un Repository solo debe manejar el acceso a datos de un único modelo.

### 2. No Lógica de Negocio
Los repositorios NO deben contener lógica de negocio. Solo acceso a datos.

### 3. Abstracción de Origen de Datos
Los repositorios abstraen el origen de datos. El código que los consume no debe saber si usan Eloquent, SQL crudo, o una API externa.

### 4. Consistencia
Todos los repositorios deben seguir las mismas convenciones de nombres y estructura.

---

## ESTRUCTURA DE DIRECTORIOS

```
app/
 ├── Contracts/
 │    └── Repositories/
 │         ├── VehicleRepositoryInterface.php
 │         ├── ServiceOrderRepositoryInterface.php
 │         ├── MaintenanceRepositoryInterface.php
 │         └── UserRepositoryInterface.php
 │
 └── Repositories/
      ├── BaseRepository.php
      └── Eloquent/
           ├── VehicleRepository.php
           ├── ServiceOrderRepository.php
           ├── MaintenanceRepository.php
           └── UserRepository.php
```

---

## CONVENCIONES DE NOMBRES

### Interfaces
- Nombre: `{Model}RepositoryInterface`
- Namespace: `App\Contracts\Repositories`
- Ejemplo: `VehicleRepositoryInterface`

### Implementaciones
- Nombre: `{Model}Repository`
- Namespace: `App\Repositories\Eloquent`
- Ejemplo: `VehicleRepository`

### Métodos Estándar
- `find($id)` - Buscar por ID
- `findById($id)` - Alias de find (más explícito)
- `all()` - Obtener todos los registros
- `paginate($perPage = 15)` - Paginación
- `create(array $data)` - Crear nuevo registro
- `update($id, array $data)` - Actualizar registro
- `delete($id)` - Eliminar registro
- `where($column, $operator, $value)` - Filtro básico
- `findBy($column, $value)` - Buscar por columna específica

---

## QUÉ PUEDE HACER UN REPOSITORY

### ✅ PERMITIDO

1. **Consultas a la base de datos**
   - Eloquent queries
   - SQL crudo (cuando Eloquent no es suficiente)
   - Consultas complejas con joins, subqueries

2. **Relaciones Eloquent**
   - Cargar relaciones eager loading
   - Manejar relaciones hasMany, belongsTo, etc.

3. **Filtros y Búsquedas**
   - Métodos de búsqueda específicos (findByStatus, findByClient, etc.)
   - Scopes encapsulados

4. **Paginación**
   - Métodos de paginación con filtros
   - Ordenamiento

5. **Transacciones**
   - Manejo de transacciones cuando se requiere consistencia

---

## QUÉ NO DEBE HACER UN REPOSITORY

### ❌ PROHIBIDO

1. **Lógica de negocio**
   - Cálculos de costos
   - Validaciones de reglas de negocio
   - Notificaciones
   - Envío de emails

2. **Dependencias de otros repositorios**
   - Un repository no debe instanciar otro repository
   - Usar Services para coordinar múltiples repositorios

3. **Dependencias de HTTP/External APIs**
   - Los repositorios no deben hacer llamadas HTTP
   - Usar Services para integraciones externas

4. **Lógica de presentación**
   - Formateo de datos para vistas
   - Conversión a JSON específico para frontend

5. **Manejo de sesiones**
   - Los repositorios no deben acceder a sesiones
   - Usar Request o Controllers para datos de sesión

---

## MÉTODOS ESTÁNDAR POR REPOSITORY

### BaseRepository
Todos los repositorios deben extender de BaseRepository que contiene:

```php
abstract class BaseRepository
{
    protected $model;

    public function find($id)
    public function all()
    public function paginate($perPage = 15)
    public function create(array $data)
    public function update($id, array $data)
    public function delete($id)
    public function where($column, $operator, $value)
    public function with(array $relations)
}
```

### Métodos Específicos por Modelo
Cada repository puede tener métodos específicos según sus necesidades:

#### VehicleRepository
- `findByPlate(string $plate)`
- `findByClient(int $clientId)`
- `findByBrand(string $brand)`
- `getAvailableVehicles()`

#### ServiceOrderRepository
- `findByStatus(string $status)`
- `findByMechanic(int $mechanicId)`
- `findByVehicle(int $vehicleId)`
- `getActiveOrders()`

#### MaintenanceRepository
- `findByServiceOrder(int $serviceOrderId)`
- `findByVehicle(int $vehicleId)`
- `getRecentMaintenances()`

#### UserRepository
- `findByRole(string $role)`
- `findActiveUsers()`
- `findByEmail(string $email)`

---

## MANEJO DE RELACIONES

### Eager Loading
Los repositorios deben usar eager loading para evitar problemas N+1:

```php
// ✅ CORRECTO
public function withVehicles()
{
    return $this->model->with('vehicles')->get();
}

// ❌ INCORRECTO (causa N+1)
public function getVehicles()
{
    $users = $this->all();
    foreach ($users as $user) {
        $user->vehicles; // N+1 problem
    }
}
```

### Relaciones Encapsuladas
Los repositorios deben encapsular el acceso a relaciones:

```php
// ✅ CORRECTO
public function findWithVehicles($id)
{
    return $this->model->with('vehicles')->find($id);
}

// ❌ INCORRECTO (deja expuesta la relación)
// $vehicleRepository->find($id)->vehicles
```

---

## CRITERIOS DE RETORNO

### Model
Retornar instancias del modelo Eloquent cuando se requiere manipulación posterior:

```php
public function find($id): ?Model
{
    return $this->model->find($id);
}
```

### Collection
Retornar colecciones cuando se requiere iteración:

```php
public function all(): Collection
{
    return $this->model->all();
}
```

### Paginator
Retornar paginadores cuando se requiere paginación:

```php
public function paginate($perPage = 15): LengthAwarePaginator
{
    return $this->model->paginate($perPage);
}
```

### DTO (Futuro)
En futuras iteraciones, considerar retornar DTOs para desacoplar completamente de Eloquent.

---

## MANEJO DE TRANSACCIONES

Los repositorios deben soportar transacciones cuando se requiere consistencia:

```php
public function createWithRelations(array $data, array $relations)
{
    return DB::transaction(function () use ($data, $relations) {
        $model = $this->create($data);

        foreach ($relations as $relation) {
            // Crear relaciones
        }

        return $model;
    });
}
```

---

## TESTING

### Reglas de Testing
1. Cada repository debe tener tests unitarios
2. Los tests deben usar Factories, no datos reales
3. Los tests deben cubrir todos los métodos públicos
4. Los tests deben usar in-memory database SQLite

### Ejemplo de Test
```php
class VehicleRepositoryTest extends TestCase
{
    public function test_find_by_plate()
    {
        $vehicle = Vehicle::factory()->create(['plate' => 'ABC-123']);

        $repository = new VehicleRepository(new Vehicle());
        $found = $repository->findByPlate('ABC-123');

        $this->assertEquals($vehicle->id, $found->id);
    }
}
```

---

## EJEMPLO COMPLETO

### Interface
```php
namespace App\Contracts\Repositories;

interface VehicleRepositoryInterface
{
    public function find($id): ?Vehicle;
    public function all(): Collection;
    public function findByPlate(string $plate): ?Vehicle;
    public function findByClient(int $clientId): Collection;
    public function create(array $data): Vehicle;
    public function update($id, array $data): bool;
    public function delete($id): bool;
}
```

### Implementación
```php
namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository implements VehicleRepositoryInterface
{
    protected $model;

    public function __construct(Vehicle $model)
    {
        $this->model = $model;
    }

    public function find($id): ?Vehicle
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findByPlate(string $plate): ?Vehicle
    {
        return $this->model->where('plate', $plate)->first();
    }

    public function findByClient(int $clientId): Collection
    {
        return $this->model->where('client_id', $clientId)->get();
    }

    public function create(array $data): Vehicle
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): bool
    {
        $vehicle = $this->find($id);
        return $vehicle ? $vehicle->update($data) : false;
    }

    public function delete($id): bool
    {
        $vehicle = $this->find($id);
        return $vehicle ? $vehicle->delete() : false;
    }
}
```

---

## SERVICE PROVIDER BINDING

Registrar los bindings en un Service Provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Repositories\Eloquent\VehicleRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        // ... otros bindings
    }
}
```

---

## MIGRACIÓN INCREMENTAL

### Estrategia
1. Implementar el repository
2. Crear tests
3. Migrar UN solo controlador/servicio
4. Ejecutar tests
5. Si pasan → commit
6. Si fallan → revertir y corregir
7. Repetir para siguiente módulo

### Ejemplo de Migración
```php
// ANTES (en Controller)
public function show($id)
{
    $vehicle = Vehicle::find($id);
    return view('vehicles.show', compact('vehicle'));
}

// DESPUÉS (en Controller)
public function show($id)
{
    $vehicle = $this->vehicleRepository->find($id);
    return view('vehicles.show', compact('vehicle'));
}
```

---

## CRITERIOS DE CALIDAD

Antes de considerar un repository como "completo":

- ✅ No contiene lógica de negocio
- ✅ Solo acceso a datos
- ✅ Métodos con nombres claros y consistentes
- ✅ Sin consultas duplicadas
- ✅ Relaciones Eloquent encapsuladas
- ✅ Sin consultas N+1 nuevas
- ✅ Tests completos
- ✅ Documentation completa
- ✅ Type hints en todos los métodos
- ✅ Return types declarados

---

## REFERENCIAS

- **ADR-001:** Repository Pattern Decision
- **Laravel Documentation:** Repositories and Services
- **Martin Fowler:** Patterns of Enterprise Application Architecture

---

## APROBACIÓN

**Technical Lead:** AutoGest Project
**Fecha:** 2026-08-04
**Versión:** 1.0
**Estado:** Activo

---

## REVISIONES

| Versión | Fecha | Cambios | Autor |
|---------|-------|--------|-------|
| 1.0 | 2026-08-04 | Guías iniciales | Technical Lead |

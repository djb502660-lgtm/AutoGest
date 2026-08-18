<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->trim();
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();

        $users = $this->userService->getUsersPaginated(
            $search->isNotEmpty() ? $search->toString() : null,
            $role !== '' ? $role : null,
            $status !== '' ? $status : null
        );

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search->toString(),
            'role' => $role,
            'status' => $status,
            'roles' => UserRole::cases(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = $this->userService->createUser(new UserDTO(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            role: $validated['role'],
            phone: $validated['phone'] ?? null,
            status: $validated['status']
        ));

        ActivityLog::record(
            'user.created',
            "Se registró el usuario {$user->email} con rol {$user->role->label()}.",
            model: $user,
            user: $request->user(),
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $this->userService->updateUser($user->id, new UserDTO(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'] ?? '',
            role: $validated['role'],
            phone: $validated['phone'] ?? null,
            status: $validated['status']
        ));

        ActivityLog::record(
            'user.updated',
            "Se actualizó el usuario {$user->email}.",
            model: $user,
            user: $request->user(),
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $email = $user->email;
        $userName = $user->name;

        // Hard delete - eliminar completamente el usuario
        // Las foreign keys con cascadeOnDelete() eliminarán automáticamente
        // los registros relacionados (vehículos, órdenes de servicio, etc.)
        $user->delete();

        ActivityLog::record(
            'user.deleted',
            "Se eliminó el usuario {$email} ({$userName}). Todos sus registros relacionados fueron eliminados automáticamente.",
            user: auth()->user(),
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario eliminado correctamente. Si el cliente regresa, se puede crear una nueva cuenta.');
    }
}

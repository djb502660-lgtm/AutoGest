<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        $user = $this->userService->createUser(new \App\DTOs\UserDTO(
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

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

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

        $this->userService->updateUser($user->id, new \App\DTOs\UserDTO(
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
        $this->authorize('delete', $user);

        // Proteger al usuario autenticado actual
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Verificar si tiene registros críticos vinculados
        $hasCriticalRelations = $user->clientOrders()->exists()
            || $user->vehicles()->exists()
            || $user->assignedOrders()->exists()
            || $user->advisorOrders()->exists();

        if ($hasCriticalRelations) {
            $this->userService->updateUserStatus($user->id, 'inactivo');

            ActivityLog::record(
                'user.deactivated',
                "Se desactivó el usuario {$user->email} (tiene registros vinculados).",
                model: $user,
                user: auth()->user(),
            );

            return redirect()
                ->route('users.index')
                ->with('success', 'El usuario fue desactivado porque tiene registros vinculados.');
        }

        $email = $user->email;

        try {
            $this->userService->deleteUser($user->id);

            ActivityLog::record(
                'user.deleted',
                "Se eliminó el usuario {$email}.",
                user: auth()->user(),
            );

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Si hay error de foreign key, desactivar en su lugar
            $this->userService->updateUserStatus($user->id, 'inactivo');

            ActivityLog::record(
                'user.deactivated',
                "Se desactivó el usuario {$user->email} (error al eliminar: restricción de base de datos).",
                model: $user,
                user: auth()->user(),
            );

            return redirect()
                ->route('users.index')
                ->with('success', 'El usuario fue desactivado debido a restricciones de base de datos.');
        }
    }
}

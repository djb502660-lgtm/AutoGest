<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();

        $clients = User::where('role', 'cliente')
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('advisor.clients.index', [
            'clients' => $clients,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('advisor.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:activo,inactivo'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'cliente',
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('advisor.clients.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function show(User $client)
    {
        if ($client->role !== 'cliente') {
            return redirect()
                ->route('advisor.clients.index')
                ->with('error', 'El usuario no es un cliente.');
        }

        $client->load(['vehicles' => fn ($q) => $q->orderBy('plate')]);

        return view('advisor.clients.show', [
            'client' => $client,
        ]);
    }

    public function edit(User $client)
    {
        if ($client->role !== 'cliente') {
            return redirect()
                ->route('advisor.clients.index')
                ->with('error', 'El usuario no es un cliente.');
        }

        return view('advisor.clients.edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, User $client)
    {
        if ($client->role !== 'cliente') {
            return redirect()
                ->route('advisor.clients.index')
                ->with('error', 'El usuario no es un cliente.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($client->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:activo,inactivo'],
        ]);

        $client->name = $validated['name'];
        $client->email = $validated['email'];
        $client->phone = $validated['phone'] ?? null;
        $client->status = $validated['status'];

        if (! empty($validated['password'])) {
            $client->password = bcrypt($validated['password']);
        }

        $client->save();

        return redirect()
            ->route('advisor.clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }
}

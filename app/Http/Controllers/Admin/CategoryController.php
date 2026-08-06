<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();

        $categories = Category::query()
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        // Normalizar campos que el modal podría enviar (p. ej. 'nombre_categoria')
        $data = $request->all();

        if (isset($data['nombre_categoria']) && empty($data['name'])) {
            $data['name'] = $data['nombre_categoria'];
        }

        // Derivar slug desde el nombre y validar duplicados.
        $data['slug'] = str($data['name'] ?? '')->slug();

        $validated = validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'slug.unique' => 'La categoría ya existe.',
        ])->validate();

        $validated['is_active'] = $validated['is_active'] ?? true;

        Category::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Categoría creada correctamente.']);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->all();

        if (isset($data['nombre_categoria']) && empty($data['name'])) {
            $data['name'] = $data['nombre_categoria'];
        }

        $data['slug'] = str($data['name'] ?? '')->slug();

        $validated = validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'slug.unique' => 'La categoría ya existe.',
        ])->validate();

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Categoría actualizada correctamente.']);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}

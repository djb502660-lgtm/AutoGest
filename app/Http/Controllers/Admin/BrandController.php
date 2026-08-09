<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();

        $brands = Brand::query()
            ->search($search)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.brands.index', [
            'brands' => $brands,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        Brand::create($validated);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', [
            'brand' => $brand,
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $brand->update($validated);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return redirect()
                ->route('brands.index')
                ->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }

        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca eliminada correctamente.');
    }
}

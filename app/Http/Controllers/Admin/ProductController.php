<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $category = $request->string('category')->toString();
        $brand = $request->string('brand')->toString();

        $products = Product::query()
            ->with(['category', 'brand'])
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($category !== '', fn ($q) => $q->where('category_id', $category))
            ->when($brand !== '', fn ($q) => $q->where('brand_id', $brand))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search->toString(),
            'category' => $category,
            'brand' => $brand,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        // Mapear campos del modal a los campos del modelo
        $mappedData = [
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'description' => $validated['description'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? 0,
            'sale_price' => $validated['sale_price'],
            'stock_quantity' => $validated['stock_quantity'] ?? 1,
            'min_stock' => $validated['min_stock'] ?? 2,
            'max_stock' => $validated['max_stock'] ?? null,
            'unit' => 'unid',
            'is_active' => true,
        ];

        $product = Product::create($mappedData);

        $this->auditService->logInventoryAction(
            'product_created',
            "Producto {$product->sku} creado",
            auth()->id(),
            null,
            ['id' => $product->id, 'sku' => $product->sku, 'name' => $product->name]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto creado correctamente.']);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        $oldValues = $product->toArray();

        $product->update($validated);

        $this->auditService->logInventoryAction(
            'product_updated',
            "Producto {$product->sku} actualizado",
            auth()->id(),
            $oldValues,
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto actualizado correctamente.']);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchaseItems()->exists() || $product->stockMovements()->exists()) {
            return redirect()
                ->route('products.index')
                ->with('error', 'No se puede eliminar el producto porque tiene registros asociados.');
        }

        $this->auditService->logInventoryAction(
            'product_deleted',
            "Producto {$product->sku} eliminado",
            auth()->id(),
            ['id' => $product->id, 'sku' => $product->sku, 'name' => $product->name],
            null
        );

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}

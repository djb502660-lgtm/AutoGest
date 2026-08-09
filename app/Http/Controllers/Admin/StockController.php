<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $type = $request->string('type')->toString();

        $movements = StockMovement::query()
            ->with(['product', 'purchase', 'serviceOrder'])
            ->search($search)
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock.index', [
            'movements' => $movements,
            'search' => $search->toString(),
            'type' => $type,
        ]);
    }

    public function create()
    {
        return view('admin.stock.create', [
            'products' => Product::catalog()->get(),
        ]);
    }

    public function store(Request $request, StockService $stock)
    {
        // Mapear campos del modal a los campos del modelo
        $tipoMovimiento = $request->input('tipo_movimiento') ?? $request->input('type');
        $productId = $request->input('product_id');
        
        // Mapear tipo de movimiento al formato del modelo
        $typeMap = [
            'ingreso' => 'entrada',
            'egreso' => 'salida',
        ];
        $type = $typeMap[$tipoMovimiento] ?? 'ajuste';

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['type'] = $type;
        $validated['notes'] = $request->input('motivo') ?? $validated['notes'] ?? 'Ajuste manual de stock';

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['type'] === 'salida' && $product->stock_quantity < $validated['quantity']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No hay suficiente stock para esta salida.']);
            }

            return redirect()
                ->back()
                ->with('error', 'No hay suficiente stock para esta salida.');
        }

        $stock->applyMovement($product, $validated['type'], $validated['quantity'], [
            'notes' => $validated['notes'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Stock ajustado correctamente.']);
        }

        return redirect()
            ->route('stock.index')
            ->with('success', 'Movimiento de stock registrado correctamente.');
    }

    public function lowStock()
    {
        $products = Product::active()
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->orderBy('name')
            ->get();

        return view('admin.stock.low', [
            'products' => $products,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();

        $purchases = Purchase::query()
            ->with(['supplier', 'items'])
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('purchase_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'search' => $search->toString(),
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('admin.purchases.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchase = Purchase::create([
            'purchase_number' => 'COMP-'.date('Y-m').'-'.str_pad(Purchase::count() + 1, 4, '0', STR_PAD_LEFT),
            'supplier_id' => $validated['supplier_id'],
            'purchase_date' => $validated['purchase_date'],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'status' => 'pendiente',
            'notes' => $validated['notes'] ?? null,
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $total = $item['quantity'] * $item['unit_price'];
            $subtotal += $total;

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $tax = $subtotal * 0.16;
        $total = $subtotal + $tax;

        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Compra creada correctamente.']);
        }

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Compra creada correctamente.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        return view('admin.purchases.show', [
            'purchase' => $purchase,
        ]);
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->status !== 'pendiente') {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'Solo se pueden editar compras pendientes.');
        }

        $purchase->load(['items']);

        return view('admin.purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status !== 'pendiente') {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'Solo se pueden editar compras pendientes.');
        }

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $purchase->update([
            'supplier_id' => $validated['supplier_id'],
            'purchase_date' => $validated['purchase_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $purchase->items()->delete();

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $total = $item['quantity'] * $item['unit_price'];
            $subtotal += $total;

            $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $total,
            ]);
        }

        $tax = $subtotal * 0.16;
        $total = $subtotal + $tax;

        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Compra actualizada correctamente.');
    }

    public function receive(Purchase $purchase)
    {
        if ($purchase->status !== 'pendiente') {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'Solo se pueden recibir compras pendientes.');
        }

        $purchase->update(['status' => 'recibida']);

        foreach ($purchase->items as $item) {
            $product = $item->product;
            $previousStock = $product->stock_quantity;
            $newStock = $previousStock + $item->quantity;

            $product->update(['stock_quantity' => $newStock]);

            $product->stockMovements()->create([
                'type' => 'entrada',
                'quantity' => $item->quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'purchase_id' => $purchase->id,
                'notes' => 'Recepción de compra '.$purchase->purchase_number,
            ]);
        }

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Compra recibida y stock actualizado correctamente.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'recibida') {
            return redirect()
                ->route('purchases.index')
                ->with('error', 'No se puede eliminar una compra recibida.');
        }

        $purchase->delete();

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Compra eliminada correctamente.');
    }
}

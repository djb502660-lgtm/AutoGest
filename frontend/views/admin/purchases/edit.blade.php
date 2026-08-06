@extends('layouts.admin')

@section('title', 'Editar compra')
@section('heading', 'Editar compra')
@section('subheading', 'Modifica una orden de compra pendiente.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('purchases.update', $purchase) }}" id="purchaseForm">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="supplier_id">Proveedor *</label>
                <select id="supplier_id" name="supplier_id" required>
                    <option value="">Seleccionar proveedor</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $supplier->id == $purchase->supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="purchase_date">Fecha de compra *</label>
                <input type="date" id="purchase_date" name="purchase_date" value="{{ $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : '' }}" required>
            </div>
            
            <div class="form-group">
                <label>Productos *</label>
                <div id="itemsContainer">
                    @foreach ($purchase->items as $index => $item)
                    <div class="item-row">
                        <select name="items[{{ $index }}][product_id]" class="product-select" required>
                            <option value="">Seleccionar producto</option>
                            @foreach (\App\Models\Product::where('is_active', true)->get() as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $item->product_id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[{{ $index }}][quantity]" placeholder="Cantidad" min="1" value="{{ $item->quantity }}" required>
                        <input type="number" name="items[{{ $index }}][unit_price]" placeholder="Precio unitario" step="0.01" min="0" value="{{ $item->unit_price }}" required>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="addItem">+ Agregar producto</button>
            </div>
            
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3">{{ $purchase->notes }}</textarea>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let itemCount = {{ $purchase->items->count() }};
        
        document.getElementById('addItem').addEventListener('click', function() {
            const container = document.getElementById('itemsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <select name="items[${itemCount}][product_id]" class="product-select" required>
                    <option value="">Seleccionar producto</option>
                    @foreach (\App\Models\Product::where('is_active', true)->get() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
                <input type="number" name="items[${itemCount}][quantity]" placeholder="Cantidad" min="1" required>
                <input type="number" name="items[${itemCount}][unit_price]" placeholder="Precio unitario" step="0.01" min="0" required>
                <button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button>
            `;
            container.appendChild(newRow);
            itemCount++;
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.parentElement.remove();
            }
        });
    </script>
    @endpush
@endsection

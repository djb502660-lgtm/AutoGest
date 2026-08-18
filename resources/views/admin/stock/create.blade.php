@extends('layouts.admin')

@section('title', 'Nuevo movimiento de stock')
@section('heading', 'Nuevo movimiento de stock')
@section('subheading', 'Registra un movimiento manual de inventario.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="form-group">
                <label for="product_id">Producto *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Seleccionar producto</option>
                    @foreach (\App\Models\Product::where('is_active', true)->get() as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }}) - Stock: {{ $product->stock_quantity }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="type">Tipo de movimiento *</label>
                <select id="type" name="type" required>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                    <option value="ajuste">Ajuste</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Cantidad *</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('stock.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

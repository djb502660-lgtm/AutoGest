@extends('layouts.admin')

@section('title', 'Nuevo producto')
@section('heading', 'Nuevo producto')
@section('subheading', 'Registra un nuevo producto en el inventario.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" required autofocus>
            </div>
            <div class="form-group">
                <label for="sku">SKU *</label>
                <input type="text" id="sku" name="sku" required>
            </div>
            <div class="form-group">
                <label for="category_id">Categoría</label>
                <select id="category_id" name="category_id">
                    <option value="">Seleccionar categoría</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="brand_id">Marca</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Seleccionar marca</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="purchase_price">Precio compra *</label>
                <input type="number" id="purchase_price" name="purchase_price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="sale_price">Precio venta *</label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock actual *</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
            </div>
            <div class="form-group">
                <label for="min_stock">Stock mínimo *</label>
                <input type="number" id="min_stock" name="min_stock" min="0" required>
            </div>
            <div class="form-group">
                <label for="max_stock">Stock máximo</label>
                <input type="number" id="max_stock" name="max_stock" min="0">
            </div>
            <div class="form-group">
                <label for="unit">Unidad *</label>
                <input type="text" id="unit" name="unit" value="pieza" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" checked> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

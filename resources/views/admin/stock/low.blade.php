@extends('layouts.admin')

@section('title', 'Stock bajo')
@section('heading', 'Productos con stock bajo')
@section('subheading', 'Productos que han alcanzado su stock mínimo.')

@section('top-actions')
    <a href="{{ route('stock.index') }}" class="btn btn-secondary">Volver a movimientos</a>
@endsection

@section('content')
    <div class="panel">
        @forelse ($products as $product)
            <div class="alert alert-warning">
                <strong>{{ $product->name }} ({{ $product->sku }})</strong>
                <p>Stock actual: {{ $product->stock_quantity }} | Stock mínimo: {{ $product->min_stock }}</p>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary btn-sm">Gestionar producto</a>
            </div>
        @empty
            <p>No hay productos con stock bajo.</p>
        @endforelse
    </div>
@endsection

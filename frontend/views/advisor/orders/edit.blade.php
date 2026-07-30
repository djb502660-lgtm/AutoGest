@extends('layouts.advisor')

@section('title', 'Editar orden')
@section('heading', 'Editar orden '.$order->order_number)
@section('subheading')
    Actualiza los datos de la orden de trabajo.
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.orders.update', $order) }}">
            @csrf
            @method('PUT')
            @include('advisor.orders._form', ['order' => $order])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

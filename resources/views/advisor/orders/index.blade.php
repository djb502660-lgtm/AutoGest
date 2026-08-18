@extends('layouts.advisor')

@section('title', 'Órdenes')
@section('heading', 'Órdenes de trabajo')
@section('subheading')
    Registra órdenes y asígnalas a los mecánicos del taller.
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.create') }}" class="btn btn-primary">
        + Nueva orden
    </a>
@endsection

@section('content')
    <div class="panel">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <form method="GET" class="filters mb-0">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar orden o placa...">
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="recibida" @selected($status === 'recibida')>Recibida</option>
                    <option value="en_proceso" @selected($status === 'en_proceso')>En proceso</option>
                    <option value="completada" @selected($status === 'completada')>Completada</option>
                    <option value="entregada" @selected($status === 'entregada')>Entregada</option>
                    <option value="cancelada" @selected($status === 'cancelada')>Cancelada</option>
                </select>
                @if (! empty($unassigned))
                    <input type="hidden" name="unassigned" value="1">
                @endif
                <button type="submit" class="btn btn-secondary">Filtrar</button>
                @if ($status !== '' || ! empty($unassigned) || $search->isNotEmpty())
                    <a href="{{ route('advisor.orders.index') }}" class="btn btn-secondary">Quitar filtros</a>
                @endif
            </form>
            <a href="{{ route('advisor.orders.create') }}" class="btn btn-primary d-md-none">
                + Nueva orden
            </a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th>
                        <th>Vehículo</th>
                        <th>Cliente</th>
                        <th>Mecánico</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ $order->client->name }}</td>
                            <td>{{ $order->mechanic?->name ?? 'Sin asignar' }}</td>
                            <td>{{ Str::limit($order->description, 35) }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td>
                                <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-primary btn-sm">Ver</a>
                                <a href="{{ route('advisor.orders.edit', $order) }}" class="btn btn-secondary btn-sm">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                @if ($status === 'en_proceso')
                                    No hay órdenes en proceso.
                                @elseif (! empty($unassigned))
                                    No hay órdenes sin mecánico.
                                @else
                                    No hay órdenes registradas.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">{{ $orders->links('pagination.simple') }}</div>
    </div>
@endsection

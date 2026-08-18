@include('layouts.partials.panel-brand', ['subtitle' => $subtitle ?? 'Panel'])

@if (! empty($roleLabel))
    <div class="user-box">
        <strong>{{ auth()->user()->name }}</strong>
        {{ $roleLabel }}
    </div>
@endif

@include($nav)

<div class="sidebar-footer">
    @if (($footer ?? 'logout') === 'session')
        <strong>Sesión activa</strong><br>
        {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
    @else
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn logout btn-block">Cerrar sesión</button>
        </form>
    @endif
</div>

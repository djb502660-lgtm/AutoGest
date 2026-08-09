@extends('layouts.advisor')

@section('title', 'Nueva orden')
@section('heading', 'Nueva orden de trabajo')
@section('subheading')
    Registra la orden y asígnala a un mecánico del taller.
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <form method="POST" action="{{ route('advisor.orders.store') }}" id="orderForm">
                @csrf
                @include('advisor.orders._form')
                <div class="actions">
                    <button type="submit" class="btn btn-primary">Registrar orden</button>
                    <a href="{{ route('advisor.orders.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Mantenimientos sugeridos por modelo</h3>
            <p style="color:var(--muted);font-size:0.88rem;margin:0 0 12px;">Selecciona un vehículo para ver los servicios recomendados según su marca y modelo.</p>
            <div id="templateSuggestions">
                <p style="color:var(--muted);font-size:0.84rem;">Sin vehículo seleccionado.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const vehicleSelect = document.getElementById('vehicle_id');
    const descriptionField = document.getElementById('description');
    const box = document.getElementById('templateSuggestions');

    async function loadTemplates() {
        const id = vehicleSelect.value;
        if (!id) {
            box.innerHTML = '<p style="color:var(--muted);font-size:0.84rem;">Sin vehículo seleccionado.</p>';
            return;
        }
        try {
            const res = await fetch(`{{ url('/asesor/vehiculos') }}/${id}/plantillas`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) {
                throw new Error(`El servidor respondió con estado ${res.status}`);
            }
            const data = await res.json();
            if (!data.templates?.length) {
                box.innerHTML = '<p style="color:var(--muted);font-size:0.84rem;">No hay plantillas para este modelo.</p>';
                return;
            }
            box.innerHTML = data.templates.map(t => `
                <div class="notif-item" style="cursor:pointer;margin-bottom:8px;" data-title="${t.title}">
                    <strong>${t.title}</strong>
                    <span style="font-size:0.78rem;color:var(--muted);">${t.maintenance_type}${t.interval_km ? ' · '+t.interval_km+' km' : ''}</span>
                    ${t.description ? `<span style="display:block;font-size:0.78rem;color:var(--muted);">${t.description}</span>` : ''}
                </div>
            `).join('');
            box.querySelectorAll('[data-title]').forEach(el => {
                el.addEventListener('click', () => {
                    descriptionField.value = el.dataset.title;
                });
            });
        } catch (err) {
            console.error('No se pudieron cargar las plantillas sugeridas', err);
            box.innerHTML = '<p style="color:var(--danger);">Error al cargar sugerencias.</p>';
        }
    }

    vehicleSelect.addEventListener('change', loadTemplates);
    if (vehicleSelect.value) loadTemplates();
</script>
@endpush

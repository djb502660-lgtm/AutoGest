<div class="panel">
    <h3 style="margin:0 0 12px;">Plantillas aplicables al vehículo</h3>
    <p style="color:var(--muted);font-size:0.88rem;margin:0 0 12px;">
        {{ $helperText ?? 'Consulta los mantenimientos sugeridos según la marca y modelo del vehículo.' }}
    </p>

    <div id="{{ $containerId ?? 'templateSuggestions' }}">
        @forelse (($vehicleTemplates ?? collect()) as $template)
            <div class="notif-item {{ ($interactive ?? false) ? 'template-option template-option-interactive' : '' }}" @if(($interactive ?? false)) data-title="{{ $template->title }}" @endif>
                <strong>{{ $template->title }}</strong>
                <span style="font-size:0.78rem;color:var(--muted);">
                    {{ $template->maintenanceTypeLabel() }}
                    @if ($template->interval_km)
                        · {{ number_format($template->interval_km) }} km
                    @endif
                    @if ($template->interval_months)
                        · {{ $template->interval_months }} mes(es)
                    @endif
                </span>
                @if ($template->description)
                    <span style="display:block;font-size:0.78rem;color:var(--muted);">{{ $template->description }}</span>
                @endif
            </div>
        @empty
            <p style="color:var(--muted);font-size:0.84rem;">
                {{ $emptyText ?? 'Selecciona un vehículo para ver los servicios recomendados según su marca y modelo.' }}
            </p>
        @endforelse
    </div>

    @if (($interactive ?? false))
        <p style="color:var(--muted);font-size:0.78rem;margin:12px 0 0;">
            Haz clic sobre una plantilla para usarla como base de la descripción de la orden.
        </p>
    @endif
</div>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        .header { border-bottom: 2px solid #0d9488; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 18px; font-weight: bold; color: #0d9488; margin: 0; }
        .meta { font-size: 10px; color: #64748b; margin-top: 6px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .summary { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .summary td { border: 1px solid #cbd5e1; padding: 8px 10px; background: #f8fafc; }
        .summary strong { display: block; font-size: 13px; margin-top: 2px; }
        .summary span { font-size: 9px; color: #64748b; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0f766e; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        table.data td { border: 1px solid #e2e8f0; padding: 6px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">AutoGest</p>
        <h1>{{ $title }}</h1>
        <p class="meta">Generado: {{ $generatedAt }} · {{ $filtersLabel }}</p>
    </div>

    <table class="summary">
        <tr>
            @foreach ($summary as $label => $value)
                <td>
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </td>
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">No hay datos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">AutoGest — Instituto Superior Tecnológico Alberto Enríquez · San Lorenzo</p>
</body>
</html>

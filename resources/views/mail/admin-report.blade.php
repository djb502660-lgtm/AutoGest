<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #334155; line-height: 1.5;">
    <h2 style="color: #0d9488; margin-bottom: 4px;">AutoGest</h2>
    <p style="margin-top: 0;">Hola, {{ $adminName }}.</p>
    <p>Se adjunta el reporte <strong>{{ $title }}</strong> generado el {{ $generatedAt }}.</p>
    <p style="font-size: 14px; color: #64748b;">Filtros aplicados: {{ $filtersLabel }}</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin: 16px 0;">
        @foreach ($summary as $label => $value)
            <tr>
                <td style="border: 1px solid #e2e8f0; background: #f8fafc; font-size: 12px; color: #64748b;">{{ $label }}</td>
                <td style="border: 1px solid #e2e8f0; font-weight: bold;">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <p style="font-size: 13px; color: #64748b;">El detalle completo está en el archivo PDF adjunto.</p>
    <p style="font-size: 12px; color: #94a3b8;">AutoGest — Sistema de gestión de mantenimiento vehicular</p>
</body>
</html>

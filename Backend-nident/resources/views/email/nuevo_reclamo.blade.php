<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro - Valento</title>
    @include('email.partials.styles')
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <p class="header-brand">Valento</p>
            </div>

            <div class="content">
                <h1>Nuevo Registro Recibido</h1>
                <p>Se ha registrado un nuevo <strong>{{ $tipo_solicitud == 'complaint' ? 'Queja' : 'Reclamo' }}</strong>.</p>

                <div class="code-section">
                    <div class="code-label">EXPEDIENTE</div>
                    <div class="code-value">{{ $numero_reclamo }}</div>
                </div>

                <div style="text-align: left; background-color: #f8f9fa; border-left: 4px solid #c0392b; padding: 20px; border-radius: 8px;">
                    <p style="margin: 0 0 8px 0;"><strong>De:</strong> {{ $nombres }} {{ $apellidos }}</p>
                    <p style="margin: 0;"><strong>Doc:</strong> {{ $tipo_documento }} {{ $numero_documento }}</p>
                </div>

                <a href="{{ config('app.url') }}/admin/libro_reclamo/index" class="button">GESTIONAR →</a>
            </div>

            <div class="footer">
                <p class="footer-brand">Valento</p>
                <p class="footer-meta">Notificación automática</p>
            </div>
        </div>
    </div>
</body>
</html>

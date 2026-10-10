<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Reclamo - Detalles Rossety</title>
    @include('email.partials.styles')
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <p class="header-brand">Detalles Rossety</p>
            </div>

            <div class="content">
                <h1>¡Novedades en tu solicitud!</h1>
                <p>Hola <strong>{{ $nombres }}</strong>,</p>
                <p>Te informamos que el estado de tu reclamo <strong>{{ $numero_reclamo }}</strong> ha sido actualizado.</p>

                <div class="status-badge status-{{ $estado }}">
                    {{ str_replace('_', ' ', $estado) }}
                </div>

                @if(!empty($comentario_atencion))
                <div style="text-align: left; background-color: #f8f8f8; border-left: 4px solid #c0392b; padding: 20px; margin-top: 28px; border-radius: 8px;">
                    <h4 style="margin: 0 0 10px 0; color: #c0392b; font-size: 13px; text-transform: uppercase;">Respuesta de Detalles Rossety:</h4>
                    <p style="margin: 0; font-style: italic; color: #444;">{!! nl2br(e($comentario_atencion)) !!}</p>
                </div>
                @endif

                <a href="https://rossety.pe/libro-reclamos/estado" class="button">VER ESTADO ACTUAL →</a>
            </div>

            <div class="footer">
                <p class="footer-brand">Detalles Rossety</p>
                <p class="footer-meta">Lima, Perú</p>
            </div>
        </div>
    </div>
</body>
</html>

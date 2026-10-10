<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reclamo - Detalles Rossety</title>
    @include('email.partials.styles')
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <p class="header-brand">Detalles Rossety</p>
            </div>

            <div class="content">
                <h1>¡Hola, {{ $nombres }}!</h1>
                <p>Hemos recibido tu solicitud en <strong>Detalles Rossety</strong>. Tu tranquilidad es nuestra prioridad.</p>

                <div class="code-section">
                    <div class="code-label">TU CÓDIGO DE SEGUIMIENTO</div>
                    <div class="code-value">{{ $numero_reclamo }}</div>
                </div>

                <p>Tu solicitud ha sido registrada correctamente. Un especialista de nuestro equipo revisará los detalles y te responderá lo antes posible.</p>

                <table class="grid" role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="grid-item">
                            <h4>TIPO</h4>
                            <p>{{ $tipo_solicitud == 'complaint' ? 'Queja' : 'Reclamo' }}</p>
                        </td>
                        <td class="grid-item">
                            <h4>FECHA</h4>
                            <p>{{ $fecha_registro }}</p>
                        </td>
                    </tr>
                </table>

                <a href="https://rossety.pe/libro-reclamos/estado" class="button">SEGUIR MI RECLAMO →</a>
            </div>

            <div class="footer">
                <p class="footer-brand">Detalles Rossety</p>
                <p class="footer-meta">Lima, Perú</p>
            </div>
        </div>
    </div>
</body>
</html>

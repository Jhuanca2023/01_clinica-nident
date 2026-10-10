@php
    // Colores de marca (mismos del footer de la web y de los demás correos)
    $navy      = '#121a36';
    $navyDeep  = '#0b1126';
    $gold      = '#e7b669';
    $goldDark  = '#b8893a';
    $ivory     = '#f7f3ec';
    $muted     = '#8d93aa';
    $text      = '#3a3f52';
    $serif     = "Georgia, 'Times New Roman', serif";
    $sans      = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $urlRestablecer = route('reset.password.get', $token);
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Restablece tu contraseña — Valento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <style>
        @media only screen and (max-width: 600px) {
            .vc-pad   { padding-left: 24px !important; padding-right: 24px !important; }
            .vc-btn a { display: block !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:{{ $ivory }}; -webkit-text-size-adjust:none; width:100% !important;">

<!-- Texto de vista previa (bandeja de entrada) -->
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
    Recibimos una solicitud para cambiar la contraseña de tu cuenta Valento.
</div>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:{{ $ivory }};">
<tr>
<td align="center" style="padding:32px 12px;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; margin:0 auto;">

        <!-- ══ ENCABEZADO ══ -->
        <tr>
            <td align="center" style="background-color:{{ $navy }}; border-top:3px solid {{ $gold }}; padding:34px 24px 28px;">
                <table cellpadding="0" cellspacing="0" role="presentation" align="center">
                    <tr>
                        <td align="center" valign="middle" width="44" height="44" style="width:44px; height:44px; border:1px solid {{ $gold }}; font-family:{{ $serif }}; font-size:22px; color:{{ $gold }}; line-height:44px;">V</td>
                    </tr>
                </table>
                <p style="margin:16px 0 0; font-family:{{ $serif }}; font-size:24px; letter-spacing:8px; color:#ffffff; text-transform:uppercase;">Valento</p>
                <p style="margin:4px 0 0; font-family:{{ $serif }}; font-size:14px; font-style:italic; color:{{ $gold }};">Semplicemente Valento</p>
            </td>
        </tr>

        <!-- ══ CUERPO ══ -->
        <tr>
            <td class="vc-pad" align="center" style="background-color:#ffffff; padding:44px 48px 38px;">

                <p style="margin:0 0 14px; font-family:{{ $sans }}; font-size:11px; font-weight:bold; letter-spacing:4px; text-transform:uppercase; color:{{ $goldDark }};">
                    &mdash;&nbsp; Seguridad de tu cuenta &nbsp;&mdash;
                </p>

                <h1 style="margin:0; font-family:{{ $serif }}; font-size:30px; font-weight:normal; line-height:1.25; color:{{ $navy }};">
                    Restablece tu<br><span style="font-style:italic; color:{{ $goldDark }};">contraseña</span>
                </h1>

                <p style="margin:22px 0 0; font-family:{{ $sans }}; font-size:15px; line-height:1.7; color:{{ $text }};">
                    Recibimos una solicitud para cambiar la contraseña de la cuenta:
                </p>

                <!-- Cuenta -->
                <table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin:14px auto 0;">
                    <tr>
                        <td style="border:1px solid {{ $gold }}; background-color:{{ $ivory }}; padding:9px 18px; font-family:{{ $sans }}; font-size:14px; font-weight:bold; color:{{ $navy }};">
                            {{ $email ?? '' }}
                        </td>
                    </tr>
                </table>

                <p style="margin:22px 0 0; font-family:{{ $sans }}; font-size:15px; line-height:1.7; color:{{ $text }};">
                    Pulsa el botón para crear una contraseña nueva.
                </p>

                <!-- Botón principal -->
                <table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin:26px auto 0;">
                    <tr>
                        <td class="vc-btn" align="center" style="background-color:{{ $navy }};">
                            <a href="{{ $urlRestablecer }}" style="display:inline-block; padding:16px 38px; font-family:{{ $sans }}; font-size:12px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#ffffff; text-decoration:none; border-bottom:2px solid {{ $gold }};">Crear nueva contraseña</a>
                        </td>
                    </tr>
                </table>

                <p style="margin:22px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.7; color:{{ $muted }};">
                    ¿El botón no funciona? Copia este enlace en tu navegador:<br>
                    <a href="{{ $urlRestablecer }}" style="color:{{ $goldDark }}; word-break:break-all;">{{ $urlRestablecer }}</a>
                </p>

                <!-- Aviso de seguridad -->
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:34px 0 0;">
                    <tr>
                        <td align="left" style="background-color:{{ $ivory }}; border-left:3px solid {{ $gold }}; padding:18px 22px;">
                            <p style="margin:0; font-family:{{ $serif }}; font-size:16px; color:{{ $navy }};">¿No lo solicitaste tú?</p>
                            <p style="margin:6px 0 0; font-family:{{ $sans }}; font-size:13px; line-height:1.7; color:{{ $text }};">
                                Ignora este correo: tu contraseña actual sigue igual. Nunca te pediremos tu contraseña por correo ni por WhatsApp.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <!-- ══ PIE (estilo footer web) ══ -->
        <tr>
            <td align="center" style="background-color:{{ $navyDeep }}; padding:26px 24px 28px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="border-bottom:1px solid #4a4a52; font-size:0; line-height:0;">&nbsp;</td>
                        <td width="40" align="center" style="width:40px; font-family:{{ $serif }}; font-size:18px; color:{{ $gold }}; line-height:18px;">V</td>
                        <td style="border-bottom:1px solid #4a4a52; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>
                </table>
                <p style="margin:18px 0 0; font-family:{{ $sans }}; font-size:11px; letter-spacing:1px;">
                    <a href="{{ url('/tienda') }}" style="color:{{ $gold }}; text-decoration:none;">Tienda</a>
                    <span style="color:#4a4a52;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                    <a href="{{ url('/nosotros') }}" style="color:{{ $gold }}; text-decoration:none;">Nosotros</a>
                    <span style="color:#4a4a52;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                    <a href="{{ url('/contacto') }}" style="color:{{ $gold }}; text-decoration:none;">Contacto</a>
                    <span style="color:#4a4a52;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                    <a href="{{ \App\Support\ContactoValento::whatsappUrl() }}" style="color:{{ $gold }}; text-decoration:none;">WhatsApp</a>
                </p>
                <p style="margin:14px 0 0; font-family:{{ $sans }}; font-size:11px; letter-spacing:1px; color:{{ $muted }};">
                    Valento Stile S.A.C. &nbsp;·&nbsp; La Victoria, Lima
                </p>
                <p style="margin:6px 0 0; font-family:{{ $sans }}; font-size:11px; color:{{ $muted }};">
                    <a href="{{ url('/politica-privacidad') }}" style="color:{{ $muted }}; text-decoration:underline;">Privacidad</a>
                    &nbsp;·&nbsp;
                    <a href="{{ url('/terminos-condiciones') }}" style="color:{{ $muted }}; text-decoration:underline;">Términos</a>
                </p>
                <p style="margin:10px 0 0; font-family:{{ $serif }}; font-size:13px; font-style:italic; color:{{ $muted }};">
                    &copy; {{ date('Y') }} <span style="color:{{ $gold }}; font-style:normal;">Valento</span> &nbsp;·&nbsp; Semplicemente Valento
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>

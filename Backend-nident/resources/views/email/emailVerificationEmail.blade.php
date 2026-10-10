@php
    // Colores de marca (mismos del footer de la web y del correo de contacto)
    $navy      = '#121a36';
    $navyDeep  = '#0b1126';
    $gold      = '#e7b669';
    $goldDark  = '#b8893a';
    $ivory     = '#f7f3ec';
    $muted     = '#8d93aa';
    $text      = '#3a3f52';
    $serif     = "Georgia, 'Times New Roman', serif";
    $sans      = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $urlVerificar = route('user.verify', $token);
    $saludo = !empty($nombre) ? 'Hola ' . $nombre : 'Hola';
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Verifica tu correo — Valento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <style>
        @media only screen and (max-width: 600px) {
            .vc-pad   { padding-left: 24px !important; padding-right: 24px !important; }
            .vc-cell  { display: block !important; width: 100% !important; border-left: 0 !important; border-top: 1px dotted #e3d6bd !important; }
            .vc-cell-first { border-top: 0 !important; }
            .vc-btn a { display: block !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:{{ $ivory }}; -webkit-text-size-adjust:none; width:100% !important;">

<!-- Texto de vista previa (bandeja de entrada) -->
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
    Confirma tu correo para activar tu cuenta Valento.
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
                    &mdash;&nbsp; Bienvenido &nbsp;&mdash;
                </p>

                <h1 style="margin:0; font-family:{{ $serif }}; font-size:30px; font-weight:normal; line-height:1.25; color:{{ $navy }};">
                    Ya eres parte del<br><span style="font-style:italic; color:{{ $goldDark }};">estilo Valento</span>
                </h1>

                <p style="margin:22px 0 0; font-family:{{ $sans }}; font-size:15px; line-height:1.7; color:{{ $text }};">
                    {{ $saludo }}, gracias por crear tu cuenta.<br>
                    Solo falta un paso: confirma tu correo electrónico para activarla.
                </p>

                <!-- Botón principal -->
                <table cellpadding="0" cellspacing="0" role="presentation" align="center" style="margin:30px auto 0;">
                    <tr>
                        <td class="vc-btn" align="center" style="background-color:{{ $navy }};">
                            <a href="{{ $urlVerificar }}" style="display:inline-block; padding:16px 38px; font-family:{{ $sans }}; font-size:12px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#ffffff; text-decoration:none; border-bottom:2px solid {{ $gold }};">Verificar mi correo</a>
                        </td>
                    </tr>
                </table>

                <p style="margin:22px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.7; color:{{ $muted }};">
                    ¿El botón no funciona? Copia este enlace en tu navegador:<br>
                    <a href="{{ $urlVerificar }}" style="color:{{ $goldDark }}; word-break:break-all;">{{ $urlVerificar }}</a>
                </p>

                <!-- Lo que puedes hacer -->
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:36px 0 0; background-color:{{ $ivory }};">
                    <tr>
                        <td class="vc-cell vc-cell-first" width="33%" align="center" valign="top" style="padding:20px 12px;">
                            <p style="margin:0; font-family:{{ $sans }}; font-size:10px; font-weight:bold; letter-spacing:2px; color:{{ $goldDark }};">01</p>
                            <p style="margin:6px 0 0; font-family:{{ $serif }}; font-size:16px; color:{{ $navy }};">Compra al por mayor</p>
                            <p style="margin:4px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.5; color:{{ $muted }};">Stock permanente</p>
                        </td>
                        <td class="vc-cell" width="34%" align="center" valign="top" style="padding:20px 12px; border-left:1px dotted #e3d6bd;">
                            <p style="margin:0; font-family:{{ $sans }}; font-size:10px; font-weight:bold; letter-spacing:2px; color:{{ $goldDark }};">02</p>
                            <p style="margin:6px 0 0; font-family:{{ $serif }}; font-size:16px; color:{{ $navy }};">Personaliza</p>
                            <p style="margin:4px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.5; color:{{ $muted }};">Con el logo de tu empresa</p>
                        </td>
                        <td class="vc-cell" width="33%" align="center" valign="top" style="padding:20px 12px; border-left:1px dotted #e3d6bd;">
                            <p style="margin:0; font-family:{{ $sans }}; font-size:10px; font-weight:bold; letter-spacing:2px; color:{{ $goldDark }};">03</p>
                            <p style="margin:6px 0 0; font-family:{{ $serif }}; font-size:16px; color:{{ $navy }};">Sigue tus pedidos</p>
                            <p style="margin:4px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.5; color:{{ $muted }};">Desde tu cuenta</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:30px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.7; color:{{ $muted }};">
                    Si tú no creaste esta cuenta, puedes ignorar este correo.
                </p>

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

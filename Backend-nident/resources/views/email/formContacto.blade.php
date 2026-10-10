@php
    // Colores de marca (mismos del footer de la web)
    $navy      = '#121a36';
    $navyDeep  = '#0b1126';
    $gold      = '#e7b669';
    $goldDark  = '#b8893a';
    $ivory     = '#f7f3ec';
    $muted     = '#8d93aa';
    $text      = '#3a3f52';
    $serif     = "Georgia, 'Times New Roman', serif";
    $sans      = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $waNumero = preg_replace('/\D/', '', $txt_telefono ?? '');
    if (strlen($waNumero) === 9) {
        $waNumero = '51' . $waNumero; // celular peruano sin código de país
    }
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Nuevo mensaje de contacto — Valento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <style>
        @media only screen and (max-width: 600px) {
            .vc-pad    { padding-left: 24px !important; padding-right: 24px !important; }
            .vc-label  { display: block !important; width: 100% !important; padding-bottom: 2px !important; }
            .vc-value  { display: block !important; width: 100% !important; text-align: left !important; padding-top: 0 !important; }
            .vc-btn    { display: block !important; width: 100% !important; margin: 0 0 10px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:{{ $ivory }}; -webkit-text-size-adjust:none; width:100% !important;">

<!-- Texto de vista previa (bandeja de entrada) -->
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
    {{ $txt_nombre }} escribió por la web: {{ $txt_asunto }}
</div>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:{{ $ivory }};">
<tr>
<td align="center" style="padding:32px 12px;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; margin:0 auto;">

        <!-- ══ ENCABEZADO ══ -->
        <tr>
            <td align="center" style="background-color:{{ $navy }}; border-top:3px solid {{ $gold }}; padding:34px 24px 28px;">
                <!-- Monograma -->
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
            <td class="vc-pad" style="background-color:#ffffff; padding:40px 44px 36px;">

                <!-- Etiqueta -->
                <p style="margin:0 0 12px; font-family:{{ $sans }}; font-size:11px; font-weight:bold; letter-spacing:4px; text-transform:uppercase; color:{{ $goldDark }};">
                    &mdash;&nbsp; Nuevo mensaje de contacto
                </p>

                <h1 style="margin:0; font-family:{{ $serif }}; font-size:28px; font-weight:normal; line-height:1.25; color:{{ $navy }};">
                    <span style="font-style:italic; color:{{ $goldDark }};">{{ $txt_nombre }}</span> te escribió desde la web
                </h1>

                <p style="margin:18px 0 0; font-family:{{ $sans }}; font-size:14px; line-height:1.7; color:{{ $text }};">
                    Llegó una consulta por el formulario de contacto de la tienda. Estos son los datos para atenderla:
                </p>

                <!-- Asunto -->
                <table cellpadding="0" cellspacing="0" role="presentation" style="margin:22px 0 0;">
                    <tr>
                        <td style="border:1px solid {{ $gold }}; background-color:{{ $ivory }}; padding:8px 16px; font-family:{{ $sans }}; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:{{ $navy }};">
                            Asunto: <span style="color:{{ $goldDark }};">{{ $txt_asunto }}</span>
                        </td>
                    </tr>
                </table>

                <!-- Datos del cliente -->
                <p style="margin:32px 0 6px; font-family:{{ $serif }}; font-size:19px; color:{{ $navy }};">
                    <span style="font-family:{{ $sans }}; font-size:10px; font-weight:bold; letter-spacing:2px; color:{{ $goldDark }};">01</span>&nbsp; Datos del cliente
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="font-family:{{ $sans }}; font-size:14px;">
                    <tr>
                        <td class="vc-label" width="38%" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $muted }}; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Nombre</td>
                        <td class="vc-value" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $navy }}; text-align:right; font-weight:bold;">{{ $txt_nombre }}</td>
                    </tr>
                    <tr>
                        <td class="vc-label" width="38%" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $muted }}; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Correo</td>
                        <td class="vc-value" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; text-align:right;"><a href="mailto:{{ $txt_email }}" style="color:{{ $goldDark }}; text-decoration:none;">{{ $txt_email }}</a></td>
                    </tr>
                    @if(!empty($txt_telefono))
                    <tr>
                        <td class="vc-label" width="38%" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $muted }}; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Teléfono</td>
                        <td class="vc-value" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $navy }}; text-align:right;"><a href="tel:{{ $txt_telefono }}" style="color:{{ $navy }}; text-decoration:none;">{{ $txt_telefono }}</a></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="vc-label" width="38%" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $muted }}; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Recibido</td>
                        <td class="vc-value" style="padding:12px 0; border-bottom:1px dotted #e3d6bd; color:{{ $navy }}; text-align:right;">{{ now()->format('d/m/Y · H:i') }}</td>
                    </tr>
                </table>

                <!-- Mensaje -->
                <p style="margin:32px 0 10px; font-family:{{ $serif }}; font-size:19px; color:{{ $navy }};">
                    <span style="font-family:{{ $sans }}; font-size:10px; font-weight:bold; letter-spacing:2px; color:{{ $goldDark }};">02</span>&nbsp; Mensaje
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="background-color:{{ $ivory }}; border-left:3px solid {{ $gold }}; padding:20px 22px; font-family:{{ $serif }}; font-size:16px; font-style:italic; line-height:1.7; color:{{ $navy }};">
                            @if(!empty($txt_mensaje))
                                &ldquo;{!! nl2br(e($txt_mensaje)) !!}&rdquo;
                            @else
                                <span style="color:{{ $muted }};">El cliente no escribió un mensaje.</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Acciones -->
                <table cellpadding="0" cellspacing="0" role="presentation" style="margin:30px 0 0;">
                    <tr>
                        <td class="vc-btn" style="padding:0 10px 0 0;">
                            <a href="mailto:{{ $txt_email }}?subject={{ rawurlencode('Re: ' . $txt_asunto) }}" style="display:inline-block; background-color:{{ $navy }}; color:#ffffff; font-family:{{ $sans }}; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; text-decoration:none; padding:14px 26px;">Responder por correo</a>
                        </td>
                        @if(strlen($waNumero) >= 9)
                        <td class="vc-btn">
                            <a href="https://wa.me/{{ $waNumero }}" style="display:inline-block; border:1px solid {{ $gold }}; color:{{ $navy }}; font-family:{{ $sans }}; font-size:11px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; text-decoration:none; padding:13px 26px;">Escribir por WhatsApp</a>
                        </td>
                        @endif
                    </tr>
                </table>

                <p style="margin:30px 0 0; font-family:{{ $sans }}; font-size:12px; line-height:1.7; color:{{ $muted }};">
                    El mensaje quedó guardado en el panel de administración, en <strong style="color:{{ $text }};">Mensajes de contacto</strong>.
                </p>

            </td>
        </tr>

        <!-- ══ PIE (estilo footer web) ══ -->
        <tr>
            <td align="center" style="background-color:{{ $navyDeep }}; padding:26px 24px 28px;">
                <!-- Separador con V -->
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td style="border-bottom:1px solid #4a4a52; font-size:0; line-height:0;">&nbsp;</td>
                        <td width="40" align="center" style="width:40px; font-family:{{ $serif }}; font-size:18px; color:{{ $gold }}; line-height:18px;">V</td>
                        <td style="border-bottom:1px solid #4a4a52; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>
                </table>
                <p style="margin:18px 0 0; font-family:{{ $sans }}; font-size:11px; letter-spacing:1px; color:{{ $muted }};">
                    Valento Stile S.A.C. &nbsp;·&nbsp; La Victoria, Lima
                </p>
                <p style="margin:6px 0 0; font-family:{{ $serif }}; font-size:13px; font-style:italic; color:{{ $muted }};">
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

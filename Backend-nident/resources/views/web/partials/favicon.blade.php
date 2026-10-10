{{-- Favicons Valento — sitio web público (nueva marca) --}}
@php
    $favMeta = is_file(public_path('favicon-meta.json'))
        ? (json_decode((string) file_get_contents(public_path('favicon-meta.json')), true) ?: [])
        : [];
    $favV = (int) ($favMeta['version'] ?? time());
    $faviconSvg = Helpers::siteFaviconUrl('favicon.svg') . '?v=' . $favV;
    $faviconIco = Helpers::siteFaviconUrl('favicon.ico') . '?v=' . $favV;
    $favicon32 = Helpers::siteFaviconUrl('imagenes/valento-favicon-32.png') . '?v=' . $favV;
    $favicon96 = Helpers::siteFaviconUrl('icon/favicon-96x96.png') . '?v=' . $favV;
    $appleTouch = Helpers::siteFaviconUrl('apple-touch-icon.png') . '?v=' . $favV;
@endphp
<link rel="icon" href="{{ $faviconSvg }}" type="image/svg+xml">
<link rel="icon" href="{{ $faviconIco }}" sizes="any">
<link rel="shortcut icon" href="{{ $faviconIco }}">
<link rel="icon" href="{{ $favicon32 }}" sizes="32x32" type="image/png">
<link rel="icon" href="{{ $favicon96 }}" sizes="96x96" type="image/png">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $appleTouch }}">
<link rel="manifest" href="{{ Helpers::cmsAbsoluteUrl('icon/site.webmanifest') }}?v={{ $favV }}">
<meta name="apple-mobile-web-app-title" content="Valento">
<meta name="application-name" content="Valento">
<meta name="theme-color" content="#ffffff">
<meta name="msapplication-TileImage" content="{{ $appleTouch }}">
<meta name="msapplication-TileColor" content="#ffffff">

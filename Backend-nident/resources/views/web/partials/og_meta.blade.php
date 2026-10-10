{{-- Open Graph / Twitter — vista previa al compartir en WhatsApp, Facebook, etc. --}}
@php
    $ogSiteName = $ogSiteName ?? 'Valento';
    $ogImageDefault = $ogImageDefault ?? \App\Helpers\Helper::LIAM_OG_IMAGE;
    $ogTitle = html_entity_decode(trim($__env->yieldContent('title')) ?: 'Valento | Ropa laboral y textil', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ogDescription = html_entity_decode(
        trim($__env->yieldContent('meta_description'))
            ?: 'Valento — Ropa laboral y textil de calidad para tu empresa.',
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );
    $ogImageOverride = trim($__env->yieldContent('og_image'));
    $ogImage = $ogImageOverride !== ''
        ? Helpers::cmsAbsoluteUrl($ogImageOverride)
        : Helpers::siteOgImageUrl(null, $ogImageDefault);
    $ogUrl = Helpers::canonicalShareUrl();
    $ogImgW = \App\Helpers\Helper::LIAM_OG_IMAGE_WIDTH;
    $ogImgH = \App\Helpers\Helper::LIAM_OG_IMAGE_HEIGHT;
@endphp
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $ogSiteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:secure_url" content="{{ $ogImage }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="{{ $ogImgW }}">
<meta property="og:image:height" content="{{ $ogImgH }}">
<meta property="og:image:alt" content="{{ $ogSiteName }}">
<meta property="og:locale" content="es_PE">
@if(config('services.facebook.app_id'))
<meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<link rel="image_src" href="{{ $ogImage }}">

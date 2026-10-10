<!DOCTYPE html>
<html lang="es" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:secure_url" content="{{ $image }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="{{ $imageWidth ?? 1200 }}">
    <meta property="og:image:height" content="{{ $imageHeight ?? 1200 }}">
    <meta property="og:image:alt" content="{{ $siteName }}">
    <meta property="og:locale" content="es_PE">
    @if(config('services.facebook.app_id'))
    <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
    <link rel="canonical" href="{{ $url }}">
</head>
<body>{{ $siteName }}</body>
</html>

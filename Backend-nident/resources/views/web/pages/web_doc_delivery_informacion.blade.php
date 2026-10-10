@extends('web.base')

<!-- Contenido en el Head de la pagina -->
@section('head_page')
<!-- extras here!!-->
@section('title', $metaData->titulo_pagina ?? 'Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
@endsection
<!-- COOKIES AND POLICE : HEADER-->
<!-- Contenido en el Body -->
@section('content')
<!-- Start breadcrumb -->
@include('web.partials.breadcrumb')
<div class="container pt-2 mb-5 mt-3">
    <div class="row">
        <div class="col-lg-12">
            @if(count($terminosData) != 0)
            <h1 class="section-title text-center">{{ $terminosData[0]->titulo ?? '' }}</h1>
            @endif
            @if(count($terminosData) != 0 && !empty($terminosData[0]->descripcion))
            @php
            $docBaseStyles = '<style>
                body {
                    margin: 0 auto;
                    padding: 1rem 3rem;
                    max-width: 900px;
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.5;
                    color: #333;
                    box-sizing: border-box;
                }
                body * {
                    box-sizing: border-box;
                }
                p {
                    margin: 0 0 0.3em 0;
                }
                p:empty,
                p:has(> br:only-child) {
                    margin: 0;
                }
            </style>';
    $docBodyHtml = str_replace("\n", "", $terminosData[0]->descripcion);
    $docHtml = '
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">' . $docBaseStyles . '
    </head>

    <body>' . $docBodyHtml . '</body>

    </html>';
    $docEscaped = str_replace(['&', '"'], ['&amp;', '&quot;'], $docHtml);
            @endphp
    <div class="ficha-tecnica-iframe-wrapper" style="overflow:visible;">
        <iframe
            class="ficha-tecnica-iframe-autoheight"
            title="{{ $terminosData[0]->titulo ?? 'Información de delivery' }}"
            srcdoc="{!! $docEscaped !!}"
            style="width:100%; border:none; display:block; overflow:visible; min-height:100px;"
            sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
    </div>
            @else
            <div class="description-text"></div>
            @endif
        </div>
    </div>
</div>

@endsection
<!-- COOKIES AND POLICE: FOOTER -->
@section('footer_page')
@if(session('success'))
<script>
    toastr.success("{{ session('success') }}");
</script>
@endif
@if(count($terminosData) != 0 && !empty($terminosData[0]->descripcion))
<script>
(function() {
    function ajustarAlturaDocIframe() {
        document.querySelectorAll('.ficha-tecnica-iframe-autoheight').forEach(function(iframe) {
            try {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body) {
                    iframe.style.height = 'auto';
                    var body = doc.body, html = doc.documentElement;
                    var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
                    iframe.style.height = height + 'px';
                    iframe.style.overflow = 'visible';
                    iframe.style.minHeight = '100px';
                }
            } catch (e) {}
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var iframes = document.querySelectorAll('.ficha-tecnica-iframe-autoheight');
        iframes.forEach(function(iframe) {
            try {
                if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') ajustarAlturaDocIframe();
            } catch (e) {}
            iframe.addEventListener('load', function() {
                ajustarAlturaDocIframe();
                setTimeout(ajustarAlturaDocIframe, 50);
                setTimeout(ajustarAlturaDocIframe, 150);
                setTimeout(ajustarAlturaDocIframe, 500);
                try {
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc && doc.body && typeof ResizeObserver !== 'undefined') {
                        new ResizeObserver(ajustarAlturaDocIframe).observe(doc.body);
                    }
                    if (doc) {
                        doc.querySelectorAll('img').forEach(function(img) {
                            if (!img.complete) img.addEventListener('load', ajustarAlturaDocIframe);
                        });
                    }
                } catch (e) {}
            });
        });
        window.addEventListener('resize', ajustarAlturaDocIframe);
    });
})();
</script>
@endif
@endsection

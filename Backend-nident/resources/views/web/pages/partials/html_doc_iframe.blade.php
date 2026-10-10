{{-- Contenido HTML aislado en iframe (sin estilos externos de la landing) --}}
@php
    $iframeTitle = $iframeTitle ?? 'Contenido';
    $htmlBody = $htmlBody ?? '';
    $docBaseStyles = '<style>
        body {
            margin: 0 auto;
            padding: 1rem 2rem;
            max-width: 900px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            box-sizing: border-box;
        }
        body * { box-sizing: border-box; }
        img { max-width: 100%; height: auto; }
        table { max-width: 100%; border-collapse: collapse; }
        p { margin: 0 0 0.75em 0; }
        p:empty, p:has(> br:only-child) { margin: 0; }
    </style>';
    $docBodyHtml = str_replace("\n", "", $htmlBody);
    $docHtml = '<!DOCTYPE html><html><head><meta charset="utf-8">' . $docBaseStyles . '</head><body>' . $docBodyHtml . '</body></html>';
    $docEscaped = str_replace(['&', '"'], ['&amp;', '&quot;'], $docHtml);
@endphp
<div class="ficha-tecnica-iframe-wrapper pub-article-iframe-wrapper" style="overflow:visible;">
    <iframe
        class="ficha-tecnica-iframe-autoheight pub-article-iframe"
        title="{{ $iframeTitle }}"
        srcdoc="{!! $docEscaped !!}"
        style="width:100%; border:none; display:block; overflow:visible; min-height:120px;"
        sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
</div>

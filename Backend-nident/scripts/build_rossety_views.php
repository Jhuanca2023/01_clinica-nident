<?php

$srcDir = 'C:/xampp/htdocs_pro/56-Rossety/template/anvogue';
$outDir = 'C:/xampp/htdocs_pro/56-Rossety/rossety-backend/resources/views/web/rossety';
if (! is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$map = [
    'index.html' => 'inicio.blade.php',
    'tienda.html' => 'tienda.blade.php',
    'producto.html' => 'producto.blade.php',
    'carrito.html' => 'carrito.blade.php',
    'checkout.html' => 'checkout.blade.php',
    'gracias.html' => 'gracias.blade.php',
    'nosotros.html' => 'nosotros.blade.php',
    'contacto.html' => 'contacto.blade.php',
];

foreach ($map as $file => $blade) {
    $html = file_get_contents($srcDir.'/'.$file);
    $html = preg_replace_callback(
        '/\b(href|src|action)="assets\/([^"]+)"/',
        function ($m) {
            return $m[1].'="{{ asset(\'rossety/assets/'.$m[2].'\') }}"';
        },
        $html
    );
    $repls = [
        'href="tienda.html?cat=' => 'href="/tienda?cat=',
        'href="tienda.html?tag=' => 'href="/tienda?tag=',
        'href="producto.html?id=' => 'href="/producto?id=',
        'href="index.html"' => 'href="{{ route(\'home\') }}"',
        'href="nosotros.html"' => 'href="{{ route(\'nosotros\') }}"',
        'href="contacto.html#' => 'href="{{ route(\'contacto.pagina\') }}#',
        'href="contacto.html"' => 'href="{{ route(\'contacto.pagina\') }}"',
        'href="carrito.html"' => 'href="/carrito"',
        'href="checkout.html"' => 'href="/checkout"',
        'href="gracias.html"' => 'href="/gracias"',
        'href="tienda.html"' => 'href="{{ route(\'tienda\') }}"',
        'action="tienda.html"' => 'action="{{ route(\'tienda\') }}"',
    ];
    $html = str_replace(array_keys($repls), array_values($repls), $html);
    $html = str_replace(
        "{{ asset('rossety/assets/images/inicio/logo-principal.svg') }}",
        '{{ $rsLogo }}',
        $html
    );
    $html = str_replace('Relave, Ayacucho, Perú', '{{ $rsDireccion }}', $html);
    $html = str_replace('Relave, Ayacucho', '{{ $rsCiudad }}', $html);
    $html = str_replace('989 914 154', '{{ $rsTelefono }}', $html);
    $html = str_replace(
        'class="rs-claims" href="{{ route(\'contacto.pagina\') }}"',
        'class="rs-claims" href="{{ route(\'liam.reclamaciones\') }}"',
        $html
    );
    $html = str_replace(
        '<meta charset="UTF-8" />',
        '<meta charset="UTF-8" />'."\n".'    <meta name="csrf-token" content="{{ csrf_token() }}" />',
        $html
    );
    $html = str_replace(
        '<script src="{{ asset(\'rossety/assets/js/rossety/data/catalog.js\') }}"></script>',
        '@include(\'web.rossety.partials.catalog\')',
        $html
    );
    $html = str_replace(
        '<script src="{{ asset(\'rossety/assets/js/rossety/core.js\') }}"></script>',
        '<script src="{{ asset(\'rossety/assets/js/rossety/core.js\') }}"></script>'."\n".
        '    <script src="{{ asset(\'rossety/assets/js/rossety/cms.js\') }}"></script>',
        $html
    );
    $html = str_replace(
        '</body>',
        '    <script src="{{ asset(\'rossety/assets/js/rossety/laravel.js\') }}"></script>'."\n".'</body>',
        $html
    );
    file_put_contents($outDir.'/'.$blade, $html);
    echo "wrote $blade\n";
}

$partialDir = $outDir.'/partials';
if (! is_dir($partialDir)) {
    mkdir($partialDir, 0775, true);
}
$partial = <<<'BLADE'
<script>
window.RS_BASE = @json(\App\Support\RossetyCatalog::assetBase());
window.ROSSETY = @json($rsCatalog);
</script>
BLADE;
file_put_contents($partialDir.'/catalog.blade.php', $partial);
echo "wrote catalog partial\n";

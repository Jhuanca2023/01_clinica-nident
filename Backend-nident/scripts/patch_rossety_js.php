<?php
$dir = __DIR__ . '/../public/rossety/assets/js/rossety';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$repls = [
    'producto.html?id=' => '/producto?id=',
    'tienda.html?cat=' => '/tienda?cat=',
    'tienda.html?tag=' => '/tienda?tag=',
    'tienda.html' => '/tienda',
    'carrito.html' => '/carrito',
    'checkout.html' => '/checkout',
    'gracias.html' => '/gracias',
    'nosotros.html' => '/nosotros',
    'contacto.html' => '/contacto',
    'index.html' => '/',
];
foreach ($it as $file) {
    if ($file->getExtension() !== 'js') {
        continue;
    }
    $path = $file->getPathname();
    $c = file_get_contents($path);
    $n = str_replace(array_keys($repls), array_values($repls), $c);
    if ($n !== $c) {
        file_put_contents($path, $n);
        echo basename($path), "\n";
    }
}
$core = $dir.'/core.js';
$t = file_get_contents($core);
$t = str_replace(
    "var PLACEHOLDER = 'assets/images/catalogo/placeholder.svg';",
    "var PLACEHOLDER = (window.RS_BASE || '') + '/images/catalogo/placeholder.svg';",
    $t
);
file_put_contents($core, $t);

$home = $dir.'/home.js';
$h = file_get_contents($home);
$h = str_replace(
    '<img src="assets/images/inicio/cat-',
    '<img src="\' + window.RS_BASE + \'/images/inicio/cat-',
    $h
);
file_put_contents($home, $h);
echo "home/core patched\n";

$co = $dir.'/checkout.js';
$k = file_get_contents($co);
$old = "        sent = true;\n        window.open(order.whatsapp, '_blank', 'noopener');\n        RS.cart.clear();\n        setTimeout(function () { window.location.href = '/gracias'; }, 900);";
$new = "        if (window.RS_PLACE_ORDER) { window.RS_PLACE_ORDER(order, btn); return; }\n".$old;
if (! str_contains($k, 'RS_PLACE_ORDER')) {
    if (! str_contains($k, $old)) {
        echo "checkout pattern missing\n";
    } else {
        file_put_contents($co, str_replace($old, $new, $k));
        echo "checkout patched\n";
    }
} else {
    echo "checkout already\n";
}

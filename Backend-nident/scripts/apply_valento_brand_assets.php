<?php
/**
 * Aplica el logo original y el pack de favicon de Valento.
 * Logo: public/imagenes/logo-principal.png
 * Favicons: public/favicon/ (RealFaviconGenerator)
 *
 * Uso: php scripts/apply_valento_brand_assets.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$public = $root . '/public';
$frontendPublic = dirname($root) . '/Valento_Frontend/public';
$logo = $public . '/imagenes/logo-principal.png';
$pack = $public . '/favicon';

if (! is_file($logo)) {
    fwrite(STDERR, "No existe el logo original: {$logo}\n");
    exit(1);
}
if (! extension_loaded('gd')) {
    fwrite(STDERR, "Se requiere la extensión GD.\n");
    exit(1);
}

function ensureDir(string $dir): void
{
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function copyFile(string $from, string $to): void
{
    if (! is_file($from)) {
        fwrite(STDERR, "Falta origen: {$from}\n");
        exit(1);
    }
    ensureDir(dirname($to));
    if (! copy($from, $to)) {
        fwrite(STDERR, "No se pudo copiar a {$to}\n");
        exit(1);
    }
    echo "COPY {$to}\n";
}

function resizePng(string $srcPath, string $destPath, int $size): void
{
    $src = imagecreatefrompng($srcPath);
    if (! $src) {
        fwrite(STDERR, "No se pudo leer {$srcPath}\n");
        exit(1);
    }
    $sw = imagesx($src);
    $sh = imagesy($src);
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
    imagealphablending($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, $sw, $sh);
    ensureDir(dirname($destPath));
    imagepng($dst, $destPath, 6);
    imagedestroy($src);
    imagedestroy($dst);
    echo "PNG  {$size} {$destPath}\n";
}

/** Recorta el logo para llenar un lienzo cuadrado. */
function canvasCover(string $logoPath, int $width, int $height): \GdImage
{
    $src = imagecreatefrompng($logoPath);
    if (! $src) {
        fwrite(STDERR, "No se pudo leer el logo.\n");
        exit(1);
    }
    $sw = imagesx($src);
    $sh = imagesy($src);
    $scale = max($width / $sw, $height / $sh);
    $nw = (int) max(1, round($sw * $scale));
    $nh = (int) max(1, round($sh * $scale));
    $ox = (int) (($width - $nw) / 2);
    $oy = (int) (($height - $nh) / 2);

    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, $ox, $oy, 0, 0, $nw, $nh, $sw, $sh);
    imagedestroy($src);

    return $dst;
}

/** Encaja el logo completo y rellena los lados con el borde de la propia imagen. */
function canvasContain(string $logoPath, int $width, int $height): \GdImage
{
    $src = imagecreatefrompng($logoPath);
    if (! $src) {
        fwrite(STDERR, "No se pudo leer el logo.\n");
        exit(1);
    }
    $sw = imagesx($src);
    $sh = imagesy($src);
    $scale = min($width / $sw, $height / $sh);
    $nw = (int) max(1, round($sw * $scale));
    $nh = (int) max(1, round($sh * $scale));
    $ox = (int) (($width - $nw) / 2);
    $oy = (int) (($height - $nh) / 2);

    $fitted = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($fitted, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
    imagedestroy($src);

    $dst = imagecreatetruecolor($width, $height);
    for ($x = 0; $x < $ox; $x++) {
        imagecopy($dst, $fitted, $x, $oy, 0, 0, 1, $nh);
    }
    for ($x = $ox + $nw; $x < $width; $x++) {
        imagecopy($dst, $fitted, $x, $oy, $nw - 1, 0, 1, $nh);
    }
    for ($y = 0; $y < $oy; $y++) {
        imagecopy($dst, $fitted, $ox, $y, 0, 0, $nw, 1);
    }
    for ($y = $oy + $nh; $y < $height; $y++) {
        imagecopy($dst, $fitted, $ox, $y, 0, $nh - 1, $nw, 1);
    }
    imagecopy($dst, $fitted, $ox, $oy, 0, 0, $nw, $nh);
    imagedestroy($fitted);

    return $dst;
}

// --- Logo en los sitios que lo muestran ---
$logoTargets = [
    $public . '/temp02/img/inicio/logo-principal.png',
    $public . '/imagenes/imagen_favicon.png',
    $public . '/imagenes/valento-favicon-source.png',
];
if (is_dir($frontendPublic)) {
    $logoTargets[] = $frontendPublic . '/assets/logos/logo-principal.png';
    $logoTargets[] = $frontendPublic . '/icon/logo-principal.png';
}
foreach ($logoTargets as $target) {
    copyFile($logo, $target);
}

// JPG para referencias viejas (schema / correos) que aún piden .jpg
$jpg = canvasCover($logo, 1200, 1200);
imagejpeg($jpg, $public . '/imagenes/logo-principal.jpg', 90);
imagedestroy($jpg);
echo "JPG  {$public}/imagenes/logo-principal.jpg\n";

// --- Pack de favicon ---
$ico = $pack . '/favicon.ico';
$svg = $pack . '/favicon.svg';
$png96 = $pack . '/favicon-96x96.png';
$apple = $pack . '/apple-touch-icon.png';
$png192 = $pack . '/web-app-manifest-192x192.png';
$png512 = $pack . '/web-app-manifest-512x512.png';

$icoTargets = [
    $public . '/favicon.ico',
    $public . '/icon/favicon.ico',
];
$svgTargets = [
    $public . '/favicon.svg',
    $public . '/temp02/favicon.svg',
];
$png96Targets = [
    $public . '/favicon-96x96.png',
    $public . '/icon/favicon-96x96.png',
];
$appleTargets = [
    $public . '/apple-touch-icon.png',
];
$png192Targets = [
    $public . '/icon/favicon-192x192.png',
    $public . '/icon/web-app-manifest-192x192.png',
];
$png512Targets = [
    $public . '/icon/valento-icon-512.png',
    $public . '/icon/web-app-manifest-512x512.png',
];

if (is_dir($frontendPublic)) {
    $icoTargets[] = $frontendPublic . '/favicon.ico';
    $icoTargets[] = $frontendPublic . '/icon/favicon.ico';
    $svgTargets[] = $frontendPublic . '/favicon.svg';
    $png96Targets[] = $frontendPublic . '/icon/favicon-96x96.png';
    $appleTargets[] = $frontendPublic . '/icon/apple-touch-icon.png';
    $appleTargets[] = $frontendPublic . '/apple-touch-icon.png';
    $png192Targets[] = $frontendPublic . '/icon/web-app-manifest-192x192.png';
    $png512Targets[] = $frontendPublic . '/icon/valento-icon-512.png';
    $png512Targets[] = $frontendPublic . '/icon/web-app-manifest-512x512.png';
}

foreach ($icoTargets as $target) {
    copyFile($ico, $target);
}
foreach ($svgTargets as $target) {
    copyFile($svg, $target);
}
foreach ($png96Targets as $target) {
    copyFile($png96, $target);
}
foreach ($appleTargets as $target) {
    copyFile($apple, $target);
}
foreach ($png192Targets as $target) {
    copyFile($png192, $target);
}
foreach ($png512Targets as $target) {
    copyFile($png512, $target);
}

resizePng($png96, $public . '/imagenes/valento-favicon-16.png', 16);
resizePng($png96, $public . '/imagenes/valento-favicon-32.png', 32);
resizePng($png96, $public . '/imagenes/valento-favicon-48.png', 48);
resizePng($png96, $public . '/temp02/favicon-48.png', 48);
if (is_dir($frontendPublic)) {
    resizePng($png96, $frontendPublic . '/icon/valento-favicon-32.png', 32);
}

// Open Graph: misma marca (V sobre azul marino), no el wordmark negro.
$square = canvasCover($logo, 1200, 1200);
ensureDir($public . '/wa-og');
imagejpeg($square, $public . '/wa-og/valento-square.jpg', 90);
imagedestroy($square);
echo "JPG  wa-og/valento-square.jpg\n";

$wide = canvasContain($logo, 1200, 630);
imagejpeg($wide, $public . '/wa-og/valento-wide.jpg', 90);
imagedestroy($wide);
echo "JPG  wa-og/valento-wide.jpg\n";

$version = time();
$meta = [
    'version' => $version,
    'updated_at' => date('c', $version),
    'has_svg' => true,
    'has_ico' => true,
    'has_png' => true,
    'last_upload' => 'logo-principal.png',
    'source' => 'imagenes/logo-principal.png',
    'favicon_pack' => 'favicon/',
];
file_put_contents(
    $public . '/favicon-meta.json',
    json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);
echo "META favicon-meta.json v={$version}\n";
echo "OK\n";

<?php

$imgs = [
    ['gallery-1.jpg', 'Masaje Sensorial', 'sensorial'],
    ['gallery-2.jpg', 'Relajación Profunda', 'relajacion'],
    ['gallery-3.jpg', 'Masaje Tántrico', 'tantrico'],
    ['gallery-4.jpg', 'Espacio Privado', 'vip'],
    ['gallery-5.jpg', 'Masaje Sensorial', 'sensorial'],
    ['gallery-6.jpg', 'Relajación', 'relajacion'],
    ['gallery-7.jpg', 'Masaje Tántrico', 'tantrico'],
    ['gallery-8.jpg', 'Espacio VIP', 'vip'],
];

$orden = 1;
foreach ($imgs as [$file, $titulo, $cat]) {
    DB::table('web_pagina_galeria')->insert([
        'contexto'       => 'royal_galeria',
        'categoria'      => $cat,
        'url_imagen'     => 'temp02/img/' . $file,
        'alt_imagen'     => $titulo,
        'titulo_overlay' => $titulo,
        'orden'          => $orden++,
        'Activo'         => 'S',
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
}
echo "Images seeded.\n";

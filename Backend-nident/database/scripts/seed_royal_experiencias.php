<?php
// Script: seed_royal_experiencias.php
// Uso: php artisan tinker --execute="require database_path('scripts/seed_royal_experiencias.php');"

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$wa = 'https://wa.me/51982311335?text=' . urlencode('¡Hola! Me gustaría hacer una reserva en Royal Masajes. ¿Podrían indicarme la disponibilidad? ¡Gracias!');

// Limpiar
DB::table('web_experiencias')->truncate();
echo "Tabla limpiada\n";

$imgs = [
    'tantrico'  => ['temp02/img/gallery-3.jpg','temp02/img/gallery-7.jpg','temp02/img/gallery-13.jpg','temp02/img/gallery-1.jpg','temp02/img/gallery-8.jpg'],
    'bienestar' => ['temp02/img/gallery-2.jpg','temp02/img/gallery-6.jpg','temp02/img/gallery-11.jpg','temp02/img/gallery-12.jpg'],
    'corporal'  => ['temp02/img/gallery-5.jpg','temp02/img/gallery-9.jpg','temp02/img/gallery-4.jpg'],
    'estetica'  => ['temp02/img/gallery-10.jpg','temp02/img/gallery-14.jpg','temp02/img/gallery-6.jpg','temp02/img/gallery-4.jpg'],
];

$servicios = [
    // CATEGORÍA 1 — Experiencias Tántricas Sensoriales
    ['categoria'=>'tantrico','icono_categoria'=>'fas fa-spa','badge'=>'NURU PREMIUM',
     'titulo'=>'Masaje Tántrico Nuru Premium','subtitulo'=>'Experiencia Sensorial Suprema',
     'descripcion'=>'La experiencia más íntima y profunda de nuestro catálogo. Técnica japonesa Nuru combinada con el arte tántrico para una reconexión total del cuerpo y la mente.',
     'duracion'=>'90 – 120 min','orden'=>1],

    ['categoria'=>'tantrico','icono_categoria'=>'fas fa-spa','badge'=>'SENSORIAL',
     'titulo'=>'Masaje Tántrico Sensorial','subtitulo'=>'Despertar de los Sentidos',
     'descripcion'=>'Caricias conscientes que despiertan cada sentido de tu cuerpo. Un viaje de relajación profunda y reconexión energética a través del tacto más delicado.',
     'duracion'=>'60 – 90 min','orden'=>2],

    ['categoria'=>'tantrico','icono_categoria'=>'fas fa-spa','badge'=>'RECONEXIÓN',
     'titulo'=>'Masaje Tántrico Reconexión','subtitulo'=>'Equilibrio Energético',
     'descripcion'=>'Diseñado para mujeres que necesitan reconectar con su cuerpo después de períodos de alto estrés. Libera bloqueos energéticos y devuelve el equilibrio interior.',
     'duracion'=>'90 – 150 min','orden'=>3],

    ['categoria'=>'tantrico','icono_categoria'=>'fas fa-spa','badge'=>'EMBARAZO',
     'titulo'=>'Masaje Tántrico para Embarazadas','subtitulo'=>'Bienestar Maternal',
     'descripcion'=>'Técnica especialmente adaptada para gestantes. Alivia molestias propias del embarazo, reduce el estrés y fortalece la conexión con total seguridad.',
     'duracion'=>'60 – 90 min','orden'=>4],

    ['categoria'=>'tantrico','icono_categoria'=>'fas fa-spa','badge'=>'BONDAGE',
     'titulo'=>'Bondage Sensory Experience','subtitulo'=>'Exploración Sensorial Profunda',
     'descripcion'=>'Experiencia única que combina privación sensorial con masaje tántrico. Potencia la sensibilidad y lleva la relajación a un nivel completamente nuevo.',
     'duracion'=>'90 – 120 min','orden'=>5],

    // CATEGORÍA 2 — Bienestar y Relajación Femenina
    ['categoria'=>'bienestar','icono_categoria'=>'fas fa-leaf','badge'=>'ARMÓNICO 360°',
     'titulo'=>'Masaje Relajante Armónico 360','subtitulo'=>'Relajación Total del Cuerpo',
     'descripcion'=>'Masaje de cuerpo completo que trabaja cada zona muscular en armonía. Técnica de presión progresiva que libera la tensión acumulada de jornadas exigentes.',
     'duracion'=>'60 – 90 min','orden'=>6],

    ['categoria'=>'bienestar','icono_categoria'=>'fas fa-leaf','badge'=>'VOLCÁNICO',
     'titulo'=>'Ritual Volcánico de Relajación','subtitulo'=>'Calor y Profundidad',
     'descripcion'=>'Las piedras volcánicas calientes penetran la tensión muscular más profunda mientras el masaje libera el estrés y el calor reconforta cada célula de tu cuerpo.',
     'duracion'=>'75 – 90 min','orden'=>7],

    ['categoria'=>'bienestar','icono_categoria'=>'fas fa-leaf','badge'=>'HOLÍSTICO',
     'titulo'=>'Masaje Holístico Royal Sensory','subtitulo'=>'Cuerpo, Mente y Espíritu',
     'descripcion'=>'Integra técnicas de diversas tradiciones terapéuticas para lograr un bienestar completo. Aborda el cuerpo como un todo, liberando tensiones físicas y emocionales.',
     'duracion'=>'90 – 120 min','orden'=>8],

    ['categoria'=>'bienestar','icono_categoria'=>'fas fa-leaf','badge'=>'DULCE ESPERA',
     'titulo'=>'Ritual Dulce Espera','subtitulo'=>'Para Futuras Mamás',
     'descripcion'=>'Ritual completo de bienestar para embarazadas que incluye masaje suave, hidratación corporal y técnicas de relajación para cada trimestre.',
     'duracion'=>'60 min','orden'=>9],

    // CATEGORÍA 3 — Renovación Corporal Profunda
    ['categoria'=>'corporal','icono_categoria'=>'fas fa-dumbbell','badge'=>'TEJIDO PROFUNDO',
     'titulo'=>'Masaje Tejido Profundo 360','subtitulo'=>'Liberación Muscular Total',
     'descripcion'=>'Técnica de alta presión que trabaja las capas más profundas del tejido muscular. Ideal para contracturas crónicas, dolor de espalda y tensión por estrés.',
     'duracion'=>'60 – 90 min','orden'=>10],

    ['categoria'=>'corporal','icono_categoria'=>'fas fa-dumbbell','badge'=>'PIERNAS LIGERAS',
     'titulo'=>'Ritual Piernas Ligeras','subtitulo'=>'Circulación y Ligereza',
     'descripcion'=>'Masaje especializado en piernas y pies que activa la circulación, reduce la retención de líquidos y elimina la sensación de pesadez al final del día.',
     'duracion'=>'45 – 60 min','orden'=>11],

    ['categoria'=>'corporal','icono_categoria'=>'fas fa-dumbbell','badge'=>'MUSCULAR',
     'titulo'=>'Descarga Muscular Relajante','subtitulo'=>'Alivio Intensivo',
     'descripcion'=>'Sesión intensiva enfocada en liberar la tensión muscular acumulada por el deporte, el trabajo o el estrés. Combinación de técnicas deportivas y terapéuticas.',
     'duracion'=>'60 min','orden'=>12],

    // CATEGORÍA 4 — Modelación y Estética Corporal
    ['categoria'=>'estetica','icono_categoria'=>'fas fa-star','badge'=>'REDUCTORA',
     'titulo'=>'Maderoterapia Reductora','subtitulo'=>'Modelación Natural',
     'descripcion'=>'Técnica colombiana con implementos de madera que rompe la grasa localizada, activa el drenaje linfático y moldea la figura de forma natural y efectiva.',
     'duracion'=>'60 – 90 min','orden'=>13],

    ['categoria'=>'estetica','icono_categoria'=>'fas fa-star','badge'=>'DRENAJE',
     'titulo'=>'Drenaje Linfático Corporal Sensory','subtitulo'=>'Depuración Profunda',
     'descripcion'=>'Masaje de suaves movimientos rítmicos que estimula el sistema linfático, elimina toxinas, reduce la inflamación y mejora la apariencia de la piel.',
     'duracion'=>'60 – 75 min','orden'=>14],

    ['categoria'=>'estetica','icono_categoria'=>'fas fa-star','badge'=>'ANTICELULITIS',
     'titulo'=>'Masaje Anticelulitis Royal Sensory','subtitulo'=>'Piel Tersa y Firme',
     'descripcion'=>'Tratamiento intensivo que combina técnicas de presión, rodamiento y drenaje para combatir la celulitis y devolver tersura y firmeza a tu piel.',
     'duracion'=>'60 – 90 min','orden'=>15],

    ['categoria'=>'estetica','icono_categoria'=>'fas fa-star','badge'=>'REAFIRMANTE',
     'titulo'=>'Masaje Reafirmante de Glúteos','subtitulo'=>'Tonificación y Lifting',
     'descripcion'=>'Técnica especializada que tonifica, levanta y reafirma la zona glútea a través de maniobras específicas combinadas con aceites bioactivos.',
     'duracion'=>'45 – 60 min','orden'=>16],
];

$imgIdx = ['tantrico'=>0,'bienestar'=>0,'corporal'=>0,'estetica'=>0];
foreach ($servicios as $s) {
    $cat = $s['categoria'];
    $img = $imgs[$cat][$imgIdx[$cat] % count($imgs[$cat])];
    $imgIdx[$cat]++;

    DB::table('web_experiencias')->insert([
        'badge'           => $s['badge'],
        'categoria'       => $cat,
        'icono_categoria' => $s['icono_categoria'],
        'duracion'        => $s['duracion'],
        'titulo'          => $s['titulo'],
        'subtitulo'       => $s['subtitulo'],
        'descripcion'     => $s['descripcion'],
        'precio_nota'     => 'Consultar precio',
        'url_imagen'      => $img,
        'btn_texto'       => 'Reservar',
        'btn_url'         => $wa,
        'orden'           => $s['orden'],
        'Activo'          => 'S',
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    echo $s['orden'] . '. [' . $cat . '] ' . $s['titulo'] . "\n";
}

echo "\nTotal insertado: " . DB::table('web_experiencias')->count() . " servicios\n";

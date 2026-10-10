<?php
use Illuminate\Support\Facades\DB;

$wa = 'https://wa.me/51982311335?text=' . urlencode('¡Hola! Me gustaría hacer una reserva en Royal Masajes. ¿Podrían indicarme la disponibilidad? ¡Gracias!');

$secTitulo    = 'Publicaciones <span class="color-primary">Recientes</span>';
$secSubtitulo = 'Consejos de bienestar, relajación profunda y equilibrio para la mujer profesional y ejecutiva.';

$pubs = [
    1 => [
        'seccion_titulo'    => $secTitulo,
        'seccion_subtitulo' => $secSubtitulo,
        'titulo'            => 'Cómo reconectar con tu cuerpo después de jornadas exigentes',
        'slug'              => 'reconectar-cuerpo-jornadas-exigentes',
        'resumen'           => 'El estrés acumulado de semanas de alta presión laboral afecta tu cuerpo de formas que quizás no percibes. Descubre técnicas sencillas para reconectar.',
        'contenido'         => '<h4>El impacto del estrés crónico en el cuerpo femenino</h4>
<p>Las mujeres ejecutivas enfrentan niveles de estrés que raramente se traducen en descanso real. La tensión muscular, el insomnio y la desconexión emocional son señales de que tu cuerpo necesita atención urgente.</p>
<h4>Señales de que tu cuerpo pide ayuda</h4>
<ul>
<li>Tensión constante en cuello y hombros</li>
<li>Dificultad para conciliar el sueño o descansar profundamente</li>
<li>Sensación de pesadez o entumecimiento en el cuerpo</li>
<li>Irritabilidad o dificultad para concentrarse</li>
</ul>
<h4>El poder del tacto consciente</h4>
<p>El masaje tántrico y sensorial va mucho más allá de la relajación superficial. A través del tacto consciente, el cuerpo libera oxitocina, reduce el cortisol y activa el sistema nervioso parasimpático — el estado natural de recuperación.</p>
<p>En Royal Masajes, cada sesión está diseñada para guiarte en ese proceso de reconexión profunda, en un entorno absolutamente privado y diseñado para tus sentidos.</p>',
        'autor'             => 'Royal Masajes',
        'categoria'         => 'Bienestar',
        'chip'              => 'Bienestar',
        'url_imagen'        => 'temp02/img/gallery-1.jpg',
        'url_enlace'        => '/publicaciones/reconectar-cuerpo-jornadas-exigentes',
        'orden'             => 1,
    ],
    2 => [
        'seccion_titulo'    => $secTitulo,
        'seccion_subtitulo' => $secSubtitulo,
        'titulo'            => 'Masaje tántrico: beneficios del tacto consciente para mujeres ejecutivas',
        'slug'              => 'masaje-tantrico-beneficios-tacto-consciente',
        'resumen'           => 'El masaje tántrico no es solo relajación — es una práctica milenaria de reconexión energética que puede transformar tu relación con tu cuerpo y tu mente.',
        'contenido'         => '<h4>¿Qué es realmente el masaje tántrico?</h4>
<p>Más allá de los mitos y malentendidos, el masaje tántrico es una práctica terapéutica ancestral que trabaja con la energía del cuerpo para liberar bloqueos físicos y emocionales. No es un servicio de carácter sexual — es una experiencia de reconexión profunda.</p>
<h4>Beneficios comprobados para la mujer ejecutiva</h4>
<ul>
<li><strong>Reducción del cortisol:</strong> El estrés crónico eleva los niveles de cortisol. Una sesión de masaje tántrico puede reducirlos hasta en un 30% según estudios recientes.</li>
<li><strong>Mejora del sueño:</strong> La activación del sistema parasimpático facilita un sueño más profundo y reparador.</li>
<li><strong>Reconexión emocional:</strong> Muchas mujeres ejecutivas desarrollan una desconexión con su cuerpo. El tacto consciente revierte este proceso.</li>
<li><strong>Aumento de la productividad:</strong> Un cuerpo relajado y una mente en calma son la base de un rendimiento ejecutivo sostenible.</li>
</ul>
<h4>¿Con qué frecuencia conviene realizarlo?</h4>
<p>Para mujeres con altos niveles de responsabilidad, recomendamos sesiones cada 2-3 semanas como parte de una rutina de bienestar integral.</p>',
        'autor'             => 'Royal Masajes',
        'categoria'         => 'Tántrico',
        'chip'              => 'Sensorial',
        'url_imagen'        => 'temp02/img/gallery-3.jpg',
        'url_enlace'        => '/publicaciones/masaje-tantrico-beneficios-tacto-consciente',
        'orden'             => 2,
    ],
    3 => [
        'seccion_titulo'    => $secTitulo,
        'seccion_subtitulo' => $secSubtitulo,
        'titulo'            => 'Por qué la privacidad y la discreción son esenciales en tu bienestar',
        'slug'              => 'privacidad-discrecion-bienestar',
        'resumen'           => 'Para la mujer profesional, la confidencialidad no es un lujo — es una necesidad. Descubre por qué el entorno privado marca la diferencia en tu experiencia de bienestar.',
        'contenido'         => '<h4>El valor de un espacio verdaderamente privado</h4>
<p>Cuando una mujer con responsabilidades ejecutivas busca bienestar, la privacidad no es opcional — es fundamental. La capacidad de soltar el control, relajarse profundamente y ser vulnerable requiere un entorno donde te sientas completamente segura.</p>
<h4>Lo que diferencia un espacio privado de uno convencional</h4>
<ul>
<li>Atención exclusiva: sin otras clientas, sin interferencias</li>
<li>Confidencialidad total sobre tu visita y tu experiencia</li>
<li>Dirección compartida solo al confirmar la reserva</li>
<li>Sin registros públicos ni datos compartidos con terceros</li>
</ul>
<h4>Cómo la discreción potencia la relajación</h4>
<p>Neurológicamente, la sensación de seguridad activa el sistema nervioso parasimpático — el estado de "descanso y digestión" donde ocurre la verdadera recuperación. Cuando tu mente sabe que estás en un entorno privado y seguro, el cuerpo puede soltar tensiones que de otro modo nunca liberaría.</p>
<p>En Royal Masajes, la discreción es un valor fundacional — no un servicio adicional.</p>',
        'autor'             => 'Royal Masajes',
        'categoria'         => 'Privacidad',
        'chip'              => 'Equilibrio',
        'url_imagen'        => 'temp02/img/gallery-4.jpg',
        'url_enlace'        => '/publicaciones/privacidad-discrecion-bienestar',
        'orden'             => 3,
    ],
    4 => [
        'seccion_titulo'    => $secTitulo,
        'seccion_subtitulo' => $secSubtitulo,
        'titulo'            => 'Ritual volcánico: el poder de las piedras calientes para tu bienestar',
        'slug'              => 'ritual-volcanico-piedras-calientes-bienestar',
        'resumen'           => 'Las piedras volcánicas tienen propiedades únicas para penetrar la tensión muscular más profunda. Descubre cómo este ritual puede transformar tu experiencia de relajación.',
        'contenido'         => '<h4>¿Qué son las piedras volcánicas y por qué son especiales?</h4>
<p>Las piedras basálticas volcánicas retienen el calor de manera uniforme y duradera. Su textura lisa y su peso permiten una presión profunda que los dedos humanos difícilmente pueden igualar.</p>
<h4>Beneficios del ritual con piedras calientes</h4>
<ul>
<li>Penetración muscular profunda sin dolor</li>
<li>Mejora de la circulación sanguínea</li>
<li>Reducción de la inflamación y el dolor crónico</li>
<li>Activación del sistema linfático</li>
<li>Sensación de calor envolvente que reduce la ansiedad</li>
</ul>
<h4>Combinado con técnicas tántricas</h4>
<p>En Royal Masajes, el ritual volcánico se combina con técnicas de masaje tántrico para potenciar cada beneficio. El calor prepara los músculos, el masaje libera las tensiones y la energía fluye libremente a través del cuerpo.</p>',
        'autor'             => 'Royal Masajes',
        'categoria'         => 'Técnicas',
        'chip'              => 'Relajación',
        'url_imagen'        => 'temp02/img/gallery-6.jpg',
        'url_enlace'        => '/publicaciones/ritual-volcanico-piedras-calientes-bienestar',
        'orden'             => 4,
    ],
    5 => [
        'seccion_titulo'    => $secTitulo,
        'seccion_subtitulo' => $secSubtitulo,
        'titulo'            => 'El bienestar como estilo de vida: más allá del lujo ocasional',
        'slug'              => 'bienestar-estilo-vida-lujo-ocasional',
        'resumen'           => 'Integrar el bienestar en tu rutina semanal no es un gasto — es una inversión en tu rendimiento, tu salud y tu calidad de vida como mujer profesional.',
        'contenido'         => '<h4>El error de tratar el bienestar como recompensa</h4>
<p>Muchas mujeres ejecutivas tratan el bienestar como una recompensa que se permite solo después de lograr objetivos. Este enfoque perpetúa un ciclo de agotamiento que finalmente afecta tanto el rendimiento profesional como la salud.</p>
<h4>El bienestar como inversión estratégica</h4>
<ul>
<li><strong>Rendimiento cognitivo:</strong> Las pausas de bienestar mejoran la toma de decisiones y la creatividad.</li>
<li><strong>Gestión emocional:</strong> Un cuerpo relajado procesa el estrés de forma más efectiva.</li>
<li><strong>Longevidad profesional:</strong> El burnout es la principal causa de abandono de carreras ejecutivas en mujeres.</li>
</ul>
<h4>Construyendo una rutina de bienestar sostenible</h4>
<p>No se trata de dedicar horas interminables al autocuidado — se trata de crear espacios regulares donde el cuerpo y la mente puedan recuperarse genuinamente. Una sesión quincenal de masaje tántrico puede ser más efectiva que semanas de "descanso" superficial.</p>
<p>En Royal Masajes trabajamos con mujeres que han decidido que su bienestar es una prioridad — no una concesión.</p>',
        'autor'             => 'Royal Masajes',
        'categoria'         => 'Lifestyle',
        'chip'              => 'Bienestar',
        'url_imagen'        => 'temp02/img/gallery-9.jpg',
        'url_enlace'        => '/publicaciones/bienestar-estilo-vida-lujo-ocasional',
        'orden'             => 5,
    ],
];

foreach ($pubs as $id => $data) {
    DB::table('web_publicaciones')->where('id_publicacion', $id)->update(array_merge($data, [
        'fecha_publicacion' => now()->subDays((5 - $id) * 7)->toDateString(),
        'updated_at' => now(),
    ]));
    echo $id . '. ' . $data['titulo'] . " → OK\n";
}

echo "\nPublicaciones actualizadas: " . DB::table('web_publicaciones')->count() . "\n";

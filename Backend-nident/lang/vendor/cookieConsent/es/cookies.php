<?php
return [
    'title' => 'Utilizamos cookies',
    'intro' => 'Este sitio utiliza cookies para mejorar su experiencia de usuario.',
    'link' => 'Consulte nuestra <a href=":url">politica de cookies</a> para obtener mas informacion.',

    'essentials' => 'Solo esenciales',
    'all' => 'Aceptar todas',
    'customize' => 'Personalizar',
    'manage' => 'Administrar cookies',
    'details' => [
        'more' => 'Mas informacion',
        'less' => 'Menos informacion',
    ],
    'save' => 'Guardar configuracion',
    'cookie' => 'Cookie',
    'purpose' => 'Finalidad',
    'duration' => 'Duracion',
    'year' => 'Ano|Anos',
    'day' => 'Dia|Dias',
    'hour' => 'Hora|Horas',
    'minute' => 'Minuto|Minutos',

    'categories' => [
        'essentials' => [
            'title' => 'Cookies de funcionamiento',
            'description' => 'Necesitamos usar ciertas cookies para que ciertas paginas web funcionen. Por eso no requieren tu consentimiento.',
        ],
        'analytics' => [
            'title' => 'Cookies analiticas',
            'description' => 'Utilizamos estas cookies con fines de investigacion interna sobre como podemos mejorar el servicio que brindamos a nuestros usuarios. Estas cookies se utilizan para evaluar como interactua con nuestro sitio web como un usuario anonimo (los datos recopilados no lo identifican personalmente).',
        ],
        'optional' => [
            'title' => 'Cookies opcionales',
            'description' => 'Estas cookies habilitan funciones que pueden mejorar su experiencia de usuario, pero su ausencia no afecta su capacidad para navegar por nuestro sitio web',
        ],
    ],

    'defaults' => [
        'consent' => 'Se utiliza para almacenar las preferencias de consentimiento de cookies del usuario.',
        'session' => 'Se utiliza para identificar la sesion de navegacion del usuario.',
        'csrf' => 'Se utiliza para asegurar tanto al usuario como a nuestro sitio web contra ataques de falsificacion de solicitudes entre sitios.',
        '_ga' => 'Cookie principal utilizada por Google Analytics, permite distinguir a un visitante de otro.',
        '_ga_ID' => 'Utilizada por Google Analytics para persistir el estado de la sesion.',
        '_gid' => 'Utilizada por Google Analytics para identificar al usuario.',
        '_gat' => 'Utilizada por Google Analytics para limitar la tasa de solicitudes.',
    ]
];
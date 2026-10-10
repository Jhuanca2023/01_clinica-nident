@php
    $lista = isset($testimonios) ? $testimonios : collect();
    $primero = $lista->first();
    $badgeSeccion = $primero->badge_seccion ?? 'Testimonios y Reseñas';
    $tituloSeccion = $primero->seccion_titulo ?? 'Negocios reales. Resultados reales. Clientes felices.';

    $fallbackImgs = [
        'temp02/assets/img/results-img-1.jpg',
        'temp02/assets/img/results-img-4.jpg',
        'temp02/assets/img/results-img-3.jpg',
        'temp02/assets/img/results-img-2.jpg',
    ];

    $columnas = [[], [], []];
    foreach ($lista as $idx => $t) {
        $columnas[$idx % 3][] = $t;
    }

    $rellenarColumna = function (array $items) {
        if (count($items) === 0) {
            return [];
        }
        $out = $items;
        while (count($out) < 4) {
            $out = array_merge($out, $items);
        }
        return array_merge($out, $out);
    };

    $columnasMarquee = array_map($rellenarColumna, $columnas);
    $clasesColumna = ['', ' two', ' three'];
@endphp

<section style="background-color: #f3efe9; padding: 4rem 0;" class="gap">
  <div class="container">
    <div class="heading sec-title-animation animation-style2">
      <span class="title-animation">{{ $badgeSeccion }}</span>
      <h2 class="title-animation">{{ $tituloSeccion }}</h2>
    </div>
    @if(count($lista) > 0)
    <div class="row row-full">
      @foreach($columnasMarquee as $colIdx => $colItems)
      <div class="col-lg-4">
        <div class="reviews-full{{ $clasesColumna[$colIdx] ?? '' }}">
          @foreach($colItems as $loopIdx => $t)
          @php
            $esCaptura = ($t->tipo ?? 'texto') === 'captura' && !empty($t->url_captura);
            $avatar = !empty($t->url_avatar)
              ? asset($t->url_avatar)
              : asset($fallbackImgs[$loopIdx % count($fallbackImgs)]);
            $calificacion = number_format((float) ($t->calificacion ?? 4.8), 1);
            $texto = $t->testimonio ?? '';
          @endphp
          <div class="reviews{{ $esCaptura ? ' reviews-captura' : '' }}">
            @if($esCaptura)
              <img
                src="{{ asset($t->url_captura) }}"
                alt="{{ $t->nombre ?? 'Evidencia de cliente' }}"
                class="testimonio-captura-img"
                loading="lazy"
              >
            @else
              <div class="reviews-admin">
                <img src="{{ $avatar }}" alt="{{ $t->nombre ?? 'Cliente' }}">
                <div>
                  <h5>{{ $t->nombre ?? 'Cliente Satisfecho' }}</h5>
                  <span>{{ $t->subtitulo ?? 'Cliente de Valento' }}</span>
                </div>
              </div>
              <p>{{ $texto }}</p>
              <ul class="star">
                <li><i class="fa-solid fa-star"></i></li>
                <li><span>({{ $calificacion }})</span></li>
              </ul>
            @endif
          </div>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
    @endif
    <div class="review customers">
      <ul>
        <li><img src="{{ asset('temp02/assets/img/clients-1.jpg') }}" alt="img"></li>
        <li><img src="{{ asset('temp02/assets/img/clients-2.jpg') }}" alt="img"></li>
        <li><img src="{{ asset('temp02/assets/img/clients-3.jpg') }}" alt="img"></li>
        <li><a href="#">+</a></li>
      </ul>
      <span>Más de 500 negocios confían en Valento para crecer en redes sociales</span>
    </div>
  </div>
</section>

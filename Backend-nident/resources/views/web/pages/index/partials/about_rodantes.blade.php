@php
    $about = $webAbout;
    $imgAbout = Helpers::cmsAssetUrl($about->url_imagen ?? 'temp02/assets/image/inicio/librerias-rodantes-02.jpg');
@endphp
<section class="about-sec mb-96 wow fadeInUp animated mt-5" id="nosotros" data-wow-delay="350ms">
  <div class="container-fluid">
    <div class="row row-gap-4 align-items-center">
      <div class="col-lg-6">
        <div class="img-block">
          <img src="{{ $imgAbout }}" alt="Bibliotecas Rodantes">
        </div>
      </div>
      <div class="col-lg-6">
        <div class="about-content">
          <div class="heading mb-24">
            <h2 class="h2">{!! $about->titulo ?? 'Quiénes Somos' !!}</h2>
          </div>
          @if($about->subtitulo ?? null)
          <h4 class="fw-400 mb-24">{!! $about->subtitulo !!}</h4>
          @endif
          @if(($about->lema_label ?? null) || ($about->lema_texto ?? null))
          <blockquote class="about-lema mb-32">
            @if($about->lema_label)<p class="about-lema__label">{{ $about->lema_label }}</p>@endif
            @if($about->lema_texto)<p class="about-lema__quote">{{ $about->lema_texto }}</p>@endif
          </blockquote>
          @endif
          <div class="about-text mb-48" id="about-text-block">
            {!! $about->descripcion ?? '' !!}
            @if($about->texto_extendido ?? null)
            <div class="about-extra" id="about-extra" aria-hidden="true">
              {!! $about->texto_extendido !!}
            </div>
            @endif
          </div>
          @if(!empty($aboutContadores) && count($aboutContadores) > 0)
          <div class="num-section p-0 mb-48">
            @foreach($aboutContadores as $stat)
            <div class="counter-count text-start">
              <div class="">
                <span class="h5 black fw-400">{{ $stat->valor }}</span>
                <span class="h5 fw-400 black">{{ $stat->sufijo ?? '+' }}</span>
                <p>{{ $stat->etiqueta }}</p>
              </div>
            </div>
            @endforeach
          </div>
          @endif
          @if($about->btn_texto ?? null)
          <a href="{{ $about->btn_url ?? '#' }}" class="cus-btn-2">
            <span>{{ $about->btn_texto }}</span>
            <span>{{ $about->btn_texto }}</span>
          </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

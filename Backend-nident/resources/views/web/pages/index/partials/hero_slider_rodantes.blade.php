@php
    $slides = collect($sliderData ?? [])->where('Activo', 'S')->sortBy('id_slider')->values();
    if ($slides->isEmpty()) {
        $slides = collect([(object)['slide_tag'=>'','titulo'=>'','descripcion'=>'','texto_boton'=>'','url_link'=>'#','texto_boton_2'=>'','url_link_2'=>'#','url_imagen'=>null]]);
    }
    $sideLabel = $webHeader->etiqueta_hero_lateral ?? 'Bibliotecas Rodantes';
@endphp
<section class="hero-slider">
  <div class="side-line"></div>
  <div class="side-label">{{ $sideLabel }}</div>
  <div class="slider-main">
    @foreach($slides as $slide)
    @php $bg = Helpers::cmsAssetUrl($slide->url_imagen ?? 'temp02/assets/image/inicio/slider-2.jpg'); @endphp
    <div class="slide" style="background-image:url('{{ $bg }}')">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        @if(!empty($slide->slide_tag))
        <span class="slide-tag">{{ $slide->slide_tag }}</span>
        @endif
        <h1 class="slide-title">{!! $slide->titulo !!}</h1>
        <p class="slide-subtitle">{!! $slide->descripcion !!}</p>
        <div class="slide-actions">
          @if(!empty($slide->texto_boton))
          <a href="{{ $slide->url_link ?: '#' }}" class="btn-primary-hero">
            <i class="fa-solid fa-book-open"></i> {{ $slide->texto_boton }}
          </a>
          @endif
          @if(!empty($slide->texto_boton_2))
          <a href="{{ $slide->url_link_2 ?: '#' }}" class="btn-outline-white">
            <i class="fa-solid fa-arrow-right"></i> {{ $slide->texto_boton_2 }}
          </a>
          @endif
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div class="scroll-hint"><span>Scroll</span><div class="scroll-arrow"></div></div>
  <div class="slider-bottom">
    <div class="counter-block">
      <span class="counter-current">01</span>
      <span class="counter-sep">/</span>
      <span class="counter-total">{{ str_pad($slides->count(), 2, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="progress-track"><div class="progress-fill" id="sliderProgress"></div></div>
    <div class="d-flex align-items-center gap-3">
      <div class="slide-dots" id="sliderDots"></div>
      <div class="nav-arrows">
        <button class="nav-btn" id="heroPrev" aria-label="Anterior"><i class="fa fa-chevron-left"></i></button>
        <button class="nav-btn" id="heroNext" aria-label="Siguiente"><i class="fa fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</section>
@push('scripts')
<script>
  init_pointer({
    container: ".hero-slider",
    pointerColor: "#878C53",
    ringColor: "#878C53",
    ringIdleColor: "#d4d9a0",
    ringSize: 22,
    ringClickSize: 14
  });
</script>
@endpush

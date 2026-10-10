@php
    $srvFirst = $servicios->isNotEmpty() ? $servicios->first() : null;
    $srvTitulo = $srvFirst->seccion_titulo ?? 'Nuestros <span class="color-primary">Servicios</span>';
    $srvSub = 'De la página al patio del colegio: lectura, cultura y aprendizaje que llegan hasta tu comunidad';
@endphp
<section class="service-sec mb-120" id="servicios">
  <div class="container-fluid">
    <div class="heading mb-48">
      <div class="title mb-12">
        <h2 class="h2 wow fadeIn animated" data-wow-delay="330ms">{!! $srvTitulo !!}</h2>
      </div>
      <p>{{ $srvSub }}</p>
    </div>
    <div class="row row-gap-4 mb-48">
      @foreach($servicios as $srv)
      @php $imgSrv = Helpers::cmsAssetUrl($srv->url_foto ?? 'temp02/assets/image/inicio/servicio-rodante-01.jpg'); @endphp
      <div class="col-lg-3 col-md-6">
        <div class="service-card">
          <a href="javascript:void(0)" class="card-img" role="button" aria-label="Ver imagen: {{ $srv->titulo }}">
            <img src="{{ $imgSrv }}" alt="{{ $srv->titulo }}">
          </a>
          <div class="card-content">
            <a href="#" class="fw-400 h5 hover-content mb-8">{{ $srv->titulo }}</a>
            <p>{{ $srv->descripcion }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="text-center">
      <a href="#servicios" class="cus-btn-2">
        <span>Ver todos los servicios</span>
        <span>Ver todos los servicios</span>
      </a>
    </div>
  </div>
</section>

@php
    $lista = isset($faqPreguntas) ? $faqPreguntas : collect();
    $cfg = $lista->first();
    $badge = $cfg->badge_seccion ?? 'Preguntas Frecuentes';
    $titulo = $cfg->seccion_titulo ?? '¿Tienes dudas? Te respondemos';
    $tel1Label = $cfg->telefono_principal_label ?? 'Llámanos directamente:';
    $tel1 = $cfg->telefono_principal ?? '939 691 536';
    $tel2Label = $cfg->telefono_callcenter_label ?? 'Call Center:';
    $tel2 = $cfg->telefono_callcenter ?? '901 967 564';
    $tel1Href = preg_replace('/\s+/', '', $tel1);
    $tel2Href = preg_replace('/\s+/', '', $tel2);
@endphp

@if($lista->count() > 0)
<section class="gap no-top accordion-section" style="padding: 4rem 0;">
  <div class="container">
    <div class="row ">
      <div class="col-lg-4">
        <div class="heading lf-side sec-title-animation animation-style2">
          <span class="title-animation">{{ $badge }}</span>
          <h2 class="title-animation">{{ $titulo }}</h2>
        </div>
        @if($tel1 !== '')
        <div class="mailnumber">
          <i class="fa-solid fa-mobile-screen-button"></i>
          <div>
            <span>{{ $tel1Label }}</span>
            <a href="tel:{{ $tel1Href }}">{{ $tel1 }}</a>
          </div>
        </div>
        @endif
        @if($tel2 !== '')
        <div class="mailnumber">
          <i class="fa-solid fa-mobile-screen-button"></i>
          <div>
            <span>{{ $tel2Label }}</span>
            <a href="tel:{{ $tel2Href }}">{{ $tel2 }}</a>
          </div>
        </div>
        @endif
      </div>
      <div class="col-lg-8">
        <div class="accordion">
          @foreach($lista as $index => $faq)
          <div class="accordion-item{{ $index === 0 ? ' active' : '' }}">
            <a href="#" class="heading">
              <div class="icon"></div>
              <div class="title">{{ $faq->pregunta }}</div>
            </a>
            <div class="content" @if($index === 0) style="display: block;" @endif>
              <p>{{ $faq->respuesta }}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  <img src="{{ asset('temp02/assets/img/shap-img-5.png') }}" alt="shap-img">
</section>
@endif

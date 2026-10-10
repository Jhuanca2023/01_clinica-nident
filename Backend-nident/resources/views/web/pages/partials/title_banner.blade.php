@php
  $bannerBg = $bannerImagenUrl ?? null;
  $bannerStyle = $bannerBg
    ? 'background-image:url(' . e($bannerBg) . ');background-size:cover;background-position:center;background-repeat:no-repeat;'
    : '';
@endphp
<section class="title-banner {{ $bannerClass ?? 'mb-80' }}" @if($bannerStyle) style="{{ $bannerStyle }}" @endif>
  <div class="container-fluid">
    <div class="title">
      <h2 class="h2">{!! $bannerTitulo ?? 'Página' !!}</h2>
    </div>
  </div>
</section>

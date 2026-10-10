@extends('web.base_landing')

@section('content')
<div id="preloader">
  <div class="loading loading07">
    @foreach(str_split('Rodantes') as $ch)
    <span data-text="{{ $ch }}">{{ $ch }}</span>
    @endforeach
  </div>
</div>
<div id="scroll-container">
  @include('web.pages.index.partials.header_rodantes', ['paginaActiva' => $paginaActiva ?? ''])
  @yield('page_content')
  @include('web.pages.index.partials.footer_rodantes')
</div>
@include('web.pages.index.partials.floating_rodantes')
@endsection

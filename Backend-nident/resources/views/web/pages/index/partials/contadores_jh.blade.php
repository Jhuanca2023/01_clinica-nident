@php
    $items = $contadores ?? collect();
    $bg = Helpers::cmsAssetUrl($contadoresSeccion->url_imagen_fondo ?? 'temp02/assets/img/banner/2.jpg');
@endphp
@if($items->isNotEmpty())
<div class="fun-factor-area default-padding bottom-less text-center bg-fixed shadow dark-hard" style="background-image: url('{{ $bg }}');">
    <div class="container">
        <div class="row">
            @foreach($items as $c)
            <div class="col-lg-3 col-md-6 item">
                <div class="fun-fact">
                    @if($c->icono_clase ?? null)<div class="icon"><i class="{{ $c->icono_clase }}"></i></div>@endif
                    <div class="info">
                        <span class="timer" data-to="{{ preg_replace('/\D/','',$c->valor) }}" data-speed="5000"></span>
                        <span class="medium">{{ $c->etiqueta }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

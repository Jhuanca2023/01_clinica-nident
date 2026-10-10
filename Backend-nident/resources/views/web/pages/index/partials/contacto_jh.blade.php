@php
    $cl = $contactoLanding ?? null;
    $asuntos = is_array($cl->asuntos ?? null) ? $cl->asuntos : (json_decode($cl->asuntos ?? '[]', true) ?: []);
    $imgSide = Helpers::cmsAssetUrl($cl->url_imagen ?? 'temp02/assets/img/jh_importaciones/inicio/jh_importaciones_img_5.jpg');
@endphp
<div id="contacto" class="reg-area default-padding-top bg-gray">
    <div class="container">
        <div class="row">
            <div class="reg-items">
                <div class="col-lg-6 reg-form default-padding-bottom">
                    <div class="site-heading text-left">
                        <h2>{{ $cl->titulo ?? 'Escríbenos un mensaje' }}</h2>
                        <p>{{ $cl->descripcion ?? '' }}</p>
                    </div>
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('contact.landing.submit') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><input class="form-control" name="nombre" placeholder="Nombre" type="text" required value="{{ old('nombre') }}"></div></div>
                            <div class="col-md-6"><div class="form-group"><input class="form-control" name="apellido" placeholder="Apellido" type="text" required value="{{ old('apellido') }}"></div></div>
                            <div class="col-md-12"><div class="form-group"><input class="form-control" name="email" placeholder="Email*" type="email" required value="{{ old('email') }}"></div></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <select name="asunto" class="form-control">
                                        <option value="">Selecciona un asunto</option>
                                        @foreach($asuntos as $as)
                                        <option value="{{ $as }}" @selected(old('asunto') === $as)>{{ $as }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12"><div class="form-group"><input class="form-control" name="telefono" placeholder="Teléfono / Celular" type="text" value="{{ old('telefono') }}"></div></div>
                            <div class="col-md-12"><div class="form-group"><textarea class="form-control" name="mensaje" placeholder="Mensaje" rows="4" required>{{ old('mensaje') }}</textarea></div></div>
                            <div class="col-md-12"><button type="submit">Enviar mensaje</button></div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 thumb"><img src="{{ $imgSide }}" alt="Contacto"></div>
            </div>
        </div>
    </div>
</div>

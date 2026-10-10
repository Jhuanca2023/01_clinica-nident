@php $imgContacto = asset('temp02/assets/image/inicio/atencion.jpg'); @endphp
<section class="contact-sec mb-8 wow fadeInUp animated" id="contacto" data-wow-delay="370ms" style="margin-top: 5rem !important;">
  <div class="container-fluid">
    <div class="heading mb-48">
      <div class="title mb-12">
        <h2 class="h2 wow fadeIn animated" data-wow-delay="330ms">Contácta<span class="color-primary">nos</span></h2>
      </div>
      <p>¿Quieres coordinar una visita, un taller o un conversatorio? Escríbenos desde Lambayeque o cualquier rincón.</p>
    </div>
    <div class="row row-gap-4 align-items-stretch">
      <div class="col-lg-6">
        @if(session('success'))
        <div class="alert alert-success mb-24">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger mb-24">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form method="post" action="{{ route('contact.landing.submit') }}" class="contact-form h-100">
          @csrf
          <h4 class="mb-16">Escríbenos</h4>
          <p class="mb-48">¡Nos encantaría escucharte! Cuéntanos tu idea, tu comunidad o tu escuela.</p>
          <div class="row row-gap-4 align-items-center mb-48">
            <div class="col-lg-6">
              <p class="subtitle black mb-8">Nombre</p>
              <div class="input-block">
                <input type="text" name="nombre" class="form-control" required placeholder="ej. Juan" value="{{ old('nombre') }}">
              </div>
            </div>
            <div class="col-lg-6">
              <p class="subtitle black mb-8">Apellidos</p>
              <div class="input-block">
                <input type="text" name="apellidos" class="form-control" required placeholder="ej. Pérez" value="{{ old('apellidos') }}">
              </div>
            </div>
            <div class="col-lg-12">
              <p class="subtitle black mb-8">Correo electrónico</p>
              <div class="input-block">
                <input type="email" name="email" class="form-control" required placeholder="tucorreo@mail.com" value="{{ old('email') }}">
              </div>
            </div>
            <div class="col-lg-12">
              <p class="subtitle black mb-8">Tu mensaje</p>
              <div class="input-block">
                <textarea name="mensaje" class="form-control" required placeholder="Escribe tu mensaje aquí...">{{ old('mensaje') }}</textarea>
              </div>
            </div>
          </div>
          <div class="text-end">
            <button type="submit" class="cus-btn">
              <span class="text">Enviar</span>
            </button>
          </div>
        </form>
      </div>
      <div class="col-lg-6">
        <img src="{{ $imgContacto }}" class="br-24 h-100 object-fit-cover" alt="Bibliotecas Rodantes — atención comunitaria">
      </div>
    </div>
  </div>
</section>

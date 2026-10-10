@php $pc = $paginaContacto ?? null; @endphp
@if($pc->suscribe_titulo ?? $pc->suscribe_texto ?? true)
<section class="jh-contacto-suscribe default-padding">
    <div class="container">
        <div class="jh-contacto-suscribe-box text-center">
            @if($pc->suscribe_titulo ?? null)<h3>{{ $pc->suscribe_titulo }}</h3>@endif
            @if($pc->suscribe_texto ?? null)<p>{{ $pc->suscribe_texto }}</p>@endif
            <form action="{{ route('subscribe') }}" method="post" class="jh-contacto-suscribe-form" id="jh-suscribe-form">
                @csrf
                <div class="input-group stylish-input-group justify-content-center">
                    <input type="email" name="email" class="form-control" required
                        placeholder="{{ $pc->suscribe_placeholder ?? 'Tu correo electrónico' }}">
                    <button type="submit"><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
            <p class="jh-suscribe-msg text-success mt-2" style="display:none;"></p>
            <p class="jh-suscribe-err text-danger mt-2" style="display:none;"></p>
        </div>
    </div>
</section>
@push('scripts')
<script>
$(function() {
    $('#jh-suscribe-form').on('submit', function(e) {
        e.preventDefault();
        var $f = $(this);
        var $ok = $f.closest('.jh-contacto-suscribe-box').find('.jh-suscribe-msg');
        var $err = $f.closest('.jh-contacto-suscribe-box').find('.jh-suscribe-err');
        $ok.hide(); $err.hide();
        $.ajax({
            url: $f.attr('action'),
            method: 'POST',
            data: $f.serialize(),
            success: function(res) {
                $ok.text(res.message || '¡Gracias por suscribirte!').show();
                $f[0].reset();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.errors?.email?.[0] || xhr.responseJSON?.message || 'No se pudo suscribir.';
                $err.text(msg).show();
            }
        });
    });
});
</script>
@endpush
@endif

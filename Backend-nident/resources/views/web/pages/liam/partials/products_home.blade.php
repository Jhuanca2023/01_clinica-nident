@php
    $hayProductos = count($productos ?? []) > 0;
@endphp

{{-- Si no hay productos destacados, no se muestra la sección al público.
     Se mantiene visible para el admin autenticado para poder editar el título. --}}
@if($hayProductos || auth()->check())
<section class="latest-products spad">
    <div class="container lv-container-wide">
        @php
            $secSubtitulo = $seccionDestacada->seccion_subtitulo ?? 'Mayorista · stock permanente';
            $secTitulo    = $seccionDestacada->seccion_titulo    ?? 'Líneas Valento';
            $tituloPartes = explode(' ', $secTitulo, 2);
        @endphp

        <div class="lv-section-title wow fadeInDown" data-wow-duration="0.7s" data-wow-delay="0.1s" style="position:relative; display:inline-block; width:100%; text-align:center;">
            <span class="lv-subtitle" id="lv-sec-subtitulo">{{ $secSubtitulo }}</span>
            <h2 id="lv-sec-titulo">
                <span class="lv-accent">{{ $tituloPartes[0] }} </span>{{ $tituloPartes[1] ?? '' }}
            </h2>

            @auth
            {{-- Lápiz CMS inline — solo visible para admin autenticado --}}
            <button id="lv-cms-edit-btn"
                title="Editar sección"
                onclick="lvCmsToggle()"
                style="
                    position:absolute; top:0; right:0;
                    background:#1e3a5f; color:#fff; border:none;
                    border-radius:6px; width:32px; height:32px;
                    cursor:pointer; display:flex; align-items:center;
                    justify-content:center; font-size:15px; opacity:0;
                    transition:opacity .2s, background .15s; z-index:10;
                ">
                <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>

            <div id="lv-cms-panel" style="
                display:none; position:absolute; top:40px; left:50%; transform:translateX(-50%);
                background:#1e3a5f; border-radius:10px; padding:16px 20px;
                width:340px; max-width:90vw; z-index:100; box-shadow:0 8px 32px rgba(0,0,0,.25);
                text-align:left;
            ">
                <p style="color:rgba(255,255,255,.5); font-size:11px; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Subtítulo</p>
                <input id="lv-inp-sub" type="text" value="{{ $secSubtitulo }}"
                    placeholder="ej: Moda que impacta"
                    style="width:100%; box-sizing:border-box; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
                           color:#fff; border-radius:6px; padding:8px 10px; font-size:13px; margin-bottom:10px; outline:none;" />
                <p style="color:rgba(255,255,255,.5); font-size:11px; text-transform:uppercase; letter-spacing:1px; margin:0 0 4px;">Título principal</p>
                <input id="lv-inp-titulo" type="text" value="{{ $secTitulo }}"
                    placeholder="ej: Nuestra Colección"
                    style="width:100%; box-sizing:border-box; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
                           color:#fff; border-radius:6px; padding:8px 10px; font-size:13px; margin-bottom:6px; outline:none;" />
                <p style="color:rgba(255,255,255,.35); font-size:11px; margin:0 0 12px;">La primera palabra se mostrará en rojo</p>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button onclick="lvCmsToggle()" style="background:rgba(255,255,255,.1); color:rgba(255,255,255,.7); border:1px solid rgba(255,255,255,.2); border-radius:6px; padding:6px 14px; font-size:12px; cursor:pointer;">
                        Cancelar
                    </button>
                    <button id="lv-cms-save-btn" onclick="lvCmsSave()" style="background:#c0392b; color:#fff; border:none; border-radius:6px; padding:6px 16px; font-size:12px; cursor:pointer; font-weight:600;">
                        <i class="fa fa-save" aria-hidden="true"></i> Guardar
                    </button>
                </div>
            </div>
            @endauth
        </div>

        @if($hayProductos)
        <div class="row lv-products-grid" id="product-list">
            @foreach($productos as $producto)
                <div class="col-6 col-md-4 col-lg-3 lv-products-grid__item mb-4">
                    @include('web.partials.liam.product_card', ['producto' => $producto])
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ url('/tienda') }}" class="primary-btn">Ver catálogo completo</a>
        </div>
        @else
        {{-- Solo el admin llega aquí (sin productos). Aviso para editar/agregar. --}}
        <div class="text-center" style="padding:24px 0; color:#94a3b8; font-size:14px;">
            <p style="margin:0;">No hay productos destacados. Esta sección está oculta para los visitantes.</p>
        </div>
        @endif
    </div>
</section>
@endif

@auth
<script>
(function () {
    var btn   = document.getElementById('lv-cms-edit-btn');
    var panel = document.getElementById('lv-cms-panel');
    var section = btn ? btn.closest('.lv-section-title') : null;

    if (!btn || !section) return;

    section.addEventListener('mouseenter', function () { btn.style.opacity = '1'; });
    section.addEventListener('mouseleave', function () { if (!panel.style.display || panel.style.display === 'none') btn.style.opacity = '0'; });

    window.lvCmsToggle = function () {
        var open = panel.style.display === 'block';
        panel.style.display = open ? 'none' : 'block';
        btn.style.background = open ? '#1e3a5f' : '#c0392b';
        btn.style.opacity = '1';
    };

    window.lvCmsSave = function () {
        var saveBtn = document.getElementById('lv-cms-save-btn');
        var titulo    = document.getElementById('lv-inp-titulo').value.trim();
        var subtitulo = document.getElementById('lv-inp-sub').value.trim();
        if (!titulo) return;

        saveBtn.disabled = true;
        saveBtn.textContent = 'Guardando...';

        fetch('{{ url("/web_home_coleccion_seccion") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ seccion_titulo: titulo, seccion_subtitulo: subtitulo }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var partes = titulo.split(' ');
                var p1 = partes[0], p2 = partes.slice(1).join(' ');
                var h2 = document.getElementById('lv-sec-titulo');
                h2.innerHTML = '<span class="lv-accent">' + p1 + ' </span>' + p2;
                document.getElementById('lv-sec-subtitulo').textContent = subtitulo;
                lvCmsToggle();
            }
        })
        .catch(function (e) { console.error(e); })
        .finally(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa fa-save"></i> Guardar';
        });
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.style.display === 'block') lvCmsToggle();
    });

    document.addEventListener('click', function (e) {
        if (panel.style.display === 'block' && !panel.contains(e.target) && e.target !== btn) {
            lvCmsToggle();
        }
    });
})();
</script>
@endauth

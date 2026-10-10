@php
    $rsTitle = 'Seguimiento de reclamo';
    $rsDescription = 'Consulta el estado de tu reclamo en Detalles Rossety.';
@endphp
@push('head')
@vite(['resources/sass/web_reclamos_estado.scss'])
@endpush
@include('web.rossety.partials.page_open')

<section class="rs-pagehero">
    <div class="rs-container">
        <div class="rs-crumbs"><a href="{{ route('home') }}">Inicio</a><span>/</span><a href="{{ route('liam.reclamaciones') }}">Libro de Reclamaciones</a><span>/</span><span>Seguimiento</span></div>
        <h1 class="rs-pagehero__title">Seguimiento de reclamo</h1>
        <p class="rs-pagehero__text">Consulta el estado de tu solicitud con tu número de reclamo o documento.</p>
    </div>
</section>
<main>
<!-- Sprite SVG mínimo para que funcionen los <use href="#ico-..."> -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
    <symbol id="ico-file" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></symbol>
    <symbol id="ico-check-circle" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol>
    <symbol id="ico-warning" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></symbol>
    <symbol id="ico-info" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></symbol>
    <symbol id="ico-search" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 4a7.5 7.5 0 006.15 12.65z"/></symbol>
</svg>
<style>
.lr-track-search-row { display: flex; gap: 1rem; align-items: flex-start; }
.lr-track-search-row > * { flex: 1; }
.lr-track-search-btn { flex: 0 0 auto !important; height: 3.2rem; }
.lr-track-input-group { position: relative; display: flex; flex-direction: column; width: 100%; }
.lr-track-input.is-invalid {
    border-color: #dc3545 !important;
    background-image: none !important;
}
.lr-track-input.is-valid {
    border-color: #3bb77e !important;
    padding-right: 2.5rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233bb77e' stroke-width='2.5'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px;
}
.lr-track-error-msg {
    color: #dc3545;
    font-size: 11px;
    margin-top: 5px;
    min-height: 1.2rem;
    font-weight: 600;
}
@media (max-width: 768px) {
    .lr-track-search-row { flex-direction: column; }
    .lr-track-search-btn { width: 100%; }
}
</style>

<div class="lr-track-wrapper">
    <div class="lr-track-container">

        <div style="margin-bottom: 2.5rem;">
            <a href="{{ route('libro.reclamos') }}" class="lr-back-link">
                <svg viewBox="0 0 36 24" class="lr-ico" style="width:2.5rem;height:1.2rem;stroke-width:3;"><path d="M34 12H2m0 0l8-8m-8 8l8 8"/></svg>
                Regresar al Libro de Reclamaciones
            </a>
        </div>

        <div class="lr-track-title">
            <h1>Consultar Estado de Reclamo</h1>
            <p>Ingresa el número de reclamo <strong>(REC-123)</strong> o tu número de documento para ver el estado.</p>
        </div>

        <div class="lr-track-search-card">
            <form method="POST" action="{{ route('libro.reclamos.estado') }}">
                @csrf
                <div class="lr-track-search-label">Datos para la búsqueda</div>
                <div class="lr-track-search-row">
                    <div class="lr-track-input-group">
                        <input type="text"
                               id="track_codigo"
                               name="codigo"
                               class="lr-track-input"
                               placeholder="Número de reclamo (ej: REC-0001)"
                               value="{{ old('codigo', $codigo) }}">
                        <div class="lr-track-error-msg"></div>
                    </div>

                    <div class="lr-track-input-group">
                        <input type="text"
                               id="track_documento"
                               name="documento"
                               class="lr-track-input"
                               placeholder="DNI / RUC / CE"
                               value="{{ old('documento', $documento) }}">
                        <div class="lr-track-error-msg"></div>
                    </div>

                    <button type="submit" class="lr-track-search-btn">
                        <svg class="lr-ico lr-ico-lg"><use href="#ico-search"/></svg>
                        Buscar
                    </button>
                </div>
                <p class="lr-track-search-help">
                    Puedes usar <strong>solo el número de reclamo</strong> o <strong>solo tu documento</strong>. Si colocas ambos, se validarán juntos.
                </p>
            </form>

            @if($mensaje)
                <div class="lr-track-alert">
                    {{ $mensaje }}
                </div>
            @endif
        </div>

        @if($reclamos->isNotEmpty())
            @foreach($reclamos as $index => $reclamo)
                @php
                    $numeroRec = 'REC-' . str_pad($reclamo->id_web_reclamos, 4, '0', STR_PAD_LEFT);
                    $estado    = $reclamo->estado ?? 'pendiente';
                    $mapEstado = [
                        'pendiente'  => ['label' => 'Pendiente',  'class' => 'lr-tag-warning'],
                        'en_proceso' => ['label' => 'En revisión', 'class' => 'lr-tag-warning'],
                        'resuelto'   => ['label' => 'Resuelto',  'class' => 'lr-tag-success'],
                        'rechazado'  => ['label' => 'Rechazado', 'class' => 'lr-tag-danger'],
                    ];
                    $cfg = $mapEstado[$estado] ?? $mapEstado['pendiente'];
                    $fechaReg = $reclamo->fecha_registro ? \Carbon\Carbon::parse($reclamo->fecha_registro)->format('d/m/Y H:i') : null;
                    $nombreCompleto = trim(($reclamo->nombres ?? '') . ' ' . ($reclamo->apellido_paterno ?? '') . ' ' . ($reclamo->apellido_materno ?? ''));
                @endphp

                <div class="lr-track-result-card {{ $index === 0 ? 'open' : '' }}" id="reclamo-{{ $reclamo->id_web_reclamos }}">
                    
                    {{-- CABECERA ACORDEON (MODO LISTA) --}}
                    <div class="lr-track-accordion-header" onclick="this.parentElement.classList.toggle('open')">
                        <div class="lr-track-icon">
                            <svg class="lr-ico"><use href="#ico-file"/></svg>
                        </div>
                        <div class="lr-track-header-main">
                            <div class="lr-track-header-title">
                                Reclamo <span>{{ $numeroRec }}</span>
                            </div>
                            <div class="lr-track-header-sub">
                                Registrado el {{ $fechaReg }}
                            </div>
                        </div>
                        <span class="lr-tag-pill {{ $cfg['class'] }}">
                            {{ $cfg['label'] }}
                        </span>
                        <svg viewBox="0 0 24 24" class="lr-arrow-toggle" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>

                    {{-- CUERPO COLAPSIBLE --}}
                    <div class="lr-track-accordion-body">
                        <div class="lr-track-inner-content">
                            
                            <div class="lr-track-status-line">
                                <div class="lr-track-timeline">
                                    @php
                                        $stepIndex = [
                                            'registrado' => 0,
                                            'pendiente'  => 0,
                                            'en_proceso' => 1,
                                            'resuelto'   => 2,
                                            'rechazado'  => 1,
                                        ][$estado] ?? 0;
                                    @endphp

                                    {{-- PASO 1 --}}
                                    <div class="lr-track-timeline-step">
                                        @php $step1Class = $stepIndex > 0 ? 'done' : 'active'; @endphp
                                        <div class="lr-track-circle {{ $step1Class }}">
                                            <svg class="lr-ico"><use href="#ico-check-circle"/></svg>
                                        </div>
                                        <div class="lr-track-line {{ $stepIndex >= 1 ? 'done' : '' }}"></div>
                                        <div class="lr-track-step-label">
                                            <strong style="color:{{ $stepIndex >= 0 ? 'var(--lr-primary)' : '' }}">Registrado</strong>
                                            <span>Completado</span>
                                        </div>
                                    </div>

                                    {{-- PASO 2 --}}
                                    <div class="lr-track-timeline-step">
                                        @php $step2Class = $stepIndex > 1 ? 'done' : ($stepIndex >= 1 ? 'active' : ''); @endphp
                                        <div class="lr-track-circle {{ $step2Class }}">
                                            @if($stepIndex > 1)
                                                <svg class="lr-ico"><use href="#ico-check-circle"/></svg>
                                            @elseif($stepIndex >= 1)
                                                <svg class="lr-ico"><use href="#ico-warning"/></svg>
                                            @endif
                                        </div>
                                        <div class="lr-track-line {{ $estado === 'resuelto' ? 'done' : '' }}"></div>
                                        <div class="lr-track-step-label">
                                            <strong style="color:{{ $stepIndex >= 1 ? 'var(--lr-primary)' : '' }}">En Revisión</strong>
                                            <span>{{ $stepIndex > 1 ? 'Completado' : ($stepIndex >= 1 ? 'En proceso' : 'Pendiente') }}</span>
                                        </div>
                                    </div>

                                    {{-- PASO 3 --}}
                                    <div class="lr-track-timeline-step">
                                        @php $step3Class = $estado === 'resuelto' ? 'done active' : ''; @endphp
                                        <div class="lr-track-circle {{ $step3Class }}">
                                            @if($estado === 'resuelto')
                                                <svg class="lr-ico"><use href="#ico-check-circle"/></svg>
                                            @endif
                                        </div>
                                        <div class="lr-track-step-label">
                                            <strong style="color:{{ $estado === 'resuelto' ? 'var(--lr-primary)' : '' }}">Resultado</strong>
                                            <span>{{ $estado === 'resuelto' ? 'Disponible' : 'Pendiente' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="lr-track-alert">
                                    Tu reclamo ha sido registrado y está {{ $cfg['label'] === 'Pendiente' ? 'pendiente de revisión.' : strtolower($cfg['label']) . '.' }}
                                </div>
                            </div>

                            <div class="lr-track-grid">
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Tipo de Solicitud</div>
                                    <div class="lr-track-field-value">
                                        {{ $reclamo->tipo_solicitud === 'complaint' ? 'Queja' : 'Reclamo' }}
                                    </div>
                                </div>
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Fecha de Registro</div>
                                    <div class="lr-track-field-value">{{ $fechaReg ?? '—' }}</div>
                                </div>
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Documento</div>
                                    <div class="lr-track-field-value">
                                        {{ $reclamo->tipo_documento }}: {{ $reclamo->numero_documento }}
                                    </div>
                                </div>
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Nombre Completo</div>
                                    <div class="lr-track-field-value">{{ $nombreCompleto ?: '—' }}</div>
                                </div>
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Teléfono</div>
                                    <div class="lr-track-field-value">{{ $reclamo->telefono ?? '—' }}</div>
                                </div>
                                <div class="lr-track-field">
                                    <div class="lr-track-field-label">Email</div>
                                    <div class="lr-track-field-value">{{ $reclamo->email ?? '—' }}</div>
                                </div>
                            </div>

                            @if(!empty($reclamo->comentario_atencion))
                                <div class="lr-response-wrapper">
                                    <div class="lr-response-header">
                                        <svg class="lr-ico lr-ico-lg"><use href="#ico-check-circle"/></svg>
                                        Respuesta de la Institución
                                    </div>
                                    <div class="lr-response-box">
                                        <div class="lr-response-content">
                                            {!! nl2br(e($reclamo->comentario_atencion)) !!}
                                        </div>
                                        @if(!empty($reclamo->fecha_atencion))
                                            <div class="lr-response-date">
                                                Respuesta enviada el {{ \Carbon\Carbon::parse($reclamo->fecha_atencion)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!empty($reclamo->detalles_solicitud))
                                <div class="lr-details-wrapper">
                                    <div class="lr-track-field-label">Detalles de la Solicitud</div>
                                    <div class="lr-details-box">
                                        {{ $reclamo->detalles_solicitud }}
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('track_codigo');
    const docInput = document.getElementById('track_documento');
    const form = document.querySelector('form');

    function validateTrackField(el) {
        const val = el.value.trim();
        const errorDiv = el.nextElementSibling;
        if (val === '') {
            el.classList.remove('is-invalid', 'is-valid');
            return true;
        }

        let isValid = true;
        let message = '';

        if (el.name === 'codigo') {
            isValid = /^REC-\d+$/i.test(val);
            if (!isValid) message = 'Formato invalido (Ej: REC-0001)';
        } else if (el.name === 'documento') {
            // Si tiene letras y parece DNI/RUC (solo números)
            if (/[a-zA-Z]/.test(val) && val.length <= 11) {
                isValid = false;
                message = 'El campo requiere numeros (DNI/RUC)';
            } else {
                isValid = /^[a-zA-Z0-9]{8,20}$/.test(val);
                if (!isValid) message = 'Documento invalido';
            }
        }

        if (!isValid) {
            el.classList.add('is-invalid');
            el.classList.remove('is-valid');
            if (errorDiv) errorDiv.textContent = message;
        } else {
            el.classList.remove('is-invalid');
            el.classList.add('is-valid');
        }
        return isValid;
    }

    [codeInput, docInput].forEach(el => {
        el.addEventListener('input', () => {
            if (el.name === 'documento') {
                // Si el usuario empieza a escribir algo que parece DNI/RUC, podríamos restringir, 
                // pero dejaremos que el mensaje de error aparezca si pone letras.
            }
            validateTrackField(el);
        });
    });

    form.addEventListener('submit', function(e) {
        const isCodeValid = validateTrackField(codeInput);
        const isDocValid = validateTrackField(docInput);
        const hasContent = codeInput.value.trim() !== '' || docInput.value.trim() !== '';

        if (!isCodeValid || !isDocValid || !hasContent) {
            e.preventDefault();
            if (!hasContent) alert('Ingresa al menos un dato para buscar.');
        }
    });
});
</script>
@if($reclamos->isNotEmpty())
<script>
  document.addEventListener('DOMContentLoaded', function () {
    console.log('libro_reclamos_estado', {
      codigo: @json($codigo),
      documento: @json($documento),
      reclamoEncontrado: true
    });
  });
</script>
@else
<script>
  document.addEventListener('DOMContentLoaded', function () {
    console.log('libro_reclamos_estado', {
      codigo: @json($codigo),
      documento: @json($documento),
      reclamoEncontrado: false
    });
  });
</script>
@endif
@endpush
@include('web.rossety.partials.page_close')

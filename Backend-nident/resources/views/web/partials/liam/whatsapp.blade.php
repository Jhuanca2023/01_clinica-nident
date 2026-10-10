@php $waConfig = \App\Models\WebWhatsappConfig::first(); @endphp
@if(!$waConfig || $waConfig->Activo === 'S')
<script>
window.HQ_WA = {
  numero:   '{{ $waConfig?->wa_numero ?? "51964381275" }}',
  mensaje:  @json($waConfig?->wa_mensaje ?? 'Hola Valento, quiero cotizar ropa laboral.'),
  burbuja1: @json($waConfig?->wa_burbuja_linea1 ?? '¿Cotizamos ropa laboral, promocional o deportiva?'),
  burbuja2: @json($waConfig?->wa_burbuja_linea2 ?? 'Escríbenos y te atendemos al instante.'),
  label:    @json($waConfig?->wa_label ?? 'WhatsApp'),
  badge:    '{{ $waConfig?->wa_badge ?? "1" }}',
  delay:    {{ $waConfig?->wa_delay_segundos ?? 2.5 }},
  activo:   {{ ($waConfig && $waConfig->Activo === 'S') ? 'true' : 'false' }},
};
</script>
<script src="{{ asset('temp02/js/whatsapp-float.js') }}?v={{ file_exists(public_path('temp02/js/whatsapp-float.js')) ? filemtime(public_path('temp02/js/whatsapp-float.js')) : time() }}"></script>
@endif

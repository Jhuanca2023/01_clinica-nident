{{-- Widget Producto Live Search (pls-) --}}
@php
    $plsVer = static fn (string $rel) => file_exists(public_path($rel)) ? filemtime(public_path($rel)) : time();
@endphp
<link rel="stylesheet" href="{{ asset('temp02/css/pls-live-search.css') }}?v={{ $plsVer('temp02/css/pls-live-search.css') }}">
<script>
window.PLS_LIVE_SEARCH_CONFIG = {
  input: '#lv-search-input',
  form: '.lv-header__search-form',
  trigger: '.lv-search-trigger',
  endpoint: @json(route('products.search')),
  resultsUrl: @json(url('/tienda')),
  limit: 8,
  minChars: 2,
  debounceMs: 280
};
</script>
<script src="{{ asset('temp02/js/pls-live-search.js') }}?v={{ $plsVer('temp02/js/pls-live-search.js') }}"></script>
<script>
(function () {
  function start() {
    var box = document.querySelector('.lv-header__search');
    if (box) box.classList.add('pls-ready');
    if (!window.ProductoLiveSearch) {
      console.warn('[pls] script no cargó');
      return;
    }
    if (window.ProductoLiveSearch._booted) return;
    window.ProductoLiveSearch._booted = true;
    window.ProductoLiveSearch.init(window.PLS_LIVE_SEARCH_CONFIG || {});
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>

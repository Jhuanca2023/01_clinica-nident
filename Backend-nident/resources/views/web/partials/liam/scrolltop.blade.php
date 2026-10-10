<button type="button" class="lv-scrolltop" id="lv-scrolltop" aria-label="Volver arriba">
    <svg class="lv-scrolltop__ring" viewBox="0 0 48 48" aria-hidden="true">
        <circle class="lv-scrolltop__track" cx="24" cy="24" r="21" pathLength="100"/>
        <circle class="lv-scrolltop__bar" cx="24" cy="24" r="21" pathLength="100"/>
    </svg>
    <span class="lv-scrolltop__pct" aria-hidden="true">0%</span>
    <i class="fa fa-arrow-up lv-scrolltop__arrow" aria-hidden="true"></i>
</button>
<script src="{{ asset('temp02/js/scrolltop.js') }}?v={{ file_exists(public_path('temp02/js/scrolltop.js')) ? filemtime(public_path('temp02/js/scrolltop.js')) : time() }}"></script>

    @include('web.rossety.partials.footer')
    <a class="rs-wa-float js-wa js-wa-float" href="#" aria-label="Escríbenos por WhatsApp">
        <span class="rs-wa-float__pulse"></span>
        <span class="rs-wa-float__icon"><i class="fa fa-whatsapp" aria-hidden="true"></i></span>
        <span class="rs-wa-float__label"><b>¿Buscas un detalle especial?</b><small>Escríbenos y te atendemos al instante.</small></span>
    </a>
    <button class="rs-top-btn js-top" type="button" aria-label="Subir"><i class="ph-bold ph-caret-up"></i></button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    @include('web.rossety.partials.catalog')
    <script src="{{ asset('rossety/assets/js/rossety/core.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cms.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cart.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/animations.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/cursor.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/footer.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/loader.js') }}"></script>
    <script src="{{ asset('rossety/assets/js/rossety/laravel.js') }}"></script>
    @stack('scripts')
</body>
</html>

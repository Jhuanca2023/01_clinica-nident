@php
    $bannerBg = $bannerImagenUrl
        ?? \Helpers::cmsBannerUrl(null);
@endphp
<div class="breadcrumb-area text-center text-light jh-page-banner"
    style="background-image: url('{{ $bannerBg }}');">
    <div class="jh-page-banner-overlay"></div>
    <div class="box-table">
        <div class="box-cell">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <h1>{{ $bannerTitulo ?? 'Página' }}</h1>
                        @if(!empty($bannerSubtitulo))
                        <p>{{ $bannerSubtitulo }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

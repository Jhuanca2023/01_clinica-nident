@php
    $esYoutube = ($v->tipo_video ?? '') === 'youtube' || !empty($v->url_youtube);
    $embedId = $esYoutube ? Helpers::youtubeEmbedId($v->url_youtube ?? '') : null;
    $poster = !empty($v->url_imagen_portada) ? Helpers::cmsAssetUrl($v->url_imagen_portada) : null;
    $videoUrl = !$esYoutube && !empty($v->url_video) ? Helpers::cmsAssetUrl($v->url_video) : null;
    $cardId = 'jh-video-' . ($v->id ?? uniqid());
@endphp
<div class="col-lg-4 col-md-6 mb-30">
    <div class="jh-video-card" data-video-card="{{ $cardId }}">
        <div class="jh-video-thumb">
            @if($poster && ($embedId || $videoUrl))
            <div class="jh-video-poster" data-poster-for="{{ $cardId }}">
                <img src="{{ $poster }}" alt="{{ $v->titulo ?? 'Video' }}">
                <button type="button" class="jh-video-play-btn" aria-label="Reproducir video"
                    data-play-target="{{ $cardId }}">
                    <i class="fas fa-play"></i>
                </button>
            </div>
            <div class="jh-video-player-wrap is-hidden" data-player="{{ $cardId }}">
            @else
            <div class="jh-video-player-wrap" data-player="{{ $cardId }}">
            @endif
                @if($embedId)
                <div class="jh-video-embed" @if($poster) data-yt-lazy="{{ $embedId }}" @endif>
                    @if(!$poster)
                    <iframe src="https://www.youtube.com/embed/{{ $embedId }}?rel=0"
                        title="{{ $v->titulo ?? 'Video' }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy"></iframe>
                    @endif
                </div>
                @elseif($videoUrl)
                <video controls playsinline preload="metadata" @if($poster) poster="{{ $poster }}" @endif>
                    <source src="{{ $videoUrl }}" type="video/mp4">
                </video>
                @else
                <div class="jh-video-placeholder">
                    <i class="fas fa-video"></i>
                    <span>Sin fuente de video</span>
                </div>
                @endif
            </div>
        </div>
        <div class="jh-video-info">
            @if($v->tag ?? null)<span class="jh-video-tag">{{ $v->tag }}</span>@endif
            <h4>{{ $v->titulo }}</h4>
            @if($v->descripcion ?? null)<p>{{ $v->descripcion }}</p>@endif
        </div>
    </div>
</div>

@extends('web.base_shop')
@section('title', $metaData->titulo_pagina ?? 'Legales | Valento')
@section('meta_description', $metaData->descripcion_pagina ?? '')
@section('head_page')
<style>
.doc-page{padding:36px 0 70px}
.doc-breadcrumb{font-family:'Montserrat',sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#8C6B2E;margin-bottom:36px;display:flex;align-items:center;gap:8px}
.doc-breadcrumb a{color:#8C6B2E;text-decoration:none}
.doc-breadcrumb a:hover{text-decoration:underline}
.doc-title{text-align:center;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:clamp(28px,4vw,48px);font-weight:700;letter-spacing:5px;color:#1a1a1a;text-transform:uppercase;margin-bottom:36px}
.doc-date{font-family:'Montserrat',sans-serif;font-size:11px;color:#aaa;text-align:center;margin-bottom:36px;letter-spacing:1px}
.doc-divider{border:none;border-top:1px solid #eee;margin:36px 0}
.doc-body{max-width:900px;margin:0 auto}
.doc-body p{font-family:'Montserrat',sans-serif;font-size:14px;color:#333;line-height:1.9;text-align:justify;margin-bottom:18px}
.doc-body h2{font-family:'Montserrat',sans-serif;font-size:16px;font-weight:700;color:#1a1a1a;margin:32px 0 12px;letter-spacing:.5px}
.doc-body ul{margin:0 0 18px 22px;font-family:'Montserrat',sans-serif;font-size:14px;color:#333;line-height:1.8}
.doc-body a{color:#8C6B2E}
</style>
@endsection

@php
    $tipo  = $legalTipo ?? 'terminos';
    $doc   = ($terminosData ?? collect())->first();
    $titulo = match ($tipo) {
        'privacidad'    => 'Política de Privacidad',
        'reclamaciones' => 'Libro de Reclamaciones',
        default         => 'Términos y Condiciones',
    };
    $contenido = $doc ? ($doc->descripcion ?? '') : '';
    $fechaDoc = $doc->updated_at ?? $doc->fecha_actualizacion ?? null;
    $fechaTxt = $fechaDoc
        ? \Illuminate\Support\Carbon::parse($fechaDoc)->locale('es')->translatedFormat('F Y')
        : 'Enero 2025';
@endphp

@section('content')
@include('web.partials.liam.header')

<main class="doc-page">
    <div class="container">
        <div class="doc-breadcrumb">
            <a href="{{ url('/') }}">Inicio</a><span>&gt;</span><span>{{ $titulo }}</span>
        </div>
        <h1 class="doc-title">{{ $titulo }}</h1>
        <p class="doc-date">Última actualización: {{ ucfirst($fechaTxt) }} &nbsp;|&nbsp; Valento Stile S.A.C. &nbsp;|&nbsp; RUC: 20608473159</p>
        <hr class="doc-divider">
        <div class="doc-body">
            @if($contenido !== '')
                {!! $contenido !!}
            @else
                <p>El contenido de este documento se encuentra en actualización. Para consultas escriba a <a href="mailto:legal@rossety.pe">legal@rossety.pe</a>.</p>
            @endif
        </div>
    </div>
</main>

@include('web.partials.liam.footer')
@endsection

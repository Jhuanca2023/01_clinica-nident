@extends('web.base_shop')
@section('title', 'Guía de tallas | Valento')
@section('meta_description', 'Guía de tallas Valento — mide tu pecho, cintura y largo para elegir la talla correcta en ropa laboral y textil promocional.')
@section('head_page')
<style>
.guia-page{padding:40px 0 80px}
.guia-breadcrumb{font-family:'Montserrat',sans-serif;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#8C6B2E;margin-bottom:28px;display:flex;align-items:center;gap:8px}
.guia-breadcrumb a{color:#8C6B2E;text-decoration:none}
.guia-title{font-family:Georgia,serif;font-style:italic;font-size:clamp(28px,4vw,42px);letter-spacing:3px;text-align:center;margin-bottom:12px;text-transform:uppercase}
.guia-lead{font-family:'Montserrat',sans-serif;font-size:14px;color:#555;text-align:center;max-width:640px;margin:0 auto 36px;line-height:1.7}
.guia-table-wrap{max-width:720px;margin:0 auto;overflow-x:auto}
.guia-table{width:100%;border-collapse:collapse;font-family:'Montserrat',sans-serif;font-size:13px}
.guia-table th,.guia-table td{border:1px solid #e8e8e8;padding:12px 14px;text-align:center}
.guia-table th{background:#1a1a1a;color:#fff;font-weight:600;letter-spacing:1px;text-transform:uppercase;font-size:11px}
.guia-table tr:nth-child(even) td{background:#fafafa}
.guia-note{max-width:720px;margin:28px auto 0;font-family:'Montserrat',sans-serif;font-size:13px;color:#666;line-height:1.7}
.guia-note a{color:#8C6B2E}
</style>
@endsection

@section('content')
@include('web.partials.liam.header')
<main class="guia-page">
    <div class="container">
        <div class="guia-breadcrumb">
            <a href="{{ url('/') }}">Inicio</a><span>&gt;</span>
            <a href="{{ url('/tienda') }}">Tienda</a><span>&gt;</span>
            <span>Guía de tallas</span>
        </div>
        <h1 class="guia-title">Guía de tallas</h1>
        <p class="guia-lead">
            Medidas orientativas en centímetros para polos y prendas laborales Valento.
            Si cotizas uniformes por volumen, escríbenos por WhatsApp y te ayudamos a definir tallaje.
        </p>
        <div class="guia-table-wrap">
            <table class="guia-table" aria-label="Tabla de tallas Valento">
                <thead>
                    <tr>
                        <th>Talla</th>
                        <th>Pecho</th>
                        <th>Cintura</th>
                        <th>Largo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>S</td><td>86 – 91</td><td>71 – 76</td><td>68</td></tr>
                    <tr><td>M</td><td>96 – 101</td><td>81 – 86</td><td>70</td></tr>
                    <tr><td>L</td><td>106 – 111</td><td>91 – 96</td><td>72</td></tr>
                    <tr><td>XL</td><td>116 – 121</td><td>101 – 106</td><td>74</td></tr>
                    <tr><td>XXL / 2XL</td><td>126 – 131</td><td>111 – 116</td><td>76</td></tr>
                </tbody>
            </table>
        </div>
        <p class="guia-note">
            Las medidas pueden variar según el modelo. La imagen del producto es referencial.
            Cotiza al <a href="https://wa.me/{{ \App\Support\ContactoValento::whatsappNumero() }}" target="_blank" rel="noopener">WhatsApp Valento</a>
            o revisa el <a href="{{ url('/tienda') }}">catálogo</a>.
        </p>
    </div>
</main>
@include('web.partials.liam.footer')
@endsection

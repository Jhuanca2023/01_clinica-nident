@php
    $rows = [];
    if (!empty($ficha->nuevo_caracteristicas)) {
        $decoded = json_decode($ficha->nuevo_caracteristicas, true);
        $rows = is_array($decoded) ? $decoded : [];
    }
@endphp
@if(count($rows) > 0)
<table class="sd-details-table">
    @foreach($rows as $car)
    <tr>
        <td>{{ $car['nombre'] ?? '' }}</td>
        <td>{{ $car['descripcion'] ?? '' }}</td>
    </tr>
    @endforeach
</table>
@endif

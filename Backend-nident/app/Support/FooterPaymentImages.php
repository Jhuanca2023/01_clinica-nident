<?php
namespace App\Support;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FooterPaymentImages
{
    public static function prepare(Request $request): ?array
    {
        if (!$request->has('metodos_pago')) return null;
        $value = $request->input('metodos_pago');
        $rows = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($rows) || !array_is_list($rows)) {
            throw ValidationException::withMessages(['metodos_pago'=>'Los medios de pago deben ser una lista de logos.']);
        }
        $request->validate([
            'pago_imagenes'=>'sometimes|array',
            'pago_imagenes.*'=>'file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,avif|max:5120',
        ]);
        foreach ($request->file('pago_imagenes', []) as $index => $file) {
            if (!isset($rows[$index]) || !is_array($rows[$index])) {
                throw ValidationException::withMessages(['pago_imagenes'=>'No se pudo asociar una imagen a su medio de pago.']);
            }
        }
        $prepared = [];
        foreach ($rows as $index => $row) {
            // Las etiquetas anteriores (BCP/Yape) no son logos.
            if (!is_array($row)) continue;
            $image = trim((string) ($row['imagen'] ?? ''));
            $file = $request->file('pago_imagenes.'.$index);
            if (!$file && $image === '') continue;
            if (!$file && (str_starts_with($image, 'blob:') || str_starts_with($image, 'data:'))) {
                throw ValidationException::withMessages(['pago_imagenes'=>'Vuelve a seleccionar el logo antes de guardar.']);
            }
            $name = trim((string) ($row['nombre'] ?? 'Medio de pago'));
            if (mb_strlen($name) > 120) {
                throw ValidationException::withMessages(['metodos_pago'=>'El nombre de cada medio de pago debe tener hasta 120 caracteres.']);
            }
            $prepared[] = ['id'=>(string)($row['id'] ?? 'logo-'.$index), 'nombre'=>$name ?: 'Medio de pago', 'imagen'=>$image, '_file'=>$file];
        }
        return $prepared;
    }

    public static function store(array $rows, array &$createdPaths): string
    {
        foreach ($rows as &$row) {
            if ($row['_file']) {
                $row['imagen'] = CmsImageStorage::store($row['_file'], 'footer/pagos');
                $createdPaths[] = $row['imagen'];
            }
            unset($row['_file']);
        }
        unset($row);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}

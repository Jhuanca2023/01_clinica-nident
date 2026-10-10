<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnvioService
{

 /**
     * Prepara los parámetros y llama al SP de cálculo de envío.
     * La lógica de determinar el mayor peso se mantiene para preparar los datos.
     *
     * @param array $params Datos necesarios para el SP, incluyendo address, peso, y dimensiones.
     * @return object|null El resultado del cálculo del envío o null si falla.
     */
    public function calcularCostoEnvioSp(array $params): ?object
    {
        $departamento = trim((string) ($params['departamento'] ?? ''));
        $distrito = trim((string) ($params['distrito'] ?? ''));
        $pesoMayor = $params['pesoMayor'] ?? 0;
        $paqueteMedidas = filled($params['paqueteMedidas'] ?? null)
            ? (string) $params['paqueteMedidas']
            : '0';
        $idProductoMayorPeso = $params['idProductoMayorPeso'] ?? null;

        if ($this->esLimaMetropolitana($departamento)) {
            $departamento = 'Lima Metropolitana';
        }

        try {
            $result = DB::select(
                'CALL USP_WEB_OPE_ENVIO_CALCULAR(?, ?, ?, ?, ?)',
                [
                    $departamento,
                    $distrito,
                    $pesoMayor,
                    $paqueteMedidas,
                    $idProductoMayorPeso,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('USP_WEB_OPE_ENVIO_CALCULAR falló: ' . $e->getMessage(), [
                'departamento' => $departamento,
                'distrito' => $distrito,
            ]);
            return null;
        }

        return $result[0] ?? null;
    }

    /**
     * Lógica completa de preparación de datos y llamada al cálculo de envío.
     *
     * @param Request $request La instancia de Request
     * @param bool $usarCart Determina si se debe usar Cart::content() o los datos directos del request.
     * @return object|null
     */
    public function getEnvioData(Request $request, bool $usarCart): ?object
    {
        $pesoMayor = 0;
        $idProductoMayorPeso = null;

        if (Cart::count() > 0) {
            $cartItems = Cart::content();

            $productoMayorPeso = $cartItems->sortByDesc(function ($item) {
                return $item->options->peso_kilogramo ?? 0;
            })->first();

            $idProductoMayorPeso = $productoMayorPeso ? $productoMayorPeso->options->id_producto : null;
            $pesoMayor = $productoMayorPeso ? ($productoMayorPeso->options->peso_kilogramo ?? 0) : 0;
        } else {
            $pesoMayor = $request->input('txt_peso_kilogramo') ?? 0;
            $idProductoMayorPeso = $request->input('txt_id_producto') ?? null;
        }

        if (blank($pesoMayor) && $idProductoMayorPeso) {
            $pesoMayor = DB::table('administracion_producto')
                ->where('id_producto', $idProductoMayorPeso)
                ->value('peso_kilogramo') ?? 0;
        }

        $departamento = $request->input('address_departamento') ?? $request->input('txt_listar_departamentos');
        $distrito = $request->input('address_distrito') ?? $request->input('txt_listar_distritos');
        $paquetaMedidas = $request->input('txt_paqueta_medidas');

        if (blank($paquetaMedidas) && $idProductoMayorPeso) {
            $paquetaMedidas = DB::table('administracion_producto')
                ->where('id_producto', $idProductoMayorPeso)
                ->value('paquete_medidas');
        }

        if (blank($paquetaMedidas) && $request->filled('txt_id_producto')) {
            $paquetaMedidas = DB::table('administracion_producto')
                ->where('id_producto', $request->input('txt_id_producto'))
                ->value('paquete_medidas');
        }

        $paquetaMedidas = filled($paquetaMedidas) ? (string) $paquetaMedidas : '0';
        $pesoMayor = is_numeric($pesoMayor) ? (float) $pesoMayor : 0;

        if (!$departamento || !$distrito) {
            return null;
        }

        $params = [
            'departamento' => $departamento,
            'distrito' => $distrito,
            'pesoMayor' => $pesoMayor,
            'paqueteMedidas' => $paquetaMedidas,
            // 'paqueteDimension' => $paqueteDimencion,
            'idProductoMayorPeso' => $idProductoMayorPeso,
        ];
        return $this->calcularCostoEnvioSp($params);
    }

    /**
     * Departamentos disponibles para envío (SP + fallback ubigeo).
     *
     * @return \Illuminate\Support\Collection<int, object{address_departamento: string}>
     */
    public function listarDepartamentosEnvio()
    {
        try {
            $rows = DB::select('CALL USP_WEB_OPE_ENVIO_DEPARTAMENTOS');
        } catch (\Throwable $e) {
            Log::warning('USP_WEB_OPE_ENVIO_DEPARTAMENTOS falló: ' . $e->getMessage());
            $rows = [];
        }

        if (!empty($rows)) {
            return collect($rows);
        }

        return collect([
            (object) ['address_departamento' => 'Lima Metropolitana'],
        ]);
    }

    /**
     * Distritos por departamento de envío (SP + fallback ubigeo Lima).
     *
     * @return array<int, object{address_distrito: string}>
     */
    public function listarDistritosEnvio(string $departamento): array
    {
        $departamento = trim(urldecode($departamento));
        $departamentoSp = $this->esLimaMetropolitana($departamento)
            ? 'Lima Metropolitana'
            : $departamento;

        try {
            $rows = DB::select('CALL USP_WEB_OPE_ENVIO_DISTRITO(?)', [$departamentoSp]);
            if (!empty($rows)) {
                return $rows;
            }
        } catch (\Throwable $e) {
            Log::warning('USP_WEB_OPE_ENVIO_DISTRITO falló: ' . $e->getMessage(), [
                'departamento' => $departamento,
                'departamento_sp' => $departamentoSp,
            ]);
        }

        if ($this->esLimaMetropolitana($departamento)) {
            return $this->distritosLimaMetropolitanaUbigeo();
        }

        return [];
    }

    private function esLimaMetropolitana(string $departamento): bool
    {
        $norm = strtolower(trim($departamento));
        return in_array($norm, ['lima metropolitana', 'lima', 'provincia de lima'], true);
    }

    /**
     * @return array<int, object{address_distrito: string}>
     */
    private function distritosLimaMetropolitanaUbigeo(): array
    {
        return DB::table('ubigeo_peru_districts')
            ->where('department_id', 15)
            ->where('province_id', 1501)
            ->orderByRaw('CONVERT(name USING utf8mb4) ASC')
            ->get()
            ->map(fn ($row) => (object) ['address_distrito' => trim($row->name)])
            ->values()
            ->all();
    }

}

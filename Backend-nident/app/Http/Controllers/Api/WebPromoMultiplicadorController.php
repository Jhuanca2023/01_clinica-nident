<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPromoMultiplicador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class WebPromoMultiplicadorController extends Controller
{
    public function listar(Request $request)
    {
        if (! Schema::hasTable('web_promo_multiplicador')) {
            return response()->json(['result' => $this->fallbackDefaults()]);
        }

        $soloActivos = filter_var($request->query('solo_activos', false), FILTER_VALIDATE_BOOLEAN);

        $q = WebPromoMultiplicador::query()->orderBy('orden')->orderBy('id_promo_multiplicador');
        if ($soloActivos) {
            $q->where('Activo', 'S');
        }

        $rows = $q->get()->map(fn ($m) => $this->format($m))->values();

        if ($rows->isEmpty()) {
            return response()->json(['result' => $this->fallbackDefaults()]);
        }

        return response()->json(['result' => $rows]);
    }

    public function crear(Request $request)
    {
        if (! Schema::hasTable('web_promo_multiplicador')) {
            return response()->json(['success' => false, 'message' => 'Tabla no disponible'], 500);
        }

        $etiqueta = $this->normalizeEtiqueta($request->input('etiqueta'));

        $request->merge(['etiqueta' => $etiqueta]);
        $request->validate([
            'etiqueta' => [
                'required',
                'string',
                'max:40',
                Rule::unique('web_promo_multiplicador', 'etiqueta'),
            ],
        ]);

        $maxOrden = (int) WebPromoMultiplicador::max('orden');
        $item = WebPromoMultiplicador::create([
            'etiqueta' => $etiqueta,
            'orden' => $maxOrden + 1,
            'Activo' => $request->input('Activo', 'S') === 'N' ? 'N' : 'S',
        ]);

        return response()->json([
            'success' => true,
            'result' => $this->format($item),
            'message' => 'Multiplicador creado',
        ]);
    }

    public function actualizar(Request $request)
    {
        if (! Schema::hasTable('web_promo_multiplicador')) {
            return response()->json(['success' => false, 'message' => 'Tabla no disponible'], 500);
        }

        $request->validate([
            'id_promo_multiplicador' => 'required|integer|exists:web_promo_multiplicador,id_promo_multiplicador',
        ]);

        $item = WebPromoMultiplicador::findOrFail((int) $request->id_promo_multiplicador);

        if ($request->has('etiqueta')) {
            $etiqueta = $this->normalizeEtiqueta($request->input('etiqueta'));
            $request->merge(['etiqueta' => $etiqueta]);
            $request->validate([
                'etiqueta' => [
                    'required',
                    'string',
                    'max:40',
                    Rule::unique('web_promo_multiplicador', 'etiqueta')
                        ->ignore($item->id_promo_multiplicador, 'id_promo_multiplicador'),
                ],
            ]);
            $item->etiqueta = $etiqueta;
        }

        if ($request->has('Activo')) {
            $item->Activo = $request->input('Activo') === 'N' ? 'N' : 'S';
        }

        if ($request->has('orden')) {
            $item->orden = max(0, (int) $request->input('orden'));
        }

        $item->save();

        return response()->json([
            'success' => true,
            'result' => $this->format($item->fresh()),
            'message' => 'Multiplicador actualizado',
        ]);
    }

    public function eliminar(Request $request)
    {
        if (! Schema::hasTable('web_promo_multiplicador')) {
            return response()->json(['success' => false, 'message' => 'Tabla no disponible'], 500);
        }

        $request->validate([
            'id_promo_multiplicador' => 'required|integer|exists:web_promo_multiplicador,id_promo_multiplicador',
        ]);

        WebPromoMultiplicador::destroy((int) $request->id_promo_multiplicador);

        return response()->json(['success' => true, 'message' => 'Multiplicador eliminado']);
    }

    private function normalizeEtiqueta(?string $raw): string
    {
        $v = trim((string) $raw);
        // Conservar mayúsculas típicas de etiqueta (2X, HASTA)
        return mb_strtoupper($v);
    }

    private function format(WebPromoMultiplicador $m): array
    {
        return [
            'id_promo_multiplicador' => (int) $m->id_promo_multiplicador,
            'etiqueta' => $m->etiqueta,
            'orden' => (int) $m->orden,
            'Activo' => $m->Activo,
        ];
    }

    /** Fallback si aún no migró la tabla. */
    private function fallbackDefaults(): array
    {
        return [
            ['id_promo_multiplicador' => 0, 'etiqueta' => 'HASTA', 'orden' => 1, 'Activo' => 'S'],
            ['id_promo_multiplicador' => 0, 'etiqueta' => '2X', 'orden' => 2, 'Activo' => 'S'],
            ['id_promo_multiplicador' => 0, 'etiqueta' => '3X', 'orden' => 3, 'Activo' => 'S'],
            ['id_promo_multiplicador' => 0, 'etiqueta' => 'DESDE', 'orden' => 4, 'Activo' => 'S'],
        ];
    }
}

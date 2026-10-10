<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\ProductoLiveSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints del widget pls- (Producto Live Search).
 * Compatible con la ruta histórica products.search (/web_buscar_productos).
 */
class ProductoLiveSearchController extends Controller
{
    /**
     * Autocomplete (dropdown tipo Google).
     * GET /web_buscar_productos?query=si&limit=8
     * También acepta ?q=
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = $request->input('query', $request->input('q', ''));
        $limit = (int) $request->input('limit', ProductoLiveSearch::DEFAULT_SUGGEST_LIMIT);

        try {
            $payload = ProductoLiveSearch::suggest($query, $limit);

            // Compatibilidad con frontends antiguos que esperaban un array plano
            if ($request->boolean('legacy')) {
                return response()->json($payload['products']);
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'query' => (string) $query,
                'products' => [],
                'suggestions' => [],
                'error' => 'Error en la búsqueda',
            ], 500);
        }
    }

    /**
     * Búsqueda completa (lupa): más resultados + sugerencias cercanas.
     * GET /web_buscar_productos/resultados?q=silicona
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', $request->input('q', ''));
        $limit = (int) $request->input('limit', ProductoLiveSearch::DEFAULT_SEARCH_LIMIT);

        try {
            return response()->json(ProductoLiveSearch::search($query, $limit));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'query' => (string) $query,
                'products' => [],
                'suggestions' => [],
                'error' => 'Error en la búsqueda',
            ], 500);
        }
    }
}

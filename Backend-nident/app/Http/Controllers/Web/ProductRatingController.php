<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductUserRating;
use Illuminate\Support\Facades\Validator;

class ProductRatingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_producto' => 'required|exists:administracion_producto,id_producto',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $producto = Producto::findOrFail($request->id_producto);

        if (!$producto->ratings_enabled) {
            return response()->json(['message' => 'Las calificaciones para este producto no están habilitadas.'], 403);
        }

        $userId = auth()->id();

        $rating = ProductUserRating::updateOrCreate(
            ['id_producto' => $request->id_producto, 'id_usuario' => $userId],
            ['rating' => $request->rating]
        );

        $wasRecentlyCreated = $rating->wasRecentlyCreated;

        return response()->json([
            'message' => $wasRecentlyCreated ? 'Producto calificado con éxito' : 'Calificación actualizada',
            'rating' => $rating,
            'summary' => $producto->fresh()->rating_summary
        ], 200);
    }
}

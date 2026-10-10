<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\WebHomeColeccion;
use App\Models\WebHomeColeccionSeccion;
use App\Support\WebHomeColeccionSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebHomeColeccionController extends Controller
{
    public function listar(Request $request): JsonResponse
    {
        $search = $request->query('search', '');

        if (! WebHomeColeccion::where('Activo', 'S')->exists()) {
            WebHomeColeccionSync::syncFromCatalogFallback(WebHomeColeccionSync::defaultLimit(), true);
        }

        $enHome = WebHomeColeccion::where('Activo', 'S')
            ->pluck('orden', 'id_producto')
            ->toArray();

        $query = Producto::query()
            ->where('Activo', 'S')
            ->select('id_producto', 'nombre', 'precio', 'precio_old', 'url_imagen', 'codigo_producto', 'etiqueta_badge')
            ->when(!empty($search), fn($q) => $q->where('nombre', 'like', "%{$search}%"));

        $productos = $query->orderBy('id_producto')->get()->map(function ($p) use ($enHome) {
            return [
                'id_producto'  => $p->id_producto,
                'nombre'       => $p->nombre,
                'precio'       => $p->precio,
                'precio_old'   => $p->precio_old,
                'url_imagen'   => $p->url_imagen,
                'etiqueta'     => $p->etiqueta_badge ?? null,
                'en_home'      => isset($enHome[$p->id_producto]),
                'orden'        => $enHome[$p->id_producto] ?? null,
            ];
        })->sortByDesc('en_home')->values();

        $seccion = WebHomeColeccionSeccion::firstOrCreate(
            ['id' => 1],
            ['seccion_titulo' => 'Nuestra Colección', 'seccion_subtitulo' => 'Productos destacados']
        );

        return response()->json([
            'success' => true,
            'result'  => [
                'seccion'   => $seccion,
                'productos' => $productos,
                'total_en_home' => count($enHome),
            ],
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['id_producto' => 'required|integer']);

        $idProducto = $request->id_producto;
        $existente  = WebHomeColeccion::where('id_producto', $idProducto)->first();

        if ($existente) {
            $existente->delete();
            $mensaje = 'Producto eliminado del home';
            $enHome  = false;
        } else {
            $maxOrden = WebHomeColeccion::max('orden') ?? 0;
            WebHomeColeccion::create([
                'id_producto' => $idProducto,
                'orden'       => $maxOrden + 1,
                'Activo'      => 'S',
            ]);
            $mensaje = 'Producto agregado al home';
            $enHome  = true;
        }

        return response()->json(['success' => true, 'message' => $mensaje, 'en_home' => $enHome]);
    }

    public function actualizar_seccion(Request $request): JsonResponse
    {
        $request->validate([
            'seccion_titulo'    => 'nullable|string|max:255',
            'seccion_subtitulo' => 'nullable|string|max:255',
        ]);

        $seccion = WebHomeColeccionSeccion::firstOrCreate(['id' => 1]);
        $seccion->update([
            'seccion_titulo'    => $request->input('seccion_titulo', $seccion->seccion_titulo),
            'seccion_subtitulo' => $request->input('seccion_subtitulo', $seccion->seccion_subtitulo),
        ]);

        return response()->json(['success' => true, 'message' => 'Sección actualizada', 'result' => $seccion->fresh()]);
    }

    public function reordenar(Request $request): JsonResponse
    {
        $request->validate(['orden' => 'required|array']);

        foreach ($request->orden as $index => $idProducto) {
            WebHomeColeccion::where('id_producto', $idProducto)->update(['orden' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Orden actualizado']);
    }

    /**
     * Repuebla la colección con los mismos productos que la web muestra por defecto (primeros N activos).
     */
    public function sincronizar(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', WebHomeColeccionSync::defaultLimit());
        $limit = max(1, min($limit, 50));

        WebHomeColeccion::query()->delete();

        $total = WebHomeColeccionSync::syncFromCatalogFallback($limit, false);

        return response()->json([
            'success' => true,
            'message' => "Colección sincronizada: {$total} producto(s)",
            'result'  => ['total' => $total],
        ]);
    }
}

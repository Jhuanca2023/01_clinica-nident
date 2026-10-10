<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\ProductoCategoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductoCategoriaController extends Controller
{
    public function obtener(Request $request): JsonResponse
    {
        $id = $request->id;
        $result = ProductoCategoria::where('id_producto_categoria', $id)->first();
        return response()->json(['success' => true, 'message' => 'Obtener registros!', 'result' => $result ? [$result] : []]);
    }

    public function listar(): JsonResponse
    {
        $select = ['id_producto_categoria', 'nombre', 'url_imagen', 'Activo', 'created_at', 'updated_at'];
        if (Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')) {
            $select[] = 'mostrar_panel_principal';
        }

        $result = ProductoCategoria::query()
            ->select($select)
            ->orderByDesc('id_producto_categoria')
            ->get();

        return response()->json(['success' => true, 'message' => 'Listar registros', 'result' => $result]);
    }

    public function crear(Request $request): JsonResponse
    {
        try {
            $existe = DB::table('administracion_producto_categoria')
                ->where('nombre', $request->nombre)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'La categoría "' . $request->nombre . '" ya existe',
                    'result'  => null
                ], 422);
            }

            // id_producto_categoria no es auto-increment en la BD → lo generamos manualmente
            $nextId = (int) DB::table('administracion_producto_categoria')->max('id_producto_categoria') + 1;

            $mostrarPanel = strtoupper((string) ($request->mostrar_panel_principal ?? 'N')) === 'S' ? 'S' : 'N';

            $payload = [
                'id_producto_categoria' => $nextId,
                'nombre'     => $request->nombre,
                'Activo'     => $request->Activo ?? 'S',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            if (Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')) {
                $payload['mostrar_panel_principal'] = $mostrarPanel;
            }

            $result = ProductoCategoria::create($payload);

            return response()->json(['success' => true, 'message' => 'Registro insertado', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la categoría: ' . $e->getMessage(), 'result' => null], 500);
        }
    }

    public function actualizar(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $cat = ProductoCategoria::find($id);
        if (! $cat) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada', 'result' => null], 404);
        }

        $cat->nombre = $request->nombre;
        $cat->Activo = $request->Activo;
        $cat->updated_at = date('Y-m-d H:i:s');
        if (Schema::hasColumn('administracion_producto_categoria', 'mostrar_panel_principal')
            && $request->has('mostrar_panel_principal')) {
            $cat->mostrar_panel_principal = strtoupper((string) $request->mostrar_panel_principal) === 'S' ? 'S' : 'N';
        }
        $cat->save();

        return response()->json(['success' => true, 'message' => 'Registro actualizado', 'result' => $cat]);
    }

    public function subirImagen(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if (! $id) {
            return response()->json(['success' => false, 'message' => 'ID requerido'], 422);
        }

        if (! $request->hasFile('imagen') || ! $request->file('imagen')->isValid()) {
            return response()->json(['success' => false, 'message' => 'Imagen inválida'], 422);
        }

        $categoria = ProductoCategoria::find($id);
        if ($categoria && $categoria->url_imagen) {
            \App\Support\CmsImageStorage::deleteIfOwned($categoria->url_imagen);
        }

        $file    = $request->file('imagen');
        $path    = \App\Support\CmsImageStorage::store($file, 'categorias');

        ProductoCategoria::where('id_producto_categoria', $id)->update([
            'url_imagen' => $path,
            'updated_at' => date("Y-m-d H:i:s"),
        ]);

        return response()->json(['success' => true, 'message' => 'Imagen subida', 'result' => ['url_imagen' => $path]]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id');

        $tieneProductos = DB::table('administracion_producto')
            ->where('id_producto_categoria', $id)
            ->where('Activo', 'S')
            ->exists();

        if ($tieneProductos) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene productos asociados', 'result' => null], 422);
        }

        $tieneSubcategorias = DB::table('administracion_producto_categoria_sub')
            ->where('id_producto_categoria', $id)
            ->exists();

        if ($tieneSubcategorias) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene subcategorías asociadas', 'result' => null], 422);
        }

        $categoria = ProductoCategoria::find($id);
        if ($categoria && $categoria->url_imagen) {
            \App\Support\CmsImageStorage::deleteIfOwned($categoria->url_imagen);
        }

        $result = ProductoCategoria::where('id_producto_categoria', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Registro eliminado!', 'result' => $result]);
    }

    public function listar_tipo(): JsonResponse
    {
        $result = DB::table('administracion_producto_tipo')->get();
        return response()->json(['success' => true, 'message' => 'Listar registros', 'result' => $result]);
    }
}

<?php

namespace App\Http\Controllers\Api\AppFinanza;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sincronización catálogo y maestros para app MAUI POS (prefijo api/app/finanza).
 *
 * JSON consumido por MauiAppPOS\CatalogSyncService: categories, products,
 * clientes, proveedores (opcional), usuarios (opcional, incluye password bcrypt para login offline).
 */
class SyncController extends Controller
{
    /**
     * GET /api/app/finanza/sync/catalog — Bearer JWT (mismo auth:api que el resto de la API).
     */
    public function catalog(): JsonResponse
    {
        $categories = DB::table('administracion_producto_categoria')->get()->values();
        $products = DB::table('administracion_producto')->get()->values();

        $clientes = DB::table('administracion_cliente')
            ->select([
                'id_cliente',
                DB::raw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) as nombre"),
                DB::raw('ruc as documento'),
                'telefono',
                'email',
                'direccion',
                'Activo',
                'updated_at',
            ])
            ->get()
            ->values();

        $proveedores = $this->pullProveedores();

        $usuarios = DB::table('users')
            ->select(['id', 'name', 'email', 'password', 'Activo'])
            ->get()
            ->values();

        return response()->json([
            'categories' => $categories,
            'products' => $products,
            'categorias' => $categories,
            'productos' => $products,
            'clientes' => $clientes,
            'proveedores' => $proveedores,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Proveedores: solo si existe una tabla conocida con columnas compatibles con el POS.
     * Hoy muchas instalaciones no tienen tabla de proveedores; en ese caso se devuelve [].
     */
    private function pullProveedores(): \Illuminate\Support\Collection
    {
        $candidates = [
            'administracion_proveedor',
            'finanza_proveedor',
            'compras_proveedor',
        ];

        foreach ($candidates as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $cols = Schema::getColumnListing($table);
            if (! in_array('id_proveedor', $cols, true) || ! in_array('razon_social', $cols, true)) {
                continue;
            }

            return DB::table($table)->get()->map(function ($row) use ($cols) {
                return (object) [
                    'id_proveedor' => $row->id_proveedor,
                    'razon_social' => $row->razon_social,
                    'ruc' => in_array('ruc', $cols, true) ? ($row->ruc ?? null) : null,
                    'telefono' => in_array('telefono', $cols, true) ? ($row->telefono ?? null) : null,
                    'email' => in_array('email', $cols, true) ? ($row->email ?? null) : null,
                    'direccion' => in_array('direccion', $cols, true) ? ($row->direccion ?? null) : null,
                    'Activo' => in_array('Activo', $cols, true) ? ($row->Activo ?? 'S') : 'S',
                    'updated_at' => in_array('updated_at', $cols, true) ? ($row->updated_at ?? null) : null,
                ];
            })->values();
        }

        return collect();
    }
}

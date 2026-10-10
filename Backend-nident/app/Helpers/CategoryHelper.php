<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Gloudemans\Shoppingcart\Facades\Cart;
class CategoryHelper
{
    /**
     * Obtiene el menú completo de categorías con sus subcategorías
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function getFullMenu()
    {
        // Obtener categorías principales activas
        $categories = DB::table('administracion_producto_categoria')
            ->select('id_producto_categoria', 'nombre')
            ->where('Activo', 'S')
            ->whereIn('id_producto_categoria', function ($query) {
                $query->select('id_producto_categoria')
                    ->from('administracion_producto')
                    ->where('Activo', 'S')
                    ->distinct();
            })
            ->orderBy('nombre')
            ->get();

        // Obtener subcategorías activas
        $subcategories = DB::table('administracion_producto_categoria_sub')
            ->select('id_producto_categoria_sub', 'id_producto_categoria', 'nombre')
            // ->where('Active', '1')
            ->orderBy('nombre')
            ->get();

        // Organizar subcategorías bajo sus categorías
        return $categories->map(function ($category) use ($subcategories) {
            return [
                'id' => $category->id_producto_categoria,
                'name' => $category->nombre,
                'subcategories' => $subcategories->where('id_producto_categoria', $category->id_producto_categoria)
                    ->map(function ($subcategory) {
                        return [
                            'id' => $subcategory->id_producto_categoria_sub,
                            'name' => $subcategory->nombre
                        ];
                    })->values()
            ];
        });
    }

    /**
     * Obtiene solo las categorías principales
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function getMainCategories()
    {
        return DB::table('administracion_producto_categoria')
            ->select('id_producto_categoria', 'nombre')
            // ->where('Active', '1')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtiene subcategorías por categoría padre
     * 
     * @param int $categoryId
     * @return \Illuminate\Support\Collection
     */
    public static function getSubcategories($categoryId)
    {
        return DB::table('administracion_producto_categoria_sub')
            ->select('id_producto_categoria_sub', 'nombre')
            ->where('id_producto_categoria', $categoryId)
            // ->where('Active', '1')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtiene todas las categorías y subcategorías para el panel de administración
     * 
     * @return array
     */
    public static function getAdminMenu()
    {
        $categories = DB::table('administracion_producto_categoria')
            ->select('id_producto_categoria', 'nombre', 'Active', 'created_at', 'updated_at')
            ->orderBy('nombre')
            ->get();

        $subcategories = DB::table('administracion_producto_categoria_sub')
            ->select('id_producto_categoria_sub', 'id_producto_categoria', 'nombre', 'Active', 'created_at', 'updated_at')
            ->orderBy('nombre')
            ->get();

        return [
            'categories' => $categories,
            'subcategories' => $subcategories
        ];
    }
}
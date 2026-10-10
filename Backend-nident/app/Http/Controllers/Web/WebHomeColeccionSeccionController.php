<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebHomeColeccionSeccion;
use Illuminate\Http\Request;

class WebHomeColeccionSeccionController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'seccion_titulo'    => 'nullable|string|max:255',
            'seccion_subtitulo' => 'nullable|string|max:255',
        ]);

        $seccion = WebHomeColeccionSeccion::firstOrCreate(
            ['id' => 1],
            ['seccion_titulo' => 'Nuestra Colección', 'seccion_subtitulo' => 'Productos destacados']
        );

        $seccion->update([
            'seccion_titulo'    => $request->input('seccion_titulo', $seccion->seccion_titulo),
            'seccion_subtitulo' => $request->input('seccion_subtitulo', $seccion->seccion_subtitulo),
        ]);

        return response()->json(['success' => true, 'message' => 'Sección actualizada']);
    }
}

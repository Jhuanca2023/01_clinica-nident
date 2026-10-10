<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WebPlan;

class WebPlanesController extends Controller
{
    public function listar(): JsonResponse
    {
        $result = WebPlan::orderBy('orden')->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar planes',
            'result'  => $result,
        ]);
    }

    public function obtener(Request $request): JsonResponse
    {
        $result = WebPlan::find($request->id_plan);

        return response()->json([
            'success' => true,
            'message' => 'Obtener plan',
            'result'  => $result,
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $car = $request->input('caracteristicas', []);
        if (is_string($car)) {
            $car = json_decode($car, true) ?? [];
        }

        $result = WebPlan::create([
            'nombre'          => $request->nombre,
            'descripcion'     => $request->descripcion,
            'precio'          => $request->precio ?? 0,
            'precio_nota'     => $request->precio_nota,
            'caracteristicas' => $car,
            'url_whatsapp'    => $request->url_whatsapp,
            'es_destacado'    => $request->es_destacado ? 1 : 0,
            'icono'           => $request->icono,
            'orden'           => $request->orden ?? 99,
            'Activo'          => 'S',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan creado',
            'result'  => $result,
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $id   = $request->input('id_plan');
        $plan = WebPlan::find($id);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan no encontrado', 'result' => null], 404);
        }

        $car = $request->input('caracteristicas', $plan->caracteristicas);
        if (is_string($car)) {
            $car = json_decode($car, true) ?? [];
        }

        $plan->update([
            'nombre'          => $request->nombre          ?? $plan->nombre,
            'descripcion'     => $request->descripcion     ?? $plan->descripcion,
            'precio'          => $request->precio          ?? $plan->precio,
            'precio_nota'     => $request->precio_nota     ?? $plan->precio_nota,
            'caracteristicas' => $car,
            'url_whatsapp'    => $request->url_whatsapp    ?? $plan->url_whatsapp,
            'es_destacado'    => $request->has('es_destacado') ? ($request->es_destacado ? 1 : 0) : $plan->es_destacado,
            'icono'           => $request->icono           ?? $plan->icono,
            'orden'           => $request->orden           ?? $plan->orden,
            'Activo'          => $request->input('Activo', $plan->Activo),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan actualizado',
            'result'  => WebPlan::find($id),
        ]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        WebPlan::where('id_plan', $request->input('id_plan'))->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan eliminado',
            'result'  => null,
        ]);
    }
}

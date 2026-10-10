<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\WebPreguntasFrecuentes;
use Illuminate\Support\Facades\DB;

class WebPreguntasFrecuentesController extends Controller
{
    public function listar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result'  => WebPreguntasFrecuentes::orderBy('orden', 'ASC')->get(),
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'id_pregunta'               => 'required|exists:web_preguntas_frecuentes,id_pregunta',
            'pregunta'                  => 'required|string|max:500',
            'respuesta'                 => 'nullable|string',
            'badge_seccion'             => 'nullable|string|max:200',
            'seccion_titulo'            => 'nullable|string|max:500',
            'telefono_principal_label'  => 'nullable|string|max:150',
            'telefono_principal'        => 'nullable|string|max:50',
            'telefono_callcenter_label' => 'nullable|string|max:150',
            'telefono_callcenter'       => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $registro = WebPreguntasFrecuentes::find($request->id_pregunta);
            $registro->update([
                'pregunta'                  => $request->pregunta,
                'respuesta'                 => $request->respuesta,
                'badge_seccion'             => $request->badge_seccion,
                'seccion_titulo'            => $request->seccion_titulo,
                'telefono_principal_label'  => $request->telefono_principal_label,
                'telefono_principal'        => $request->telefono_principal,
                'telefono_callcenter_label' => $request->telefono_callcenter_label,
                'telefono_callcenter'       => $request->telefono_callcenter,
                'updated_at'                => now(),
            ]);
            $registro->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente.',
                'result'  => $registro,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al actualizar FAQ: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al actualizar el registro.',
            ], 500);
        }
    }

    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'pregunta' => 'required|string|max:500',
            'respuesta' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $first = WebPreguntasFrecuentes::orderBy('orden', 'ASC')->first();
            $maxOrden = WebPreguntasFrecuentes::max('orden') ?? 0;

            $nuevo = WebPreguntasFrecuentes::create([
                'pregunta'                  => $request->pregunta,
                'respuesta'                 => $request->respuesta ?? '',
                'orden'                     => $maxOrden + 1,
                'badge_seccion'             => $first?->badge_seccion ?? '',
                'seccion_titulo'            => $first?->seccion_titulo ?? '',
                'telefono_principal_label'  => $first?->telefono_principal_label ?? '',
                'telefono_principal'        => $first?->telefono_principal ?? '',
                'telefono_callcenter_label' => $first?->telefono_callcenter_label ?? '',
                'telefono_callcenter'       => $first?->telefono_callcenter ?? '',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pregunta creada correctamente.',
                'result'  => $nuevo,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al crear FAQ: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear la pregunta.'], 500);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        $request->validate([
            'id_pregunta' => 'required|exists:web_preguntas_frecuentes,id_pregunta',
        ]);

        DB::beginTransaction();
        try {
            WebPreguntasFrecuentes::find($request->id_pregunta)->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pregunta eliminada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al eliminar FAQ: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
        }
    }

    public function actualizarSeccion(Request $request): JsonResponse
    {
        $request->validate([
            'badge_seccion'             => 'nullable|string|max:200',
            'seccion_titulo'            => 'nullable|string|max:500',
            'telefono_principal_label'  => 'nullable|string|max:150',
            'telefono_principal'        => 'nullable|string|max:50',
            'telefono_callcenter_label' => 'nullable|string|max:150',
            'telefono_callcenter'       => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            WebPreguntasFrecuentes::query()->update([
                'badge_seccion'             => $request->badge_seccion,
                'seccion_titulo'            => $request->seccion_titulo,
                'telefono_principal_label'  => $request->telefono_principal_label,
                'telefono_principal'        => $request->telefono_principal,
                'telefono_callcenter_label' => $request->telefono_callcenter_label,
                'telefono_callcenter'       => $request->telefono_callcenter,
                'updated_at'                => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encabezado de sección actualizado en todos los registros.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stderr')->error('Error al actualizar sección FAQ: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al actualizar la sección.',
            ], 500);
        }
    }
}

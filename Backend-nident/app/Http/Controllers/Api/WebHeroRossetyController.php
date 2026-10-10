<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebHeroCard;
use App\Models\WebHeroSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebHeroRossetyController extends Controller
{
    public function listarSlides(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Slides',
            'result' => WebHeroSlide::query()->orderBy('orden')->get(),
        ]);
    }

    public function guardarSlide(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'orden' => 'nullable|integer',
            'eyebrow' => 'nullable|string|max:120',
            'titulo' => 'required|string|max:300',
            'descripcion' => 'nullable|string|max:400',
            'cta_texto' => 'nullable|string|max:80',
            'cta_url' => 'nullable|string|max:300',
            'url_imagen' => 'nullable|string|max:400',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = ! empty($data['id'])
            ? WebHeroSlide::query()->findOrFail($data['id'])
            : new WebHeroSlide();
        $row->fill($data);
        $row->Activo = $data['Activo'] ?? 'S';
        $row->save();

        return response()->json(['success' => true, 'message' => 'Slide guardado', 'result' => $row]);
    }

    public function eliminarSlide(Request $request): JsonResponse
    {
        WebHeroSlide::query()->where('id', (int) $request->input('id'))->delete();

        return response()->json(['success' => true, 'message' => 'Slide eliminado', 'result' => null]);
    }

    public function listarCards(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tarjetas',
            'result' => WebHeroCard::query()->orderBy('orden')->get(),
        ]);
    }

    public function guardarCard(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'orden' => 'nullable|integer',
            'badge' => 'nullable|string|max:80',
            'titulo' => 'required|string|max:200',
            'precio_texto' => 'nullable|string|max:80',
            'url' => 'nullable|string|max:300',
            'url_imagen' => 'nullable|string|max:400',
            'Activo' => 'nullable|in:S,N',
        ]);
        $row = ! empty($data['id'])
            ? WebHeroCard::query()->findOrFail($data['id'])
            : new WebHeroCard();
        $row->fill($data);
        $row->Activo = $data['Activo'] ?? 'S';
        $row->save();

        return response()->json(['success' => true, 'message' => 'Tarjeta guardada', 'result' => $row]);
    }

    public function eliminarCard(Request $request): JsonResponse
    {
        WebHeroCard::query()->where('id', (int) $request->input('id'))->delete();

        return response()->json(['success' => true, 'message' => 'Tarjeta eliminada', 'result' => null]);
    }
}

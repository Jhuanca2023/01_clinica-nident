<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Branding\AppFaviconService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppFaviconController extends Controller
{
    public function __construct(private AppFaviconService $faviconService)
    {
    }

    public function obtener(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Favicon actual',
            'result' => $this->faviconService->urlsForApi(),
        ]);
    }

    public function actualizar(Request $request): JsonResponse
    {
        $request->validate([
            'favicon' => 'required|file|mimes:svg,png,jpg,jpeg,webp,ico|max:2048',
        ]);

        try {
            $stored = $this->faviconService->storeUpload($request->file('favicon'));

            return response()->json([
                'success' => true,
                'message' => 'Favicon actualizado. Recargue la pestaña del navegador (Ctrl+F5).',
                'result' => array_merge($this->faviconService->urlsForApi(), ['written' => count($stored['files'])]),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'result' => 0,
            ], 422);
        }
    }
}

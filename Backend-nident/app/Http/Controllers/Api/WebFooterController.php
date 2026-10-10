<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\VendedorResource;
use App\Models\WebFooter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebFooterController extends Controller
{
    /**
     * URLs absolutas para el panel admin (SPA en otro puerto no puede adivinar la ruta pública solo con la ruta relativa).
     */
    protected function footerPayloadWithLogoUrls(array $attrs): array
    {
        $menu = ! empty($attrs['logo_menu']) ? (string) $attrs['logo_menu'] : null;
        $foot = ! empty($attrs['logo_footer']) ? (string) $attrs['logo_footer'] : null;

        $attrs['logo_menu_url'] = $menu
            ? (preg_match('#^https?://#i', $menu) ? $menu : asset($menu))
            : null;
        $attrs['logo_footer_url'] = $foot
            ? (preg_match('#^https?://#i', $foot) ? $foot : asset($foot))
            : null;

        $central = ! empty($attrs['url_imagen_central']) ? (string) $attrs['url_imagen_central'] : null;
        $attrs['url_imagen_central_url'] = $central
            ? (preg_match('#^https?://#i', $central) ? $central : asset($central))
            : null;

        return $this->footerPayloadWithLists($attrs);
    }

    /**
     * Normaliza listas JSON del footer para el admin y la vista pública.
     */
    protected function footerPayloadWithLists(array $attrs): array
    {
        if (!empty($attrs['footer_redes']) && is_string($attrs['footer_redes'])) {
            $decoded = json_decode($attrs['footer_redes'], true);
            $attrs['footer_redes'] = is_array($decoded) ? $decoded : [];
        }
        if (!isset($attrs['footer_redes']) || !is_array($attrs['footer_redes'])) {
            $attrs['footer_redes'] = [];
        }

        return $attrs;
    }

    protected function encodeFooterRedes($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
        }

        return null;
    }


    /**
     * @OA\Get(
     *     path="/web_footer/obtener",
     *     tags={"Sitio Web"},
     *     summary="Obtener configuración de footer por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Configuración encontrada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $rows = DB::select('select * from web_footer where id_footer = ?', [$id]);
        $result = array_map(
            fn ($row) => $this->footerPayloadWithLogoUrls((array) $row),
            $rows
        );

        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result,
        ]);
    }
    /**
     * @OA\Get(
     *     path="/web_footer/listar",
     *     tags={"Sitio Web"},
     *     summary="Listar configuraciones de footer",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de configuraciones", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = WebFooter::orderBy('id_footer', 'DESC')
            ->get()
            ->map(fn (WebFooter $m) => $this->footerPayloadWithLogoUrls($m->toArray()))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/web_footer/crear",
     *     tags={"Sitio Web"},
     *     summary="Crear nueva configuración de footer",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"nombre","Activo"},
     *         @OA\Property(property="nombre", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Configuración creada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
		$result = WebFooter::create([
                    'nombre'          => $request->nombre,
                    'created_at'      => date("Y-m-d H:i:s"),
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'Activo'          => $request->Activo,
            
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/web_footer/actualizar",
     *     tags={"Sitio Web"},
     *     summary="Actualizar configuración de footer existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_footer"},
     *         @OA\Property(property="id_footer", type="integer", example=1),
     *         @OA\Property(property="nuestros_horarios", type="string"),
     *         @OA\Property(property="sobre_la_empresa", type="string"),
     *         @OA\Property(property="red_social_facebook", type="string"),
     *         @OA\Property(property="red_social_youtobe", type="string"),
     *         @OA\Property(property="red_social_twitter", type="string"),
     *         @OA\Property(property="red_social_instagram", type="string"),
     *         @OA\Property(property="red_social_linkedin", type="string"),
     *         @OA\Property(property="contacto_direccion", type="string"),
     *         @OA\Property(property="contacto_telefono", type="string"),
     *         @OA\Property(property="contacto_email", type="string"),
     *         @OA\Property(property="url_mapa", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Configuración actualizada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    /**
     * Decodifica un base64 de imagen y lo guarda en disco.
     * Retorna la ruta relativa guardada o null si no hay dato.
     */
    private function storeUploadedImage($file, string $folder, string $disk): ?string
    {
        if (!$file || !$file->isValid()) {
            Log::warning('[WebFooter::storeUploadedImage] archivo inválido', [
                'error' => $file ? $file->getError() : 'no file',
            ]);
            return null;
        }
        $mime = $file->getMimeType() ?: '';
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!in_array($ext, ['jpg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif', 'jfif'], true)) {
            $ext = match (true) {
                str_contains($mime, 'png') => 'png',
                str_contains($mime, 'gif') => 'gif',
                str_contains($mime, 'webp') => 'webp',
                str_contains($mime, 'svg') => 'svg',
                str_contains($mime, 'bmp') => 'bmp',
                str_contains($mime, 'avif') => 'avif',
                default => 'jpg',
            };
        }
        if ($ext === 'jfif') {
            $ext = 'jpg';
        }
        $folder = ltrim(str_replace('storage_/', '', $folder), '/');

        return \App\Support\CmsImageStorage::store($file, $folder ?: 'footer');
    }

    private function saveBase64Image(?string $base64, string $folder, string $disk): ?string
    {
        if (! $base64) return null;

        // Formato: "data:image/png;base64,XXXX" o solo "XXXX"
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $ext     = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $data    = substr($base64, strpos($base64, ',') + 1);
        } else {
            $ext  = 'jpg';
            $data = $base64;
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false) return null;

        $folder = ltrim(str_replace('storage_/', '', $folder), '/');

        return \App\Support\CmsImageStorage::put($folder ?: 'footer', $decoded, $ext);
    }

    /**
     * Subida de logos por multipart (evita JSON gigante en base64 que tumba PHP/sesión).
     */
    public function actualizar_logos(Request $request): JsonResponse
    {
        $request->validate([
            'id_footer'      => 'required|integer|exists:web_footer,id_footer',
            'logo_menu'      => 'nullable|image|mimes:jpg,jpeg,jfif,png,gif,webp,svg,bmp,avif|max:5120',
            'logo_footer'    => 'nullable|image|mimes:jpg,jpeg,jfif,png,gif,webp,svg,bmp,avif|max:5120',
            'imagen_central' => 'nullable|image|mimes:jpg,jpeg,jfif,png,gif,webp,svg,bmp,avif|max:8192',
        ]);

        $id = (int) $request->input('id_footer');
        $row = WebFooter::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado', 'result' => null], 404);
        }

        DB::beginTransaction();
        try {
            $data = ['updated_at' => date('Y-m-d H:i:s')];

            if ($request->hasFile('logo_menu')) {
                \App\Support\CmsImageStorage::deleteIfOwned($row->logo_menu);
                $saved = $this->storeUploadedImage($request->file('logo_menu'), 'storage_/footer', 'public_imagenes');
                if ($saved) {
                    $data['logo_menu'] = $saved;
                }
            }

            if ($request->hasFile('logo_footer')) {
                \App\Support\CmsImageStorage::deleteIfOwned($row->logo_footer);
                $saved = $this->storeUploadedImage($request->file('logo_footer'), 'storage_/footer', 'public_imagenes');
                if ($saved) {
                    $data['logo_footer'] = $saved;
                }
            }

            if ($request->hasFile('imagen_central')) {
                \App\Support\CmsImageStorage::deleteIfOwned($row->url_imagen_central);
                $saved = $this->storeUploadedImage($request->file('imagen_central'), 'storage_/footer', 'public_imagenes');
                if ($saved) {
                    $data['url_imagen_central'] = $saved;
                }
            }

            if (count($data) > 1) {
                WebFooter::where('id_footer', $id)->update($data);
            }
            DB::commit();

            $fresh = WebFooter::find($id);

            return response()->json([
                'success' => true,
                'message' => 'Logos actualizados',
                'result' => $fresh ? $this->footerPayloadWithLogoUrls($fresh->toArray()) : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[WebFooter::actualizar_logos]', ['message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al subir logos', 'result' => null], 500);
        }
    }

    public function actualizar(Request $request) : JsonResponse
    {
        Log::info('[WebFooter::actualizar] REQUEST RECIBIDA', [
            'ip'             => $request->ip(),
            'user_id'        => optional($request->user())->id,
            'id_footer'      => $request->input('id_footer'),
            'has_logo_menu'  => ! empty($request->input('logo_menu_base64')),
            'has_logo_footer'=> ! empty($request->input('logo_footer_base64')),
            'content_type'   => $request->header('Content-Type'),
        ]);

        try {
            $request->validate([
                'id_footer' => 'required|integer|exists:web_footer,id_footer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::error('[WebFooter::actualizar] VALIDATION FAILED', ['errors' => $ve->errors()]);
            throw $ve;
        }

        Log::info('[WebFooter::actualizar] VALIDACION OK, id_footer=' . $request->input('id_footer'));

        $id  = (int) $request->input('id_footer');
        $row = WebFooter::find($id);

        if (! $row) {
            Log::warning('[WebFooter::actualizar] REGISTRO NO ENCONTRADO id_footer=' . $id);
            return response()->json(['success' => false, 'message' => 'Registro no encontrado', 'result' => null], 404);
        }

        $paymentRows = \App\Support\FooterPaymentImages::prepare($request);
        $paymentPaths = [];
        DB::beginTransaction();

        try {
            $telefono     = $request->input('contacto_telefono');
            $telefonoNorm = $telefono !== null && $telefono !== ''
                ? str_replace(' ', '', trim((string) $telefono))
                : null;
            $telefonoSec  = $request->input('contacto_telefono_secundario');
            $telefonoSecNorm = $telefonoSec !== null && $telefonoSec !== ''
                ? str_replace(' ', '', trim((string) $telefonoSec))
                : null;

            $data = ['updated_at' => date('Y-m-d H:i:s')];
            $textFields = [
                'nuestros_horarios', 'sobre_la_empresa', 'descripcion_footer',
                'red_social_facebook', 'red_social_youtobe', 'red_social_twitter',
                'red_social_instagram', 'red_social_linkedin', 'red_social_tiktok',
                'contacto_direccion', 'contacto_email', 'url_mapa', 'url_whatsapp', 'whatsapp_mensaje',
                'footer_cta_subtitulo', 'footer_cta_titulo', 'Activo', 'rossety_footer_config',
                'titulo_sobre_nosotros', 'etiqueta_redes', 'titulo_llamanos',
                'titulo_escribenos', 'titulo_ubicacion', 'footer_telefonos',
                'footer_emails', 'texto_copyright', 'nav_footer',
                'links_tienda', 'links_legales', 'horarios_data',
                'metodos_pago', 'whatsapp_numero',
            ];
            foreach ($textFields as $field) {
                if ($request->has($field)) {
                    $data[$field] = $request->input($field);
                }
            }
            if ($paymentRows !== null) {
                $data['metodos_pago'] = \App\Support\FooterPaymentImages::store($paymentRows, $paymentPaths);
            }
            if ($request->has('footer_redes')) {
                $data['footer_redes'] = $this->encodeFooterRedes($request->input('footer_redes'));
            }
            if ($request->has('footer_telefonos')) {
                $lineas = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('footer_telefonos')))));
                if (count($lineas) > 0) {
                    $data['contacto_telefono'] = str_replace(' ', '', $lineas[0]);
                }
            }
            if ($request->has('footer_emails')) {
                $mails = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('footer_emails')))));
                if (count($mails) > 0 && ! $request->has('contacto_email')) {
                    $data['contacto_email'] = $mails[0];
                }
            }
            if ($request->has('contacto_telefono')) {
                $data['contacto_telefono'] = $telefonoNorm;
            }

            if ($request->has('whatsapp_mensaje') || $request->has('contacto_telefono') || $request->has('footer_telefonos')) {
                $merged = (object) array_merge($row->toArray(), $data);
                $data['url_whatsapp'] = \Helpers::whatsappUrl($merged);
            }
            if ($request->has('contacto_telefono_secundario')) {
                $data['contacto_telefono_secundario'] = $telefonoSecNorm;
            }

            // ── Logo menú (base64) ────────────────────────────────────────────
            $logoMenuB64 = $request->input('logo_menu_base64');
            if ($logoMenuB64) {
                Log::info('[WebFooter::actualizar] Procesando logo_menu base64');
                \App\Support\CmsImageStorage::deleteIfOwned($row->logo_menu);
                $saved = $this->saveBase64Image($logoMenuB64, 'storage_/footer', 'public_imagenes');
                if ($saved) {
                    $data['logo_menu'] = $saved;
                    Log::info('[WebFooter::actualizar] logo_menu guardado: ' . $saved);
                }
            }

            // ── Logo footer (base64) ──────────────────────────────────────────
            $logoFooterB64 = $request->input('logo_footer_base64');
            if ($logoFooterB64) {
                Log::info('[WebFooter::actualizar] Procesando logo_footer base64');
                \App\Support\CmsImageStorage::deleteIfOwned($row->logo_footer);
                $saved = $this->saveBase64Image($logoFooterB64, 'storage_/footer', 'public_imagenes');
                if ($saved) {
                    $data['logo_footer'] = $saved;
                    Log::info('[WebFooter::actualizar] logo_footer guardado: ' . $saved);
                }
            }

            WebFooter::where('id_footer', $id)->update($data);
            DB::commit();

            Log::info('[WebFooter::actualizar] ACTUALIZADO CORRECTAMENTE id_footer=' . $id);

            $fresh = WebFooter::find($id);

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado',
                'result'  => $fresh ? $this->footerPayloadWithLogoUrls($fresh->toArray()) : null,
            ]);

        } catch (\Exception $e) {
            foreach ($paymentPaths as $path) { \App\Support\CmsImageStorage::deleteIfOwned($path); }
            DB::rollBack();
            Log::error('[WebFooter::actualizar] EXCEPCION', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar', 'result' => null], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/web_footer/eliminar",
     *     tags={"Sitio Web"},
     *     summary="Eliminar configuración de footer",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Configuración eliminada", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id = $request->input('id');
        $result = WebFooter::where('id_footer',$id)->delete();
 
        return response()->json([
            'success' => true,
           'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }


    /**
     * @OA\Get(
     *     path="/web_footer/obtener_document",
     *     tags={"Sitio Web"},
     *     summary="Obtener documento legal del footer (Términos, etc.)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_footer_terminos", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Documento obtenido", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener_document(Request $request) : JsonResponse
    {
        $id = $request->id_footer_terminos;
        $result = DB::select('select * from web_terminos where id_footer_document = ?', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/web_footer/actualizar_document",
     *     tags={"Sitio Web"},
     *     summary="Actualizar documento legal del footer",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_footer_document","titulo","descripcion","Activo"},
     *         @OA\Property(property="id_footer_document", type="integer"),
     *         @OA\Property(property="titulo", type="string"),
     *         @OA\Property(property="descripcion", type="string"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Documento actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar_document(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->titulo);
        DB::beginTransaction();
        
        try {
            $id_footer_document = $request->input('id_footer_document');
          
            $result = DB::select('UPDATE web_terminos SET titulo = ?, descripcion =?, Activo = ? WHERE `id_footer_document` = ?', [
                $request->titulo,
                $request->descripcion,
                $request->Activo,
                $id_footer_document,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('stderr')->info($e);
            DB::rollback();
            // something went wrong
        }
       
        return response()->json([
            'success' => true,
            'message' => 'Actualizado correctamente',
            'result'  => $result
        ]);
    }

}
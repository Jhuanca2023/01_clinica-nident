<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReportePrecioPesoExport;
use App\Exports\ReporteProgramacionExport;
use App\Http\Controllers\Controller;
use App\Imports\PreciosPesoImport;
use App\Models\PrecioPeso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PrecioPesoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/precio_peso/listar",
     *     tags={"Configuración Envíos"},
     *     summary="Listar todos los rangos de precios por peso",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de rangos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_WEB_OFERTAS_GESTION_PRECIOS_PESO_LISTAR()');
        return response()->json(['success' => true, 'result' => $result]);
    }

    /**
     * @OA\Post(
     *     path="/precio_peso/crear",
     *     tags={"Configuración Envíos"},
     *     summary="Crear un nuevo rango de precio por peso",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"rango_min","rango_max","precio"},
     *         @OA\Property(property="rango_min", type="number"),
     *         @OA\Property(property="rango_max", type="number"),
     *         @OA\Property(property="precio", type="number"),
     *         @OA\Property(property="Activo", type="string"),
     *         @OA\Property(property="pago_contra_entrega", type="boolean"),
     *         @OA\Property(property="hora_regresiva", type="string"),
     *         @OA\Property(property="address_ubigueo", type="string")
     *     )),
     *     @OA\Response(response=200, description="Rango creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'rango_min' => 'required|numeric|min:0',
            'rango_max' => 'required|numeric|gt:rango_min',
            'precio' => 'required|numeric|min:0',
            'Activo' => 'nullable|string|max:255',
            // Nuevas reglas de validación para los campos adicionales (ajusta según sea necesario)
            'pago_contra_entrega' => 'nullable|boolean',
            'hora_regresiva' => 'nullable|string|max:255',
            'hora_regresiva_descripcion' => 'nullable|string|max:255',
            'paquete_medidas' => 'nullable|string|max:255',
            // 'paquete_dimencion' => 'nullable|numeric|min:0',
            'address_id_departamento' => 'nullable|string|max:255',
            'address_id_provincia' => 'nullable|string|max:255',
            'address_ubigueo' => 'nullable|string|max:255',
            'address_departamento' => 'nullable|string|max:255',
            'address_provincia' => 'nullable|string|max:255',
            'address_distrito' => 'nullable|string|max:255',
            'address_direccion' => 'nullable|string|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::select('CALL USP_WEB_OFERTAS_GESTION_PRECIOS_PESO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [ // Se agregó un '?'
            'INSERT',
            null, // p_id
            $request->rango_min,
            $request->rango_max,
            $request->precio,
            $request->Activo ?? true,
            // Nuevos campos
            $request->pago_contra_entrega ?? null,
            $request->hora_regresiva ?? null,
            $request->hora_regresiva_descripcion ?? null,
            $request->paquete_medidas ?? null,
            $request->paquete_dimencion ?? null,
            $request->address_id_departamento ?? null,
            $request->address_id_provincia ?? null,
            $request->address_ubigueo ?? null,
            $request->address_departamento ?? null,
            $request->address_provincia ?? null,
            $request->address_distrito ?? null,
            $request->address_direccion ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Rango de precio creado']);
    }

    /**
     * @OA\Put(
     *     path="/precio_peso/actualizar/{id}",
     *     tags={"Configuración Envíos"},
     *     summary="Actualizar un rango de precio por peso existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"rango_min","rango_max","precio"},
     *         @OA\Property(property="rango_min", type="number"),
     *         @OA\Property(property="rango_max", type="number"),
     *         @OA\Property(property="precio", type="number"),
     *         @OA\Property(property="Activo", type="string")
     *     )),
     *     @OA\Response(response=200, description="Rango actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'rango_min' => 'required|numeric|min:0',
            'rango_max' => 'required|numeric|gt:rango_min',
            'precio' => 'required|numeric|min:0',
            'Activo' => 'nullable|string|max:255',
            // Nuevas reglas de validación para los campos adicionales (ajusta según sea necesario)
            'pago_contra_entrega' => 'nullable|boolean',
            'hora_regresiva' => 'nullable|string|max:255',
            'hora_regresiva_descripcion' => 'nullable|string|max:255',
            'paquete_medidas' => 'nullable|string|max:255',
            // 'paquete_dimencion' => 'nullable|numeric|min:0',
            'address_id_departamento' => 'nullable|string|max:255',
            'address_id_provincia' => 'nullable|string|max:255',
            'address_ubigueo' => 'nullable|string|max:255',
            'address_departamento' => 'nullable|string|max:255',
            'address_provincia' => 'nullable|string|max:255',
            'address_distrito' => 'nullable|string|max:255',
            'address_direccion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }


        DB::select('CALL USP_WEB_OFERTAS_GESTION_PRECIOS_PESO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [ // Se agregó un '?'
            'UPDATE',
            $id,
            $request->rango_min,
            $request->rango_max,
            $request->precio,
            $request->Activo,
            // Nuevos campos
            $request->pago_contra_entrega ?? null,
            $request->hora_regresiva ?? null,
            $request->hora_regresiva_descripcion ?? null,
            $request->paquete_medidas ?? null,
            $request->paquete_dimencion ?? null,
            $request->address_id_departamento ?? null,
            $request->address_id_provincia ?? null,
            $request->address_ubigueo ?? null,
            $request->address_departamento ?? null,
            $request->address_provincia ?? null,
            $request->address_distrito ?? null,
            $request->address_direccion ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Rango de precio actualizado']);
    }

    /**
     * @OA\Delete(
     *     path="/precio_peso/eliminar/{id}",
     *     tags={"Configuración Envíos"},
     *     summary="Eliminar un rango de precio por peso",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Rango eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar($id)
    {
        // Para la operación DELETE, solo se necesitan 'p_accion' y 'p_id'.
        // Los demás parámetros se pueden pasar como null.

        DB::select('CALL USP_WEB_OFERTAS_GESTION_PRECIOS_PESO(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [ // Se agregó un '?'
            'DELETE',
            $id,
            null, // p_rango_min
            null, // p_rango_max
            null, // p_precio
            null, // p_activo
            null, // p_pago_contra_entrega
            null, // p_hora_regresiva
            null, // p_hora_regresiva_descripcion
            null, // p_paqueta_medidas
            null, // p_paquete_dimencion
            null, // p_address_id_departamento
            null, // p_address_id_provincia
            null, // p_address_ubigueo
            null, // p_address_departamento
            null, // p_address_provincia
            null, // p_address_distrito
            null  // p_address_direccion
        ]);

        return response()->json(['success' => true, 'message' => 'Rango de precio eliminado']);
    }

    /**
     * @OA\Get(
     *     path="/precio_peso/obtener",
     *     tags={"Configuración Envíos"},
     *     summary="Calcular precio basado en medidas y peso",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="paquete_medidas", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="paquete_dimencion", in="query", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="peso_kilogramo", in="query", required=true, @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Precio obtenido", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener(Request $request)
    {
        
        $paquete_medidas = $request->paquete_medidas;
        $paquete_dimencion = $request->paquete_dimencion ?? null;
        $peso_kilogramo = $request->peso_kilogramo;

        //  Log::channel('stderr')->info("test" . json_encode($paquete_medidas));
        //     Log::channel('stderr')->info("test" . json_encode($paquete_dimencion));
        //     Log::channel('stderr')->info("test" . json_encode($peso_kilogramo));

        $result = DB::select('CALL USP_WEB_OFERTAS_GESTION_PRECIOS_OBTENER(?,?,?)', [$paquete_medidas,$paquete_dimencion,$peso_kilogramo]);
      

       
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'data' => $result
        ]);
    }

    // ==========================  IMPORTAR EXCEL PRECIOS PESO ==========================
    /**
     * @OA\Post(
     *     path="/precio_peso/importar_excel",
     *     tags={"Configuración Envíos"},
     *     summary="Importar rangos de precios desde Excel",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"archivo_excel"},
     *                 @OA\Property(property="archivo_excel", type="string", format="binary"),
     *                 @OA\Property(property="truncate", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Importación exitosa", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function importar_excel(Request $request)
    {

        // Debug: verificar si llega el archivo
        // Log::channel('stderr')->info("Archivos recibidos: " . json_encode($_FILES));
        // Log::channel('stderr')->info("Request all: " . json_encode($request->all()));
        // Log::channel('stderr')->info("¿Tiene archivo?: " . ($request->hasFile('archivo_excel') ? 'Sí' : 'No'));

        // Validar que se haya enviado un archivo
        $validator = Validator::make($request->all(), [
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener el archivo
            $archivo = $request->file('archivo_excel');
            $truncate = $request->input('truncate', true); // Por defecto true para limpiar la tabla

            // Log::channel('stderr')->info("Archivo obtenido: " . $archivo->getClientOriginalName());

            // Importar los datos usando la clase de importación
            Excel::import(new PreciosPesoImport($truncate), $archivo);

            return response()->json([
                'success' => true,
                'message' => 'Datos importados correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/precio_peso/descargar_reporte_x_peso",
     *     tags={"Configuración Envíos"},
     *     summary="Descargar reporte de precios por peso (Excel/Base64)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Reporte generado con base64", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    protected function descargar_reporte_x_peso(Request $request)
    {
        $fileName = 'reportes/reporte_x_peso.xlsx';
        $FechaInicio =  null;
        $FechaFin = null;
        $Activo = null;
        // Usa la nueva clase de exportación con los parámetros correctos (los extras se ignoran en el constructor)
        Excel::store(new ReportePrecioPesoExport(
            $FechaInicio,
            $FechaFin,
            $Activo
            // Nota: id_programacion y diseno no se usan aquí; si los necesitas para filtrar, ajusta la consulta en collection()
        ), $fileName);

        $file = Storage::get($fileName);

        if ($file) {
            $fileLink = 'data:application/vnd.ms-excel;base64,' . base64_encode($file);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reporte de precios por peso exportado exitosamente.',
            'result' => [
                'fileName' => 'reporte_x_peso.xlsx',
                'fileBase64' => $fileLink,
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/precio_peso/listar_productos",
     *     tags={"Configuración Envíos"},
     *     summary="Listar productos con información de medidas/peso configurada",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de productos", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_productos()
    {
        try {
            $result = DB::table('administracion_producto')
                ->whereNotNull('paquete_medidas')
                // ->whereNotNull('paquete_dimencion')
                ->whereNotNull('peso_kilogramo')  // Ajusta el campo si es necesario
                ->get();  // O ->paginate(10) para paginación

            return response()->json([
                'success' => true,
                'result' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en listar_productos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al listar productos: ' . $e->getMessage()
            ], 500);
        }
    }
}

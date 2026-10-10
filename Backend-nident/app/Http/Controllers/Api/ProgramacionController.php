<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Programacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\ReporteProgramacionExport;
use Maatwebsite\Excel\Facades\Excel;

//use App\Exports\UsersExport;

class ProgramacionController extends Controller
{

    use Exportable;
    /**
     * @OA\Get(
     *     path="/programacion/obtener",
     *     tags={"Programación"},
     *     summary="Obtener registro de programación por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Registro encontrado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function obtener(Request $request) : JsonResponse
    {
        $id = $request->id;
        $result = DB::select('CALL USP_ADMINISTRACION_PROGRAMACION_OBTENER(?)', [$id]);
        return response()->json([
            'success' => true,
            'message' => 'Obtener registros!',
            'result' => $result
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/programacion/listar",
     *     tags={"Programación"},
     *     summary="Listar todos los registros de programación",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de programación", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_PROGRAMACION_LISTAR()');
        return response()->json([
            'success' => true,
            'message' => 'Listar registros',
            'result' => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/programacion/crear",
     *     tags={"Programación"},
     *     summary="Crear nuevo registro de programación",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_cliente","id_vendedor","fecha","Activo"},
     *         @OA\Property(property="id_cliente", type="integer", example=1),
     *         @OA\Property(property="id_vendedor", type="integer", example=1),
     *         @OA\Property(property="id_forma_pago", type="integer", example=1),
     *         @OA\Property(property="id_comprobante", type="integer", example=1),
     *         @OA\Property(property="id_diseno", type="integer", example=1),
     *         @OA\Property(property="fecha", type="string", format="date", example="2024-06-01"),
     *         @OA\Property(property="dia", type="string", example="Sábado"),
     *         @OA\Property(property="hora", type="string", example="08:00"),
     *         @OA\Property(property="v_guia", type="number", example=5.5),
     *         @OA\Property(property="v_real", type="number", example=5.2),
     *         @OA\Property(property="v_carga", type="number", example=5.0),
     *         @OA\Property(property="ubicacion", type="string"),
     *         @OA\Property(property="comentario", type="string"),
     *         @OA\Property(property="id_tipo_bomba", type="integer", example=1),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
     *     )),
     *     @OA\Response(response=200, description="Registro creado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function crear(Request $request) : JsonResponse
    {
        // Log::channel('stderr')->info($request->Activo);
        $result = DB::select('call USP_ADMINISTRACION_PROGRAMACION_CREAR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
            $request->id_cliente,
            $request->id_vendedor,
            $request->id_forma_pago,
            $request->id_comprobante,
            $request->id_diseno,

            $request->fecha,
            $request->dia,
            $request->hora,
            $request->v_guia,
            $request->v_real,

            $request->v_carga,
            $request->ubicacion,
            $request->comentario,
            $request->id_tipo_bomba,
            $request->Activo
        ]); 

        return response()->json([
            'success' => true,
            'message' => 'Registro insertado',
            'result' =>  $result
        ]);
    }

    /**
     * @OA\Put(
     *     path="/programacion/actualizar",
     *     tags={"Programación"},
     *     summary="Actualizar registro de programación existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_programacion","id_pedido"},
     *         @OA\Property(property="id_pedido", type="integer", example=1),
     *         @OA\Property(property="id_programacion", type="integer", example=1),
     *         @OA\Property(property="id_cliente", type="integer"),
     *         @OA\Property(property="id_vendedor", type="integer"),
     *         @OA\Property(property="Activo", type="string", enum={"S","N"})
     *     )),
     *     @OA\Response(response=200, description="Registro actualizado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function actualizar(Request $request) : JsonResponse
    {
         $result = DB::select('call USP_ADMINISTRACION_PROGRAMACION_ACTUALIZAR(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                    $request->id_pedido,
                    $request->id_programacion,

                    $request->id_cliente,
                    $request->id_vendedor,
                    $request->id_forma_pago,
                    $request->id_comprobante,
                    $request->id_diseno,

                    $request->fecha,
                    $request->dia,
                    $request->hora,
                    $request->v_guia,
                    $request->v_real,
                    
                    $request->v_carga,
                    $request->ubicacion,
                    $request->comentario,
                    $request->id_tipo_bomba,
                    $request->Activo
                ]); 
 
        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado',
            'result'  => $result
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/programacion/eliminar",
     *     tags={"Programación"},
     *     summary="Eliminar registro de programación",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id_pedido","id_programacion"},
     *         @OA\Property(property="id_pedido", type="integer"),
     *         @OA\Property(property="id_programacion", type="integer")
     *     )),
     *     @OA\Response(response=200, description="Registro eliminado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request) : JsonResponse
    {
        $id_pedido = $request->input('id_pedido');
        $id_programacion = $request->input('id_programacion');
        $result = DB::select('CALL USP_ADMINISTRACION_PROGRAMACION_ELIMINAR(?,?)', [$id_pedido,$id_programacion]);

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado!',
            'result'  => $result
        ]);
    }

    protected function descargar_reporte()
    {
        // $myFile = Excel::download(new ReporteProgramacionExport, 'reporte_programacion.xlsx');
        // $response = array(
        //     'fileName' => "jorge.xlsx", 
        //     'fileBase64' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($myFile), 
        // );

        // return response()->json($response);
        // $location = storage_path() .'/fresh_data_658954.xlsx';
        // return $location;
        $random = rand(100000, 999999);
        $fileName = 'fresh_data_'.$random.'.xlsx';
        //Excel::store(new ReporteProgramacionExport, $fileName, 'real_public');
         return response()->json(['file' => $fileName, 'path' => 'http://' . $_SERVER['HTTP_HOST'] .'/'. $fileName]);


        //  Excel::store(new ReporteProgramacionExport, 'exports/reporte_programacion.xlsx');
                 // $data = [
        //     'succes' => true,
        //     'path' => ['http://' . $_SERVER['HTTP_HOST'] . '/exports/reporte_programacion.xlsx']
        //   ];

        // return $data;
        //  $filename = 'list_users_' . time();
        // $builderview = $this->builderview($this->listar(), 'export');
        // return $this->BuilderExport($builderview, $filename, 'xlsx', 'exports');

    }

    
  protected function builderview($user_list_query, $type = '')
  {
    $posicion = 0;
    foreach ($user_list_query as $query) {
        if (is_array($query) || is_object($query))
        {
            foreach ($query as  $value) {
          
                if(isset($value[0]->RowIndex) && is_object($value[0]))
                    {
                        foreach ($value as $query3) {
                            $builderview[$posicion]['Id'] = $query3->id_pedido;
                            $builderview[$posicion]['First Name'] = $query3->nombre;
                            $posicion++;
                             
                          }
                    }
            }
        }
    }
    if (!isset($builderview)) {
      $builderview = [];
    }

    return $builderview;
  }

  
    protected function BuilderExport($array, string $namefile, $format, $location)
    {
            //return Excel::download(new UsersExport, 'users.xlsx');
        /*
        return  Excel::download('Customer Data', function($excel) use ($array){
            $excel->setTitle('Customer Data');
            $excel->sheet('Customer Data', function($sheet) use ($array){
            $sheet->fromArray($array, null, 'A1', false, false);
            });
        })->download('xlsx');
        */
    }

    /**
     * @OA\Get(
     *     path="/programacion/listar_filtrar",
     *     tags={"Programación"},
     *     summary="Listar registros de programación con filtros de fecha",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="FechaInicio", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="FechaFin", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="Activo", in="query", required=false, @OA\Schema(type="string", enum={"S","N"})),
     *     @OA\Response(response=200, description="Lista filtrada", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar_filtrar(Request $request)
    {
        // Log::channel('stderr')->info('test1=> '.$request->Activo);
        $result = DB::select('CALL USP_ADMINISTRACION_PROGRAMACION_LISTAR_FILTRAR(?,?,?);', [
            $request->FechaInicio,
            $request->FechaFin,
            $request->Activo
        ]);    
        return response()->json([
            'success' => true,
            'message' => 'Listar registros filtrados',
            'result' => $result
        ]);
    }

    /**
     * @OA\Get(
     *     path="/programacion/descargar_reporte_programacion",
     *     tags={"Programación"},
     *     summary="Descargar reporte de programación en Excel (Base64)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="FechaInicio", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="FechaFin", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="Activo", in="query", required=false, @OA\Schema(type="string", enum={"S","N"})),
     *     @OA\Response(response=200, description="Archivo excel generado")
     * )
     */
    protected function descargar_reporte_programacion(Request $request)
    {
        //Log::channel('stderr')->info('test2 => '.$request->Activo);
         //$random = rand(100000, 999999);
        //$fileName = 'reporte_programacion_'.$random.'.xlsx';

        //=========== SE COMENTO EN EL FILE SISTEMA EL PERSONALIZADO: 
        // config\filesystems.php  (real_public)
        /*
        $fileName = 'reporte_programacion.xlsx';
        Excel::store(new ReporteProgramacionExport($request->FechaInicio,$request->FechaFin,$request->Activo), $fileName, 'real_public');
        return response()->json(['file' => $fileName, 'path' => 'http://' . $_SERVER['HTTP_HOST'] .'/'. $fileName]);
        */
        
        $fileName = 'reportes/reporte_programacion.xlsx';
        Excel::store(new ReporteProgramacionExport($request->FechaInicio,$request->FechaFin,$request->Activo), $fileName);
        $file = Storage::get($fileName);

        if ($file) {
            $fileLink = 'data:application/vnd.ms-excel;base64,' . base64_encode($file);
            // @chmod(Storage::disk('local')->path($filename), 0755);
            // @unlink(Storage::disk('local')->path($filename));
            }
   
        return response()->json([
            'success' => true,
            'message' => 'Payroll are successfully exported.',
            'result' => [
                'fileName' => 'reporte_programacion.xlsx',
                'fileBase64' => $fileLink,
            ]
        ],200);

    }

}



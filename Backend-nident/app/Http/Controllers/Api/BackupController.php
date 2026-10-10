<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\BackupResource;
use App\Models\Backup;

class BackupController extends Controller
{

    /**
     * @OA\Get(
     *     path="/backup/listar",
     *     tags={"Backup"},
     *     summary="Listar todos los backups generados",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de backups", @OA\JsonContent(ref="#/components/schemas/PaginatedListResponse"))
     * )
     */
    public function listar(Request $request): JsonResponse
    {
        $result = Backup::orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Listar backup',
            'result' => $result
        ]);
    }

    /**
     * @OA\Post(
     *     path="/backup/generar",
     *     tags={"Backup"},
     *     summary="Generar un nuevo backup de la base de datos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Backup generado", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function generar(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $filename = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            $path = storage_path('app/backup/' . $filename);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }


            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbName = env('DB_DATABASE');
            $dbHost = env('DB_HOST', '127.0.0.1');

            $mysqldump = "mysqldump";


            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $commonPaths = [
                    'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
                    'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqldump.exe',
                    'C:\Program Files\MySQL\MySQL Workbench 8.0\mysqldump.exe',
                    'C:\xampp\mysql\bin\mysqldump.exe'
                ];

                foreach ($commonPaths as $cp) {
                    if (file_exists($cp)) {
                        $mysqldump = "\"{$cp}\"";
                        break;
                    }
                }
            }

            $command = "{$mysqldump} --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$path}\"";

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $command = "cmd /c " . $command;
            }

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("Error al ejecutar mysqldump. Código: $returnVar. Asegúrese de que mysqldump esté instalado y configurado.");
            }

            if (!file_exists($path) || filesize($path) === 0) {
                throw new \Exception("El archivo de backup se generó vacío. Verifique los permisos y la configuración de MySQL.");
            }

            $backup = Backup::create([
                'nombre_archivo' => $filename,
                'ruta' => $path,
                'tamaño' => file_exists($path) ? filesize($path) : 0,
                'tipo' => 'database',
                'id_usuario' => auth()->user()->id ?? null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Backup generado correctamente',
                'result' => $backup
            ]);

        } catch (\Exception $e) {
            Log::error('Error al generar backup: ' . $e->getMessage());
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al generar backup: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/backup/descargar",
     *     tags={"Backup"},
     *     summary="Descargar un archivo de backup por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response=200, description="Archivo de backup para descarga")
     * )
     */
    public function descargar(Request $request)
    {
        $id = $request->id;
        $backup = Backup::find($id);

        if (!$backup) {
            return response()->json(['success' => false, 'message' => 'Backup no encontrado'], 404);
        }

        if (!file_exists($backup->ruta)) {
            return response()->json(['success' => false, 'message' => 'Archivo no existe en el servidor'], 404);
        }

        return response()->download($backup->ruta, $backup->nombre_archivo);
    }

    /**
     * @OA\Delete(
     *     path="/backup/eliminar",
     *     tags={"Backup"},
     *     summary="Eliminar un archivo de backup del servidor",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"id"},
     *         @OA\Property(property="id", type="integer", example=1)
     *     )),
     *     @OA\Response(response=200, description="Backup eliminado correctamente", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function eliminar(Request $request): JsonResponse
    {
        $id = $request->input('id');

        $backup = Backup::find($id);

        if (!$backup) {
            return response()->json([
                'success' => false,
                'message' => 'Backup no encontrado'
            ], 404);
        }

        if (file_exists($backup->ruta)) {
            unlink($backup->ruta);
        }

        $backup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Backup eliminado correctamente'
        ]);
    }
}
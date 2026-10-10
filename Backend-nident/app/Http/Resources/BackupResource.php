<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BackupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_backup'       => $this->id_backup,
            'nombre_archivo'  => $this->nombre_archivo,
            'ruta'            => $this->ruta,
            'tamaño'          => $this->tamaño,
            'tipo'            => $this->tipo,
            'id_usuario'      => $this->id_usuario,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
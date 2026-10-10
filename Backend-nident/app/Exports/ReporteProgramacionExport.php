<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Collector\Collector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReporteProgramacionExport implements FromCollection, WithHeadings
{
    protected $FechaInicio;
    protected $FechaFin;
    protected $Activo;

 function __construct($FechaInicio, $FechaFin, $Activo) {
        $this->FechaInicio = $FechaInicio;
        $this->FechaFin = $FechaFin;
        $this->Activo = $Activo;
 }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $result = DB::select('CALL USP_ADMINISTRACION_PROGRAMACION_LISTAR_FILTRAR(?,?,?);', [
            $this->FechaInicio,
            $this->FechaFin,
            $this->Activo
        ]);
        
        $outgoingcollection = new Collector();
        foreach($result as $results)
        {
            // var_dump(get_object_vars($results)["RowIndex"]); 
            $outgoingcollection->push([
                $results->RowIndex,
                $results->id_programacion,
                $results->fecha,
                $results->hora,
                $results->dia,
                $results->ubicacion,
                $results->nombre,

                $results->v_guia,
                $results->v_real,
                $results->v_carga,

                $results->nombre_bomba,
                $results->comentario,
                $results->nombre_vendedor,
                $results->comprobante,
                //$results->nombre_cliente,
                $results->razon_social,
                $results->ruc,

            ]);
        }
        return  $outgoingcollection;
    }

   /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'N°',
            'Codigo',
            'Fecha',
            'Hora',
            'Dia',
            'Ubicacion',
            'Nombre',
            'V. Guia',
            'V. Real',
            'V. Carga',

            'Bomba',
            'Comentario',
            'Vendedor',
            'Comprobante',
            'Razon Social',
            'RUC',


        ];
    }
}

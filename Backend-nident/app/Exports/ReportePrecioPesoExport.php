<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Collector\Collector;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportePrecioPesoExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $FechaInicio;
    protected $FechaFin;
    protected $Activo;

    function __construct($FechaInicio = null, $FechaFin = null, $Activo = null)
    {
        $this->FechaInicio = $FechaInicio;
        $this->FechaFin = $FechaFin;
        $this->Activo = $Activo;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Construye la consulta base
        $query = 'SELECT * FROM oferta_precios_peso WHERE 1=1';

        // Filtros opcionales
        $params = [];
        // if ($this->FechaInicio) {
        //     $query .= ' AND created_at >= ?';
        //     $params[] = $this->FechaInicio;
        // }
        // if ($this->FechaFin) {
        //     $query .= ' AND created_at <= ?';
        //     $params[] = $this->FechaFin;
        // }
        // if ($this->Activo !== null) {
        //     $query .= ' AND Activo = ?';
        //     $params[] = $this->Activo;
        // }

        // Ejecuta la consulta (usa DB::select para obtener todos los campos como objetos)
        $result = DB::select($query, $params);

        $outgoingcollection = new Collector();
        foreach ($result as $row) {
            // Accede a todos los campos directamente (ya que SELECT * los trae todos), excluyendo timestamps para coincidir con el formato
            $outgoingcollection->push([
                $row->id_precio_peso,
                $row->rango_min,
                $row->rango_max,
                $row->precio,
                $row->pago_contra_entrega,
                $row->hora_regresiva,
                $row->hora_regresiva_descripcion,
                $row->paquete_medidas,
                // $row->paquete_dimencion,
                $row->address_departamento,
                $row->address_distrito,
                $row->Activo,
            ]);
        }

        return $outgoingcollection;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id_precio_peso',
            'rango_min',
            'rango_max',
            'precio',
            'pago_contra_entrega',
            'hora_regresiva',
            'hora_regresiva_descripcion',
            'paquete_medidas',
            // 'paquete_dimencion',
            'address_departamento',
            'address_distrito',
            'Activo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Aplicar estilo a la primera fila (headers): fondo amarillo, negrita
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'FFFF00', // Color amarillo
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Auto-ajustar ancho de columnas para las 12 columnas (A a L)
                for ($col = 'A'; $col <= 'L'; $col++) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BalanceVentasMensualExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $datos;
    protected $resumen;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($datos, $resumen, $fechaInicio, $fechaFin)
    {
        $this->datos = $datos;
        $this->resumen = $resumen;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $collection = collect();

        foreach (($this->datos ?? []) as $dato) {
            $collection->push([
                'fecha' => $dato->fecha ?? '-',
                'cantidad_pedidos' => (int)($dato->cantidad_pedidos ?? 0),
                'total_ventas' => number_format((float)($dato->total_ventas ?? 0), 2, '.', ''),
                'promedio' => number_format((float)($dato->promedio ?? 0), 2, '.', ''),
            ]);
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Fecha',
            'Cantidad de Pedidos',
            'Total Ventas (S/)',
            'Promedio por Venta (S/)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

            
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'REPORTE MENSUAL DE BALANCE DE VENTAS');
                $sheet->setCellValue('A2', 'Período: ' . $this->fechaInicio . ' al ' . $this->fechaFin);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Estilo para la cabecera (Fila 3)
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1A2038'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

               
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

              
                $sheet->getRowDimension(3)->setRowHeight(25);

               
                $lastRow = count($this->datos ?? []) + 4;
                $sheet->setCellValue('A' . $lastRow, 'RESUMEN');
                $sheet->setCellValue('B' . $lastRow, $this->resumen->total_pedidos ?? 0);
                $sheet->setCellValue('C' . $lastRow, number_format($this->resumen->total_ventas ?? 0, 2, '.', ''));
                $sheet->setCellValue('D' . $lastRow, number_format($this->resumen->promedio_venta ?? 0, 2, '.', ''));

                $sheet->getStyle('A' . $lastRow . ':D' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE0E0E0'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

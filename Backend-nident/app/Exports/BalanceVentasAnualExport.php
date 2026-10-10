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

class BalanceVentasAnualExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $datos;
    protected $resumen;
    protected $anio;
    protected $mejorMes;
    protected $peorMes;

    public function __construct($datos, $resumen, $anio, $mejorMes, $peorMes)
    {
        $this->datos = $datos;
        $this->resumen = $resumen;
        $this->anio = $anio;
        $this->mejorMes = $mejorMes;
        $this->peorMes = $peorMes;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $total = (float)($this->resumen->total_ventas ?? 0);
        $collection = collect();

        foreach (($this->datos ?? []) as $dato) {
            $totalVentasMes = (float)($dato->total_ventas ?? 0);
            $porcentaje = $total > 0 ? ($totalVentasMes / $total) * 100 : 0;

            $collection->push([
                'mes' => $dato->mes ?? '-',
                'cantidad_pedidos' => (int)($dato->cantidad_pedidos ?? 0),
                'total_ventas' => number_format($totalVentasMes, 2, '.', ''),
                'promedio' => number_format((float)($dato->promedio ?? 0), 2, '.', ''),
                '%_del_total' => number_format($porcentaje, 2, '.', '') . '%',
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
            'Mes',
            'Cantidad de Pedidos',
            'Total Ventas (S/)',
            'Promedio por Venta (S/)',
            '% del Total',
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

                // Autosize columnas (A-E)
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Título
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'REPORTE ANUAL DE BALANCE DE VENTAS');
                $sheet->setCellValue('A2', 'Año: ' . $this->anio);

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Estilo para la cabecera (Fila 3)
                $sheet->getStyle('A3:E3')->applyFromArray([
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

                
                $sheet->getRowDimension(3)->setRowHeight(25);

                
                $lastRow = count($this->datos ?? []) + 4;
                $sheet->setCellValue('A' . $lastRow, 'RESUMEN');
                $sheet->setCellValue('B' . $lastRow, $this->resumen->total_pedidos ?? 0);
                $sheet->setCellValue('C' . $lastRow, number_format($this->resumen->total_ventas ?? 0, 2, '.', ''));
                $sheet->setCellValue('D' . $lastRow, number_format($this->resumen->promedio_venta ?? 0, 2, '.', ''));

                $sheet->getStyle('A' . $lastRow . ':E' . $lastRow)->applyFromArray([
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

                
                $infoRow = $lastRow + 2;
                $sheet->setCellValue('A' . $infoRow, 'Mejor Mes:');
                $sheet->setCellValue('B' . $infoRow, $this->mejorMes ?? '-');
                $sheet->setCellValue('C' . $infoRow, 'Peor Mes:');
                $sheet->setCellValue('D' . $infoRow, $this->peorMes ?? '-');
            },
        ];
    }
}

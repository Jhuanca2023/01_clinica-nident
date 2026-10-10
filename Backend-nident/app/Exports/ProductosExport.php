<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Exporta productos con el formato de importación masiva.
 *
 * Columnas (16):
 *   codigo_producto | codigo_barra | nombre_producto | nombre_categoria | nombre_sub_categoria |
 *   descripcion | ubicacion | precio_venta | stock | precio_costo |
 *   stock_minimo | peso_kilogramos | imagen_principal | imagen_segundaria |
 *   precio_mayorista | precio_anterior
 */
class ProductosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $fechaInicio;
    protected $fechaFin;
    /** @var array<int, string> */
    protected array $fotosSecundariasPorProducto = [];

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;

        $this->fotosSecundariasPorProducto = DB::table('administracion_producto_fotos')
            ->select('id_producto', DB::raw('GROUP_CONCAT(url_imagen ORDER BY orden SEPARATOR \',\') as rutas'))
            ->groupBy('id_producto')
            ->pluck('rutas', 'id_producto')
            ->all();
    }

    public function query()
    {
        $query = Producto::query()
            ->with(['categoria', 'subcategoria'])
            ->orderBy('created_at', 'desc');

        if ($this->fechaInicio) {
            $query->whereDate('created_at', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->whereDate('created_at', '<=', $this->fechaFin);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'codigo_producto',      // → codigo_producto_new en BD
            'codigo_barra',
            'nombre_producto',      // → nombre en BD
            'nombre_categoria',     // → resuelto a id_producto_categoria
            'nombre_sub_categoria', // → resuelto a id_producto_categoria_sub
            'descripcion',
            'ubicacion',
            'precio_venta',         // → precio en BD
            'stock',
            'precio_costo',
            'stock_minimo',
            'peso_kilogramos',      // → peso_kilogramo en BD
            'imagen_principal',     // → url_imagen en BD
            'imagen_segundaria',    // → administracion_producto_fotos (rutas separadas por coma)
            'precio_mayorista',
            'precio_anterior',      // → precio_old en BD
        ];
    }

    public function map($row): array
    {
        return [
            $row->codigo_producto_new ?? '',
            $row->codigo_barra        ?? '',
            $row->nombre              ?? '',
            $row->categoria    ? $row->categoria->nombre    : '',
            $row->subcategoria ? $row->subcategoria->nombre : '',
            $row->descripcion  ?? '',
            $row->ubicacion    ?? '',
            $row->precio       !== null ? (float) $row->precio       : '',
            $row->stock        ?? '',
            $row->precio_costo !== null ? (float) $row->precio_costo : '',
            $row->stock_minimo !== null ? (float) $row->stock_minimo : '',
            $row->peso_kilogramo !== null ? (float) $row->peso_kilogramo : '',
            $row->url_imagen   ?? '',
            $this->fotosSecundariasPorProducto[$row->id_producto] ?? '',
            $row->precio_mayorista !== null ? (float) $row->precio_mayorista : '',
            $row->precio_old   !== null ? (float) $row->precio_old   : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A2038'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF000000'],
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

                // 16 columnas: A → P
                foreach (range('A', 'P') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}

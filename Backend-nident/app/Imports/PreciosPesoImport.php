<?php

namespace App\Imports;

use App\Models\PrecioPeso;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;

class PreciosPesoImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $truncateTable;

    /**
     * Constructor - acepta parámetro de truncate
     */
    public function __construct($truncate = false)
    {
        $this->truncateTable = $truncate;

        if ($this->truncateTable) {
            $this->truncateTableBeforeImport();
        }
    }

    /**
     * Truncar la tabla antes de importar
     */
    private function truncateTableBeforeImport()
    {
        try {
            // Log::channel('stderr')->info("Iniciando truncate de la tabla...");

            // Desactivar foreign key checks temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Truncar la tabla
            PrecioPeso::truncate();

            // Reactivar foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Log::channel('stderr')->info("Tabla truncada exitosamente");
        } catch (\Exception $e) {
            // Log::channel('stderr')->info("Error al truncar tabla: " . $e->getMessage());
            throw new \Exception("No se pudo limpiar la tabla: " . $e->getMessage());
        }
    }

    public function model(array $row)
    {
        // VERIFICAR SI LA FILA ESTÁ VACÍA
        if ($this->isRowEmpty($row)) {
            $this->skippedCount++;
            // Log::channel('stderr')->info("Fila vacía - omitida");
            return null;
        }

        // VERIFICAR DATOS MÍNIMOS REQUERIDOS
        if (
            empty($row['rango_min']) || empty($row['rango_max']) || empty($row['precio']) ||
            empty($row['address_departamento']) || empty($row['address_distrito'])
        ) {
            $this->skippedCount++;
            // Log::channel('stderr')->info("Fila omitida - Datos incompletos: " . json_encode($row));
            return null;
        }

        $this->importedCount++;
        // Log::channel('stderr')->info("Procesando fila {$this->importedCount}: " . json_encode($row));

        return new PrecioPeso([
            'rango_min' => $this->parseDecimal($row['rango_min']),
            'rango_max' => $this->parseDecimal($row['rango_max']),
            'precio' => $this->parseDecimal($row['precio']),
            'pago_contra_entrega' => $this->parsePagoContraEntrega($row['pago_contra_entrega'] ?? null),
            'hora_regresiva' => $this->parseHoraRegresiva($row['hora_regresiva'] ?? null),
            'hora_regresiva_descripcion' => $row['hora_regresiva_descripcion'] ?? $row['hora_regresiva_description'] ?? null,
            'paquete_medidas' => $row['paquete_medidas'] ?? $row['paquete medidas'] ?? null,
            // 'paquete_dimencion' => $this->parseDimension($row['paquete_dimencion'] ?? $row['paquete dimencion'] ?? null),
            'address_departamento' => $row['address_departamento'],
            'address_distrito' => $row['address_distrito'],
            'Activo' => $this->parseActivo($row['activo'] ?? $row['Activo'] ?? 'S'),
        ]);
    }

    /**
     * Verificar si la fila está vacía
     */
    private function isRowEmpty($row)
    {
        foreach ($row as $value) {
            if (!empty($value) && $value !== null && $value !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Reglas de validación SOLO para filas no vacías
     */
    public function rules(): array
    {
        return [
            'rango_min' => 'sometimes|required',
            'rango_max' => 'sometimes|required',
            'precio' => 'sometimes|required|numeric',
            'address_departamento' => 'sometimes|required',
            'address_distrito' => 'sometimes|required',
        ];
    }

    /**
     * Mensajes de validación
     */
    public function customValidationMessages()
    {
        return [
            'rango_min.required' => 'El peso mínimo es obligatorio',
            'rango_max.required' => 'El peso máximo es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser numérico',
            'address_departamento.required' => 'El departamento es obligatorio',
            'address_distrito.required' => 'El distrito es obligatorio',
        ];
    }

    /**
     * Parsear hora regresiva (manejar formato Excel y string)
     */
    private function parseHoraRegresiva($value)
    {
        if (empty($value)) return null;

        // Si es un número de Excel (fracción de día)
        if (is_numeric($value)) {
            $hours = $value * 24;
            $wholeHours = floor($hours);
            $minutes = ($hours - $wholeHours) * 60;
            $wholeMinutes = floor($minutes);
            $seconds = ($minutes - $wholeMinutes) * 60;

            return sprintf('%02d:%02d:%02d', $wholeHours, $wholeMinutes, round($seconds));
        }

        // Si ya es string de hora
        return $value;
    }

    /**
     * Parsear valores decimales
     */
    private function parseDecimal($value)
    {
        if (is_null($value)) return 0;

        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
            $value = preg_replace('/[^0-9.]/', '', $value);
        }

        return (float) $value;
    }

    /**
     * Parsear pago contra entrega
     */
    private function parsePagoContraEntrega($value)
    {
        if (is_null($value)) return '0';

        if (is_numeric($value)) {
            return $value == 1 ? '1' : '0';
        }

        $value = strtolower(trim($value));
        return in_array($value, ['si', 'sí', 'yes', 'true', '1']) ? '1' : '0';
    }

    /**
     * Parsear dimensión
     */
    private function parseDimension($value)
    {
        if (is_null($value)) return null;

        if (is_numeric($value)) {
            return (string) $value;
        }

        $value = strtolower(trim($value));
        if (str_contains($value, 'pequeño') || str_contains($value, 'pequeno')) return '1';
        if (str_contains($value, 'mediano')) return '2';
        if (str_contains($value, 'grande')) return '3';

        return null;
    }

    /**
     * Parsear activo
     */
    private function parseActivo($value)
    {
        if (is_null($value)) return 'S';

        if (is_numeric($value)) {
            return $value == 1 ? 'S' : 'N';
        }

        $value = strtoupper(trim($value));
        return in_array($value, ['S', 'SI', 'SÍ', 'YES', 'TRUE', '1']) ? 'S' : 'N';
    }

    /**
     * Obtener estadísticas
     */
    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors),
            'failures' => count($this->failures),
            'truncated' => $this->truncateTable
        ];
    }
}

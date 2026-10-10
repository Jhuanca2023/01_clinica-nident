<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige PK + AUTO_INCREMENT en tablas legacy (MySQL 1364 / 1075).
 */
final class DatabaseAutoIncrementFixer
{
    /** @var array<string, string> */
    public const TABLES = [
        // Etiquetas de menú
        'administracion_entidad_etiqueta' => 'id',
        'administracion_cliente'          => 'id_cliente',
        'administracion_cliente_direcciones' => 'id',
        'administracion_producto_fotos'      => 'id_producto_foto',
        'administracion_producto_ficha_tecnica' => 'id_ficha_tecnica',
    ];

    public static function fixAll(): void
    {
        foreach (self::TABLES as $table => $column) {
            self::fix($table, $column);
        }
    }

    public static function fix(string $table, string $column): bool
    {
        if (!Schema::hasTable($table) || Schema::getConnection()->getDriverName() !== 'mysql') {
            return false;
        }

        $cols = DB::select('
            SELECT COLUMN_NAME, COLUMN_KEY, EXTRA, COLUMN_TYPE, IS_NULLABLE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ', [$table]);

        $target = null;
        foreach ($cols as $c) {
            if ($c->COLUMN_NAME === $column) {
                $target = $c;
                break;
            }
        }

        if (!$target) {
            return false;
        }

        $targetExtra = strtolower((string) ($target->EXTRA ?? ''));
        if (str_contains($targetExtra, 'auto_increment') && ($target->COLUMN_KEY ?? '') === 'PRI') {
            return false;
        }

        $parts = [];

        // Quitar AUTO_INCREMENT de otras columnas (solo puede haber una por tabla).
        foreach ($cols as $c) {
            if ($c->COLUMN_NAME === $column) {
                continue;
            }
            if (!str_contains(strtolower((string) ($c->EXTRA ?? '')), 'auto_increment')) {
                continue;
            }
            $parts[] = self::modifyClause($c, false);
        }

        $pkCols = self::primaryKeyColumns($table);
        $targetIsPk = ($target->COLUMN_KEY ?? '') === 'PRI';

        if (!$targetIsPk) {
            if ($pkCols !== [] && $pkCols !== [$column]) {
                $parts[] = 'DROP PRIMARY KEY';
            }
            if ($pkCols !== [$column]) {
                $parts[] = "ADD PRIMARY KEY (`{$column}`)";
            }
        }

        $parts[] = self::modifyClause($target, true);

        DB::statement('ALTER TABLE `' . $table . '` ' . implode(', ', $parts));

        return true;
    }

    /** @return list<string> */
    private static function primaryKeyColumns(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        $cols    = [];
        foreach ($indexes as $idx) {
            $cols[(int) $idx->Seq_in_index] = $idx->Column_name;
        }
        ksort($cols);

        return array_values($cols);
    }

    private static function modifyClause(object $col, bool $autoIncrement): string
    {
        $type = self::sqlType($col);
        $null = ($col->IS_NULLABLE ?? '') === 'YES' && !$autoIncrement ? 'NULL' : 'NOT NULL';
        $ai   = $autoIncrement ? ' AUTO_INCREMENT' : '';

        return "MODIFY `{$col->COLUMN_NAME}` {$type} {$null}{$ai}";
    }

    private static function sqlType(object $col): string
    {
        $name = (string) $col->COLUMN_NAME;
        if (preg_match('/^id(_|$)/', $name)) {
            return 'BIGINT UNSIGNED';
        }

        return strtoupper((string) $col->COLUMN_TYPE);
    }
}

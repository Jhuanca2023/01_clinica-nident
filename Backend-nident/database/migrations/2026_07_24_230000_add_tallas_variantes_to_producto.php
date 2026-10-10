<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Precio y stock por talla (medida).
 * tallas_variantes: [{"talla":"S","precio":24.9,"stock":5}, ...]
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('administracion_producto')) {
            return;
        }

        if (! Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            Schema::table('administracion_producto', function (Blueprint $table) {
                $after = Schema::hasColumn('administracion_producto', 'tallas_disponibles')
                    ? 'tallas_disponibles'
                    : 'precio';
                $table->json('tallas_variantes')->nullable()->after($after);
            });
        }

        // Sembrar desde tallas_disponibles + precio/stock globales
        if (Schema::hasColumn('administracion_producto', 'tallas_disponibles')) {
            $rows = DB::table('administracion_producto')
                ->select('id_producto', 'tallas_disponibles', 'precio', 'stock', 'tallas_variantes')
                ->get();

            foreach ($rows as $row) {
                if (! empty($row->tallas_variantes)) {
                    continue;
                }
                $raw = trim((string) ($row->tallas_disponibles ?? ''));
                if ($raw === '') {
                    $raw = 'S,M,L,XL,XXL';
                }
                $tallas = array_values(array_filter(array_map('trim', explode(',', $raw))));
                if (empty($tallas)) {
                    continue;
                }
                $precio = (float) ($row->precio ?? 0);
                $stockTotal = max(0, (int) ($row->stock ?? 0));
                $n = count($tallas);
                $base = intdiv($stockTotal, $n);
                $resto = $stockTotal % $n;
                $variantes = [];
                foreach ($tallas as $i => $t) {
                    $variantes[] = [
                        'talla'  => $t,
                        'precio' => round($precio, 2),
                        'stock'  => $base + ($i < $resto ? 1 : 0),
                    ];
                }
                DB::table('administracion_producto')
                    ->where('id_producto', $row->id_producto)
                    ->update(['tallas_variantes' => json_encode($variantes, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('administracion_producto')
            && Schema::hasColumn('administracion_producto', 'tallas_variantes')) {
            Schema::table('administracion_producto', function (Blueprint $table) {
                $table->dropColumn('tallas_variantes');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('web_promo_multiplicador')) {
            Schema::create('web_promo_multiplicador', function (Blueprint $table) {
                $table->increments('id_promo_multiplicador');
                $table->string('etiqueta', 40);
                $table->unsignedInteger('orden')->default(0);
                $table->char('Activo', 1)->default('S');
                $table->unique('etiqueta', 'uk_web_promo_multiplicador_etiqueta');
            });
        }

        $defaults = [
            ['etiqueta' => 'HASTA', 'orden' => 1],
            ['etiqueta' => '2X', 'orden' => 2],
            ['etiqueta' => '3X', 'orden' => 3],
            ['etiqueta' => 'DESDE', 'orden' => 4],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('web_promo_multiplicador')
                ->where('etiqueta', $row['etiqueta'])
                ->exists();
            if (! $exists) {
                DB::table('web_promo_multiplicador')->insert([
                    'etiqueta' => $row['etiqueta'],
                    'orden' => $row['orden'],
                    'Activo' => 'S',
                ]);
            }
        }

        // Incorporar etiquetas ya usadas en ítems (p. ej. 4X personalizado)
        if (Schema::hasTable('web_promo_item')) {
            $usados = DB::table('web_promo_item')
                ->whereNotNull('multiplicador')
                ->where('multiplicador', '!=', '')
                ->distinct()
                ->pluck('multiplicador');

            $maxOrden = (int) DB::table('web_promo_multiplicador')->max('orden');
            foreach ($usados as $etiqueta) {
                $etiqueta = trim((string) $etiqueta);
                if ($etiqueta === '') {
                    continue;
                }
                $exists = DB::table('web_promo_multiplicador')
                    ->where('etiqueta', $etiqueta)
                    ->exists();
                if (! $exists) {
                    $maxOrden++;
                    DB::table('web_promo_multiplicador')->insert([
                        'etiqueta' => $etiqueta,
                        'orden' => $maxOrden,
                        'Activo' => 'S',
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('web_promo_multiplicador');
    }
};

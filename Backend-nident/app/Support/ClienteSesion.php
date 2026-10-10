<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Resuelve administracion_cliente.id_cliente a partir del usuario autenticado.
 * Mis Compras (USP_WEB_OPE_PEDIDO_LISTAR) filtra por id_cliente, no por users.id.
 */
class ClienteSesion
{
    /**
     * @return int|null id_cliente de administracion_cliente, o null si es invitado
     */
    public static function idCliente(): ?int
    {
        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $cliente = DB::table('administracion_cliente')
            ->select('id_cliente')
            ->where('id_usuario', $user->id)
            ->first();

        if ($cliente) {
            return (int) $cliente->id_cliente;
        }

        $payload = [
            'email' => $user->email ?? '',
            'id_usuario' => $user->id,
            'Activo' => 'S',
        ];

        if (Schema::hasColumn('administracion_cliente', 'created_at')) {
            $payload['created_at'] = now();
            $payload['updated_at'] = now();
        }

        return (int) DB::table('administracion_cliente')->insertGetId($payload);
    }

    /**
     * Datos de contacto del usuario logueado para cabecera de pedido.
     *
     * @return array{nombre:string,apellido:string,email:string,telefono:string}
     */
    public static function datosContacto(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'nombre' => 'Cliente WhatsApp',
                'apellido' => '',
                'email' => '',
                'telefono' => '',
            ];
        }

        $name = trim((string) ($user->name ?? ''));
        $parts = preg_split('/\s+/u', $name, 2) ?: [];
        $nombre = $parts[0] ?? 'Cliente';
        $apellido = $parts[1] ?? '';

        $cliente = DB::table('administracion_cliente')
            ->where('id_usuario', $user->id)
            ->first();

        return [
            'nombre' => $nombre !== '' ? $nombre : 'Cliente',
            'apellido' => $apellido,
            'email' => (string) ($user->email ?? $cliente->email ?? ''),
            'telefono' => (string) ($cliente->telefono ?? $user->telefono ?? ''),
        ];
    }

    /**
     * Corrige pedidos WhatsApp donde se guardó users.id en id_cliente por error.
     *
     * @return int filas actualizadas
     */
    public static function repararPedidosWhatsappMalVinculados(): int
    {
        $rows = DB::table('ope_pedidos as OP')
            ->join('administracion_cliente as AC', 'AC.id_usuario', '=', 'OP.id_cliente')
            ->leftJoin('administracion_cliente as AC_OK', 'AC_OK.id_cliente', '=', 'OP.id_cliente')
            ->whereNull('AC_OK.id_cliente')
            ->where(function ($q) {
                $q->where('OP.radio_tipo_pago', 'whatsapp')
                    ->orWhere('OP.metodo_pago', 'WhatsApp');
            })
            ->select('OP.id_pedido', 'AC.id_cliente as id_cliente_real')
            ->get();

        $n = 0;
        foreach ($rows as $row) {
            $updated = DB::table('ope_pedidos')
                ->where('id_pedido', $row->id_pedido)
                ->update([
                    'id_cliente' => $row->id_cliente_real,
                    'updated_at' => now(),
                ]);
            $n += (int) $updated;
        }

        return $n;
    }
}

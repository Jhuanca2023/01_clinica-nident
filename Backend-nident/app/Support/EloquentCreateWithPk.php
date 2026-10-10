<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Crea registros cuando la PK no tiene AUTO_INCREMENT en MySQL (error 1364).
 * La solución definitiva es ejecutar: php artisan db:fix-autoincrement
 */
final class EloquentCreateWithPk
{
    /**
     * @param  class-string<Model>|Model  $model
     */
    public static function create(string|Model $model, array $attributes = []): Model
    {
        $instance = is_string($model) ? new $model() : $model;

        if ($attributes !== []) {
            $instance->fill($attributes);
        }

        try {
            $instance->save();

            return $instance;
        } catch (QueryException $e) {
            if (!self::isMissingPkDefaultError($e)) {
                throw $e;
            }
            $lastError = $e;
        }

        $pk = $instance->getKeyName();
        if (!$pk || !$instance->getIncrementing()) {
            throw $lastError ?? new QueryException('mysql', '', [], new \RuntimeException('PK no incremental'));
        }

        $table = $instance->getTable();
        $next  = (int) DB::table($table)->max($pk) + 1;

        $instance->setAttribute($pk, $next);
        $instance->exists = false;
        $instance->save();

        return $instance;
    }

    public static function isMissingPkDefaultError(QueryException $e): bool
    {
        if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1364) {
            return true;
        }

        return str_contains($e->getMessage(), "doesn't have a default value");
    }
}

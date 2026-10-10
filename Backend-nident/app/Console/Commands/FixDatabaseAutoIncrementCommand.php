<?php

namespace App\Console\Commands;

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class FixDatabaseAutoIncrementCommand extends Command
{
    protected $signature = 'db:fix-autoincrement {--table= : Solo una tabla}';

    protected $description = 'Corrige PK + AUTO_INCREMENT (errores MySQL 1364 y 1075)';

    public function handle(): int
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            $this->warn('Solo aplica a MySQL.');

            return self::SUCCESS;
        }

        $only = $this->option('table');

        if ($only) {
            $column = DatabaseAutoIncrementFixer::TABLES[$only] ?? null;
            if (!$column) {
                $this->error("Tabla desconocida: {$only}");

                return self::FAILURE;
            }
            $this->runOne($only, $column);

            return self::SUCCESS;
        }

        foreach (DatabaseAutoIncrementFixer::TABLES as $table => $column) {
            $this->runOne($table, $column);
        }

        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }

    private function runOne(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) {
            $this->line("Omitida: {$table}");

            return;
        }

        try {
            if (DatabaseAutoIncrementFixer::fix($table, $column)) {
                $this->info("OK: {$table}.{$column}");
            } else {
                $this->line("Ya correcta: {$table}.{$column}");
            }
        } catch (\Throwable $e) {
            $this->error("{$table}: {$e->getMessage()}");
        }
    }
}

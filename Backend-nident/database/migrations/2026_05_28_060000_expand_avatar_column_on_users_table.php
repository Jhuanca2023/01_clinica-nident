<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'avatar')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `avatar` TEXT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "users" ALTER COLUMN "avatar" TYPE TEXT');
            DB::statement('ALTER TABLE "users" ALTER COLUMN "avatar" DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'avatar')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `avatar` VARCHAR(255) NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "users" ALTER COLUMN "avatar" TYPE VARCHAR(255)');
        }
    }
};

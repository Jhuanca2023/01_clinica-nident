<?php

namespace App\Providers;

use App\View\Composers\CustomizerComposer;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($publicPath = config('app.public_path')) {
            $this->app->usePublicPath($publicPath);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMysqlSessionTimezone();

        // Inyecta isAdmin y customizerStyles en el layout base de todas las páginas web.
        // El composer se ejecuta una sola vez por request, al renderizar web.base.
        View::composer('web.base', CustomizerComposer::class);
    }

    /**
     * Alinea la sesión MySQL con la zona horaria de la app (p. ej. America/Lima en hosting UTC).
     * Se ejecuta en cada conexión nueva (request, colas, reconexiones).
     */
    private function registerMysqlSessionTimezone(): void
    {
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            if ($event->connection->getDriverName() !== 'mysql') {
                return;
            }

            $timezone = config('database.connections.mysql.timezone');
            if (!$timezone) {
                return;
            }

            try {
                $event->connection->statement('SET time_zone = ?', [$timezone]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        if (config('database.default') === 'mysql') {
            try {
                $timezone = config('database.connections.mysql.timezone');
                if ($timezone) {
                    DB::statement('SET time_zone = ?', [$timezone]);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}

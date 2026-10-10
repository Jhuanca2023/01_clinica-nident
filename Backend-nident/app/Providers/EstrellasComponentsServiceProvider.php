<?php

namespace App\Providers;

use App\Http\Controllers\Web\Components\StarRating;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class EstrellasComponentsServiceProvider extends ServiceProvider
{
     /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registra el componente con el alias 'star-rating'
        Blade::component('star-rating', StarRating::class);
    }
}

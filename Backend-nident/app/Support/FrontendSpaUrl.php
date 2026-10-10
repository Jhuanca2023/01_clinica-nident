<?php

namespace App\Support;

/**
 * URL base del SPA React (VITE_BASE_PATH, normalmente /admin).
 */
class FrontendSpaUrl
{
    public static function base(): string
    {
        $base = env('FRONTEND_URL');
        if (!$base) {
            $frond = rtrim((string) env('APP_URL_FROND', config('app.url', 'http://localhost:8000')), '/');
            $path  = rtrim((string) env('FRONTEND_OAUTH_PATH', '/admin'), '/');
            $base  = ($path && str_contains($frond, $path)) ? $frond : $frond . $path;
        }

        $spaPath = rtrim((string) env('FRONTEND_OAUTH_PATH', '/admin'), '/');
        if ($spaPath && !str_contains($base, $spaPath)) {
            $base = rtrim($base, '/') . $spaPath;
        }

        return rtrim($base, '/');
    }

    public static function to(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return $path ? self::base() . '/' . $path : self::base();
    }

    public static function onboarding(string $token): string
    {
        return self::to('onboarding/' . $token);
    }

    public static function onboardingReporte(string $token): string
    {
        return self::to('onboarding/reporte/' . $token);
    }
}

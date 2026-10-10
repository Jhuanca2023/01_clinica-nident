<?php

namespace App\Services\Auth;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;

class AuthTokenService
{
    public function emitirRespuestaLogin(
        User $user,
        bool $remember = true,
        ?string $ip = null,
        ?string $trustedDeviceToken = null
    ): array {
        return app(AuthTwoFactorService::class)->respuestaLoginOChallenge(
            $user,
            $remember,
            $ip,
            $trustedDeviceToken
        );
    }
}

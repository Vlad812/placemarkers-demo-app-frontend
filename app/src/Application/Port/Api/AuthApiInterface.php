<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\Auth\AuthMessageResponse;
use App\Application\DTO\Api\Auth\AuthTokenResponse;
use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\AuthLoginPayload;
use App\Application\DTO\Api\Payload\AuthRefreshPayload;
use App\Application\DTO\Api\Payload\AuthRequestPasswordResetPayload;
use App\Application\DTO\Api\Payload\AuthResetPasswordPayload;
use App\Application\DTO\Api\Payload\AuthSignupPayload;

interface AuthApiInterface
{
    public function login(AuthLoginPayload $payload): AuthTokenResponse;

    public function signup(AuthSignupPayload $payload): AuthMessageResponse;

    public function refresh(AuthRefreshPayload $payload): AuthTokenResponse;

    public function logout(string $accessToken, ?string $refreshToken = null): HttpApiResult;

    public function confirmEmail(string $token): AuthMessageResponse;

    public function requestPasswordReset(AuthRequestPasswordResetPayload $payload): AuthMessageResponse;

    public function resetPassword(AuthResetPasswordPayload $payload): AuthMessageResponse;
}

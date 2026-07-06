<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\Auth\AuthMessageResponse;
use App\Application\DTO\Api\Auth\AuthTokenResponse;
use App\Application\DTO\Api\HttpApiResult;

interface AuthApiInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function login(array $data): AuthTokenResponse;

    /**
     * @param array<string, mixed> $data
     */
    public function signup(array $data): AuthMessageResponse;

    /**
     * @param array<string, mixed> $data
     */
    public function refresh(array $data): AuthTokenResponse;

    public function logout(string $accessToken, ?string $refreshToken = null): HttpApiResult;

    public function confirmEmail(string $token): AuthMessageResponse;

    /**
     * @param array<string, mixed> $data
     */
    public function requestPasswordReset(array $data): AuthMessageResponse;

    /**
     * @param array<string, mixed> $data
     */
    public function resetPassword(array $data): AuthMessageResponse;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

interface AuthSessionStorageInterface
{
    public function store(string $accessToken, string $refreshToken): void;

    public function getAccessToken(): ?string;

    public function getRefreshToken(): ?string;

    public function clear(): void;

    public function invalidate(): void;
}

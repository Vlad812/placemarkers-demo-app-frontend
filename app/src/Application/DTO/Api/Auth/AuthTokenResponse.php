<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Auth;

final readonly class AuthTokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
        public int $refreshExpiresIn,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            refreshToken: (string) ($data['refresh_token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 3600),
            refreshExpiresIn: (int) ($data['refresh_expires_in'] ?? 2592000),
        );
    }
}

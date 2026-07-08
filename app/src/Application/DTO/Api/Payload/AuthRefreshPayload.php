<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

final readonly class AuthRefreshPayload
{
    public function __construct(
        public string $refreshToken,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'refresh_token' => $this->refreshToken,
        ];
    }
}

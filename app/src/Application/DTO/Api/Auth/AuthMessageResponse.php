<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Auth;

final readonly class AuthMessageResponse
{
    public function __construct(
        public string $message,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $fallback = ''): self
    {
        $message = $data['message'] ?? $fallback;

        return new self(is_string($message) && $message !== '' ? $message : $fallback);
    }
}

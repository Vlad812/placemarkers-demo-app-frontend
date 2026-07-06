<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\Cookie;

final readonly class CookieData
{
    public function __construct(
        public string $name,
        public string $value,
        public int $expires = 0,
        public string $path = '/',
        public ?string $domain = null,
        public bool $secure = false,
        public bool $httpOnly = true,
        public bool $raw = false,
        public ?string $sameSite = Cookie::SAMESITE_LAX,
    ) {
    }

    /**
     * @param array<string, mixed> $cookie
     */
    public static function fromArray(array $cookie): self
    {
        return new self(
            name: (string) ($cookie['name'] ?? ''),
            value: (string) ($cookie['value'] ?? ''),
            expires: (int) ($cookie['expires'] ?? 0),
            path: (string) ($cookie['path'] ?? '/'),
            domain: $cookie['domain'] ?? null,
            secure: (bool) ($cookie['secure'] ?? false),
            httpOnly: (bool) ($cookie['httpOnly'] ?? true),
            raw: (bool) ($cookie['raw'] ?? false),
            sameSite: $cookie['sameSite'] ?? Cookie::SAMESITE_LAX,
        );
    }
}

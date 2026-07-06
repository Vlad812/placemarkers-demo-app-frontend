<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\Response;

final readonly class RedirectPageResponse implements ResponsePayloadInterface
{
    /**
     * @param array<string, mixed> $routeParams
     * @param list<CookieData> $cookies
     * @param list<CookieData> $clearCookies
     */
    public function __construct(
        public string $route,
        public array $routeParams = [],
        public array $cookies = [],
        public array $clearCookies = [],
        public int $statusCode = Response::HTTP_FOUND,
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): array|null
    {
        return null;
    }
}

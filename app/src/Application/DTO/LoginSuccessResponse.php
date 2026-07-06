<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\Response;

final readonly class LoginSuccessResponse implements ResponsePayloadInterface
{
    public function __construct(
        public string $route,
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
        public int $refreshExpiresIn,
        public array $routeParams = [],
    ) {
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FOUND;
    }

    public function getBody(): array|null
    {
        return null;
    }
}

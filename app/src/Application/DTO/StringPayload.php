<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\Response;

final readonly class StringPayload implements ResponsePayloadInterface
{
    public function __construct(
        public string $content,
        public int $statusCode = Response::HTTP_OK,
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

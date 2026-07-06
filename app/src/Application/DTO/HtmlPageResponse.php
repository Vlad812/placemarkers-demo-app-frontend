<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\Response;

final readonly class HtmlPageResponse implements ResponsePayloadInterface
{
    /**
     * @param array<string, mixed> $context
     * @param array<string, string> $headers
     */
    public function __construct(
        public array $context = [],
        public int $statusCode = Response::HTTP_OK,
        public array $headers = [],
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): array|null
    {
        return $this->context;
    }
}

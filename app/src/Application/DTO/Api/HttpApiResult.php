<?php

declare(strict_types=1);

namespace App\Application\DTO\Api;

final readonly class HttpApiResult
{
    /**
     * @param array<string, mixed>|list<mixed> $body
     */
    public function __construct(
        public int $status,
        public array $body,
    ) {
    }

    /**
     * @return array{status: int, body: array<string, mixed>|list<mixed>}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'body' => $this->body,
        ];
    }
}

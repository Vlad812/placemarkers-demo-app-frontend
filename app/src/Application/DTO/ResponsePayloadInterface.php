<?php

declare(strict_types=1);

namespace App\Application\DTO;

interface ResponsePayloadInterface
{
    public function getStatusCode(): int;

    /**
     * @return array<string, mixed>|list<mixed>|null
     */
    public function getBody(): array|null;
}

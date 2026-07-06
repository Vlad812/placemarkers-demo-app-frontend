<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class ErrorResponse implements ResponsePayloadInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $message,
        public int $statusCode,
        public ?string $incidentId = null,
        public array $context = [],
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): array|null
    {
        $body = ['message' => $this->message];

        if ($this->incidentId !== null) {
            $body['incident_id'] = $this->incidentId;
        }

        return $body;
    }
}
